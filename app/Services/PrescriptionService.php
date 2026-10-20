<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PrescriptionService
{
    public const DISK = 'prescriptions'; // private disk (storage/app/private/prescriptions), no public URL

    /**
     * Store the upload on the private disk under a random name.
     * The original name is kept only as a label.
     */
    public function create(User $customer, UploadedFile $file, array $meta, User $uploader): Prescription
    {
        $ext = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'application/pdf' => 'pdf',
            default => throw new RuntimeException('Unsupported file type.'),
        };

        $path = $file->storeAs(date('Y/m'), Str::random(40) . '.' . $ext, self::DISK);

        return DB::transaction(function () use ($customer, $file, $meta, $uploader, $path) {
            $rx = Prescription::create([
                'reference'     => Prescription::newReference(),
                'customer_id'   => $customer->id,
                'uploaded_by'   => $uploader->id,
                'file_path'     => $path,
                'original_name' => Str::limit(basename($file->getClientOriginalName()), 180, ''),
                'mime_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
                'doctor_name'   => $meta['doctor_name'] ?? null,
                'prescribed_on' => $meta['prescribed_on'] ?? null,
                'staff_notes'   => $meta['staff_notes'] ?? null,
                'status'        => 'pending',
            ]);

            $rx->log('uploaded', "Uploaded for customer #{$customer->id} ({$customer->email})");

            // Optional OCR. The result is only ever a suggestion.
            $suggested = $this->extractor()?->extract(Storage::disk(self::DISK)->path($path), $rx->mime_type) ?? [];
            foreach ($suggested as $row) {
                $rx->items()->create([
                    'written_name'     => Str::limit($row['name'], 190, ''),
                    'written_strength' => $row['strength'] ?? null,
                    'written_form'     => $row['form'] ?? null,
                    'prescribed_qty'   => $row['quantity'] ?? null,
                ]);
            }
            if ($suggested) {
                $rx->update(['extraction_status' => 'suggested']);
                $rx->log('ocr_suggested', count($suggested) . ' unverified line(s) suggested by OCR');
            }

            return $rx;
        });
    }

    /**
     * OCR is not configured by default. To enable it, bind a class implementing
     * App\Services\Contracts\PrescriptionExtractor and set PRESCRIPTION_OCR_DRIVER.
     * Until then staff enter medicines by hand, which is fully supported.
     */
    private function extractor(): ?Contracts\PrescriptionExtractor
    {
        $class = config('services.prescription_ocr.class');
        return $class && class_exists($class) ? app($class) : null;
    }

    public function ocrEnabled(): bool
    {
        return $this->extractor() !== null;
    }

    /**
     * Possible catalogue matches for a written line. Never auto-selects:
     * the confidence label is shown so staff can see uncertainty.
     *
     * @return \Illuminate\Support\Collection<int,object{product:Product,confidence:string}>
     */
    public function matches(PrescriptionItem $item, int $limit = 6)
    {
        $words = collect(preg_split('/[\s,\/\-]+/', strtolower($item->written_name)))
            ->filter(fn ($w) => mb_strlen($w) >= 3)->take(4)->values();

        if ($words->isEmpty()) {
            return collect();
        }

        $query = Product::where('is_active', true)->with('category');
        $query->where(function ($q) use ($words) {
            foreach ($words as $w) {
                $q->orWhere('name', 'like', "%{$w}%")->orWhere('composition', 'like', "%{$w}%");
            }
        });

        $strength = $this->normalise($item->written_strength);

        return $query->limit(30)->get()->map(function (Product $p) use ($item, $words, $strength) {
            $haystack = strtolower($p->name . ' ' . $p->composition);
            $nameHit = $words->filter(fn ($w) => str_contains($haystack, $w))->count() === $words->count();

            if ($strength !== '') {
                $has = $this->normalise($p->dosage . ' ' . $p->name . ' ' . $p->composition);
                $confidence = str_contains($has, $strength) ? ($nameHit ? 'Name and strength match' : 'Strength matches')
                                                              : 'Strength differs or not listed';
            } else {
                $confidence = $nameHit ? 'Name matches (strength not given)' : 'Partial name match';
            }

            return (object) ['product' => $p, 'confidence' => $confidence, 'score' => str_starts_with($confidence, 'Name and') ? 3 : ($nameHit ? 2 : 1)];
        })->sortByDesc('score')->take($limit)->values();
    }

    private function normalise(?string $s): string
    {
        return preg_replace('/\s+/', '', strtolower((string) $s));
    }

    public function validityDays(): int
    {
        return max(1, (int) (Setting::get('prescription_validity_days') ?: 30));
    }

    /**
     * Verify a pending prescription after every line has been checked.
     * Throws with a readable message if anything is unsafe.
     */
    public function verify(Prescription $rx, User $reviewer): Prescription
    {
        return DB::transaction(function () use ($rx, $reviewer) {
            $rx = Prescription::whereKey($rx->id)->lockForUpdate()->firstOrFail();
            if ($rx->status !== 'pending') {
                throw new RuntimeException('This prescription has already been reviewed.');
            }

            $items = $rx->items()->get();
            if ($items->isEmpty()) {
                throw new RuntimeException('Add at least one medicine before verifying.');
            }

            foreach ($items as $item) {
                $product = $item->product_id ? Product::find($item->product_id) : null;
                if (! $product || ! $product->is_active) {
                    throw new RuntimeException("Choose an available product for \"{$item->written_name}\".");
                }
                if ($item->approved_qty < 1) {
                    throw new RuntimeException("Set an approved quantity for \"{$item->written_name}\".");
                }
                if ($item->prescribed_qty && $item->approved_qty > $item->prescribed_qty) {
                    throw new RuntimeException("Approved quantity for \"{$item->written_name}\" exceeds the prescribed quantity.");
                }
                if ($item->approved_qty > $product->stock) {
                    throw new RuntimeException("Only {$product->stock} of {$product->name} in stock; reduce the approved quantity.");
                }
            }

            $rx->items()->update(['is_verified' => true]);
            $rx->update([
                'status'      => 'verified',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'expires_at'  => now()->addDays($this->validityDays()),
            ]);
            $rx->log('verified', 'Verified by ' . $reviewer->name . ' (' . $items->count() . ' item(s))');

            return $rx;
        });
    }

    public function reject(Prescription $rx, User $reviewer, string $reason): void
    {
        DB::transaction(function () use ($rx, $reviewer, $reason) {
            $rx = Prescription::whereKey($rx->id)->lockForUpdate()->firstOrFail();
            if ($rx->status !== 'pending') {
                throw new RuntimeException('This prescription has already been reviewed.');
            }
            $rx->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'reviewed_by'      => $reviewer->id,
                'reviewed_at'      => now(),
            ]);
            $rx->log('rejected', $reason);
        });
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Models\User;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PrescriptionController extends Controller
{
    public function __construct(private PrescriptionService $service) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Prescription::class);

        $query = Prescription::with(['customer', 'reviewer'])->withCount('items');

        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%$s%")
                  ->orWhere('doctor_name', 'like', "%$s%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
            });
        }

        $prescriptions = $query->latest()->paginate(15)->withQueryString();
        $counts = Prescription::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        return view('admin.prescriptions.index', compact('prescriptions', 'counts'));
    }

    /** Step 1: pick the customer. Step 2: upload for that customer. */
    public function create(Request $request)
    {
        $this->authorize('create', Prescription::class);

        $customer = $request->customer ? User::where('role', 'customer')->find($request->customer) : null;
        $results = collect();
        if (! $customer && ($q = trim((string) $request->q)) !== '') {
            $results = User::where('role', 'customer')
                ->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")->orWhere('phone', 'like', "%$q%"))
                ->orderBy('name')->limit(15)->get();
        }

        return view('admin.prescriptions.create', compact('customer', 'results'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Prescription::class);

        $data = $request->validate([
            'customer_id'   => 'required|integer|exists:users,id',
            'file'          => 'required|file|mimes:jpg,jpeg,png,pdf|mimetypes:image/jpeg,image/png,application/pdf|max:5120',
            'doctor_name'   => 'nullable|string|max:150',
            'prescribed_on' => 'nullable|date|before_or_equal:today',
            'staff_notes'   => 'nullable|string|max:1000',
        ]);

        $customer = User::where('role', 'customer')->findOrFail($data['customer_id']);
        $rx = $this->service->create($customer, $request->file('file'), $data, $request->user());

        return redirect()->route('admin.prescriptions.show', $rx)
            ->with('success', 'Prescription uploaded. Add the medicines written on it, then verify.');
    }

    public function show(Prescription $prescription)
    {
        $this->authorize('view', $prescription);
        $prescription->load(['customer', 'uploader', 'reviewer', 'items.product', 'logs.user']);

        $suggestions = [];
        if ($prescription->status === 'pending') {
            foreach ($prescription->items as $item) {
                $suggestions[$item->id] = $this->service->matches($item);
            }
        }

        return view('admin.prescriptions.show', [
            'rx'          => $prescription,
            'suggestions' => $suggestions,
            'ocrEnabled'  => $this->service->ocrEnabled(),
            'validity'    => $this->service->validityDays(),
            'allProducts' => $prescription->status === 'pending'
                ? Product::where('is_active', true)->orderBy('name')->limit(500)->get(['id', 'name', 'dosage', 'stock', 'requires_prescription'])
                : collect(),
        ]);
    }

    /** Authorised streaming of the private file. */
    public function file(Prescription $prescription)
    {
        $this->authorize('view', $prescription);
        abort_unless(Storage::disk(PrescriptionService::DISK)->exists($prescription->file_path), 404);

        $prescription->log('file_viewed');

        return Storage::disk(PrescriptionService::DISK)->response($prescription->file_path, null, [
            'Content-Type'           => $prescription->mime_type,
            'Content-Disposition'    => 'inline; filename="prescription-' . $prescription->reference . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store',
        ]);
    }

    public function addItem(Request $request, Prescription $prescription)
    {
        $this->authorize('update', $prescription);

        $data = $request->validate([
            'written_name'     => 'required|string|max:190',
            'written_strength' => 'nullable|string|max:60',
            'written_form'     => 'nullable|string|max:60',
            'prescribed_qty'   => 'nullable|integer|min:1|max:1000',
        ]);

        $prescription->items()->create($data);
        $prescription->log('item_added', $data['written_name']);

        return back()->with('success', 'Medicine line added.');
    }

    /** Pick the catalogue product and approved quantity for one line. */
    public function updateItem(Request $request, Prescription $prescription, PrescriptionItem $item)
    {
        $this->authorize('update', $prescription);
        abort_unless($item->prescription_id === $prescription->id, 404);

        $data = $request->validate([
            'product_id'   => 'required|integer|exists:products,id',
            'approved_qty' => 'required|integer|min:1|max:1000',
        ]);

        $product = Product::where('is_active', true)->findOrFail($data['product_id']);
        if ($item->prescribed_qty && $data['approved_qty'] > $item->prescribed_qty) {
            return back()->withErrors(['approved_qty' => 'Approved quantity cannot exceed the prescribed quantity (' . $item->prescribed_qty . ').']);
        }
        if ($data['approved_qty'] > $product->stock) {
            return back()->withErrors(['approved_qty' => "Only {$product->stock} of {$product->name} in stock."]);
        }

        $item->update($data);
        $prescription->log('item_matched', "{$item->written_name} -> {$product->name} x{$data['approved_qty']}");

        return back()->with('success', 'Match saved.');
    }

    public function destroyItem(Prescription $prescription, PrescriptionItem $item)
    {
        $this->authorize('update', $prescription);
        abort_unless($item->prescription_id === $prescription->id, 404);

        $prescription->log('item_removed', $item->written_name);
        $item->delete();

        return back()->with('success', 'Line removed.');
    }

    public function verify(Request $request, Prescription $prescription)
    {
        $this->authorize('verify', $prescription);
        $request->validate(['confirm' => 'accepted']);

        try {
            $this->service->verify($prescription, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.prescriptions.show', $prescription)
            ->with('success', 'Prescription verified. The customer can now review these items and add them to their cart.');
    }

    public function reject(Request $request, Prescription $prescription)
    {
        $this->authorize('verify', $prescription);
        $data = $request->validate(['rejection_reason' => 'required|string|min:5|max:500']);

        try {
            $this->service->reject($prescription, $request->user(), $data['rejection_reason']);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.prescriptions.show', $prescription)->with('success', 'Prescription rejected.');
    }
}

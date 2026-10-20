<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Bulk-attach product photos from a folder.
 *
 *   php artisan products:import-images "D:\photos"            # preview first with --dry-run
 *
 * A file is matched to a product by its name (without extension), using any of:
 * product id, SKU, slug, or the product name (e.g. "nexium-20mg-tabs-x14.jpg").
 * Add -2, -3 ... to the file name for extra gallery images ("nexium-20mg-tabs-x14-2.jpg").
 * Names that match more than one product, or none, are skipped and reported.
 */
class ImportProductImages extends Command
{
    protected $signature = 'products:import-images
                            {folder : Folder containing the image files}
                            {--dry-run : Show what would happen without changing anything}
                            {--force : Replace the main image of products that already have one}';

    protected $description = 'Attach product photos from a folder, matching file names to products';

    private const ALLOWED = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    private const MAX_BYTES = 5 * 1024 * 1024;

    public function handle(): int
    {
        $folder = rtrim($this->argument('folder'), '\\/');
        if (! is_dir($folder)) {
            $this->error("Folder not found: {$folder}");
            return self::FAILURE;
        }

        $index = $this->buildIndex();
        $dry = (bool) $this->option('dry-run');
        $dest = public_path('images/products');
        if (! $dry && ! is_dir($dest)) {
            mkdir($dest, 0775, true);
        }

        $attached = $gallery = $skipped = 0;
        $unmatched = [];

        foreach (glob($folder . DIRECTORY_SEPARATOR . '*.*') as $path) {
            $file = basename($path);
            $base = pathinfo($file, PATHINFO_FILENAME);

            // "-2", "-3" ... marks an extra gallery image.
            $isGallery = false;
            if (preg_match('/^(.*)-([2-9])$/', Str::slug($base), $m) && isset($this->lookup($index, $m[1])[0])) {
                $isGallery = true;
                $key = $m[1];
            } else {
                $key = Str::slug($base);
            }

            $hits = $this->lookup($index, $key);
            if (count($hits) !== 1) {
                $unmatched[] = $file . (count($hits) > 1 ? '  (matches several products)' : '');
                continue;
            }
            $product = Product::find($hits[0]);

            $info = @getimagesize($path);
            if (! $info || ! isset(self::ALLOWED[$info[2]]) || filesize($path) > self::MAX_BYTES) {
                $this->warn("Skipped {$file}: not a JPG/PNG/WEBP image under 5 MB.");
                $skipped++;
                continue;
            }

            if (! $isGallery && $product->image_url && ! $this->option('force')) {
                $this->line("Kept existing image for {$product->name} (use --force to replace): {$file}");
                $skipped++;
                continue;
            }

            $name = time() . '_' . Str::slug($product->name) . '_' . Str::random(5) . '.' . self::ALLOWED[$info[2]];
            $this->line(($isGallery ? 'Gallery  ' : 'Main     ') . str_pad($file, 40) . ' -> ' . $product->name);

            if (! $dry) {
                copy($path, $dest . DIRECTORY_SEPARATOR . $name);
                if ($isGallery) {
                    $product->update(['images' => array_values(array_merge($product->images ?? [], [$name]))]);
                } else {
                    $product->update(['image' => $name, 'image_credit' => null, 'image_credit_url' => null]);
                }
            }
            $isGallery ? $gallery++ : $attached++;
        }

        $this->newLine();
        $this->info(($dry ? '[dry run] ' : '') . "Main images: {$attached}, gallery images: {$gallery}, skipped: {$skipped}, unmatched: " . count($unmatched));
        foreach ($unmatched as $u) {
            $this->warn("  unmatched: {$u}");
        }

        return self::SUCCESS;
    }

    /** @return array<string,array<int>> normalised key => product ids */
    private function buildIndex(): array
    {
        $index = [];
        foreach (Product::select('id', 'name', 'slug', 'sku')->cursor() as $p) {
            $keys = [(string) $p->id, Str::slug((string) $p->sku), Str::slug($p->slug), Str::slug($p->name),
                     Str::slug(preg_replace('/\s+x\s?\d+\b.*$/i', '', $p->name))];
            // The seeded slugs end in a random suffix; also accept them without it.
            $keys[] = preg_replace('/-[0-9a-f]{4}$/', '', Str::slug($p->slug));
            foreach (array_filter(array_unique($keys)) as $k) {
                $index[$k][] = $p->id;
            }
        }
        return array_map(fn ($ids) => array_values(array_unique($ids)), $index);
    }

    private function lookup(array $index, string $key): array
    {
        return $index[$key] ?? [];
    }
}

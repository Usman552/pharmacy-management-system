<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%")
                  ->orWhere('brand', 'like', "%$s%");
            });
        }
        if ($cat = $request->category) $query->where('category_id', $cat);
        if ($request->stock === 'low') $query->whereColumn('stock', '<', 'min_stock_alert')->where('stock', '>', 0);
        if ($request->stock === 'out') $query->where('stock', 0);
        if ($request->status === 'active')   $query->where('is_active', true);
        if ($request->status === 'inactive') $query->where('is_active', false);
        if ($request->rx === '1') $query->where('requires_prescription', true);

        // Whitelisted sorting only.
        $sort = in_array($request->sort, ['name', 'price', 'stock', 'created_at'], true) ? $request->sort : 'created_at';
        $dir  = $request->dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $products   = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    private function rules(?Product $product = null): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'category_id'           => 'required|exists:categories,id',
            'brand'                 => 'nullable|string|max:120',
            'sku'                   => 'nullable|string|max:60|unique:products,sku' . ($product ? ',' . $product->id : ''),
            'description'           => 'nullable|string|max:5000',
            'details'               => 'nullable|string|max:20000',
            'price'                 => 'required|numeric|min:0|max:9999999',
            'sale_price'            => 'nullable|numeric|min:0|lt:price',
            'stock'                 => 'required|integer|min:0|max:1000000',
            'min_stock_alert'       => 'nullable|integer|min:0|max:100000',
            'expiry_date'           => 'nullable|date',
            'manufacturer'          => 'nullable|string|max:150',
            'composition'           => 'nullable|string|max:255',
            'dosage'                => 'nullable|string|max:80',
            'sort_order'            => 'nullable|integer|min:0|max:99999',
            'weight'                => 'nullable|string|max:60',
            'image'                 => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'images.*'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /** Only these columns can ever be set from a form. */
    private function payload(Request $request): array
    {
        $data = $request->only([
            'name', 'category_id', 'brand', 'sku', 'description', 'details', 'price', 'sale_price',
            'min_stock_alert', 'expiry_date', 'manufacturer', 'composition', 'dosage', 'weight', 'sort_order',
        ]);
        foreach (['is_active', 'is_featured', 'is_new', 'is_popular', 'is_sale', 'requires_prescription'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        $data['sale_price'] = $request->filled('sale_price') ? $request->sale_price : null;
        $data['min_stock_alert'] = $request->input('min_stock_alert', 10) ?? 10;
        $data['sku'] = $request->filled('sku') ? $request->sku : null;
        $data['sort_order'] = (int) $request->input('sort_order', 0);
        $data['expiry_date'] = $request->filled('expiry_date') ? $request->expiry_date : null;
        return $data;
    }

    private function storeImage($file, string $name): string
    {
        $filename = time() . '_' . Str::slug($name) . '_' . Str::random(5) . '.' . $file->extension();
        $file->move(public_path('images/products'), $filename);
        return $filename;
    }

    private function deleteImage(?string $filename): void
    {
        if ($filename && is_file(public_path('images/products/' . basename($filename)))) {
            @unlink(public_path('images/products/' . basename($filename)));
        }
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $product = DB::transaction(function () use ($request) {
            $data = $this->payload($request);
            $data['stock'] = 0;

            $slug = $base = Str::slug($request->name) ?: 'product';
            for ($i = 1; Product::where('slug', $slug)->exists(); $i++) {
                $slug = $base . '-' . $i;
            }
            $data['slug'] = $slug;

            if ($request->hasFile('image')) {
                $data['image'] = $this->storeImage($request->file('image'), $request->name);
            }
            if ($request->hasFile('images')) {
                $data['images'] = collect($request->file('images'))->map(fn ($f) => $this->storeImage($f, $request->name))->all();
            }

            $product = Product::create($data);

            if ((int) $request->stock > 0) {
                $this->stock->adjust($product->id, (int) $request->stock, 'restock', null, 'Opening stock');
            }
            return $product;
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Product "' . $product->name . '" created.');
    }

    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate($this->rules($product) + ['remove_images' => 'nullable|array']);

        DB::transaction(function () use ($request, $product) {
            $data = $this->payload($request);

            if ($request->hasFile('image')) {
                $this->deleteImage($product->image);
                $data['image'] = $this->storeImage($request->file('image'), $request->name);
                // A photo uploaded by staff replaces any third-party image, so its credit goes too.
                $data['image_credit'] = null;
                $data['image_credit_url'] = null;
            }

            $gallery = $product->images ?? [];
            foreach ((array) $request->remove_images as $remove) {
                if (($i = array_search($remove, $gallery, true)) !== false) {
                    $this->deleteImage($remove);
                    unset($gallery[$i]);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $f) {
                    $gallery[] = $this->storeImage($f, $request->name);
                }
            }
            $data['images'] = array_values($gallery);

            $product->update($data);

            if ((int) $request->stock !== (int) $product->stock) {
                $this->stock->setLevel($product->id, (int) $request->stock, 'Edited on product form');
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        // Sold products keep their history; hide them instead of deleting.
        if ($product->orderItems()->exists()) {
            $product->update(['is_active' => false]);
            return redirect()->route('admin.products.index')
                ->with('success', 'This product appears in past orders, so it was deactivated instead of deleted.');
        }

        $this->deleteImage($product->image);
        foreach ($product->images ?? [] as $img) {
            $this->deleteImage($img);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function expiring()
    {
        $products = Product::with('category')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now()->addDays(30))
            ->orderBy('expiry_date')
            ->paginate(15);
        return view('admin.products.expiring', compact('products'));
    }
}

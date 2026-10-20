<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q'         => 'nullable|string|max:100',
            'category'  => 'nullable|string|max:120',
            'brand'     => 'nullable|string|max:120',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort'      => 'nullable|in:featured,price_asc,price_desc,newest,name',
        ]);

        $query = Product::with('category')->where('is_active', true);

        if ($s = trim((string) $request->q)) {
            $like = '%' . addcslashes($s, '%_\\') . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('brand', 'like', $like)
                  ->orWhere('composition', 'like', $like)
                  ->orWhere('description', 'like', $like);
            });
        }

        $currentCategory = null;
        if ($slug = $request->category) {
            $currentCategory = Category::where('slug', $slug)->where('is_active', true)->first();
            // An unknown category gives an empty result rather than silently showing everything.
            $currentCategory
                ? $query->where('category_id', $currentCategory->id)
                : $query->whereRaw('1 = 0');
        }

        if ($brand = $request->brand) $query->where('brand', $brand);
        if ($request->filled('min_price')) $query->whereRaw('COALESCE(sale_price, price) >= ?', [(float) $request->min_price]);
        if ($request->filled('max_price')) $query->whereRaw('COALESCE(sale_price, price) <= ?', [(float) $request->max_price]);
        if ($request->boolean('on_sale'))  $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
        if ($request->boolean('in_stock')) $query->where('stock', '>', 0);
        if ($request->boolean('no_rx'))    $query->where('requires_prescription', false);

        match ($request->sort) {
            'price_asc'  => $query->orderByRaw('COALESCE(sale_price, price) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) desc'),
            'newest'     => $query->latest(),
            'name'       => $query->orderBy('name'),
            default      => $query->orderByDesc('is_featured')->orderByDesc('is_new')->latest(),
        };

        $products = $query->paginate(16)->withQueryString();

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')->get();

        $brands = Product::where('is_active', true)->whereNotNull('brand')->where('brand', '!=', '')
            ->distinct()->orderBy('brand')->pluck('brand');

        return view('products.index', compact('products', 'categories', 'brands', 'currentCategory'));
    }

    public function show($slug, CartService $cart)
    {
        $product = Product::with(['category', 'reviews' => fn ($q) => $q->where('is_approved', true)->with('user')])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $related = Product::with('category')->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->take(4)->get();

        $isWishlisted = in_array($product->id, session('wishlist', []));
        $rxAllowance  = $product->requires_prescription ? $cart->prescriptionAllowance($product) : null;

        return view('products.show', compact('product', 'related', 'isWishlisted', 'rxAllowance'));
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }
}

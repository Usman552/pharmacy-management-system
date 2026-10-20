<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;

class HomeController extends Controller
{
    public function index(CartService $cart)
    {
        $active = fn () => Product::with('category')->where('is_active', true);

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $featured = $active()->where('is_featured', true)->orderByDesc('rating')->take(8)->get();
        $newArrivals = $active()->where('is_new', true)->latest()->take(4)->get();
        $onSale = $active()->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')->take(4)->get();

        // Best sellers only when there is real order history to back it up.
        $bestIds = OrderItem::selectRaw('product_id, SUM(quantity) q')
            ->whereNotNull('product_id')
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']))
            ->groupBy('product_id')->orderByDesc('q')->take(4)->pluck('product_id');
        $bestSellers = $bestIds->count() >= 4
            ? $active()->whereIn('id', $bestIds)->get()->sortBy(fn ($p) => $bestIds->search($p->id))->values()
            : collect();

        $posts = Blog::published()->latest('published_at')->take(3)->get();

        return view('home.index', [
            'categories'  => $categories,
            'featured'    => $featured,
            'newArrivals' => $newArrivals,
            'onSale'      => $onSale,
            'bestSellers' => $bestSellers,
            'posts'       => $posts,
            'productCount' => Product::where('is_active', true)->where('stock', '>', 0)->count(),
            'freeMin'     => $cart->freeShippingMin(),
            'flatShip'    => $cart->flatShipping(),
            'hasRx'       => Product::where('is_active', true)->where('requires_prescription', true)->exists(),
        ]);
    }
}

<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // Toggle wishlist (session-based - no DB table)
    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $productId = (int) $request->product_id;
        $wishlist  = session('wishlist', []);

        if(in_array($productId, $wishlist)) {
            $wishlist = array_values(array_diff($wishlist, [$productId]));
            $added = false;
        } else {
            $wishlist[] = $productId;
            $added = true;
        }

        session(['wishlist' => $wishlist]);

        return response()->json([
            'success' => true,
            'added'   => $added,
            'count'   => count($wishlist),
        ]);
    }

    // Wishlist page
    public function index()
    {
        $ids = session('wishlist', []);
        $products = Product::with('category')
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get();

        return view('wishlist.index', compact('products'));
    }

    // Remove from wishlist
    public function remove(Request $request, $productId)
    {
        $wishlist = session('wishlist', []);
        $wishlist = array_values(array_diff($wishlist, [(int) $productId]));
        session(['wishlist' => $wishlist]);

        return redirect()->route('wishlist.index')
            ->with('success', 'Product removed from wishlist');
    }

    // Move to cart (goes through the same stock / prescription checks as any add)
    public function moveToCart(Request $request, $productId, \App\Services\CartService $cart)
    {
        [$ok, $message] = $cart->add((int) $productId, 1);

        if ($ok) {
            session(['wishlist' => array_values(array_diff(session('wishlist', []), [(int) $productId]))]);
            return redirect()->route('wishlist.index')->with('success', 'Moved to your cart.');
        }

        return redirect()->route('wishlist.index')->with('error', $message);
    }

    // Clear wishlist
    public function clear()
    {
        session(['wishlist' => []]);
        return redirect()->route('wishlist.index')
            ->with('success', 'Wishlist cleared');
    }
}

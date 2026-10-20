<?php
namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    // Cart page
    public function index()
    {
        $totals = $this->cart->totals();
        return view('cart.index', $totals + ['freeMin' => $this->cart->freeShippingMin()]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'nullable|integer|min:1|max:20',
        ]);

        [$ok, $message] = $this->cart->add((int) $data['product_id'], (int) ($data['quantity'] ?? 1));

        return response()->json([
            'success' => $ok,
            'message' => $message,
            'count'   => $this->cart->count(),
        ], $ok ? 200 : 422);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'cart_id'  => 'required|integer',
            'quantity' => 'required|integer|min:1|max:20',
        ]);

        [$ok, $message] = $this->cart->setQuantity((int) $data['cart_id'], (int) $data['quantity']);
        $totals = $this->cart->totals();

        return response()->json([
            'success'  => $ok,
            'message'  => $message,
            'count'    => $this->cart->count(),
            'subtotal' => number_format($totals['subtotal'], 0),
            'total'    => number_format($totals['total'], 0),
        ], $ok ? 200 : 422);
    }

    public function remove(Request $request)
    {
        $request->validate(['cart_id' => 'required|integer']);
        $this->cart->remove((int) $request->cart_id);

        return response()->json(['success' => true, 'count' => $this->cart->count()]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        if ($request->boolean('remove')) {
            $this->cart->removeCoupon();
            return back()->with('success', 'Coupon removed.');
        }

        [$ok, $message] = $this->cart->applyCoupon($request->code);

        if ($request->expectsJson()) {
            return response()->json(['success' => $ok, 'message' => $message], $ok ? 200 : 422);
        }
        return back()->with($ok ? 'success' : 'error', $message);
    }

    // Cart drawer contents
    public function items()
    {
        return view('cart.partials.sidebar-items', $this->cart->totals());
    }
}

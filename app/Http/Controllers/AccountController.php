<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $orders = Order::where('user_id', $user->id)->latest()->take(5)->get();
        return view('pages.account', compact('user', 'orders'));
    }

    public function orders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items')
            ->latest()
            ->paginate(10);
        return view('pages.orders', compact('orders'));
    }

    public function orderDetail($orderNumber)
    {
        $order = Order::with(['items.product', 'tracking'])
            ->where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        return view('pages.order-detail', compact('order'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'city'  => 'nullable|string|max:100',
        ]);

        auth()->user()->update($request->only('name', 'phone', 'address', 'city'));

        return redirect()->route('account.index')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return redirect()->route('account.index')->with('success', 'Password changed successfully!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function index()
    {
        $orders = Order::with('items')
            ->where('payment_status', 'paid')
            ->latest()
            ->paginate(15);

        return view('admin.receipts.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'user']);
        return view('admin.orders.receipt', compact('order'));
    }
}

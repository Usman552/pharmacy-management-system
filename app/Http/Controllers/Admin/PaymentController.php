<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('order');

        if ($s = $request->search) {
            $query->whereHas('order', fn($q) => $q->where('order_number', 'like', "%$s%"));
        }

        if ($status = $request->status) $query->where('status', $status);

        $payments = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'pending'       => Payment::where('status', 'pending')->count(),
            'completed'     => Payment::where('status', 'completed')->count(),
        ];

        return view('admin.payments.index', compact('payments', 'stats'));
    }
}

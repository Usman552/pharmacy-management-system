<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Revenue = orders whose payment was actually received and not reversed.
        $paid = Order::where('payment_status', 'paid')->whereNotIn('status', ['cancelled', 'refunded']);

        $stats = [
            'total_orders'     => Order::count(),
            'revenue'          => (float) (clone $paid)->sum('total_amount'),
            'revenue_30'       => (float) (clone $paid)->where('created_at', '>=', now()->subDays(30))->sum('total_amount'),
            'pending_orders'   => Order::where('status', 'pending')->count(),
            'total_products'   => Product::count(),
            'active_products'  => Product::where('is_active', true)->count(),
            'low_stock'        => Product::where('stock', '>', 0)->whereColumn('stock', '<', 'min_stock_alert')->count(),
            'out_of_stock'     => Product::where('stock', 0)->count(),
            'total_customers'  => User::where('role', 'customer')->count(),
            'pending_rx'       => Prescription::where('status', 'pending')->count(),
        ];

        $recentOrders = Order::with('user')->latest()->take(8)->get();
        $lowStock = Product::where('is_active', true)->whereColumn('stock', '<', 'min_stock_alert')
            ->orderBy('stock')->take(6)->get();

        // Last 14 days of sales (paid, not reversed) for the chart.
        $days = collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->toDateString());
        $byDay = (clone $paid)->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) d, SUM(total_amount) t, COUNT(*) c')->groupBy('d')->get()->keyBy('d');
        $series = $days->map(fn ($d) => ['date' => $d, 'total' => (float) ($byDay[$d]->t ?? 0), 'orders' => (int) ($byDay[$d]->c ?? 0)]);

        $topProducts = OrderItem::selectRaw('product_name, SUM(quantity) qty, SUM(total_price) revenue')
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']))
            ->groupBy('product_name')->orderByDesc('qty')->take(5)->get();

        return view('admin.dashboard.index', compact('stats', 'recentOrders', 'lowStock', 'series', 'topProducts'));
    }
}

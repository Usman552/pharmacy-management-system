@extends('admin.layouts.app')
@section('meta_title', 'Dashboard')

@section('content')
@php $max = max(1, $series->max('total')); @endphp
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">{{ now()->format('l, d M Y') }}</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.prescriptions.create') }}" class="btn-admin-secondary"><i class="fas fa-file-prescription"></i> New prescription</a>
        <a href="{{ route('admin.products.create') }}" class="btn-admin-primary"><i class="fas fa-plus"></i> Add medicine</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6"><div class="stat-card">
        <div class="stat-icon"><i class="fas fa-bag-shopping"></i></div>
        <div class="stat-info"><div class="stat-label">Total orders</div><div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-change">{{ $stats['pending_orders'] }} pending</div></div>
    </div></div>
    <div class="col-xl-3 col-md-6"><div class="stat-card">
        <div class="stat-icon"><i class="fas fa-sack-dollar"></i></div>
        <div class="stat-info"><div class="stat-label">Revenue (paid orders)</div><div class="stat-value">Rs. {{ number_format($stats['revenue'], 0) }}</div>
            <div class="stat-change">Rs. {{ number_format($stats['revenue_30'], 0) }} in last 30 days</div></div>
    </div></div>
    <div class="col-xl-3 col-md-6"><div class="stat-card">
        <div class="stat-icon"><i class="fas fa-pills"></i></div>
        <div class="stat-info"><div class="stat-label">Products</div><div class="stat-value">{{ number_format($stats['total_products']) }}</div>
            <div class="stat-change">{{ $stats['active_products'] }} active</div></div>
    </div></div>
    <div class="col-xl-3 col-md-6"><div class="stat-card" style="--stat-color:#8A5A00">
        <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-info"><div class="stat-label">Low or out of stock</div><div class="stat-value">{{ $stats['low_stock'] + $stats['out_of_stock'] }}</div>
            <div class="stat-change">{{ $stats['out_of_stock'] }} out of stock</div></div>
    </div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="admin-card-header"><h5>Paid sales, last 14 days</h5><span class="text-muted" style="font-size:12.5px">Cancelled and refunded orders excluded</span></div>
            <div class="admin-card-body">
                @if($series->sum('total') > 0)
                <div class="chart-bars" role="img" aria-label="Daily paid sales for the last 14 days">
                    @foreach($series as $d)<div class="bar" style="height:{{ max(2, round($d['total'] / $max * 100)) }}%"><span>{{ \Carbon\Carbon::parse($d['date'])->format('d M') }}: Rs. {{ number_format($d['total'], 0) }} ({{ $d['orders'] }})</span></div>@endforeach
                </div>
                <div class="chart-axis"><span>{{ \Carbon\Carbon::parse($series->first()['date'])->format('d M') }}</span><span>{{ \Carbon\Carbon::parse($series->last()['date'])->format('d M') }}</span></div>
                @else
                <div class="empty-state"><i class="fas fa-chart-column"></i>No paid orders in the last 14 days.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="admin-card-header"><h5>Needs attention</h5></div>
            <div class="admin-card-body">
                <a href="{{ route('admin.prescriptions.index', ['status' => 'pending']) }}" class="d-flex justify-content-between py-2" style="color:inherit"><span><i class="fas fa-file-prescription text-warning me-2"></i>Prescriptions to review</span><strong>{{ $stats['pending_rx'] }}</strong></a>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="d-flex justify-content-between py-2" style="color:inherit"><span><i class="fas fa-bag-shopping text-warning me-2"></i>Pending orders</span><strong>{{ $stats['pending_orders'] }}</strong></a>
                <a href="{{ route('admin.inventory.index', ['level' => 'low']) }}" class="d-flex justify-content-between py-2" style="color:inherit"><span><i class="fas fa-box-open text-warning me-2"></i>Low stock</span><strong>{{ $stats['low_stock'] }}</strong></a>
                <a href="{{ route('admin.inventory.index', ['level' => 'out']) }}" class="d-flex justify-content-between py-2" style="color:inherit"><span><i class="fas fa-ban text-warning me-2"></i>Out of stock</span><strong>{{ $stats['out_of_stock'] }}</strong></a>
                <a href="{{ route('admin.products.expiring') }}" class="d-flex justify-content-between py-2" style="color:inherit"><span><i class="fas fa-hourglass-end text-warning me-2"></i>Expiring within 30 days</span><strong>{{ \App\Models\Product::whereNotNull('expiry_date')->whereDate('expiry_date', '<', now()->addDays(30))->count() }}</strong></a>
                <div class="d-flex justify-content-between py-2"><span><i class="fas fa-users text-warning me-2"></i>Customers</span><strong>{{ $stats['total_customers'] }}</strong></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header"><h5>Recent orders</h5><a href="{{ route('admin.orders.index') }}" style="font-size:13px;font-weight:700">View all</a></div>
            <div style="overflow-x:auto"><table class="admin-table">
                <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th style="text-align:right">Total</th></tr></thead>
                <tbody>
                @forelse($recentOrders as $o)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $o->id) }}" style="font-weight:800">{{ $o->order_number }}</a><div class="text-muted" style="font-size:12px">{{ $o->created_at->diffForHumans() }}</div></td>
                        <td>{{ $o->customer_name }}</td>
                        <td><span class="status-badge {{ $o->status }}">{{ $o->status }}</span></td>
                        <td><span class="status-badge {{ $o->payment_status }}">{{ $o->payment_status }}</span></td>
                        <td style="text-align:right"><strong>Rs. {{ number_format($o->total_amount, 0) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">No orders yet.</div></td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5>Low stock</h5><a href="{{ route('admin.inventory.index') }}" style="font-size:13px;font-weight:700">Inventory</a></div>
            <div class="admin-card-body" style="padding-top:8px;padding-bottom:8px">
                @forelse($lowStock as $p)
                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid #EEF3F0">
                        <a href="{{ route('admin.products.edit', $p->id) }}" style="color:inherit;font-weight:600">{{ Str::limit($p->name, 28) }}</a>
                        <span class="status-badge {{ $p->stock < 1 ? 'out-of-stock' : 'low-stock' }}">{{ $p->stock }} left</span>
                    </div>
                @empty<div class="empty-state" style="padding:24px">Stock levels look healthy.</div>@endforelse
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-card-header"><h5>Top selling</h5></div>
            <div class="admin-card-body" style="padding-top:8px;padding-bottom:8px">
                @forelse($topProducts as $t)
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid #EEF3F0"><span>{{ Str::limit($t->product_name, 28) }}</span><strong>{{ $t->qty }} sold</strong></div>
                @empty<div class="empty-state" style="padding:24px">No sales yet.</div>@endforelse
            </div>
        </div>
    </div>
</div>
@endsection

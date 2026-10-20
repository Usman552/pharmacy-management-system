@extends('admin.layouts.app')
@section('meta_title', 'Orders')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Orders</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Orders</li>
        </ol></nav>
    </div>
    <div class="page-header-right">
        <span style="font-size:13px;color:var(--text-muted);background:var(--body-bg);padding:8px 14px;border-radius:8px;border:1.5px solid var(--border)">
            Total: <strong>{{ $stats['total'] }}</strong> orders
        </span>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    @foreach([
        ['pending',    $stats['pending'],    '#ffc107', 'fas fa-clock',        'Pending'],
        ['processing', $stats['processing'], '#0d6efd', 'fas fa-cog',          'Processing'],
        ['shipped',    $stats['shipped'],    '#17a2b8', 'fas fa-truck',        'Shipped'],
        ['delivered',  $stats['delivered'],  '#28a745', 'fas fa-check-circle', 'Delivered'],
        ['cancelled',  $stats['cancelled'],  '#dc3545', 'fas fa-times-circle', 'Cancelled'],
    ] as [$status, $count, $color, $icon, $label])
    <div class="col">
        <a href="{{ route('admin.orders.index', ['status' => $status]) }}" class="d-block text-decoration-none">
            <div class="stat-card" style="--stat-color:{{ $color }}">
                <div class="stat-icon"><i class="{{ $icon }}"></i></div>
                <div class="stat-info">
                    <div class="stat-label">{{ $label }}</div>
                    <div class="stat-value">{{ $count }}</div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="admin-card">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center w-100">
            <input type="text" name="search" class="search-input" placeholder="Search order #, name, email, phone..." value="{{ request('search') }}">
            <select name="status">
                <option value="">All Status</option>
                @foreach(['pending','processing','shipped','delivered','cancelled','refunded'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="payment_status">
                <option value="">All Payments</option>
                @foreach(['pending','paid','failed','refunded'] as $ps)
                    <option value="{{ $ps }}" {{ request('payment_status') === $ps ? 'selected' : '' }}>{{ ucfirst($ps) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" title="From date">
            <input type="date" name="to" value="{{ request('to') }}" title="To date">
            <button type="submit" class="btn-admin-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="btn-admin-secondary"><i class="fas fa-times"></i> Clear</a>
        </form>
    </div>

    <div style="overflow-x:auto">
        <table class="admin-table searchable-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}" style="font-weight:800;color:var(--primary)">
                            #{{ $order->order_number }}
                        </a>
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:13px">{{ $order->customer_name }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $order->customer_phone }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $order->shipping_city }}</div>
                    </td>
                    <td>
                        <span style="font-weight:700">{{ $order->items->count() }}</span>
                        <span style="font-size:11px;color:var(--text-muted)"> item(s)</span>
                    </td>
                    <td>
                        <strong style="font-size:14px">Rs. {{ number_format($order->total_amount, 0) }}</strong>
                        <div style="font-size:11px;color:var(--text-muted)">{{ strtoupper(str_replace('_',' ',$order->payment_method)) }}</div>
                    </td>
                    <td><span class="status-badge {{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span></td>
                    <td><span class="status-badge {{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                    <td>
                        <div style="font-size:13px">{{ $order->created_at->format('d M Y') }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $order->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-admin-view">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.orders.receipt', $order->id) }}" target="_blank" class="btn-admin-secondary" style="padding:6px 10px;font-size:12px">
                                <i class="fas fa-print"></i>
                            </a>
                            <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" style="margin:0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-admin-danger confirm-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-shopping-bag fa-3x d-block mb-3 opacity-25"></i>
                        No orders found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-card-footer d-flex justify-content-between align-items-center">
        <div style="font-size:13px;color:var(--text-muted)">
            Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </div>
        <div>{{ $orders->links() }}</div>
    </div>
</div>
@endsection

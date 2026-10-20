{{-- ========== PAYMENTS INDEX ========== --}}
@extends('admin.layouts.app')
@section('meta_title','Payments')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Payments</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Payments</li>
        </ol></nav>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="--stat-color:#28a745">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">Rs. {{ number_format($stats['total_revenue']/1000,1) }}K</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="--stat-color:#0d6efd">
            <div class="stat-icon"><i class="fas fa-check"></i></div>
            <div class="stat-info">
                <div class="stat-label">Completed</div>
                <div class="stat-value">{{ $stats['completed'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="--stat-color:#ffc107">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $stats['pending'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="filter-bar">
        <form method="GET" class="d-flex gap-2 flex-wrap w-100">
            <input type="text" name="search" class="search-input" placeholder="Search order number..." value="{{ request('search') }}">
            <select name="status">
                <option value="">All Status</option>
                @foreach(['pending','completed','failed','refunded'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-admin-primary"><i class="fas fa-search"></i></button>
            <a href="{{ route('admin.payments.index') }}" class="btn-admin-secondary"><i class="fas fa-times"></i></a>
        </form>
    </div>
    <div style="overflow-x:auto">
        <table class="admin-table">
            <thead><tr><th>Transaction ID</th><th>Order #</th><th>Customer</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td><code style="font-size:11px">{{ $payment->transaction_id ?? 'COD-'.$payment->id }}</code></td>
                    <td>
                        <a href="{{ route('admin.orders.show', $payment->order->id) }}" style="font-weight:700;color:var(--primary)">
                            #{{ $payment->order->order_number }}
                        </a>
                    </td>
                    <td style="font-size:13px">{{ $payment->order->customer_name }}</td>
                    <td><strong>Rs. {{ number_format($payment->amount,0) }}</strong></td>
                    <td><span style="font-size:12px;font-weight:700;text-transform:uppercase">{{ str_replace('_',' ',$payment->method) }}</span></td>
                    <td><span class="status-badge {{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                    <td style="font-size:12px;color:var(--text-muted)">{{ $payment->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">No payments found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-card-footer">{{ $payments->links() }}</div>
</div>
@endsection

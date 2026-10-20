{{-- ========== USER SHOW ========== --}}
@extends('admin.layouts.app')
@section('meta_title', 'Customer: ' . $user->name)
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">{{ $user->name }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Customers</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol></nav>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="admin-card mb-3">
            <div class="admin-card-body text-center" style="padding:28px">
                <div style="width:70px;height:70px;border-radius:18px;background:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:28px;color:#fff;margin:0 auto 16px">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
                <h5 style="font-weight:800;margin-bottom:4px">{{ $user->name }}</h5>
                <div style="font-size:13px;color:var(--text-muted)">{{ $user->email }}</div>
                <div style="font-size:13px;color:var(--text-muted)">{{ $user->phone ?? 'No phone' }}</div>
                <div style="margin-top:12px">
                    <span class="status-badge {{ $user->is_active ? 'active' : 'inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div style="margin-top:16px;font-size:12px;color:var(--text-muted)">Joined {{ $user->created_at->format('d M Y') }}</div>
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-card-header"><h5>Contact Info</h5></div>
            <div class="admin-card-body" style="font-size:13px">
                <div class="mb-2"><i class="fas fa-envelope me-2 text-warning"></i>{{ $user->email }}</div>
                <div class="mb-2"><i class="fas fa-phone me-2 text-warning"></i>{{ $user->phone ?? '—' }}</div>
                <div class="mb-2"><i class="fas fa-city me-2 text-warning"></i>{{ $user->city ?? '—' }}</div>
                @if($user->address)<div><i class="fas fa-map-marker-alt me-2 text-warning"></i>{{ $user->address }}</div>@endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-card mb-3">
            <div class="admin-card-body d-flex gap-4 flex-wrap">
                <div><div class="text-muted" style="font-size:12px">Orders</div><strong style="font-size:20px">{{ $user->orders->count() }}</strong></div>
                <div><div class="text-muted" style="font-size:12px">Paid, not reversed</div><strong style="font-size:20px">Rs. {{ number_format($lifetime, 0) }}</strong></div>
            </div>
        </div>
        @if($user->role === 'customer')
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5>Prescriptions</h5><a href="{{ route('admin.prescriptions.create', ['customer' => $user->id]) }}" style="font-size:13px;font-weight:700">Upload for this customer</a></div>
            <div style="overflow-x:auto"><table class="admin-table">
                <thead><tr><th>Reference</th><th>Status</th><th>Date</th><th></th></tr></thead>
                <tbody>
                @forelse($prescriptions as $rx)
                    <tr><td><strong>{{ $rx->reference }}</strong></td><td><span class="status-badge {{ $rx->status }}">{{ $rx->status }}</span></td><td>{{ $rx->created_at->format('d M Y') }}</td><td><a href="{{ route('admin.prescriptions.show', $rx) }}" class="btn-admin-view">Open</a></td></tr>
                @empty
                    <tr><td colspan="4" class="text-center py-3 text-muted">No prescriptions on file</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
        @endif
        <div class="admin-card">
            <div class="admin-card-header"><h5>Order History</h5></div>
            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead><tr><th>Order #</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($user->orders as $order)
                        <tr>
                            <td><strong>#{{ $order->order_number }}</strong></td>
                            <td>Rs. {{ number_format($order->total_amount,0) }}</td>
                            <td><span class="status-badge {{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td><a href="{{ route('admin.orders.show',$order->id) }}" class="btn-admin-view"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted">No orders yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

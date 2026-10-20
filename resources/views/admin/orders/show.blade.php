@extends('admin.layouts.app')
@section('meta_title', 'Order #' . $order->order_number)

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Order #{{ $order->order_number }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
            <li class="breadcrumb-item active">#{{ $order->order_number }}</li>
        </ol></nav>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.orders.receipt', $order->id) }}" target="_blank" class="btn-admin-secondary">
            <i class="fas fa-print"></i> Print Receipt
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Status Bar -->
<div class="admin-card mb-3">
    <div class="admin-card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="status-badge {{ $order->status }}" style="font-size:14px;padding:7px 16px">
                    {{ ucfirst($order->status) }}
                </span>
                <span class="status-badge {{ $order->payment_status }}" style="font-size:13px">
                    Payment: {{ ucfirst($order->payment_status) }}
                </span>
            </div>
            <div style="font-size:13px;color:var(--text-muted)">
                <i class="fas fa-clock me-1"></i> Placed {{ $order->created_at->diffForHumans() }} — {{ $order->created_at->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Order Items -->
    <div class="col-lg-8">
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-box me-2 text-warning"></i> Order Items</h5></div>
            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="product-thumb-sm d-flex align-items-center justify-content-center" style="width:44px;height:44px;border-radius:8px;background:var(--body-bg);font-size:22px;flex-shrink:0">
                                        @if($item->product?->image)
                                            <img src="{{ asset('images/products/'.$item->product->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius:8px">
                                        @else
                                            💊
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight:700;font-size:13px">{{ $item->product_name }} @if($item->product?->requires_prescription)<span class="status-badge cancelled" style="font-size:10px;padding:2px 6px">Rx</span>@endif</div>
                                        @if($item->product_sku)<div style="font-size:11px;color:var(--text-muted)">{{ $item->product_sku }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td>Rs. {{ number_format($item->unit_price, 0) }}</td>
                            <td><strong>{{ $item->quantity }}</strong></td>
                            <td><strong>Rs. {{ number_format($item->total_price, 0) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="admin-card-footer">
                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between mb-2" style="font-size:13px">
                            <span>Subtotal</span><strong>Rs. {{ number_format($order->subtotal, 0) }}</strong>
                        </div>
                        @if($order->discount > 0)
                        <div class="d-flex justify-content-between mb-2" style="font-size:13px;color:var(--success)">
                            <span>Discount ({{ $order->coupon_code }})</span><strong>- Rs. {{ number_format($order->discount, 0) }}</strong>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2" style="font-size:13px">
                            <span>Shipping</span><strong>{{ $order->shipping_charge > 0 ? 'Rs. '.number_format($order->shipping_charge,0) : 'Free' }}</strong>
                        </div>
                        <hr style="margin:8px 0">
                        <div class="d-flex justify-content-between" style="font-size:16px;font-weight:800">
                            <span>Total</span><span style="color:var(--accent)">Rs. {{ number_format($order->total_amount, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Status -->
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-edit me-2 text-warning"></i> Update Order Status</h5></div>
            <div class="admin-card-body">
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" id="orderStatusForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Order Status</label>
                                <select name="status" class="form-select">
                                    <option value="{{ $order->status }}" selected>{{ ucfirst($order->status) }} (current)</option>
                                    @foreach($order->allowedNextStatuses() as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @if(empty($order->allowedNextStatuses()))<div class="help-text">This order is closed. No further status changes are possible.</div>
                                @elseif(in_array('cancelled', $order->allowedNextStatuses()))<div class="help-text">Cancelling returns the items to stock.</div>@endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Tracking Number</label>
                                <input type="text" name="tracking_number" class="form-control"
                                       value="{{ $order->tracking_number }}" placeholder="Courier tracking #">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Courier Name</label>
                                <input type="text" name="courier_name" class="form-control"
                                       value="{{ $order->courier_name }}" placeholder="e.g. TCS, Leopards, Swyft">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label>Tracking Note</label>
                                <textarea name="tracking_note" class="form-control" rows="2" placeholder="Status update message..."></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-admin-primary">
                                <i class="fas fa-save"></i> Update Order
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tracking Timeline -->
        <div class="admin-card">
            <div class="admin-card-header"><h5><i class="fas fa-route me-2 text-warning"></i> Order Timeline</h5></div>
            <div class="admin-card-body">
                @forelse($order->tracking->sortByDesc('created_at') as $track)
                <div class="activity-item">
                    <div class="activity-icon" style="background:rgba(242,151,31,0.1);color:var(--accent)">
                        <i class="fas fa-{{ match($track->status){
                            'pending'=>'clock','processing'=>'cog','shipped'=>'truck',
                            'delivered'=>'check-circle','cancelled'=>'times-circle',default=>'circle'
                        } }}"></i>
                    </div>
                    <div class="activity-info">
                        <div class="text">
                            <strong>{{ ucfirst($track->status) }}</strong>
                            @if($track->note) — {{ $track->note }}@endif
                        </div>
                        <div class="time">
                            {{ $track->created_at->format('d M Y, h:i A') }}
                            @if($track->updatedBy) · by {{ $track->updatedBy->name }}@endif
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">No tracking history</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right: Customer, Shipping, Payment -->
    <div class="col-lg-4">
        <!-- Customer -->
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-user me-2 text-warning"></i> Customer</h5></div>
            <div class="admin-card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--accent),#e08010);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;color:#fff">
                        {{ strtoupper(substr($order->customer_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:700">{{ $order->customer_name }}</div>
                        @if($order->user)<div style="font-size:12px;color:var(--text-muted)">Registered Customer</div>@endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2" style="font-size:13px">
                    <i class="fas fa-envelope text-warning"></i> {{ $order->customer_email }}
                </div>
                <div class="d-flex align-items-center gap-2" style="font-size:13px">
                    <i class="fas fa-phone text-warning"></i> {{ $order->customer_phone }}
                </div>
                @if($order->user)
                <a href="{{ route('admin.users.show', $order->user->id) }}" class="btn-admin-secondary mt-3 w-100 justify-content-center" style="font-size:12px">
                    <i class="fas fa-user"></i> View Profile
                </a>
                @endif
            </div>
        </div>

        <!-- Shipping -->
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-map-marker-alt me-2 text-warning"></i> Shipping Address</h5></div>
            <div class="admin-card-body" style="font-size:13px">
                <p style="margin:0">{{ $order->shipping_address }}</p>
                <p style="margin:4px 0 0;font-weight:700">{{ $order->shipping_city }}</p>
                @if($order->shipping_area)<p style="margin:2px 0 0;color:var(--text-muted)">{{ $order->shipping_area }}</p>@endif
                @if($order->tracking_number)
                <hr style="margin:12px 0">
                <div style="color:var(--text-muted)">Tracking: <strong style="color:var(--primary)">{{ $order->tracking_number }}</strong></div>
                @if($order->courier_name)<div style="color:var(--text-muted)">Courier: <strong>{{ $order->courier_name }}</strong></div>@endif
                @endif
            </div>
        </div>

        <!-- Payment -->
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-credit-card me-2 text-warning"></i> Payment</h5></div>
            <div class="admin-card-body" style="font-size:13px">
                <div class="d-flex justify-content-between mb-2">
                    <span>Method</span>
                    <strong>{{ strtoupper(str_replace('_',' ',$order->payment_method)) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Status</span>
                    <span class="status-badge {{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Amount</span>
                    <strong style="color:var(--primary);font-size:16px">Rs. {{ number_format($order->total_amount, 0) }}</strong>
                </div>
                @unless(in_array($order->status, ['cancelled','refunded']))
                <form action="{{ route('admin.orders.payment', $order->id) }}" method="POST" class="mt-3">
                    @csrf
                    <label style="font-weight:700;font-size:12px">Update payment status</label>
                    <div class="d-flex gap-2 mt-1">
                        <select name="payment_status" class="form-select">
                            @foreach(['pending','paid','failed','refunded'] as $ps)
                                <option value="{{ $ps }}" @selected($order->payment_status === $ps)>{{ ucfirst($ps) }}</option>
                            @endforeach
                        </select>
                        <button class="btn-admin-primary" type="submit">Save</button>
                    </div>
                    <div class="help-text mt-1">Mark a bank transfer as paid only after the money has been received.</div>
                </form>
                @endunless
            </div>
        </div>

        <!-- Note -->
        @if($order->customer_note)
        <div class="admin-card">
            <div class="admin-card-header"><h5><i class="fas fa-sticky-note me-2 text-warning"></i> Customer Note</h5></div>
            <div class="admin-card-body" style="font-size:13px;font-style:italic;color:var(--text-muted)">
                "{{ $order->customer_note }}"
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

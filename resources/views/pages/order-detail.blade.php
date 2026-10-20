@extends('layouts.app')

@section('meta_title', 'Order ' . $order->order_number . ' — CityDrug')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('account.orders') }}">Orders</a></li><li>{{ $order->order_number }}</li></ol><h1>Order {{ $order->order_number }}</h1></div></div>
<div class="page-body"><div class="container">
<div class="account-grid">
    @include('pages._account-nav')
    <div class="cart-layout" style="grid-template-columns:1fr">
        <div class="panel">
            <div class="panel-pad d-flex justify-content-between flex-wrap gap-2">
                <div><span class="status {{ $order->status }}">{{ $order->status }}</span> <span class="status {{ $order->payment_status }}">Payment {{ $order->payment_status }}</span></div>
                <span class="muted">Placed {{ $order->created_at->format('d M Y, H:i') }}</span>
            </div>
            <div class="table-wrap"><table class="tbl">
                <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th style="text-align:right">Total</th></tr></thead>
                <tbody>@foreach($order->items as $i)
                    <tr><td>@if($i->product)<a href="{{ route('products.show', $i->product->slug) }}">{{ $i->product_name }}</a>@else{{ $i->product_name }}@endif</td><td>Rs. {{ number_format($i->unit_price, 0) }}</td><td>{{ $i->quantity }}</td><td style="text-align:right">Rs. {{ number_format($i->total_price, 0) }}</td></tr>
                @endforeach</tbody>
            </table></div>
            <div class="panel-pad" style="max-width:340px;margin-left:auto">
                <div class="sum-row"><span>Subtotal</span><span>Rs. {{ number_format($order->subtotal, 0) }}</span></div>
                @if($order->discount > 0)<div class="sum-row"><span>Discount {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</span><span>− Rs. {{ number_format($order->discount, 0) }}</span></div>@endif
                <div class="sum-row"><span>Delivery</span><span>{{ $order->shipping_charge > 0 ? 'Rs. ' . number_format($order->shipping_charge, 0) : 'Free' }}</span></div>
                <div class="sum-row total"><span>Total</span><span>Rs. {{ number_format($order->total_amount, 0) }}</span></div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6"><div class="panel panel-pad h-100">
                <h2>Delivery</h2>
                <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                <p class="muted mb-1">{{ $order->shipping_address }}{{ $order->shipping_area ? ', ' . $order->shipping_area : '' }}, {{ $order->shipping_city }}</p>
                <p class="muted mb-0">{{ $order->customer_phone }}</p>
                @if($order->tracking_number)<p class="mt-3 mb-0">Tracking: <strong>{{ $order->tracking_number }}</strong>{{ $order->courier_name ? ' (' . $order->courier_name . ')' : '' }}</p>@endif
            </div></div>
            <div class="col-md-6"><div class="panel panel-pad h-100">
                <h2>Progress</h2>
                <ul class="timeline">
                    @foreach($order->tracking->sortByDesc('created_at') as $t)
                        <li><strong style="text-transform:capitalize">{{ $t->status }}</strong><small>{{ $t->created_at->format('d M Y, H:i') }}@if($t->note) · {{ $t->note }}@endif</small></li>
                    @endforeach
                </ul>
            </div></div>
        </div>
    </div>
</div>
</div></div>
@endsection

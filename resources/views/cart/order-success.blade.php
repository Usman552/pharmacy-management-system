@extends('layouts.app')

@section('meta_title', 'Order received — CityDrug')

@section('content')
<div class="page-body">
<div class="container" style="max-width:720px">
    <div class="panel panel-pad text-center">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--mint);color:var(--green);display:grid;place-items:center;margin:0 auto 16px;font-size:26px"><i class="fas fa-check"></i></div>
        <h1 style="font-size:30px">Thank you, your order is placed</h1>
        <p class="muted">Order <strong>{{ $order->order_number }}</strong> has been received. Payment is <strong>{{ $order->payment_status }}</strong>@if($order->payment_method === 'cod') and will be collected on delivery @else until we confirm your bank transfer @endif.</p>

        <div class="table-wrap" style="text-align:left;margin:24px 0">
            <table class="tbl">
                <thead><tr><th>Item</th><th>Qty</th><th style="text-align:right">Total</th></tr></thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td style="text-align:right">Rs. {{ number_format($item->total_price, 0) }}</td></tr>
                @endforeach
                    <tr><td colspan="2"><strong>Total</strong></td><td style="text-align:right"><strong>Rs. {{ number_format($order->total_amount, 0) }}</strong></td></tr>
                </tbody>
            </table>
        </div>
        <p class="muted" style="font-size:14px">Delivering to {{ $order->shipping_address }}, {{ $order->shipping_city }}.</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            @auth<a href="{{ route('account.order-detail', $order->order_number) }}" class="btn-cd">Track this order</a>@endauth
            <a href="{{ route('products.index') }}" class="btn-cd outline">Continue shopping</a>
        </div>
    </div>
</div>
</div>
@endsection

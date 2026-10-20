@extends('layouts.app')

@section('meta_title', 'My orders — CityDrug')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('account.index') }}">Account</a></li><li>Orders</li></ol><h1>My orders</h1></div></div>
<div class="page-body"><div class="container">
<div class="account-grid">
    @include('pages._account-nav')
    <div>
        @if($orders->isEmpty())
            <div class="empty"><i class="fas fa-box-open"></i><h2>No orders yet</h2><p>When you place an order it will appear here.</p><a href="{{ route('products.index') }}" class="btn-cd">Start shopping</a></div>
        @else
            <div class="panel"><div class="table-wrap"><table class="tbl">
                <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Status</th><th>Payment</th><th style="text-align:right">Total</th></tr></thead>
                <tbody>@foreach($orders as $o)
                    <tr>
                        <td><a href="{{ route('account.order-detail', $o->order_number) }}" style="font-weight:700">{{ $o->order_number }}</a></td>
                        <td>{{ $o->created_at->format('d M Y') }}</td>
                        <td>{{ $o->items->sum('quantity') }}</td>
                        <td><span class="status {{ $o->status }}">{{ $o->status }}</span></td>
                        <td><span class="status {{ $o->payment_status }}">{{ $o->payment_status }}</span></td>
                        <td style="text-align:right">Rs. {{ number_format($o->total_amount, 0) }}</td>
                    </tr>
                @endforeach</tbody>
            </table></div></div>
            {{ $orders->links('vendor.pagination.custom') }}
        @endif
    </div>
</div>
</div></div>
@endsection

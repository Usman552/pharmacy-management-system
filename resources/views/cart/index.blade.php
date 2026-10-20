@extends('layouts.app')

@section('meta_title', 'Your cart — CityDrug')

@section('content')
<div class="page-head">
    <div class="container">
        <ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li>Cart</li></ol>
        <h1>Your cart</h1>
    </div>
</div>

<div class="page-body">
<div class="container">
@if($lines->isEmpty())
    <div class="empty">
        <i class="fas fa-bag-shopping"></i>
        <h2>Your cart is empty</h2>
        <p>Browse the shop to add medicines and health products.</p>
        <a href="{{ route('products.index') }}" class="btn-cd">Start shopping</a>
    </div>
@else
    <div class="cart-layout">
        <div class="panel">
            @foreach($lines as $line)
                @php $p = $line->product; @endphp
                <div class="cart-line {{ $line->problem ? 'has-problem' : '' }}">
                    @if($p) @include('partials.product-image', ['product' => $p, 'size' => 'sm']) @else <div class="img-box sm"></div> @endif
                    <div class="info">
                        <h3>@if($p)<a href="{{ route('products.show', $p->slug) }}" style="color:inherit">{{ $p->name }}</a>@else Unavailable item @endif</h3>
                        <div class="muted" style="font-size:13px">
                            @if($p){{ collect([$p->brand, $p->dosage])->filter()->implode(' · ') }} @if($p->requires_prescription)<span class="badge-cd rx ms-1">Rx</span>@endif @endif
                        </div>
                        <div class="muted" style="font-size:13px">Rs. {{ number_format($line->unit_price, 0) }} each</div>
                        @if($line->problem)<div class="problem-msg"><i class="fas fa-circle-exclamation"></i> {{ $line->problem }}</div>@endif
                        <div class="row-b">
                            @if($p)
                            <div class="qty" data-qty="{{ $line->quantity }}" data-cart-id="{{ $line->cart_id }}" data-reload="1">
                                <button type="button" data-qty-step="-1" aria-label="Decrease quantity">−</button>
                                <span>{{ $line->quantity }}</span>
                                <button type="button" data-qty-step="1" aria-label="Increase quantity">+</button>
                            </div>
                            @endif
                            <span class="line-total">Rs. {{ number_format($line->line_total, 0) }}</span>
                            <button type="button" class="link-btn" data-cart-remove="{{ $line->cart_id }}" data-reload="1"><i class="fas fa-trash-can"></i> Remove</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <aside class="panel panel-pad summary" aria-label="Order summary">
            <h2>Order summary</h2>

            <form action="{{ route('cart.coupon') }}" method="POST" class="coupon-row">
                @csrf
                <input type="text" name="code" class="input" placeholder="Coupon code" value="{{ $coupon?->code }}" aria-label="Coupon code" {{ $coupon ? 'readonly' : '' }} required>
                @if($coupon)
                    <input type="hidden" name="remove" value="1">
                    <button class="btn-cd outline">Remove</button>
                @else
                    <button class="btn-cd ghost">Apply</button>
                @endif
            </form>

            <div class="sum-row"><span>Subtotal</span><span>Rs. {{ number_format($subtotal, 0) }}</span></div>
            @if($discount > 0)<div class="sum-row"><span>Discount ({{ $coupon->code }})</span><span>− Rs. {{ number_format($discount, 0) }}</span></div>@endif
            <div class="sum-row"><span>Delivery</span><span>{{ $shipping > 0 ? 'Rs. ' . number_format($shipping, 0) : 'Free' }}</span></div>
            @if($shipping > 0)<div class="muted" style="font-size:12.5px">Add Rs. {{ number_format(max(0, $freeMin - ($subtotal - $discount)), 0) }} more for free delivery.</div>@endif
            <div class="sum-row total"><span>Total</span><span>Rs. {{ number_format($total, 0) }}</span></div>

            @if($blocked)
                <div class="alert-cd error" style="margin-top:16px"><i class="fas fa-circle-exclamation"></i><span>Fix the highlighted items to continue.</span></div>
            @endif
            <div class="d-grid gap-2 mt-3">
                <a href="{{ route('checkout.index') }}" class="btn-cd lg {{ $blocked ? 'disabled' : '' }}" @if($blocked) aria-disabled="true" @endif><i class="fas fa-lock"></i> Proceed to checkout</a>
                <a href="{{ route('products.index') }}" class="btn-cd outline">Continue shopping</a>
            </div>
            <p class="muted mt-3 mb-0" style="font-size:12.5px"><i class="fas fa-shield-halved"></i> Prices and stock are confirmed again when you place the order.</p>
        </aside>
    </div>
@endif
</div>
</div>
@endsection

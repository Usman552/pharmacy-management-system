{{-- Cart drawer body. Receives CartService::totals() --}}
<div class="cart-sidebar-body">
    @forelse($lines as $line)
        @php $p = $line->product; @endphp
        <div class="mini-item">
            @if($p) @include('partials.product-image', ['product' => $p, 'size' => 'sm']) @else <div class="img-box sm"></div> @endif
            <div>
                <h3>{{ $p?->name ?? 'Unavailable item' }}</h3>
                <div class="price">Rs. {{ number_format($line->line_total, 0) }}</div>
                @if($p)
                <div class="qty mt-1" data-qty="{{ $line->quantity }}" data-cart-id="{{ $line->cart_id }}">
                    <button type="button" data-qty-step="-1" aria-label="Decrease quantity">−</button>
                    <span aria-live="polite">{{ $line->quantity }}</span>
                    <button type="button" data-qty-step="1" aria-label="Increase quantity">+</button>
                </div>
                @endif
                @if($line->problem)<div class="problem">{{ $line->problem }}</div>@endif
            </div>
            <button type="button" class="link-btn" data-cart-remove="{{ $line->cart_id }}" aria-label="Remove item"><i class="fas fa-trash-can"></i></button>
        </div>
    @empty
        <div class="empty" style="border:0;background:none;padding:48px 8px">
            <i class="fas fa-bag-shopping"></i>
            <h2>Your cart is empty</h2>
            <p>Find medicines and health products in the shop.</p>
            <a href="{{ route('products.index') }}" class="btn-cd">Browse products</a>
        </div>
    @endforelse
</div>

@if($lines->isNotEmpty())
<div class="cart-sidebar-footer">
    <div class="sum-row"><span>Subtotal</span><span>Rs. {{ number_format($subtotal, 0) }}</span></div>
    @if($discount > 0)<div class="sum-row"><span>Discount</span><span>− Rs. {{ number_format($discount, 0) }}</span></div>@endif
    <div class="sum-row"><span>Delivery</span><span>{{ $shipping > 0 ? 'Rs. ' . number_format($shipping, 0) : 'Free' }}</span></div>
    <div class="sum-row total"><span>Total</span><span>Rs. {{ number_format($total, 0) }}</span></div>
    <div class="d-grid gap-2 mt-3">
        <a href="{{ route('checkout.index') }}" class="btn-cd {{ $blocked ? 'disabled' : '' }}"><i class="fas fa-lock"></i> Checkout</a>
        <a href="{{ route('cart.index') }}" class="btn-cd outline">View full cart</a>
    </div>
</div>
@endif

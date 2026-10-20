@php
    $disc       = $product->discount_percent;
    $wishlisted = is_array(session('wishlist')) && in_array($product->id, session('wishlist'));
    $status     = $product->stockStatus();
@endphp
<article class="pc-card">
    <div class="pc-img">
        <div class="pc-badges">
            @if($disc > 0)<span class="badge-cd sale">-{{ $disc }}%</span>@endif
            @if($product->is_new && !$disc)<span class="badge-cd new">New</span>@endif
            @if($product->requires_prescription)<span class="badge-cd rx"><i class="fas fa-file-prescription"></i> Rx</span>@endif
        </div>
        <button type="button" class="pc-wish{{ $wishlisted ? ' wishlisted' : '' }}" onclick="toggleWishlist({{ $product->id }}, this)"
                aria-label="Save {{ $product->name }} to wishlist">
            <i class="{{ $wishlisted ? 'fas' : 'far' }} fa-heart"></i>
        </button>
        <a href="{{ route('products.show', $product->slug) }}" tabindex="-1" aria-hidden="true">
            @include('partials.product-image', ['product' => $product])
        </a>
    </div>
    <div class="pc-body">
        @if($product->category)<div class="pc-cat">{{ $product->category->name }}</div>@endif
        <a href="{{ route('products.show', $product->slug) }}" class="pc-name-link"><h3 class="pc-name">{{ $product->name }}</h3></a>
        <div class="pc-meta">{{ collect([$product->brand, $product->dosage])->filter()->implode(' · ') }}</div>

        <div class="pc-price-row">
            <span class="pc-price">Rs. {{ number_format($product->final_price, 0) }}</span>
            @if($disc > 0)<span class="pc-old">Rs. {{ number_format($product->price, 0) }}</span>@endif
        </div>

        <div class="pc-stock {{ $status }}">
            <i class="fas fa-circle"></i>
            @if($status === 'in') In stock
            @elseif($status === 'low') Only {{ $product->stock }} left
            @else Out of stock @endif
        </div>

        @if($status === 'out')
            <button type="button" class="btn-cd outline pc-add" disabled>Out of stock</button>
        @elseif($product->requires_prescription)
            <a href="{{ route('products.show', $product->slug) }}" class="btn-cd outline pc-add"><i class="fas fa-file-prescription"></i> Prescription needed</a>
        @else
            <button type="button" class="btn-cd pc-add" onclick="addToCart({{ $product->id }}, this)">
                <i class="fas fa-cart-plus"></i> Add to cart
            </button>
        @endif
    </div>
</article>

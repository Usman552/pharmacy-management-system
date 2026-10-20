@extends('layouts.app')

@php
    $disc    = $product->discount_percent;
    $status  = $product->stockStatus();
    $gallery = $product->gallery_urls;
    $maxQty  = $product->requires_prescription ? min($product->stock, max(1, (int) $rxAllowance)) : min($product->stock, 20);
    $specs = array_filter([
        'Brand'        => $product->brand,
        'Manufacturer' => $product->manufacturer,
        'Composition'  => $product->composition,
        'Strength'     => $product->dosage,
        'Pack size'    => $product->weight,
        'SKU'          => $product->sku,
        'Expiry'       => $product->expiry_date?->format('M Y'),
    ]);
@endphp

@section('meta_title', $product->name . ' — CityDrug')
@section('meta_desc', Str::limit(strip_tags($product->description ?: $product->name), 155))

@section('content')
<div class="page-head" style="padding:18px 0">
    <div class="container">
        <ol class="crumbs">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('products.index') }}">Shop</a></li>
            @if($product->category)<li><a href="{{ route('categories.show', $product->category->slug) }}">{{ $product->category->name }}</a></li>@endif
            <li>{{ Str::limit($product->name, 40) }}</li>
        </ol>
    </div>
</div>

<div class="page-body">
<div class="container">
    <div class="pd">
        <div class="gallery">
            <div class="main">
                @if($gallery)
                    <div class="img-box" style="background:#fff"><img id="galleryMain" src="{{ $gallery[0] }}" alt="{{ $product->name }}" width="700" height="700"></div>
                @else
                    @include('partials.product-image', ['product' => $product])
                @endif
            </div>
            @if($product->image_credit && $product->image_url)
                <p class="muted" style="font-size:12px;margin:8px 4px 0">
                    {{ $product->image_credit }}@if($product->image_credit_url && Str::startsWith($product->image_credit_url, ['https://', 'http://'])) · <a href="{{ $product->image_credit_url }}" target="_blank" rel="noopener nofollow">source</a>@endif
                </p>
            @endif
            @if(count($gallery) > 1)
            <div class="thumbs">
                @foreach($gallery as $i => $url)
                    <button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-gallery-src="{{ $url }}" aria-label="Show image {{ $i + 1 }}"><img src="{{ $url }}" alt="" loading="lazy"></button>
                @endforeach
            </div>
            @endif
        </div>

        <div>
            <div class="d-flex gap-2 flex-wrap mb-2">
                @if($product->category)<span class="badge-cd">{{ $product->category->name }}</span>@endif
                @if($disc > 0)<span class="badge-cd sale">-{{ $disc }}%</span>@endif
                @if($product->requires_prescription)<span class="badge-cd rx"><i class="fas fa-file-prescription"></i> Prescription only</span>@endif
            </div>
            <h1>{{ $product->name }}</h1>
            <div class="sub">{{ collect([$product->brand ? 'by ' . $product->brand : null, $product->dosage])->filter()->implode(' · ') }}</div>

            <div class="price-block">
                <span class="now">Rs. {{ number_format($product->final_price, 0) }}</span>
                @if($disc > 0)<span class="was">Rs. {{ number_format($product->price, 0) }}</span><span class="badge-cd sale">Save {{ $disc }}%</span>@endif
            </div>

            <div class="pc-stock {{ $status }}" style="font-size:14px">
                <i class="fas fa-circle"></i>
                @if($status === 'in') In stock @elseif($status === 'low') Only {{ $product->stock }} left @else Out of stock @endif
            </div>

            @if($product->description)<p class="prose" style="margin-top:14px">{{ $product->description }}</p>@endif

            @if($product->requires_prescription)
                <div class="rx-note">
                    <i class="fas fa-file-prescription"></i>
                    <div>
                        <strong>This medicine needs a prescription.</strong><br>
                        It can be added to your cart only after our pharmacist has verified your prescription for it.
                        @auth
                            @if($rxAllowance > 0)<br><span style="color:var(--green-ink);font-weight:700">Your verified prescription covers up to {{ $rxAllowance }} unit(s).</span>
                            @else<br><a href="{{ route('account.prescriptions') }}">Check my prescriptions</a> or <a href="{{ route('contact') }}">contact the pharmacy</a>.@endif
                        @else
                            <br><a href="{{ route('login') }}">Sign in</a> to see prescriptions linked to your account.
                        @endauth
                    </div>
                </div>
            @endif

            @if($status === 'out')
                <div class="buy-row"><button class="btn-cd lg" disabled>Out of stock</button></div>
            @elseif($product->requires_prescription && !$rxAllowance)
                <div class="buy-row"><a href="{{ route('contact') }}" class="btn-cd lg outline"><i class="fas fa-comments"></i> Ask the pharmacist</a></div>
            @else
                <div class="buy-row">
                    <div class="qty">
                        <button type="button" data-pdqty="-1" aria-label="Decrease quantity">−</button>
                        <input type="number" id="pdQty" value="1" min="1" max="{{ $maxQty }}" data-max="{{ $maxQty }}" aria-label="Quantity">
                        <button type="button" data-pdqty="1" aria-label="Increase quantity">+</button>
                    </div>
                    <button type="button" class="btn-cd lg" id="pdAdd" data-id="{{ $product->id }}"><i class="fas fa-cart-plus"></i> Add to cart</button>
                    <button type="button" class="icon-btn" style="width:52px;height:52px" onclick="toggleWishlist({{ $product->id }}, this)" aria-label="Save to wishlist"><i class="{{ $isWishlisted ? 'fas' : 'far' }} fa-heart"></i></button>
                </div>
            @endif

            @if($specs)
            <table class="spec" style="margin-top:20px">
                @foreach($specs as $k => $v)<tr><th>{{ $k }}</th><td>{{ $v }}</td></tr>@endforeach
            </table>
            @endif
        </div>
    </div>

    @if($product->details || $product->reviews->isNotEmpty())
    <div style="margin-top:48px">
        <div class="tabs-cd" role="tablist">
            @if($product->details)<button class="on" data-tab="tab-details" role="tab" aria-selected="true">Product information</button>@endif
            <button class="{{ $product->details ? '' : 'on' }}" data-tab="tab-reviews" role="tab" aria-selected="{{ $product->details ? 'false' : 'true' }}">Reviews ({{ $product->reviews->count() }})</button>
        </div>
        @if($product->details)<div class="tab-pane on prose" id="tab-details">{!! nl2br(e($product->details)) !!}</div>@endif
        <div class="tab-pane {{ $product->details ? '' : 'on' }}" id="tab-reviews">
            @forelse($product->reviews as $r)
                <div class="panel panel-pad mb-3" style="max-width:760px">
                    <strong>{{ $r->user?->name ?? 'Customer' }}</strong>
                    <span class="muted" style="font-size:13px"> · {{ $r->created_at->format('d M Y') }}</span>
                    <div style="color:#B7791F" aria-label="{{ $r->rating }} out of 5">{{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5 - $r->rating) }}</div>
                    @if($r->review)<p class="mb-0 mt-1">{{ $r->review }}</p>@endif
                </div>
            @empty
                <p class="muted">No reviews yet.</p>
            @endforelse
        </div>
    </div>
    @endif

    @if($related->isNotEmpty())
    <div style="margin-top:56px">
        <div class="section-head"><h2>Related products</h2></div>
        <div class="grid-products">@foreach($related as $product) @include('home.partials.product-card', ['product' => $product]) @endforeach</div>
    </div>
    @endif
</div>
</div>
@endsection

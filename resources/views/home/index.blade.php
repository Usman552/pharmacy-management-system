@extends('layouts.app')

@section('meta_title', 'CityDrug — Online Pharmacy | Medicines, Vitamins & Healthcare')
@section('meta_desc', 'Order medicines, vitamins and healthcare products online. Prescription medicines are dispensed only after pharmacist verification.')

@section('content')

{{-- HERO --}}
<section class="hero">
    <div class="container">
        <div class="wrap">
            <div>
                <span class="hero-pill reveal"><i class="fas fa-shield-heart"></i> Your neighbourhood pharmacy, online</span>
                <h1 class="reveal d1">Medicines and care, <em>checked by a pharmacist.</em></h1>
                <p class="lead reveal d2">Shop everyday health essentials and prescription medicines in one place. Prescription items are released only after our pharmacist has verified your prescription.</p>

                <div class="hero-cta reveal d2">
                    <a href="{{ route('products.index') }}" class="btn-cd lg"><i class="fas fa-pills"></i> Shop medicines</a>
                    <a href="#prescriptions" class="btn-cd lg outline"><i class="fas fa-file-prescription"></i> How prescriptions work</a>
                </div>

                <form action="{{ route('products.search') }}" method="GET" class="search hero-search reveal d3" role="search">
                    <input type="search" name="q" placeholder="Search for a medicine or health product" aria-label="Search products">
                    <button type="submit" aria-label="Search"><i class="fas fa-magnifying-glass"></i></button>
                </form>
                @if($categories->isNotEmpty())
                <div class="hero-tags reveal d3">
                    <span>Popular:</span>
                    @foreach($categories->sortByDesc('products_count')->take(4) as $c)
                        <a href="{{ route('categories.show', $c->slug) }}">{{ $c->name }}</a>
                    @endforeach
                </div>
                @endif

                @if($productCount > 0)
                <div class="hero-facts reveal d3">
                    <div><strong>{{ number_format($productCount) }}</strong><span>Products in stock to order</span></div>
                    <div><strong>{{ $categories->count() }}</strong><span>Health categories</span></div>
                    <div><strong>Rs. {{ number_format($freeMin, 0) }}+</strong><span>Free home delivery</span></div>
                </div>
                @endif
            </div>

            <div class="hero-photo reveal d2">
                <div class="frame">
                    <img src="{{ asset('images/site/hero-pharmacist.jpg') }}" alt="A healthcare professional in a white coat speaking with a patient" width="1400" height="1100" fetchpriority="high">
                </div>
                <div class="hero-chip a"><i class="fas fa-user-shield"></i><div><strong>Pharmacist verified</strong>Prescription medicines</div></div>
                <div class="hero-chip b"><i class="fas fa-lock"></i><div><strong>Secure checkout</strong>Prices checked on our server</div></div>
            </div>
        </div>
    </div>
</section>

{{-- TRUST --}}
<section class="trust" aria-label="Why shop with us">
    <div class="container">
        <div class="row-t">
            <div class="trust-item"><i class="fas fa-lock"></i><div><strong>Secure checkout</strong><span>Prices and stock confirmed at the moment you order</span></div></div>
            <div class="trust-item"><i class="fas fa-truck-fast"></i><div><strong>Home delivery</strong><span>Free over Rs. {{ number_format($freeMin, 0) }}, otherwise Rs. {{ number_format($flatShip, 0) }}</span></div></div>
            <div class="trust-item"><i class="fas fa-file-prescription"></i><div><strong>Prescription handling</strong><span>Reviewed by a pharmacist before anything is dispensed</span></div></div>
            <div class="trust-item"><i class="fas fa-comments"></i><div><strong>Pharmacist help</strong><span><a href="{{ route('contact') }}">Ask a question</a> before you order</span></div></div>
        </div>
    </div>
</section>

{{-- CATEGORIES --}}
@if($categories->isNotEmpty())
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Browse</span><h2>Shop by category</h2></div>
            <a href="{{ route('categories.index') }}" class="link-more">All categories <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="cat-grid">
            @foreach($categories->take(12) as $c)
                <a href="{{ route('categories.show', $c->slug) }}" class="cat-tile">
                    <span class="ic">@if($c->image_url)<img src="{{ $c->image_url }}" alt="" loading="lazy">@else<i class="fas {{ $c->icon_class }}"></i>@endif</span>
                    <strong>{{ $c->name }}</strong>
                    <small>{{ $c->products_count }} {{ Str::plural('product', $c->products_count) }}</small>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- FEATURED --}}
@if($featured->isNotEmpty())
<section class="section white">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Featured</span><h2>Featured products</h2><p>Selected by our team from the current catalogue.</p></div>
            <a href="{{ route('products.index') }}" class="link-more">View all <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="grid-products">
            @foreach($featured as $product) @include('home.partials.product-card', ['product' => $product]) @endforeach
        </div>
    </div>
</section>
@endif

{{-- PRESCRIPTIONS --}}
<section class="section" id="prescriptions">
    <div class="container">
        <div class="split">
            <div class="photo"><img src="{{ asset('images/site/pharmacy-interior.jpg') }}" alt="Inside a bright, modern pharmacy with shelves of health products" loading="lazy" width="1400" height="1050"></div>
            <div>
                <span class="eyebrow">Prescription medicines</span>
                <h2>Safe, pharmacist-checked dispensing</h2>
                <p class="muted">Some medicines can only be supplied against a valid prescription. Here is how we handle them.</p>
                <ol class="rx-steps">
                    <li><div><strong>Bring your prescription</strong><br><span class="muted">Our pharmacy team records it against your customer account.</span></div></li>
                    <li><div><strong>Pharmacist verification</strong><br><span class="muted">Each medicine, strength and quantity is checked against what was prescribed.</span></div></li>
                    <li><div><strong>Review and order</strong><br><span class="muted">Verified items appear in your account. You review them, add them to your cart and check out. Nothing is ordered for you.</span></div></li>
                </ol>
                <div class="hero-cta mt-4 mb-0">
                    @auth
                        <a href="{{ route('account.prescriptions') }}" class="btn-cd">My prescriptions</a>
                    @else
                        <a href="{{ route('register') }}" class="btn-cd">Create an account</a>
                    @endauth
                    <a href="{{ route('contact') }}" class="btn-cd outline">Talk to the pharmacy</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- BEST SELLERS / NEW / SALE: shown only when there is real data --}}
@if($bestSellers->isNotEmpty())
<section class="section white">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">Popular</span><h2>Best sellers</h2></div></div>
        <div class="grid-products">@foreach($bestSellers as $product) @include('home.partials.product-card', ['product' => $product]) @endforeach</div>
    </div>
</section>
@endif

@if($newArrivals->isNotEmpty())
<section class="section tight">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Just in</span><h2>New arrivals</h2></div>
            <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="link-more">See newest <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="grid-products">@foreach($newArrivals as $product) @include('home.partials.product-card', ['product' => $product]) @endforeach</div>
    </div>
</section>
@endif

@if($onSale->isNotEmpty())
<section class="section tight white">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Offers</span><h2>Reduced prices</h2></div>
            <a href="{{ route('products.index', ['on_sale' => 1]) }}" class="link-more">All offers <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="grid-products">@foreach($onSale as $product) @include('home.partials.product-card', ['product' => $product]) @endforeach</div>
    </div>
</section>
@endif

{{-- SERVICES --}}
<section class="section">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">What we offer</span><h2>Pharmacy services</h2></div></div>
        <div class="svc-grid">
            <div class="svc"><i class="ic fas fa-file-prescription"></i><h3>Prescription medicines</h3><p>Pharmacist-verified, released to your account for you to review before checkout.</p><a href="{{ route('about') }}#prescriptions">How it works <i class="fas fa-arrow-right"></i></a></div>
            <div class="svc"><i class="ic fas fa-pills"></i><h3>Over-the-counter products</h3><p>Everyday medicines, vitamins and personal care that do not need a prescription.</p><a href="{{ route('products.index', ['no_rx' => 1]) }}">Shop now <i class="fas fa-arrow-right"></i></a></div>
            <div class="svc"><i class="ic fas fa-truck-fast"></i><h3>Home delivery</h3><p>Delivery to your door. Free on orders over Rs. {{ number_format($freeMin, 0) }}.</p><a href="{{ route('contact') }}">Delivery questions <i class="fas fa-arrow-right"></i></a></div>
        </div>
    </div>
</section>

{{-- BLOG --}}
@if($posts->isNotEmpty())
<section class="section white">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Health blog</span><h2>Latest articles</h2></div>
            <a href="{{ route('blog.index') }}" class="link-more">All articles <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="post-grid">
            @foreach($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="post">
                    <div class="thumb">
                        @if($post->image && is_file(public_path('images/blogs/' . basename($post->image))))
                            <img src="{{ asset('images/blogs/' . basename($post->image)) }}" alt="" loading="lazy">
                        @else<i class="fas fa-newspaper"></i>@endif
                    </div>
                    <div class="body">
                        <span class="badge-cd" style="align-self:flex-start">{{ $post->category ?? 'Health' }}</span>
                        <h3>{{ $post->title }}</h3>
                        <p>{{ Str::limit($post->excerpt ?? strip_tags($post->content), 110) }}</p>
                        <span class="meta">{{ optional($post->published_at)->format('d M Y') }} · {{ $post->reading_time }} min read</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="section">
    <div class="container">
        <div class="cta-band">
            <div>
                <h2>Questions about a medicine?</h2>
                <p>Send our pharmacy team a message before you order. We will help you find the right product.</p>
            </div>
            <a href="{{ route('contact') }}" class="btn-cd light lg"><i class="fas fa-paper-plane"></i> Contact the pharmacy</a>
        </div>
    </div>
</section>
@endsection

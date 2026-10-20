@php
    $siteName  = $site['site_name'] ?? 'CityDrug';
    $sitePhone = trim($site['site_phone'] ?? '');
    $siteEmail = trim($site['site_email'] ?? '');
    $siteAddr  = trim(implode(', ', array_filter([$site['site_address'] ?? '', $site['site_city'] ?? ''])));
    $waNumber  = preg_replace('/\D+/', '', $site['whatsapp'] ?? '');
    $telHref   = $sitePhone ? 'tel:' . preg_replace('/[^\d+]/', '', $sitePhone) : null;
    $freeMin   = is_numeric($site['free_shipping_min'] ?? null) ? (float) $site['free_shipping_min'] : 999.0;
    $logoFile  = $site['site_logo'] ?? null;
    $logoUrl   = $logoFile && is_file(public_path('images/' . basename($logoFile))) ? asset('images/' . basename($logoFile)) : null;
    $announce  = ($site['show_announcement'] ?? '1') === '1'
        ? ($site['announcement_text'] ?? 'Free delivery on orders over Rs. ' . number_format($freeMin, 0))
        : null;
    $metaDesc  = trim($__env->yieldContent('meta_desc')) ?: 'CityDrug online pharmacy: medicines, vitamins and healthcare products, with pharmacist-verified prescriptions and secure checkout.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('meta_title', $siteName . ' — Online Pharmacy')</title>
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="theme-color" content="#087F5B">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="@yield('og_title', $siteName . ' — Online Pharmacy')">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/citydrug.css') }}?v={{ filemtime(public_path('css/citydrug.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<a href="#main" class="skip-link">Skip to content</a>

<header class="site-header">
    <div class="container">
        <div class="bar">

            <a href="{{ route('home') }}" class="brand" aria-label="{{ $siteName }} home">
                @if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $siteName }}">@else
                    <span class="brand-mark"><i class="fas fa-staff-snake"></i></span>
                    <span>City<b>Drug</b></span>
                @endif
            </a>

            <nav class="primary-nav" aria-label="Main">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">Shop</a>
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">Categories</a>
                <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'active' : '' }}">Health blog</a>
                <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
            </nav>

            <div class="header-actions">
                <button type="button" class="h-link hide-sm" id="searchToggle" aria-label="Search" aria-expanded="false" aria-controls="searchPanel"><span class="ic"><i class="fas fa-magnifying-glass"></i></span></button>
                <a href="{{ route('wishlist.index') }}" class="h-link hide-sm" aria-label="Wishlist">
                    <span class="ic"><i class="far fa-heart"></i><span class="h-badge wishlist-count" @if(!$wishCount) style="display:none" @endif>{{ $wishCount ?: '' }}</span></span>
                </a>
                @auth
                    <a href="{{ route('account.index') }}" class="h-link hide-sm" aria-label="My account"><span class="ic"><i class="far fa-user"></i></span></a>
                @else
                    <a href="{{ route('login') }}" class="h-link hide-sm" aria-label="Sign in"><span class="ic"><i class="far fa-user"></i></span></a>
                @endauth
                <a href="{{ route('cart.index') }}" class="h-link cart-btn open-cart" aria-label="Open cart">
                    <span class="ic"><i class="fas fa-bag-shopping"></i><span class="h-badge cart-count" @if(!$cartCount) style="display:none" @endif>{{ $cartCount ?: '' }}</span></span>
                </a>
            </div>
            <button class="icon-btn menu-btn" id="mobileMenuBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav"><i class="fas fa-bars"></i></button>
        </div>
    </div>

    <div class="search-panel" id="searchPanel">
        <div class="container">
            <form action="{{ route('products.search') }}" method="GET" class="search" role="search" style="max-width:none">
                <select name="category" aria-label="Category">
                    <option value="">All</option>
                    @foreach($navCategories as $c)
                        <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <input type="search" name="q" id="searchInput" value="{{ request('q') }}" placeholder="Search medicines, brands, health products" aria-label="Search products">
                <button type="submit" aria-label="Search"><i class="fas fa-magnifying-glass"></i></button>
            </form>
        </div>
    </div>
</header>

{{-- Mobile navigation --}}
<div class="drawer-overlay" id="mobileNavOverlay"></div>
<aside class="mobile-nav" id="mobileNav" aria-label="Menu">
    <header>
        <span class="brand" style="font-size:20px">City<b>Drug</b></span>
        <button class="icon-btn" id="mobileNavClose" aria-label="Close menu"><i class="fas fa-xmark"></i></button>
    </header>
    <div class="mobile-search">
        <form action="{{ route('products.search') }}" method="GET" class="search" role="search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search medicines" aria-label="Search products">
            <button type="submit" aria-label="Search"><i class="fas fa-magnifying-glass"></i></button>
        </form>
    </div>
    <nav>
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-house"></i>Home</a>
        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="fas fa-pills"></i>Shop all</a>
        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="fas fa-grip"></i>Categories</a>
        <a href="{{ route('blog.index') }}"><i class="fas fa-newspaper"></i>Health blog</a>
        <a href="{{ route('about') }}"><i class="fas fa-circle-info"></i>About</a>
        <a href="{{ route('contact') }}"><i class="fas fa-envelope"></i>Contact</a>
        <hr>
        <a href="{{ route('wishlist.index') }}"><i class="far fa-heart"></i>Wishlist <span class="h-badge wishlist-count" style="position:static;margin-left:auto;{{ $wishCount ? '' : 'display:none' }}">{{ $wishCount ?: '' }}</span></a>
        @auth
            <a href="{{ route('account.index') }}"><i class="far fa-user"></i>My account</a>
            <a href="{{ route('account.orders') }}"><i class="fas fa-box"></i>My orders</a>
            <a href="{{ route('account.prescriptions') }}"><i class="fas fa-file-prescription"></i>My prescriptions</a>
            <form action="{{ route('logout') }}" method="POST">@csrf
                <a href="#" onclick="this.closest('form').submit();return false;"><i class="fas fa-right-from-bracket"></i>Sign out</a>
            </form>
        @else
            <a href="{{ route('login') }}"><i class="fas fa-right-to-bracket"></i>Sign in</a>
            <a href="{{ route('register') }}"><i class="fas fa-user-plus"></i>Create account</a>
        @endauth
    </nav>
</aside>

{{-- Flash messages --}}
@if(session('success') || session('error') || session('warning'))
<div class="container">
    @if(session('success'))<div class="alert-cd success" role="status" data-autohide><i class="fas fa-circle-check"></i><span>{{ session('success') }}</span></div>@endif
    @if(session('error'))<div class="alert-cd error" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ session('error') }}</span></div>@endif
    @if(session('warning'))<div class="alert-cd warn" role="alert"><i class="fas fa-triangle-exclamation"></i><span>{{ session('warning') }}</span></div>@endif
</div>
@endif

<main id="main">
    @yield('content')
</main>

{{-- Cart drawer --}}
<div class="cart-overlay" id="cartOverlay"></div>
<aside class="cart-sidebar" id="cartSidebar" aria-label="Shopping cart" role="dialog" aria-modal="true">
    <div class="cart-sidebar-header">
        <h2>Your cart</h2>
        <button class="icon-btn close-cart" aria-label="Close cart"><i class="fas fa-xmark"></i></button>
    </div>
    <div id="cartBody" style="display:flex;flex-direction:column;flex:1;min-height:0">
        @include('cart.partials.sidebar-items', app(\App\Services\CartService::class)->totals())
    </div>
</aside>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a href="{{ route('home') }}" class="brand"><span class="brand-mark"><i class="fas fa-staff-snake"></i></span><span>City<b>Drug</b></span></a>
                <p style="max-width:340px;font-size:14px">{{ $site['site_tagline'] ?? 'Medicines, vitamins and everyday healthcare, with prescription medicines released only after pharmacist verification.' }}</p>
                <div class="social">
                    @if($waNumber)<a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>@endif
                    @if($siteEmail)<a href="mailto:{{ $siteEmail }}" aria-label="Email"><i class="fas fa-envelope"></i></a>@endif
                    @if($telHref)<a href="{{ $telHref }}" aria-label="Call"><i class="fas fa-phone"></i></a>@endif
                </div>
            </div>
            <div>
                <h3>Shop</h3>
                <ul>
                    <li><a href="{{ route('products.index') }}">All products</a></li>
                    @foreach($navCategories->take(5) as $c)
                        <li><a href="{{ route('categories.show', $c->slug) }}">{{ $c->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3>Customer</h3>
                <ul>
                    @auth
                        <li><a href="{{ route('account.index') }}">My account</a></li>
                        <li><a href="{{ route('account.orders') }}">My orders</a></li>
                        <li><a href="{{ route('account.prescriptions') }}">My prescriptions</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Sign in</a></li>
                        <li><a href="{{ route('register') }}">Create account</a></li>
                    @endauth
                    <li><a href="{{ route('cart.index') }}">Cart</a></li>
                    <li><a href="{{ route('blog.index') }}">Health blog</a></li>
                    <li><a href="{{ route('about') }}">About</a></li>
                </ul>
            </div>
            <div>
                <h3>Contact</h3>
                <ul class="footer-contact">
                    @if($siteAddr)<li><i class="fas fa-location-dot"></i><span>{{ $siteAddr }}</span></li>@endif
                    @if($telHref)<li><i class="fas fa-phone"></i><a href="{{ $telHref }}">{{ $sitePhone }}</a></li>@endif
                    @if($siteEmail)<li><i class="fas fa-envelope"></i><a href="mailto:{{ $siteEmail }}">{{ $siteEmail }}</a></li>@endif
                    <li><i class="fas fa-paper-plane"></i><a href="{{ route('contact') }}">Send us a message</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</span>
            <a href="{{ route('sitemap') }}">Sitemap</a>
        </div>
    </div>
</footer>

@if($waNumber)
<a href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode('Hello ' . $siteName . '! I need a free consultation.') }}" class="wa-float" target="_blank" rel="noopener" aria-label="Free consultation on WhatsApp">
    <i class="fab fa-whatsapp"></i><span>Free consultation</span>
</a>
@endif
<button class="back-to-top" id="backToTop" aria-label="Back to top"><i class="fas fa-arrow-up"></i></button>

<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@stack('scripts')
</body>
</html>

@extends('layouts.app')

@php
    $title = $currentCategory ? $currentCategory->name : (request('q') ? 'Results for "' . request('q') . '"' : 'All products');
    $active = collect([
        request('q') ? ['Search: ' . request('q'), url_without(['q', 'page'])] : null,
        request('brand') ? ['Brand: ' . request('brand'), url_without(['brand', 'page'])] : null,
        request('min_price') !== null && request('min_price') !== '' ? ['From Rs. ' . request('min_price'), url_without(['min_price', 'page'])] : null,
        request('max_price') !== null && request('max_price') !== '' ? ['Up to Rs. ' . request('max_price'), url_without(['max_price', 'page'])] : null,
        request('on_sale') ? ['Reduced price', url_without(['on_sale', 'page'])] : null,
        request('in_stock') ? ['In stock', url_without(['in_stock', 'page'])] : null,
        request('no_rx') ? ['No prescription needed', url_without(['no_rx', 'page'])] : null,
    ])->filter();
    $base = $currentCategory ? route('categories.show', $currentCategory->slug) : route('products.index');
@endphp

@section('meta_title', $title . ' — CityDrug')
@section('meta_desc', $currentCategory?->description ?: 'Browse medicines, vitamins and healthcare products at CityDrug.')

@section('content')
<div class="page-head">
    <div class="container">
        <ol class="crumbs">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('products.index') }}">Shop</a></li>
            @if($currentCategory)<li>{{ $currentCategory->name }}</li>@endif
        </ol>
        <h1>{{ $title }}</h1>
        @if($currentCategory?->description)<p>{{ $currentCategory->description }}</p>@endif
    </div>
</div>

<div class="page-body">
<div class="container">
    <button class="btn-cd ghost mb-3 filter-toggle" id="filterToggle" aria-expanded="false" aria-controls="filters"><i class="fas fa-sliders"></i> Filters</button>

    <div class="shop-grid">
        <aside class="filters" id="filters" aria-label="Filters">
            <form method="GET" action="{{ $base }}">
                @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif

                <div class="grp">
                    <h2>Categories</h2>
                    <ul class="filter-list">
                        <li><a href="{{ route('products.index', request()->only('q')) }}" class="{{ !$currentCategory ? 'active' : '' }}">All products</a></li>
                        @foreach($categories as $c)
                            <li><a href="{{ route('categories.show', $c->slug) }}{{ request('q') ? '?q=' . urlencode(request('q')) : '' }}" class="{{ $currentCategory?->id === $c->id ? 'active' : '' }}">{{ $c->name }} <small>{{ $c->products_count }}</small></a></li>
                        @endforeach
                    </ul>
                </div>

                @if($brands->isNotEmpty())
                <div class="grp field" style="margin:0">
                    <h2>Brand</h2>
                    <select name="brand" aria-label="Brand">
                        <option value="">All brands</option>
                        @foreach($brands as $b)<option value="{{ $b }}" @selected(request('brand') === $b)>{{ $b }}</option>@endforeach
                    </select>
                </div>
                @endif

                <div class="grp">
                    <h2>Price (Rs.)</h2>
                    <div class="price-row">
                        <input type="number" min="0" name="min_price" class="input" placeholder="Min" value="{{ request('min_price') }}" aria-label="Minimum price">
                        <input type="number" min="0" name="max_price" class="input" placeholder="Max" value="{{ request('max_price') }}" aria-label="Maximum price">
                    </div>
                </div>

                <div class="grp" style="display:grid;gap:10px">
                    <label class="check"><input type="checkbox" name="in_stock" value="1" @checked(request('in_stock'))> In stock only</label>
                    <label class="check"><input type="checkbox" name="on_sale" value="1" @checked(request('on_sale'))> Reduced price</label>
                    <label class="check"><input type="checkbox" name="no_rx" value="1" @checked(request('no_rx'))> No prescription needed</label>
                </div>

                <div style="display:grid;gap:8px">
                    <button class="btn-cd">Apply filters</button>
                    @if($active->isNotEmpty())<a href="{{ $base }}" class="btn-cd outline">Clear all</a>@endif
                </div>
            </form>
        </aside>

        <section aria-label="Products">
            <div class="toolbar">
                <div class="count">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</div>
                <form method="GET" action="{{ $base }}">
                    @foreach(request()->except(['sort', 'page']) as $k => $v)
                        @if(!is_array($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                    @endforeach
                    <label class="sr-only" for="sort">Sort by</label>
                    <select name="sort" id="sort" class="input" data-autosubmit>
                        @foreach(['featured' => 'Featured', 'newest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'name' => 'Name A–Z'] as $v => $l)
                            <option value="{{ $v }}" @selected(request('sort', 'featured') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if($active->isNotEmpty())
            <div class="chips">
                @foreach($active as [$label, $href])
                    <span class="chip">{{ $label }} <a href="{{ $href }}" aria-label="Remove filter {{ $label }}"><i class="fas fa-xmark"></i></a></span>
                @endforeach
            </div>
            @endif

            @if($products->isEmpty())
                <div class="empty">
                    <i class="fas fa-magnifying-glass"></i>
                    <h2>No products found</h2>
                    <p>Try a different spelling, or remove some filters.</p>
                    <a href="{{ route('products.index') }}" class="btn-cd">View all products</a>
                </div>
            @else
                <div class="grid-products" style="--cols:3">
                    @foreach($products as $product) @include('home.partials.product-card', ['product' => $product]) @endforeach
                </div>
                {{ $products->onEachSide(1)->links('vendor.pagination.custom') }}
            @endif
        </section>
    </div>
</div>
</div>
@endsection

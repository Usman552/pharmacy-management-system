@extends('layouts.app')

@section('meta_title', 'Shop by category — CityDrug')

@section('content')
<div class="page-head">
    <div class="container">
        <ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li>Categories</li></ol>
        <h1>Shop by category</h1>
        <p>Find what you need by browsing the pharmacy sections.</p>
    </div>
</div>
<div class="page-body">
    <div class="container">
        @if($categories->isEmpty())
            <div class="empty"><i class="fas fa-box-open"></i><h2>No categories yet</h2><p>Please check back soon.</p></div>
        @else
            <div class="cat-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr))">
                @foreach($categories as $c)
                    <a href="{{ route('categories.show', $c->slug) }}" class="cat-tile">
                        <span class="ic">@if($c->image_url)<img src="{{ $c->image_url }}" alt="" loading="lazy">@else<i class="fas {{ $c->icon_class }}"></i>@endif</span>
                        <strong>{{ $c->name }}</strong>
                        <small>{{ $c->active_products_count }} {{ Str::plural('product', $c->active_products_count) }}</small>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

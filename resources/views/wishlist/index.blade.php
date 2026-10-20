@extends('layouts.app')

@section('meta_title', 'Wishlist — CityDrug')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li>Wishlist</li></ol><h1>My wishlist</h1></div></div>
<div class="page-body"><div class="container">
@if($products->isEmpty())
    <div class="empty"><i class="far fa-heart"></i><h2>Your wishlist is empty</h2><p>Tap the heart on any product to save it here.</p><a href="{{ route('products.index') }}" class="btn-cd">Browse products</a></div>
@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="muted">{{ $products->count() }} saved {{ Str::plural('item', $products->count()) }}</span>
        <form action="{{ route('wishlist.clear') }}" method="POST">@csrf<button class="btn-cd sm danger">Clear all</button></form>
    </div>
    <div class="grid-products">
        @foreach($products as $product)
            <div>
                @include('home.partials.product-card', ['product' => $product])
                <div class="d-flex gap-2 mt-2">
                    @if($product->stock > 0 && !$product->requires_prescription)
                    <form action="{{ route('wishlist.move-to-cart', $product->id) }}" method="POST" class="flex-fill">@csrf<button class="btn-cd sm ghost block">Move to cart</button></form>
                    @endif
                    <form action="{{ route('wishlist.remove', $product->id) }}" method="POST" class="flex-fill">@csrf @method('DELETE')<button class="btn-cd sm outline block">Remove</button></form>
                </div>
            </div>
        @endforeach
    </div>
@endif
</div></div>
@endsection

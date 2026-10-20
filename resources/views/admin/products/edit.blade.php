@extends('admin.layouts.app')
@section('meta_title', 'Edit Product: ' . $product->name)

@section('content')
{{-- Edit product page extends the same create form --}}
@include('admin.products.create')
@endsection

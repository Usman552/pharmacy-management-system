@extends('admin.layouts.app')
@section('meta_title', 'Edit Category: ' . $category->name)
@section('content')
@include('admin.categories.create')
@endsection

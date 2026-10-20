@extends('layouts.app')

@section('meta_title', 'Health blog — CityDrug')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li>Health blog</li></ol><h1>Health blog</h1><p>Articles from the CityDrug team.</p></div></div>
<div class="page-body"><div class="container">
    <div class="toolbar">
        <div class="chips mb-0">
            <a class="chip" style="{{ request('category') ? 'background:#fff;border:1px solid var(--line)' : '' }}" href="{{ route('blog.index') }}">All</a>
            @foreach($categories as $c)<a class="chip" style="{{ request('category') === $c ? '' : 'background:#fff;border:1px solid var(--line)' }}" href="{{ route('blog.index', ['category' => $c]) }}">{{ $c }}</a>@endforeach
        </div>
        <form action="{{ route('blog.index') }}" method="GET" class="search" style="max-width:320px" role="search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search articles" aria-label="Search articles">
            <button type="submit" aria-label="Search"><i class="fas fa-magnifying-glass"></i></button>
        </form>
    </div>

    @if($blogs->isEmpty())
        <div class="empty"><i class="fas fa-newspaper"></i><h2>No articles found</h2><p>Check back soon, or try a different search.</p></div>
    @else
        <div class="post-grid">
            @foreach($blogs as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="post">
                    <div class="thumb">@if($post->image && is_file(public_path('images/blogs/' . basename($post->image))))<img src="{{ asset('images/blogs/' . basename($post->image)) }}" alt="" loading="lazy">@else<i class="fas fa-newspaper"></i>@endif</div>
                    <div class="body">
                        <span class="badge-cd" style="align-self:flex-start">{{ $post->category }}</span>
                        <h3>{{ $post->title }}</h3>
                        <p>{{ Str::limit($post->excerpt ?? strip_tags($post->content), 120) }}</p>
                        <span class="meta">{{ optional($post->published_at)->format('d M Y') }} · {{ $post->reading_time }} min read</span>
                    </div>
                </a>
            @endforeach
        </div>
        {{ $blogs->withQueryString()->links('vendor.pagination.custom') }}
    @endif
</div></div>
@endsection

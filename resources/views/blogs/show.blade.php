@extends('layouts.app')

@section('meta_title', ($blog->meta_title ?: $blog->title) . ' — CityDrug')
@section('meta_desc', Str::limit($blog->meta_description ?: strip_tags($blog->excerpt ?: $blog->content), 155))

@section('content')
<div class="page-head"><div class="container" style="max-width:820px">
    <ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('blog.index') }}">Health blog</a></li><li>{{ Str::limit($blog->title, 40) }}</li></ol>
    <span class="badge-cd mt-2">{{ $blog->category }}</span>
    <h1>{{ $blog->title }}</h1>
    <p>{{ optional($blog->published_at)->format('d M Y') }} · {{ $blog->reading_time }} min read @if($blog->author)· {{ $blog->author->name }}@endif</p>
</div></div>
<div class="page-body"><div class="container" style="max-width:820px">
    @if($blog->image && is_file(public_path('images/blogs/' . basename($blog->image))))
        <img src="{{ asset('images/blogs/' . basename($blog->image)) }}" alt="" style="border-radius:var(--radius-lg);margin-bottom:28px;width:100%">
    @endif
    <article class="prose" style="max-width:none;font-size:16.5px;line-height:1.8">
        {!! strip_tags($blog->content, '<p><br><strong><em><b><i><u><ul><ol><li><h2><h3><h4><blockquote><a><img>') !!}
    </article>
    <p class="muted mt-4" style="font-size:13px;border-top:1px solid var(--line);padding-top:16px">This article is general information, not medical advice. Speak to a doctor or pharmacist about your own situation.</p>

    @if($related->isNotEmpty())
    <div class="section-head mt-5"><h2>More to read</h2></div>
    <div class="post-grid">@foreach($related as $post)
        <a href="{{ route('blog.show', $post->slug) }}" class="post"><div class="body"><h3>{{ $post->title }}</h3><span class="meta">{{ optional($post->published_at)->format('d M Y') }}</span></div></a>
    @endforeach</div>
    @endif
</div></div>
@endsection

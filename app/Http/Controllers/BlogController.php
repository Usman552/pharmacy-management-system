<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::published()->with('author')->latest('published_at');

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->q}%")
                  ->orWhere('excerpt', 'like', "%{$request->q}%");
            });
        }

        $blogs    = $query->paginate(9);
        $featured = Blog::published()->featured()->latest()->take(3)->get();
        $categories = Blog::published()->select('category')->distinct()->pluck('category');

        return view('blogs.index', compact('blogs', 'featured', 'categories'));
    }

    public function show($slug)
    {
        $blog = Blog::published()->where('slug', $slug)->firstOrFail();
        $blog->increment('views');

        $related = Blog::published()
            ->where('id', '!=', $blog->id)
            ->where('category', $blog->category)
            ->latest()->take(3)->get();

        return view('blogs.show', compact('blog', 'related'));
    }
}

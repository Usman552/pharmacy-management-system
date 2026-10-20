<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::with('author')->latest();

        if ($request->status === 'published') $query->where('is_published', true);
        if ($request->status === 'draft')     $query->where('is_published', false);
        if ($request->q) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $blogs = $query->paginate(15);
        return view('admin.blogs.index', compact('blogs'));
    }

    public function create()
    {
        $categories = ['Health Tips', 'Medicines', 'Nutrition', 'Wellness', 'Baby Care', 'Fitness', 'Herbal'];
        return view('admin.blogs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
        ]);

        $data = $request->only([
            'title', 'excerpt', 'content', 'category',
            'meta_title', 'meta_description', 'meta_keywords',
            'is_featured',
        ]);

        $data['slug']         = Str::slug($request->title) . '-' . Str::random(4);
        $data['author_id']    = auth()->id();
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured']  = $request->boolean('is_featured');
        $data['tags']         = $request->tags
            ? array_filter(array_map('trim', explode(',', $request->tags)))
            : [];

        // Calculate reading time
        $words = str_word_count(strip_tags($data['content']));
        $data['reading_time'] = max(1, ceil($words / 200));

        if ($data['is_published']) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fn   = 'blog_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/blogs'), $fn);
            $data['image'] = $fn;
        }

        Blog::create($data);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post created successfully!');
    }

    public function edit(Blog $blog)
    {
        $categories = ['Health Tips', 'Medicines', 'Nutrition', 'Wellness', 'Baby Care', 'Fitness', 'Herbal'];
        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title'   => 'required|max:255',
            'content' => 'required',
        ]);

        $data = $request->only([
            'title', 'excerpt', 'content', 'category',
            'meta_title', 'meta_description', 'meta_keywords',
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured']  = $request->boolean('is_featured');
        $data['tags']         = $request->tags
            ? array_filter(array_map('trim', explode(',', $request->tags)))
            : [];

        $words = str_word_count(strip_tags($data['content']));
        $data['reading_time'] = max(1, ceil($words / 200));

        if ($data['is_published'] && !$blog->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fn   = 'blog_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/blogs'), $fn);
            $data['image'] = $fn;
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post updated successfully!');
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        return back()->with('success', 'Blog post deleted.');
    }
}

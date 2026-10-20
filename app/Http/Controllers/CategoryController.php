<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('activeProducts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /** A category is the shop listing with that category pre-selected. */
    public function show($slug, Request $request)
    {
        Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return app(ProductController::class)->index($request->merge(['category' => $slug]));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->orderBy('name')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    private function rules(?Category $category = null): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category?->id)],
            'description' => 'nullable|string|max:1000',
            'icon'        => 'nullable|string|max:20',
            'color'       => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order'  => 'nullable|integer|min:0|max:9999',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    private function saveImage(Request $request, ?Category $existing = null): ?string
    {
        if (! $request->hasFile('image')) {
            return $existing?->image;
        }
        if ($existing?->image && is_file(public_path('images/categories/' . basename($existing->image)))) {
            @unlink(public_path('images/categories/' . basename($existing->image)));
        }
        $fn = time() . '_' . Str::slug($request->name) . '.' . $request->file('image')->extension();
        $request->file('image')->move(public_path('images/categories'), $fn);
        return $fn;
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $data = $request->only(['name', 'description', 'icon', 'color', 'sort_order']);
        $slug = $base = Str::slug($request->name) ?: 'category';
        for ($i = 1; Category::where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }
        $data['slug']       = $slug;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = $request->boolean('is_active', true);
        $data['image']      = $this->saveImage($request);

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category "' . $request->name . '" created.');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate($this->rules($category));

        $data = $request->only(['name', 'description', 'icon', 'color', 'sort_order']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = $request->boolean('is_active');
        $data['image']      = $this->saveImage($request, $category);

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Request $request, Category $category)
    {
        $count = $category->products()->count();

        if ($count > 0) {
            // Safe reassignment: move products to another category first.
            $target = $request->filled('move_to') ? Category::where('id', '!=', $category->id)->find($request->move_to) : null;
            if (! $target) {
                return back()->with('error', "\"{$category->name}\" still has {$count} product(s). Move them to another category before deleting it.");
            }
            $category->products()->update(['category_id' => $target->id]);
        }

        if ($category->image && is_file(public_path('images/categories/' . basename($category->image)))) {
            @unlink(public_path('images/categories/' . basename($category->image)));
        }
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}

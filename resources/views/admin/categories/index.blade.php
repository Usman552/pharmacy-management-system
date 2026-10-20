{{-- ========== CATEGORIES INDEX ========== --}}
@extends('admin.layouts.app')
@section('meta_title', 'Categories')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Categories</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Categories</li>
        </ol></nav>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.categories.create') }}" class="btn-admin-primary">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>
</div>

<div class="admin-card">
    <div style="overflow-x:auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td>
                        <div style="width:44px;height:44px;border-radius:10px;background:{{ $cat->color ?? '#0d6efd' }}18;display:flex;align-items:center;justify-content:center;font-size:22px">
                            {{ $cat->icon ?? '📦' }}
                        </div>
                    </td>
                    <td>
                        <strong>{{ $cat->name }}</strong>
                        @if($cat->image)
                        <div style="font-size:11px;color:var(--text-muted)"><i class="fas fa-image"></i> Has image</div>
                        @endif
                    </td>
                    <td><code style="background:var(--body-bg);padding:3px 8px;border-radius:5px;font-size:12px">{{ $cat->slug }}</code></td>
                    <td><span style="font-weight:700">{{ $cat->products_count }}</span></td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input status-toggle" type="checkbox"
                                   data-id="{{ $cat->id }}" data-model="category"
                                   {{ $cat->is_active ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.categories.edit', $cat->id) }}" class="btn-admin-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('categories.show', $cat->slug) }}" target="_blank" class="btn-admin-view">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" style="margin:0" class="d-flex gap-1"
                                  onsubmit="return confirm('Delete {{ addslashes($cat->name) }}?')">
                                @csrf @method('DELETE')
                                @if($cat->products_count > 0)
                                <select name="move_to" class="form-select" style="min-height:34px;padding:2px 8px;width:160px;font-size:12px" aria-label="Move {{ $cat->products_count }} products to" required>
                                    <option value="">Move products to...</option>
                                    @foreach(\App\Models\Category::where('id', '!=', $cat->id)->orderBy('name')->get() as $target)<option value="{{ $target->id }}">{{ $target->name }}</option>@endforeach
                                </select>
                                @endif
                                <button type="submit" class="btn-admin-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">
                    <i class="fas fa-tags fa-3x d-block mb-3 opacity-25"></i>
                    No categories yet. <a href="{{ route('admin.categories.create') }}" class="text-warning">Add first category</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-card-footer">
        {{ $categories->links() }}
    </div>
</div>
@endsection

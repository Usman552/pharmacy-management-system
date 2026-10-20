@extends('admin.layouts.app')
@section('meta_title', isset($category) ? 'Edit Category' : 'Add Category')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">{{ isset($category) ? 'Edit Category' : 'Add Category' }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
            <li class="breadcrumb-item active">{{ isset($category) ? 'Edit' : 'Add' }}</li>
        </ol></nav>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.categories.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ isset($category) ? route('admin.categories.update', $category->id) : route('admin.categories.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($category)) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header"><h5>Category Information</h5></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Category Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ old('name', $category->name ?? '') }}"
                                       placeholder="e.g. Medicines">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="admin-form-group">
                                <label>Icon (Emoji)</label>
                                <input type="text" name="icon" class="form-control"
                                       value="{{ old('icon', $category->icon ?? '') }}"
                                       placeholder="💊" maxlength="5"
                                       style="font-size:24px;text-align:center">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="admin-form-group">
                                <label>Color</label>
                                <input type="color" name="color" class="form-control form-control-color w-100"
                                       value="{{ old('color', $category->color ?? '#0d6efd') }}"
                                       style="height:42px">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"
                                          placeholder="Category description...">{{ old('description', $category->description ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" min="0"
                                       value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group" style="padding-top:28px">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label style="margin:0">Active (visible on website)</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                               {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5>Category Image</h5></div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <div class="img-upload-box" onclick="document.getElementById('catImage').click()">
                            <i class="fas fa-image"></i>
                            <p>Upload category image<br><small class="upload-btn">PNG, JPG — 800×600 recommended</small></p>
                        </div>
                        <input type="file" id="catImage" name="image" accept="image/*" class="img-upload-input d-none">
                        @if(isset($category) && $category->image)
                            <img src="{{ asset('images/categories/'.$category->image) }}" class="img-preview mt-2" style="display:block;max-height:160px;object-fit:cover;border-radius:8px;width:100%">
                        @else
                            <img class="img-preview mt-2">
                        @endif
                    </div>
                </div>
            </div>
            <div class="admin-card">
                <div class="admin-card-body">
                    <button type="submit" class="btn-admin-primary w-100 justify-content-center" style="padding:13px">
                        <i class="fas fa-save"></i>
                        {{ isset($category) ? 'Update Category' : 'Create Category' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

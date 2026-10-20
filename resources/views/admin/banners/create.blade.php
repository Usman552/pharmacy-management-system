@extends('admin.layouts.app')
@section('meta_title', isset($banner) ? 'Edit Banner' : 'Add Banner')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">{{ isset($banner) ? 'Edit Banner' : 'Add Banner' }}</h1>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.banners.index') }}" class="btn-admin-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<form action="{{ isset($banner) ? route('admin.banners.update', $banner->id) : route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($banner)) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header"><h5>Banner Details</h5></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label>Title <span class="required">*</span></label>
                                <input type="text" name="title" class="form-control" required value="{{ old('title', $banner->title ?? '') }}" placeholder="Banner headline...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Subtitle</label>
                                <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $banner->subtitle ?? '') }}" placeholder="Small text above title">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Badge Text</label>
                                <input type="text" name="badge_text" class="form-control" value="{{ old('badge_text', $banner->badge_text ?? '') }}" placeholder="e.g. Sale, New, Hot">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Optional short description...">{{ old('description', $banner->description ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Button Text</label>
                                <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $banner->button_text ?? '') }}" placeholder="e.g. Shop Now">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Button Link</label>
                                <input type="text" name="button_link" class="form-control" value="{{ old('button_link', $banner->button_link ?? '') }}" placeholder="/products or /categories/medicines">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Position</label>
                                <select name="position" class="form-select">
                                    <option value="hero"  {{ old('position', $banner->position ?? 'hero') === 'hero'  ? 'selected' : '' }}>Hero Slider</option>
                                    <option value="promo" {{ old('position', $banner->position ?? '') === 'promo' ? 'selected' : '' }}>Promo Banner</option>
                                    <option value="side"  {{ old('position', $banner->position ?? '') === 'side'  ? 'selected' : '' }}>Side Banner</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Background Color</label>
                                <input type="color" name="background_color" class="form-control form-control-color w-100" style="height:42px" value="{{ old('background_color', $banner->background_color ?? '#edf4f6') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" min="0">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <label style="font-weight:700;font-size:13px;margin:0">Active (visible on website)</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5>Banner Image</h5></div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <div class="img-upload-box" onclick="document.getElementById('bannerImage').click()">
                            <i class="fas fa-image"></i>
                            <p>Upload Banner Image<br><small class="upload-btn">Recommended: 1280×500px</small></p>
                        </div>
                        <input type="file" id="bannerImage" name="image" accept="image/*" class="img-upload-input d-none">
                        @if(isset($banner) && $banner->image)
                            <img src="{{ asset('images/banners/'.$banner->image) }}" class="img-preview mt-2" style="display:block;max-height:160px;object-fit:cover;border-radius:8px;width:100%">
                        @else
                            <img class="img-preview mt-2">
                        @endif
                    </div>
                </div>
            </div>
            <div class="admin-card">
                <div class="admin-card-body">
                    <button type="submit" class="btn-admin-primary w-100 justify-content-center" style="padding:13px">
                        <i class="fas fa-save"></i> {{ isset($banner) ? 'Update Banner' : 'Create Banner' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

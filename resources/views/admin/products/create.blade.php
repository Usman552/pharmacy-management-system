@extends('admin.layouts.app')
@section('meta_title', isset($product) ? 'Edit Product' : 'Add Product')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">{{ isset($product) ? 'Edit Product' : 'Add New Product' }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
            <li class="breadcrumb-item active">{{ isset($product) ? 'Edit' : 'Add' }}</li>
        </ol></nav>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<form action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($product)) @method('PUT') @endif

    @if($errors->any())
    <div class="admin-alert alert-danger mb-3">
        <i class="fas fa-exclamation-circle"></i>
        <div>{{ $errors->first() }}</div>
    </div>
    @endif

    <div class="row g-3">
        <!-- Left Column -->
        <div class="col-lg-8">

            <!-- Basic Info -->
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5><i class="fas fa-info-circle me-2 text-warning"></i> Basic Information</h5></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label>Product Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ old('name', $product->name ?? '') }}"
                                       placeholder="e.g. Panadol Extra 500mg Tablets">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Category <span class="required">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Brand / Manufacturer</label>
                                <input type="text" name="brand" class="form-control"
                                       value="{{ old('brand', $product->brand ?? '') }}"
                                       placeholder="e.g. GlaxoSmithKline">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>SKU (Auto-generated if empty)</label>
                                <input type="text" name="sku" class="form-control"
                                       value="{{ old('sku', $product->sku ?? '') }}"
                                       placeholder="CD-XXXXXXXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Weight / Size</label>
                                <input type="text" name="weight" class="form-control"
                                       value="{{ old('weight', $product->weight ?? '') }}"
                                       placeholder="e.g. 500mg, 100ml, 30 Tablets">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"
                                          placeholder="Short product description...">{{ old('description', $product->description ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="admin-form-group">
                                <label>Detailed Info / Composition</label>
                                <textarea name="details" class="form-control" rows="4"
                                          placeholder="Ingredients, composition, usage instructions...">{{ old('details', $product->details ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Composition (for medicines)</label>
                                <input type="text" name="composition" class="form-control"
                                       value="{{ old('composition', $product->composition ?? '') }}"
                                       placeholder="e.g. Paracetamol 500mg">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="admin-form-group">
                                <label>Strength</label>
                                <input type="text" name="dosage" class="form-control"
                                       value="{{ old('dosage', $product->dosage ?? '') }}"
                                       placeholder="e.g. 500mg (label strength only, no usage advice)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Stock -->
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5><i class="fas fa-tags me-2 text-warning"></i> Pricing & Stock</h5></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Regular Price (Rs.) <span class="required">*</span></label>
                                <input type="number" name="price" class="form-control" required step="0.01" min="0"
                                       value="{{ old('price', $product->price ?? '') }}" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Sale Price (Rs.)</label>
                                <input type="number" name="sale_price" class="form-control" step="0.01" min="0"
                                       value="{{ old('sale_price', $product->sale_price ?? '') }}" placeholder="Leave empty if no sale">
                                <div class="help-text">Leave empty for no discount</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Stock Quantity <span class="required">*</span></label>
                                @if(isset($product))<div class="help-text">Changes are logged. For restocks use <a href="{{ route('admin.inventory.index') }}">Inventory</a>.</div>@endif
                                <input type="number" name="stock" class="form-control" required min="0"
                                       value="{{ old('stock', $product->stock ?? 0) }}" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Low Stock Alert (units)</label>
                                <input type="number" name="min_stock_alert" class="form-control" min="0"
                                       value="{{ old('min_stock_alert', $product->min_stock_alert ?? 10) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control"
                                       value="{{ old('expiry_date', isset($product->expiry_date) ? $product->expiry_date->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="admin-form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" min="0"
                                       value="{{ old('sort_order', $product->sort_order ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-lg-4">

            <!-- Product Image -->
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5><i class="fas fa-image me-2 text-warning"></i> Product Image</h5></div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <div class="img-upload-box" onclick="document.getElementById('productImage').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload image<br><small class="upload-btn">Browse files</small></p>
                            <p style="font-size:11px;color:#bbb;margin-top:8px">PNG, JPG, WEBP — max 2MB</p>
                        </div>
                        <input type="file" id="productImage" name="image" accept="image/*"
                               class="img-upload-input d-none">
                        @if(isset($product) && $product->image_url)
                            <img src="{{ $product->image_url }}"
                                 class="img-preview mt-2" style="display:block;max-height:180px;object-fit:contain;border-radius:8px;width:100%">
                        @else
                            <img class="img-preview" style="max-height:180px">
                        @endif
                    </div>
                </div>
            </div>

            <!-- Gallery -->
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5><i class="fas fa-images me-2 text-warning"></i> More Images</h5></div>
                <div class="admin-card-body">
                    <input type="file" name="images[]" accept="image/png,image/jpeg,image/webp" multiple class="form-control">
                    <div class="help-text">JPG, PNG or WEBP, max 2MB each.</div>
                    @if(isset($product) && !empty($product->images))
                        <div class="d-flex flex-wrap gap-2 mt-3">
                        @foreach($product->images as $img)
                            @if(is_file(public_path('images/products/'.basename($img))))
                            <label style="width:72px;font-size:11px;text-align:center">
                                <img src="{{ asset('images/products/'.basename($img)) }}" style="width:72px;height:72px;object-fit:cover;border-radius:8px;display:block">
                                <input type="checkbox" name="remove_images[]" value="{{ $img }}"> remove
                            </label>
                            @endif
                        @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Flags -->
            <div class="admin-card mb-3">
                <div class="admin-card-header"><h5><i class="fas fa-flag me-2 text-warning"></i> Product Flags</h5></div>
                <div class="admin-card-body">
                    @foreach([
                        ['is_active','Active (visible on website)','1'],
                        ['is_featured','Featured (on homepage)','0'],
                        ['is_new','Mark as New','0'],
                        ['is_popular','Mark as Popular','0'],
                        ['is_sale','Mark as Sale','0'],
                        ['requires_prescription','Requires Prescription','0'],
                    ] as [$name, $label, $default])
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label style="font-size:13px;font-weight:600;margin:0;cursor:pointer" for="flag_{{ $name }}">{{ $label }}</label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="flag_{{ $name }}" name="{{ $name }}" value="1"
                                   {{ old($name, isset($product) ? $product->$name : $default) ? 'checked' : '' }}>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Submit -->
            <div class="admin-card">
                <div class="admin-card-body">
                    <button type="submit" class="btn-admin-primary w-100 justify-content-center" style="padding:13px">
                        <i class="fas fa-save"></i>
                        {{ isset($product) ? 'Update Product' : 'Create Product' }}
                    </button>
                    @if(isset($product))
                    <a href="{{ route('products.show', $product->slug) }}" target="_blank"
                       class="btn-admin-secondary w-100 justify-content-center mt-2" style="padding:11px">
                        <i class="fas fa-external-link-alt"></i> View on Website
                    </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</form>
@endsection

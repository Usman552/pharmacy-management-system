@extends('admin.layouts.app')
@section('meta_title', 'Products')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Products</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Products</li></ol></nav>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.products.expiring') }}" class="btn-admin-secondary">
            <i class="fas fa-calendar-times"></i> Expiring Soon
        </a>
        <a href="{{ route('admin.products.create') }}" class="btn-admin-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
</div>

<div class="admin-card">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center w-100">
            <input type="text" name="search" class="search-input" placeholder="Search by name, SKU, brand..." value="{{ request('search') }}">
            <select name="category">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="stock">
                <option value="">All Stock</option>
                <option value="low"  {{ request('stock') === 'low'  ? 'selected' : '' }}>Low Stock (&lt;10)</option>
                <option value="out"  {{ request('stock') === 'out'  ? 'selected' : '' }}>Out of Stock</option>
            </select>
            <select name="status">
                <option value="">All Status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn-admin-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary"><i class="fas fa-times"></i> Clear</a>
        </form>
    </div>

    <div style="overflow-x:auto">
        <table class="admin-table searchable-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $level = $product->stock < 1 ? 'out' : ($product->stock < $product->min_stock_alert ? 'low' : 'ok');
                    $expired = $product->expiry_date && $product->expiry_date->isPast();
                @endphp
                <tr>
                    <td style="min-width:260px">
                        <div class="d-flex align-items-center gap-3">
                            <div class="prod-thumb">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                @else
                                    <i class="fas {{ $product->category?->icon_class ?? 'fa-capsules' }}"></i>
                                @endif
                            </div>
                            <div style="min-width:0">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="prod-name">{{ $product->name }}</a>
                                <div class="prod-meta">
                                    {{ collect([$product->brand, $product->sku])->filter()->implode(' · ') }}
                                    @if($product->requires_prescription)<span class="tag tag-rx">Rx</span>@endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><span class="tag">{{ $product->category->name ?? '—' }}</span></td>
                    <td style="white-space:nowrap">
                        @if($product->sale_price)
                            <div class="price-now">Rs. {{ number_format($product->sale_price, 0) }}</div>
                            <div class="price-old">Rs. {{ number_format($product->price, 0) }} <span class="tag tag-sale">-{{ $product->discount_percent }}%</span></div>
                        @else
                            <div class="price-now">Rs. {{ number_format($product->price, 0) }}</div>
                        @endif
                    </td>
                    <td style="white-space:nowrap">
                        @if($level === 'out')
                            <span class="status-badge out-of-stock">Out of stock</span>
                        @elseif($level === 'low')
                            <span class="status-badge low-stock">{{ $product->stock }} left</span>
                        @else
                            <span class="stock-num">{{ $product->stock }}</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap">
                        @if($product->expiry_date)
                            <span class="{{ $expired ? 'tag tag-danger' : ($product->isExpiringSoon() ? 'tag tag-warn' : '') }}" title="{{ $expired ? 'Expired' : ($product->isExpiringSoon() ? 'Expiring within 30 days' : '') }}">
                                {{ $product->expiry_date->format('M Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                   data-id="{{ $product->id }}" data-model="product" aria-label="Active"
                                   {{ $product->is_active ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-icon" title="Edit" aria-label="Edit {{ $product->name }}"><i class="fas fa-pen"></i></a>
                            <a href="{{ route('products.show', $product->slug) }}" target="_blank" rel="noopener" class="btn-icon" title="View on website" aria-label="View {{ $product->name }} on website"><i class="fas fa-eye"></i></a>
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($product->name) }}? Products that have been ordered are deactivated instead.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon danger" title="Delete" aria-label="Delete {{ $product->name }}"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-pills fa-3x d-block mb-3 opacity-25"></i>
                    No products found. <a href="{{ route('admin.products.create') }}" class="text-warning">Add first product</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-card-footer d-flex justify-content-between align-items-center">
        <div style="font-size:13px;color:var(--text-muted)">
            Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }} products
        </div>
        <div class="admin-pagination">
            {{ $products->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection

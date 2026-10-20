{{-- ========== EXPIRING PRODUCTS ========== --}}
@extends('admin.layouts.app')
@section('meta_title','Expiring Products')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">⚠️ Expiring Products</h1>
        <p class="text-muted mb-0" style="font-size:13px">Products expiring within 30 days</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary"><i class="fas fa-arrow-left"></i> All Products</a>
    </div>
</div>
<div class="admin-card">
    <div style="overflow-x:auto">
        <table class="admin-table">
            <thead><tr><th>Product</th><th>Category</th><th>Stock</th><th>Expiry Date</th><th>Days Left</th><th>Action</th></tr></thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:44px;height:44px;border-radius:8px;background:var(--body-bg);display:flex;align-items:center;justify-content:center;font-size:20px">
                                {{ $product->category->icon ?? '💊' }}
                            </div>
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $product->sku }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->stock }}</td>
                    <td><span class="expiry-warning"><i class="fas fa-calendar-times me-1"></i>{{ $product->expiry_date->format('d M Y') }}</span></td>
                    <td>
                        @php $daysLeft = now()->diffInDays($product->expiry_date, false); @endphp
                        @if($daysLeft < 0)
                            <span style="color:#e84343;font-weight:800">EXPIRED</span>
                        @elseif($daysLeft <= 7)
                            <span style="color:#e84343;font-weight:800">{{ $daysLeft }} days</span>
                        @else
                            <span style="color:#b8860b;font-weight:700">{{ $daysLeft }} days</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-admin-edit"><i class="fas fa-edit"></i> Update</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4" style="color:var(--success)">
                    <i class="fas fa-check-circle fa-2x d-block mb-2"></i>
                    No products expiring soon! 🎉
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-card-footer">{{ $products->links() }}</div>
</div>
@endsection

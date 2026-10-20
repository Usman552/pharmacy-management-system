@extends('admin.layouts.app')
@section('meta_title', 'Inventory')

@section('content')
<div class="page-header">
    <div class="page-header-left"><h1 class="page-title">Inventory</h1><p class="page-subtitle">Every stock change is recorded with who made it and why.</p></div>
    <div class="page-header-right"><a href="{{ route('admin.products.expiring') }}" class="btn-admin-secondary"><i class="fas fa-hourglass-end"></i> Expiring soon</a></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><a href="{{ route('admin.inventory.index') }}" class="stat-card" style="color:inherit"><div class="stat-icon"><i class="fas fa-cubes"></i></div><div class="stat-info"><div class="stat-label">Units on hand</div><div class="stat-value">{{ number_format($summary['units']) }}</div></div></a></div>
    <div class="col-md-4"><a href="{{ route('admin.inventory.index', ['level' => 'low']) }}" class="stat-card" style="--stat-color:#8A5A00;color:inherit"><div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div><div class="stat-info"><div class="stat-label">Low stock</div><div class="stat-value">{{ $summary['low'] }}</div></div></a></div>
    <div class="col-md-4"><a href="{{ route('admin.inventory.index', ['level' => 'out']) }}" class="stat-card" style="--stat-color:#B42318;color:inherit"><div class="stat-icon"><i class="fas fa-ban"></i></div><div class="stat-info"><div class="stat-label">Out of stock</div><div class="stat-value">{{ $summary['out'] }}</div></div></a></div>
</div>

<div class="row g-3">
    <div class="col-xl-9">
        <div class="admin-card">
            <div class="filter-bar">
                <form method="GET" class="d-flex gap-2 flex-wrap w-100">
                    <input type="text" name="search" class="search-input" placeholder="Search name, SKU or brand" value="{{ request('search') }}">
                    <select name="category" onchange="this.form.submit()"><option value="">All categories</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach</select>
                    <select name="level" onchange="this.form.submit()"><option value="">All levels</option><option value="out" @selected(request('level')==='out')>Out of stock</option><option value="low" @selected(request('level')==='low')>Low stock</option><option value="ok" @selected(request('level')==='ok')>Healthy</option></select>
                    <button class="btn-admin-secondary">Filter</button>
                </form>
            </div>
            <div style="overflow-x:auto"><table class="admin-table">
                <thead><tr><th>Product</th><th>SKU</th><th>Stock</th><th>Alert at</th><th>Adjust</th></tr></thead>
                <tbody>
                @forelse($products as $p)
                    @php $lvl = $p->stock < 1 ? 'out-of-stock' : ($p->stock < $p->min_stock_alert ? 'low-stock' : 'in-stock'); @endphp
                    <tr>
                        <td><a href="{{ route('admin.products.edit', $p->id) }}" style="font-weight:700;color:inherit">{{ $p->name }}</a><div class="text-muted" style="font-size:12px">{{ $p->category?->name }}@if($p->requires_prescription) · Rx @endif</div></td>
                        <td class="text-muted">{{ $p->sku }}</td>
                        <td><span class="status-badge {{ $lvl }}">{{ $p->stock }}</span></td>
                        <td>{{ $p->min_stock_alert }}</td>
                        <td>
                            <form action="{{ route('admin.inventory.adjust', $p->id) }}" method="POST" class="d-flex gap-1 align-items-center">
                                @csrf
                                <select name="mode" class="form-select" style="width:104px;min-height:34px;padding:4px 8px" aria-label="Adjustment type"><option value="add">Add</option><option value="remove">Remove</option><option value="set">Set to</option></select>
                                <input type="number" name="qty" min="0" required class="form-control" style="width:78px;min-height:34px;padding:4px 8px" aria-label="Quantity">
                                <input type="text" name="note" maxlength="200" class="form-control d-none d-xxl-block" style="width:130px;min-height:34px;padding:4px 8px" placeholder="Note">
                                <button class="btn-admin-view">Apply</button>
                                <a href="{{ route('admin.inventory.history', $p->id) }}" class="topbar-btn" title="History" aria-label="History of {{ $p->name }}" style="width:34px;height:34px"><i class="fas fa-clock-rotate-left"></i></a>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state"><i class="fas fa-boxes-stacked"></i>No products match.</div></td></tr>
                @endforelse
                </tbody>
            </table></div>
            @if($products->hasPages())<div class="admin-card-footer">{{ $products->links() }}</div>@endif
        </div>
    </div>
    <div class="col-xl-3">
        <div class="admin-card"><div class="admin-card-header"><h5>Recent movements</h5></div>
            <div class="admin-card-body" style="padding-top:6px;padding-bottom:6px">
                <ul class="log-list">@forelse($recent as $m)
                    <li><strong>{{ $m->change > 0 ? '+' : '' }}{{ $m->change }}</strong> {{ Str::limit($m->product?->name, 24) }}<small>{{ $m->reason }}{{ $m->reference ? ' · ' . $m->reference : '' }} · {{ $m->created_at->diffForHumans() }}</small></li>
                @empty<li class="text-muted">No movements yet.</li>@endforelse</ul>
            </div>
        </div>
    </div>
</div>
@endsection

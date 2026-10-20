@extends('admin.layouts.app')
@section('meta_title', 'Stock history')

@section('content')
<div class="page-header">
    <div class="page-header-left"><h1 class="page-title">Stock history</h1><p class="page-subtitle">{{ $product->name }} · currently {{ $product->stock }} in stock</p></div>
    <div class="page-header-right"><a href="{{ route('admin.inventory.index') }}" class="btn-admin-secondary"><i class="fas fa-arrow-left"></i> Back to inventory</a></div>
</div>
<div class="admin-card">
    <div style="overflow-x:auto"><table class="admin-table">
        <thead><tr><th>When</th><th>Change</th><th>Balance</th><th>Reason</th><th>Reference</th><th>By</th><th>Note</th></tr></thead>
        <tbody>
        @forelse($movements as $m)
            <tr><td>{{ $m->created_at->format('d M Y H:i') }}</td><td><strong style="color:{{ $m->change > 0 ? 'var(--primary)' : 'var(--danger)' }}">{{ $m->change > 0 ? '+' : '' }}{{ $m->change }}</strong></td><td>{{ $m->balance }}</td><td style="text-transform:capitalize">{{ $m->reason }}</td><td>{{ $m->reference ?: '—' }}</td><td>{{ $m->user?->name ?? 'Customer order' }}</td><td>{{ $m->note ?: '—' }}</td></tr>
        @empty
            <tr><td colspan="7"><div class="empty-state">No recorded movements.</div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($movements->hasPages())<div class="admin-card-footer">{{ $movements->links() }}</div>@endif
</div>
@endsection

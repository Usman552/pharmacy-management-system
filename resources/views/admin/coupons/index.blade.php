@extends('admin.layouts.app')
@section('meta_title', 'Coupons')

@section('content')
@php
    $formFields = function ($c = null) {
        return [
            'code' => old('code', $c?->code), 'description' => old('description', $c?->description), 'type' => old('type', $c?->type ?? 'percentage'),
            'value' => old('value', $c?->value), 'min_order' => old('min_order', $c?->min_order ?? 0), 'usage_limit' => old('usage_limit', $c?->usage_limit),
            'expires_at' => old('expires_at', $c?->expires_at?->format('Y-m-d')), 'is_active' => old('is_active', $c?->is_active ?? true),
        ];
    };
@endphp
<div class="page-header">
    <div class="page-header-left"><h1 class="page-title">Coupons</h1><p class="page-subtitle">Discounts are always re-checked on the server when an order is placed.</p></div>
</div>

@if($errors->any())<div class="admin-alert alert-danger"><i class="fas fa-circle-exclamation mt-1"></i><div>{{ $errors->first() }}</div></div>@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header"><h5>New coupon</h5></div>
            <div class="admin-card-body">
                @php $f = $formFields(); @endphp
                <form action="{{ route('admin.coupons.store') }}" method="POST">
                    @csrf
                    @include('admin.coupons._fields', ['f' => $f])
                    <button class="btn-admin-primary w-100 mt-3">Create coupon</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-card">
            <div style="overflow-x:auto"><table class="admin-table">
                <thead><tr><th>Code</th><th>Discount</th><th>Min order</th><th>Used</th><th>Expires</th><th>State</th><th></th></tr></thead>
                <tbody>
                @forelse($coupons as $c)
                    @php $state = ! $c->is_active ? 'inactive' : ($c->isValid() ? 'active' : 'expired'); @endphp
                    <tr>
                        <td><strong>{{ $c->code }}</strong><div class="text-muted" style="font-size:12px">{{ $c->description }}</div></td>
                        <td>{{ $c->type === 'percentage' ? rtrim(rtrim(number_format($c->value, 2), '0'), '.') . '%' : 'Rs. ' . number_format($c->value, 0) }}</td>
                        <td>Rs. {{ number_format($c->min_order, 0) }}</td>
                        <td>{{ $c->used_count }}{{ $c->usage_limit ? ' / ' . $c->usage_limit : '' }}</td>
                        <td>{{ $c->expires_at?->format('d M Y') ?: 'No expiry' }}</td>
                        <td><span class="status-badge {{ $state }}">{{ $state }}</span></td>
                        <td style="white-space:nowrap">
                            <button class="btn-admin-view" type="button" data-bs-toggle="collapse" data-bs-target="#edit{{ $c->id }}">Edit</button>
                            <form action="{{ route('admin.coupons.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete coupon {{ $c->code }}?')">@csrf @method('DELETE')<button class="btn-admin-danger btn-admin-sm">Delete</button></form>
                        </td>
                    </tr>
                    <tr class="collapse" id="edit{{ $c->id }}"><td colspan="7" style="background:var(--body-bg)">
                        @php $f = $formFields($c); @endphp
                        <form action="{{ route('admin.coupons.update', $c) }}" method="POST" class="p-2">@csrf @method('PUT')
                            @include('admin.coupons._fields', ['f' => $f, 'idp' => 'e' . $c->id])
                            <button class="btn-admin-primary mt-3">Save changes</button>
                        </form>
                    </td></tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-ticket"></i>No coupons yet.</div></td></tr>
                @endforelse
                </tbody>
            </table></div>
            @if($coupons->hasPages())<div class="admin-card-footer">{{ $coupons->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection

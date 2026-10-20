@extends('admin.layouts.app')
@section('meta_title', 'Prescriptions')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Prescriptions</h1>
        <p class="page-subtitle">Upload, review and verify prescriptions before any medicine is released to a customer.</p>
    </div>
    <div class="page-header-right"><a href="{{ route('admin.prescriptions.create') }}" class="btn-admin-primary"><i class="fas fa-upload"></i> Upload prescription</a></div>
</div>

<div class="admin-card">
    <div class="filter-bar">
        <form method="GET" class="d-flex gap-2 flex-wrap w-100">
            <input type="text" name="search" class="search-input" placeholder="Reference, doctor, customer name or email" value="{{ request('search') }}">
            <select name="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['pending','verified','rejected','fulfilled','expired'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }} ({{ $counts[$s] ?? 0 }})</option>
                @endforeach
            </select>
            <button class="btn-admin-secondary" type="submit">Filter</button>
        </form>
    </div>
    <div style="overflow-x:auto"><table class="admin-table">
        <thead><tr><th>Reference</th><th>Customer</th><th>Doctor</th><th>Items</th><th>Status</th><th>Received</th><th></th></tr></thead>
        <tbody>
        @forelse($prescriptions as $rx)
            <tr>
                <td><strong>{{ $rx->reference }}</strong></td>
                <td>{{ $rx->customer?->name }}<div class="text-muted" style="font-size:12px">{{ $rx->customer?->email }}</div></td>
                <td>{{ $rx->doctor_name ?: '—' }}</td>
                <td>{{ $rx->items_count }}</td>
                <td><span class="status-badge {{ $rx->status }}">{{ $rx->status }}</span></td>
                <td>{{ $rx->created_at->format('d M Y') }}</td>
                <td style="text-align:right"><a href="{{ route('admin.prescriptions.show', $rx) }}" class="btn-admin-view">{{ $rx->status === 'pending' ? 'Review' : 'View' }}</a></td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="empty-state"><i class="fas fa-file-prescription"></i>No prescriptions found.</div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($prescriptions->hasPages())<div class="admin-card-footer">{{ $prescriptions->links() }}</div>@endif
</div>
@endsection

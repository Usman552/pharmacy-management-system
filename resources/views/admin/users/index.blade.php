{{-- ========== USERS INDEX ========== --}}
@extends('admin.layouts.app')
@section('meta_title','Customers')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Customers</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Customers</li>
        </ol></nav>
    </div>
</div>
<div class="admin-card">
    <div class="filter-bar">
        <form method="GET" class="d-flex gap-2 flex-wrap w-100">
            <input type="text" name="search" class="search-input" placeholder="Search name, email, phone..." value="{{ request('search') }}">
            <select name="status">
                <option value="">All Status</option>
                <option value="active"   {{ request('status')==='active'   ? 'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')==='inactive' ? 'selected':'' }}>Inactive</option>
            </select>
            <button type="submit" class="btn-admin-primary"><i class="fas fa-search"></i> Search</button>
            <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary"><i class="fas fa-times"></i></a>
        </form>
    </div>
    <div style="overflow-x:auto">
        <table class="admin-table searchable-table">
            <thead><tr><th>Customer</th><th>Contact</th><th>City</th><th>Orders</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--primary),#1a4a6e);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;color:#fff;flex-shrink:0">
                                {{ strtoupper(substr($user->name,0,1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13px">{{ $user->name }}</div>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px">{{ $user->phone ?? '—' }}</td>
                    <td style="font-size:13px">{{ $user->city ?? '—' }}</td>
                    <td><strong style="color:var(--accent)">{{ $user->orders_count }}</strong></td>
                    <td style="font-size:12px;color:var(--text-muted)">{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input status-toggle" type="checkbox" data-id="{{ $user->id }}" data-model="user" {{ $user->is_active ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn-admin-view"><i class="fas fa-eye"></i></a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="margin:0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-admin-danger confirm-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">No customers found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-card-footer d-flex justify-content-between align-items-center">
        <span style="font-size:13px;color:var(--text-muted)">Total: {{ $users->total() }} customers</span>
        {{ $users->links() }}
    </div>
</div>
@endsection

{{-- ========== ADMINS VIEW ========== --}}
@extends('admin.layouts.app')
@section('meta_title','Admin Users')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Admin Users</h1>
    </div>
</div>
@if(auth()->user()->role === 'super_admin')
<div class="admin-card mb-3">
    <div class="admin-card-header"><h5>Add a staff member</h5></div>
    <div class="admin-card-body">
        @if($errors->any())<div class="admin-alert alert-danger"><i class="fas fa-circle-exclamation mt-1"></i><div>{{ $errors->first() }}</div></div>@endif
        <form action="{{ route('admin.users.staff.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-3"><div class="admin-form-group"><label>Name</label><input name="name" class="form-control" required value="{{ old('name') }}"></div></div>
                <div class="col-md-3"><div class="admin-form-group"><label>Email</label><input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div></div>
                <div class="col-md-2"><div class="admin-form-group"><label>Role</label><select name="role" class="form-select"><option value="pharmacist">Pharmacist (prescriptions only)</option><option value="admin">Administrator</option></select></div></div>
                <div class="col-md-2"><div class="admin-form-group"><label>Password</label><input type="password" name="password" class="form-control" required minlength="10" autocomplete="new-password"></div></div>
                <div class="col-md-2"><div class="admin-form-group"><label>Confirm</label><input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div></div>
            </div>
            <button class="btn-admin-primary mt-3">Create account</button>
        </form>
    </div>
</div>
@endif
<div class="admin-card">
    <div style="overflow-x:auto">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($admins as $admin)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff">
                                {{ strtoupper(substr($admin->name,0,1)) }}
                            </div>
                            <strong>{{ $admin->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $admin->email }}</td>
                    <td><span style="background:var(--mint);color:var(--primary-dark);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700">{{ ucfirst($admin->role) }}</span></td>
                    <td>{{ $admin->created_at->format('d M Y') }}</td>
                    <td><span class="status-badge {{ $admin->is_active ? 'active' : 'inactive' }}">{{ $admin->is_active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">No admin users found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- ========== BANNERS INDEX ========== --}}
@extends('admin.layouts.app')
@section('meta_title','Banners')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Banners</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Banners</li>
        </ol></nav>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.banners.create') }}" class="btn-admin-primary"><i class="fas fa-plus"></i> Add Banner</a>
    </div>
</div>
<div class="admin-card">
    <div style="overflow-x:auto">
        <table class="admin-table">
            <thead><tr><th>Preview</th><th>Title</th><th>Position</th><th>Button</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($banners as $banner)
                <tr>
                    <td>
                        <div style="width:100px;height:55px;border-radius:8px;overflow:hidden;background:{{ $banner->background_color ?? '#edf4f6' }};display:flex;align-items:center;justify-content:center;">
                            @if($banner->image)
                                <img src="{{ asset('images/banners/'.$banner->image) }}" style="width:100%;height:100%;object-fit:cover">
                            @else
                                <span style="font-size:11px;font-weight:700;color:var(--text-muted);padding:4px">{{ Str::limit($banner->title,15) }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <strong>{{ $banner->title }}</strong>
                        @if($banner->subtitle)<div style="font-size:11px;color:var(--text-muted)">{{ $banner->subtitle }}</div>@endif
                        @if($banner->badge_text)<span style="background:var(--accent);color:#fff;font-size:10px;padding:2px 7px;border-radius:10px">{{ $banner->badge_text }}</span>@endif
                    </td>
                    <td><span style="background:var(--body-bg);border:1px solid var(--border);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase">{{ $banner->position }}</span></td>
                    <td>
                        @if($banner->button_text)
                        <span style="font-size:13px">{{ $banner->button_text }}</span>
                        <div style="font-size:11px;color:var(--text-muted)">→ {{ $banner->button_link }}</div>
                        @else
                        <span class="text-muted" style="font-size:12px">No button</span>
                        @endif
                    </td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input status-toggle" type="checkbox" data-id="{{ $banner->id }}" data-model="banner" {{ $banner->is_active ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn-admin-edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" style="margin:0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-admin-danger confirm-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No banners yet. <a href="{{ route('admin.banners.create') }}" class="text-warning">Add first banner</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-card-footer">{{ $banners->links() }}</div>
</div>
@endsection

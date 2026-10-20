{{-- ========== SETTINGS APPEARANCE ========== --}}
@extends('admin.layouts.app')
@section('meta_title','Appearance Settings')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Appearance Settings</h1>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.settings.general') }}" class="btn-admin-secondary"><i class="fas fa-cog"></i> General Settings</a>
    </div>
</div>
<form action="{{ route('admin.settings.appearance.save') }}" method="POST">
@csrf
<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-palette me-2 text-warning"></i> Colors</h5></div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label>Primary Color</label>
                            <input type="color" name="primary_color" class="form-control form-control-color w-100" style="height:50px" value="{{ $settings['primary_color'] ?? '#0d6efd' }}">
                            <div class="help-text">Main brand color (buttons, links)</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label>Accent / CTA Color</label>
                            <input type="color" name="accent_color" class="form-control form-control-color w-100" style="height:50px" value="{{ $settings['accent_color'] ?? '#f2971f' }}">
                            <div class="help-text">Cart button, highlights</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-form-group">
                            <label>Footer Background</label>
                            <input type="color" name="footer_bg" class="form-control form-control-color w-100" style="height:50px" value="{{ $settings['footer_bg'] ?? '#184363' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-bullhorn me-2 text-warning"></i> Announcement Bar</h5></div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="admin-form-group">
                            <label>Announcement Text</label>
                            <input type="text" name="announcement_text" class="form-control"
                                   value="{{ $settings['announcement_text'] ?? 'Free delivery on orders over Rs. 999' }}"
                                   placeholder="e.g. Summer Sale — 30% off on all vitamins!">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <label style="font-weight:700;font-size:13px;margin:0">Show Announcement Bar</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="show_announcement" value="1"
                                       {{ ($settings['show_announcement'] ?? '1') === '1' ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-body">
                <button type="submit" class="btn-admin-primary w-100 justify-content-center" style="padding:13px">
                    <i class="fas fa-save"></i> Save Appearance
                </button>
                <a href="{{ route('home') }}" target="_blank" class="btn-admin-secondary w-100 justify-content-center mt-2" style="padding:11px">
                    <i class="fas fa-external-link-alt"></i> Preview Website
                </a>
                <div class="admin-alert alert-info mt-3" style="font-size:12px">
                    <i class="fas fa-info-circle"></i>
                    <span>Color changes take effect after page refresh</span>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

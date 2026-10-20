@extends('admin.layouts.app')
@section('meta_title','General Settings')
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">General Settings</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Settings</li>
        </ol></nav>
    </div>
</div>
<form action="{{ route('admin.settings.save') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="row g-3">
    <div class="col-lg-8">
        <!-- Site Info -->
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-globe me-2 text-warning"></i> Site Information</h5></div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? 'City Drug' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Tagline</label>
                            <input type="text" name="site_tagline" class="form-control" value="{{ $settings['site_tagline'] ?? 'Your Trusted Pharmacy' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Contact Email</label>
                            <input type="email" name="site_email" class="form-control" value="{{ $settings['site_email'] ?? 'info@citydrug.pk' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Phone Number</label>
                            <input type="text" name="site_phone" class="form-control" value="{{ $settings['site_phone'] ?? '+92 300 1234567' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>WhatsApp Number</label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ $settings['whatsapp'] ?? '' }}" placeholder="923001234567">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Currency</label>
                            <select name="currency" class="form-select">
                                <option value="PKR" {{ ($settings['currency'] ?? 'PKR') === 'PKR' ? 'selected' : '' }}>PKR (Rs.)</option>
                                <option value="USD" {{ ($settings['currency'] ?? 'PKR') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="admin-form-group">
                            <label>Address</label>
                            <textarea name="site_address" class="form-control" rows="2">{{ $settings['site_address'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>City</label>
                            <input type="text" name="site_city" class="form-control" value="{{ $settings['site_city'] ?? 'Multan' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label>Free Shipping Min. Order (Rs.)</label>
                            <input type="number" name="free_shipping_min" class="form-control" value="{{ $settings['free_shipping_min'] ?? 999 }}">
                            <div class="help-text">Orders at or above this amount (after any discount) get free delivery.</div>
                        </div>
                        <div class="admin-form-group mt-3">
                            <label>Delivery charge (Rs.)</label>
                            <input type="number" min="0" name="shipping_charge" class="form-control" value="{{ $settings['shipping_charge'] ?? 150 }}">
                            <div class="help-text">Charged on orders below the free-delivery amount.</div>
                        </div>
                        <div class="admin-form-group mt-3">
                            <label>Prescription validity (days)</label>
                            <input type="number" min="1" max="365" name="prescription_validity_days" class="form-control" value="{{ $settings['prescription_validity_days'] ?? 30 }}">
                            <div class="help-text">How long a verified prescription can be used to order its medicines. Set this according to your pharmacy's policy.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logo -->
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><i class="fas fa-image me-2 text-warning"></i> Site Logo</h5></div>
            <div class="admin-card-body">
                <div class="admin-form-group">
                    <div class="img-upload-box" onclick="document.getElementById('logoInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Upload Logo<br><small class="upload-btn">PNG, SVG recommended — transparent background</small></p>
                    </div>
                    <input type="file" id="logoInput" name="site_logo" accept="image/*" class="img-upload-input d-none">
                    @if($settings['site_logo'] ?? false)
                    <img src="{{ asset('images/'.$settings['site_logo']) }}" class="img-preview mt-2" style="display:block;max-height:80px;object-fit:contain">
                    @else
                    <img class="img-preview mt-2">
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-body">
                <button type="submit" class="btn-admin-primary w-100 justify-content-center" style="padding:13px">
                    <i class="fas fa-save"></i> Save Settings
                </button>
                <a href="{{ route('home') }}" target="_blank" class="btn-admin-secondary w-100 justify-content-center mt-2" style="padding:11px">
                    <i class="fas fa-external-link-alt"></i> View Website
                </a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@php $idp = $idp ?? 'n'; @endphp
<div class="row g-2">
    <div class="col-md-6 col-lg-12"><div class="admin-form-group"><label for="{{ $idp }}code">Code <span class="required">*</span></label><input id="{{ $idp }}code" name="code" class="form-control" required maxlength="40" value="{{ $f['code'] }}" style="text-transform:uppercase"></div></div>
    <div class="col-md-6 col-lg-12"><div class="admin-form-group"><label for="{{ $idp }}desc">Description</label><input id="{{ $idp }}desc" name="description" class="form-control" maxlength="255" value="{{ $f['description'] }}"></div></div>
    <div class="col-6 col-lg-6"><div class="admin-form-group"><label for="{{ $idp }}type">Type</label><select id="{{ $idp }}type" name="type" class="form-select"><option value="percentage" @selected($f['type']==='percentage')>Percentage</option><option value="fixed" @selected($f['type']==='fixed')>Fixed amount (Rs.)</option></select></div></div>
    <div class="col-6 col-lg-6"><div class="admin-form-group"><label for="{{ $idp }}value">Value <span class="required">*</span></label><input id="{{ $idp }}value" type="number" step="0.01" min="0.01" name="value" class="form-control" required value="{{ $f['value'] }}"></div></div>
    <div class="col-6 col-lg-6"><div class="admin-form-group"><label for="{{ $idp }}min">Minimum order (Rs.)</label><input id="{{ $idp }}min" type="number" min="0" name="min_order" class="form-control" value="{{ $f['min_order'] }}"></div></div>
    <div class="col-6 col-lg-6"><div class="admin-form-group"><label for="{{ $idp }}limit">Usage limit</label><input id="{{ $idp }}limit" type="number" min="1" name="usage_limit" class="form-control" value="{{ $f['usage_limit'] }}" placeholder="Unlimited"></div></div>
    <div class="col-12"><div class="admin-form-group"><label for="{{ $idp }}exp">Expires on</label><input id="{{ $idp }}exp" type="date" name="expires_at" class="form-control" value="{{ $f['expires_at'] }}"></div></div>
    <div class="col-12"><label class="d-flex gap-2 align-items-center" style="cursor:pointer"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($f['is_active'])> Active</label></div>
</div>

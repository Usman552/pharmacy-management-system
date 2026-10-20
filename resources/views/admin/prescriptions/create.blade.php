@extends('admin.layouts.app')
@section('meta_title', 'Upload prescription')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Upload prescription</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.prescriptions.index') }}">Prescriptions</a></li>
            <li class="breadcrumb-item active">Upload</li>
        </ol></nav>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        {{-- Step 1: choose customer --}}
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5><span class="status-badge {{ $customer ? 'verified' : 'pending' }}" style="margin-right:8px">Step 1</span> Customer</h5></div>
            <div class="admin-card-body">
                @if($customer)
                    <div class="d-flex justify-content-between align-items-center">
                        <div><strong>{{ $customer->name }}</strong><div class="text-muted">{{ $customer->email }}{{ $customer->phone ? ' · ' . $customer->phone : '' }}</div></div>
                        <a href="{{ route('admin.prescriptions.create') }}" class="btn-admin-secondary">Change customer</a>
                    </div>
                @else
                    <p class="text-muted">Prescriptions are always recorded against one customer. Search for them first.</p>
                    <form method="GET" class="d-flex gap-2">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name, email or phone" autofocus required>
                        <button class="btn-admin-primary">Search</button>
                    </form>
                    @if(request('q') !== null)
                        <div class="mt-3">
                            @forelse($results as $u)
                                <a href="{{ route('admin.prescriptions.create', ['customer' => $u->id]) }}" class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid #EEF3F0;color:inherit">
                                    <span><strong>{{ $u->name }}</strong> <span class="text-muted">· {{ $u->email }}{{ $u->phone ? ' · ' . $u->phone : '' }}</span></span>
                                    <span class="btn-admin-view">Select</span>
                                </a>
                            @empty
                                <div class="text-muted">No matching customers. The customer needs a registered account first.</div>
                            @endforelse
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Step 2: upload --}}
        <div class="admin-card" style="{{ $customer ? '' : 'opacity:.55;pointer-events:none' }}">
            <div class="admin-card-header"><h5><span class="status-badge {{ $customer ? 'pending' : '' }}" style="margin-right:8px">Step 2</span> Prescription file</h5></div>
            <div class="admin-card-body">
                @if($errors->any())<div class="admin-alert alert-danger"><i class="fas fa-circle-exclamation mt-1"></i><div>{{ $errors->first() }}</div></div>@endif
                <form action="{{ route('admin.prescriptions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer?->id }}">
                    <div class="admin-form-group mb-3">
                        <label>Prescription (JPG, PNG or PDF, up to 5 MB) <span class="required">*</span></label>
                        <input type="file" name="file" class="form-control" accept="image/jpeg,image/png,application/pdf" required>
                        <div class="help-text">Stored privately. It is never available through a public link.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="admin-form-group"><label>Doctor name</label><input type="text" name="doctor_name" class="form-control" value="{{ old('doctor_name') }}" maxlength="150"></div></div>
                        <div class="col-md-6"><div class="admin-form-group"><label>Date prescribed</label><input type="date" name="prescribed_on" class="form-control" value="{{ old('prescribed_on') }}" max="{{ now()->toDateString() }}"></div></div>
                        <div class="col-12"><div class="admin-form-group"><label>Internal notes</label><textarea name="staff_notes" class="form-control" rows="2" maxlength="1000">{{ old('staff_notes') }}</textarea></div></div>
                    </div>
                    <button class="btn-admin-primary mt-3" type="submit"><i class="fas fa-upload"></i> Upload and continue</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="admin-card"><div class="admin-card-header"><h5>How this works</h5></div>
            <div class="admin-card-body">
                <ol class="steps-list">
                    <li><span class="n">1</span><div>Pick the right customer. Verified medicines can only be ordered by that customer.</div></li>
                    <li><span class="n">2</span><div>Upload the prescription. Then enter each medicine exactly as written.</div></li>
                    <li><span class="n">3</span><div>Match each line to a catalogue product and confirm quantities.</div></li>
                    <li><span class="n">4</span><div>A pharmacist verifies. The customer then reviews the items and decides whether to order. Nothing is ordered or charged automatically.</div></li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

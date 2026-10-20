@extends('admin.layouts.app')
@section('meta_title', 'Prescription ' . $rx->reference)

@section('content')
@php
    $pending = $rx->status === 'pending';
    $matchedTotal = $rx->items->filter(fn ($i) => $i->product)->sum(fn ($i) => $i->approved_qty * $i->product->final_price);
    $ready = $rx->items->isNotEmpty() && $rx->items->every(fn ($i) => $i->product_id && $i->approved_qty > 0);
@endphp
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">{{ $rx->reference }} <span class="status-badge {{ $rx->status }}" style="vertical-align:middle;margin-left:6px">{{ $rx->status }}</span></h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.prescriptions.index') }}">Prescriptions</a></li>
            <li class="breadcrumb-item active">{{ $rx->reference }}</li>
        </ol></nav>
    </div>
</div>

@if($errors->any())<div class="admin-alert alert-danger"><i class="fas fa-circle-exclamation mt-1"></i><div>{{ $errors->first() }}</div></div>@endif

<div class="row g-3">
    {{-- LEFT: the document and facts --}}
    <div class="col-lg-5">
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5>Prescription document</h5><a href="{{ route('admin.prescriptions.file', $rx) }}" target="_blank" rel="noopener" style="font-size:13px;font-weight:700">Open full size</a></div>
            <div class="admin-card-body">
                @if($rx->mime_type === 'application/pdf')
                    <iframe class="rx-frame" src="{{ route('admin.prescriptions.file', $rx) }}" title="Prescription PDF"></iframe>
                @else
                    <img class="rx-img" src="{{ route('admin.prescriptions.file', $rx) }}" alt="Uploaded prescription">
                @endif
                <div class="help-text mt-2">Private file. Viewing is recorded in the audit trail.</div>
            </div>
        </div>

        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5>Details</h5></div>
            <div class="admin-card-body" style="font-size:13.5px">
                <div class="info-row"><span class="text-muted">Customer</span><strong>{{ $rx->customer?->name }}</strong></div>
                <div class="info-row"><span class="text-muted">Email</span><span>{{ $rx->customer?->email }}</span></div>
                @if($rx->customer?->phone)<div class="info-row"><span class="text-muted">Phone</span><span>{{ $rx->customer->phone }}</span></div>@endif
                <div class="info-row"><span class="text-muted">Doctor</span><span>{{ $rx->doctor_name ?: '—' }}</span></div>
                <div class="info-row"><span class="text-muted">Prescribed on</span><span>{{ $rx->prescribed_on?->format('d M Y') ?: '—' }}</span></div>
                <div class="info-row"><span class="text-muted">Uploaded by</span><span>{{ $rx->uploader?->name ?: '—' }}</span></div>
                @if($rx->reviewed_by)<div class="info-row"><span class="text-muted">Reviewed by</span><span>{{ $rx->reviewer?->name }}, {{ $rx->reviewed_at?->format('d M Y H:i') }}</span></div>@endif
                @if($rx->expires_at)<div class="info-row"><span class="text-muted">Valid until</span><span>{{ $rx->expires_at->format('d M Y') }}</span></div>@endif
                @if($rx->staff_notes)<hr><div class="text-muted" style="font-size:12.5px">Internal notes</div><div>{{ $rx->staff_notes }}</div>@endif
                @if($rx->rejection_reason)<hr><div class="text-muted" style="font-size:12.5px">Rejection reason</div><div>{{ $rx->rejection_reason }}</div>@endif
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header"><h5>Audit trail</h5></div>
            <div class="admin-card-body" style="padding-top:8px;padding-bottom:8px">
                <ul class="log-list">
                    @foreach($rx->logs as $log)
                        <li><strong>{{ str_replace('_', ' ', $log->action) }}</strong>@if($log->detail) — {{ $log->detail }}@endif
                            <small>{{ $log->created_at->format('d M Y H:i') }} · {{ $log->user?->name ?? 'System' }}</small></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- RIGHT: medicines and verification --}}
    <div class="col-lg-7">
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5>Medicines on this prescription</h5>
                @if($pending)<span class="text-muted" style="font-size:12.5px">{{ $ocrEnabled ? 'OCR suggestions are unverified.' : 'Enter each line exactly as written.' }}</span>@endif</div>
            <div class="admin-card-body">
                @forelse($rx->items as $item)
                    <div style="border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:14px;{{ $item->product && $item->approved_qty ? 'background:#FBFDFC' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="text-muted" style="font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.06em">As written</div>
                                <strong style="font-size:15px">{{ $item->written_name }}</strong>
                                <span class="text-muted">{{ collect([$item->written_strength, $item->written_form])->filter()->implode(' · ') }}</span>
                                @if($item->prescribed_qty)<span class="status-badge" style="margin-left:6px">Prescribed qty {{ $item->prescribed_qty }}</span>@endif
                            </div>
                            @if($pending)
                            <form action="{{ route('admin.prescriptions.items.destroy', [$rx, $item]) }}" method="POST" onsubmit="return confirm('Remove this line?')">@csrf @method('DELETE')
                                <button class="btn-admin-danger" style="min-height:32px;padding:0 10px" aria-label="Remove line"><i class="fas fa-xmark"></i></button></form>
                            @endif
                        </div>

                        @if($pending)
                        <form action="{{ route('admin.prescriptions.items.update', [$rx, $item]) }}" method="POST" class="mt-3">
                            @csrf @method('PUT')
                            <div class="row g-2 align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label">Dispense this product</label>
                                    <select name="product_id" class="form-select" required>
                                        <option value="">Choose the matching product…</option>
                                        @if(($suggestions[$item->id] ?? collect())->isNotEmpty())
                                        <optgroup label="Possible matches (check strength and form)">
                                            @foreach($suggestions[$item->id] as $m)
                                                <option value="{{ $m->product->id }}" @selected($item->product_id === $m->product->id)>{{ $m->product->name }} — {{ $m->confidence }} (stock {{ $m->product->stock }})</option>
                                            @endforeach
                                        </optgroup>
                                        @endif
                                        <optgroup label="All products">
                                            @foreach($allProducts as $p)<option value="{{ $p->id }}" @selected($item->product_id === $p->id)>{{ $p->name }} (stock {{ $p->stock }})</option>@endforeach
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="col-md-2"><label class="form-label">Qty</label><input type="number" name="approved_qty" min="1" class="form-control" value="{{ $item->approved_qty ?: ($item->prescribed_qty ?: '') }}" required></div>
                                <div class="col-md-2"><button class="btn-admin-secondary w-100">Save</button></div>
                            </div>
                            @if(($suggestions[$item->id] ?? collect())->isEmpty())<div class="help-text">No close catalogue match found. Pick a product manually or contact the prescriber.</div>@endif
                        </form>
                        @endif

                        @if($item->product)
                            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size:13.5px">
                                <span><i class="fas fa-circle-check text-warning me-1"></i> <strong>{{ $item->product->name }}</strong> × {{ $item->approved_qty }}
                                    @if($item->product->requires_prescription)<span class="status-badge cancelled" style="font-size:10px;padding:2px 6px">Rx</span>@endif</span>
                                <span>Rs. {{ number_format($item->product->final_price * $item->approved_qty, 0) }}
                                    @if(!$pending && $item->fulfilled_qty)<span class="text-muted"> · ordered {{ $item->fulfilled_qty }}/{{ $item->approved_qty }}</span>@endif</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="empty-state"><i class="fas fa-prescription-bottle-medical"></i>No medicines recorded yet. Add each one below.</div>
                @endforelse

                @if($pending)
                <form action="{{ route('admin.prescriptions.items.store', $rx) }}" method="POST" class="mt-3" style="background:var(--body-bg);border:1px dashed #BFDCCB;border-radius:12px;padding:16px">
                    @csrf
                    <div class="fw-bold mb-2" style="font-size:13px">Add a medicine line</div>
                    <div class="row g-2">
                        <div class="col-md-4"><input type="text" name="written_name" class="form-control" placeholder="Medicine name as written" required maxlength="190"></div>
                        <div class="col-md-2"><input type="text" name="written_strength" class="form-control" placeholder="Strength" maxlength="60"></div>
                        <div class="col-md-2"><input type="text" name="written_form" class="form-control" placeholder="Form" maxlength="60"></div>
                        <div class="col-md-2"><input type="number" name="prescribed_qty" class="form-control" placeholder="Qty" min="1" aria-label="Prescribed quantity"></div>
                        <div class="col-md-12"><button class="btn-admin-primary">Add line</button></div>
                    </div>
                </form>
                @endif
            </div>
        </div>

        @if($pending)
        <div class="admin-card mb-3">
            <div class="admin-card-header"><h5>Verify</h5></div>
            <div class="admin-card-body">
                <p class="text-muted mb-3">Review the summary. Verifying makes these items available to <strong>{{ $rx->customer?->name }}</strong> for {{ $validity }} days. They add the items to their own cart and choose whether to pay; nothing is ordered for them.</p>
                <table class="admin-table mb-3" style="border:1px solid var(--border);border-radius:10px">
                    <thead><tr><th>Customer</th><th>Medicine</th><th>Qty</th><th style="text-align:right">Price</th></tr></thead>
                    <tbody>
                    @foreach($rx->items as $item)
                        <tr><td>{{ $loop->first ? $rx->customer?->name : '' }}</td><td>{{ $item->product?->name ?? '— not matched —' }}</td><td>{{ $item->approved_qty ?: '—' }}</td><td style="text-align:right">{{ $item->product ? 'Rs. ' . number_format($item->product->final_price * $item->approved_qty, 0) : '—' }}</td></tr>
                    @endforeach
                    @if($rx->items->isNotEmpty())<tr><td colspan="3"><strong>Estimated total</strong></td><td style="text-align:right"><strong>Rs. {{ number_format($matchedTotal, 0) }}</strong></td></tr>@endif
                    </tbody>
                </table>
                <form action="{{ route('admin.prescriptions.verify', $rx) }}" method="POST">
                    @csrf
                    <label class="d-flex gap-2 align-items-start mb-3" style="font-size:13.5px;cursor:pointer">
                        <input type="checkbox" name="confirm" value="1" class="form-check-input mt-1" required>
                        <span>I have compared every line with the prescription document and confirm the products, strengths and quantities are correct.</span>
                    </label>
                    <button class="btn-admin-primary" type="submit" {{ $ready ? '' : 'disabled' }}><i class="fas fa-check"></i> Verify prescription</button>
                    @unless($ready)<span class="help-text ms-2">Match every line to a product and quantity first.</span>@endunless
                </form>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header"><h5>Reject</h5></div>
            <div class="admin-card-body">
                <form action="{{ route('admin.prescriptions.reject', $rx) }}" method="POST" onsubmit="return confirm('Reject this prescription?')">
                    @csrf
                    <div class="admin-form-group mb-2"><label>Reason (shown to the customer)</label><textarea name="rejection_reason" class="form-control" rows="2" required minlength="5" maxlength="500" placeholder="e.g. Prescription is unreadable or has expired"></textarea></div>
                    <button class="btn-admin-danger" type="submit"><i class="fas fa-ban"></i> Reject prescription</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

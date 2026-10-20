@extends('layouts.app')

@section('meta_title', 'Prescription ' . $rx->reference . ' — CityDrug')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('account.prescriptions') }}">Prescriptions</a></li><li>{{ $rx->reference }}</li></ol><h1>Prescription {{ $rx->reference }}</h1></div></div>
<div class="page-body"><div class="container">
<div class="account-grid">
    @include('pages._account-nav')
    <div>
        <div class="panel panel-pad mb-3">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                <span class="status {{ $rx->status }}">{{ $rx->status }}</span>
                <a href="{{ route('account.prescriptions.file', $rx) }}" target="_blank" rel="noopener" class="btn-cd sm outline"><i class="fas fa-file"></i> View my prescription</a>
            </div>
            <p class="muted mb-0">@if($rx->doctor_name)Prescribed by {{ $rx->doctor_name }}. @endif Recorded {{ $rx->created_at->format('d M Y') }}.
                @if($rx->status === 'verified' && $rx->expires_at) Verified items can be ordered until <strong>{{ $rx->expires_at->format('d M Y') }}</strong>.@endif</p>
            @if($rx->status === 'rejected')<div class="alert-cd error"><i class="fas fa-circle-exclamation"></i><span>This prescription could not be accepted: {{ $rx->rejection_reason }}</span></div>@endif
            @if($rx->status === 'pending')<div class="alert-cd info"><i class="fas fa-hourglass-half"></i><span>Our pharmacist is still reviewing this prescription. You will be able to order once it is verified.</span></div>@endif
        </div>

        @if($rx->status !== 'rejected' && $rx->items->isNotEmpty())
        <div class="panel">
            <div class="table-wrap"><table class="tbl">
                <thead><tr><th>Medicine</th><th>Approved qty</th><th>Already ordered</th><th style="text-align:right">Price</th></tr></thead>
                <tbody>@foreach($rx->items as $item)
                    <tr>
                        <td><strong>{{ $item->product?->name ?? $item->written_name }}</strong>@if($item->written_strength)<div class="muted" style="font-size:12.5px">Prescribed: {{ $item->written_name }} {{ $item->written_strength }}</div>@endif</td>
                        <td>{{ $item->is_verified ? $item->approved_qty : '—' }}</td>
                        <td>{{ $item->fulfilled_qty }}</td>
                        <td style="text-align:right">{{ $item->product ? 'Rs. ' . number_format($item->product->final_price, 0) . ' each' : '—' }}</td>
                    </tr>
                @endforeach</tbody>
            </table></div>
            @can('useItems', $rx)
                <div class="panel-pad d-flex justify-content-between align-items-center flex-wrap gap-3" style="border-top:1px solid var(--line-soft)">
                    <span class="muted">You can review these in your cart before paying. Nothing is ordered automatically.</span>
                    <form action="{{ route('account.prescriptions.add-to-cart', $rx) }}" method="POST">@csrf
                        <button class="btn-cd"><i class="fas fa-cart-plus"></i> Add verified items to cart</button></form>
                </div>
            @endcan
        </div>
        @endif
    </div>
</div>
</div></div>
@endsection

@extends('layouts.app')

@section('meta_title', 'My prescriptions — CityDrug')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('account.index') }}">Account</a></li><li>Prescriptions</li></ol><h1>My prescriptions</h1><p>Prescriptions recorded by our pharmacy team for your account.</p></div></div>
<div class="page-body"><div class="container">
<div class="account-grid">
    @include('pages._account-nav')
    <div>
        @if($prescriptions->isEmpty())
            <div class="empty"><i class="fas fa-file-prescription"></i><h2>No prescriptions on file</h2>
                <p>Bring your prescription to our pharmacy team. Once it is recorded and verified it will appear here, and you can add the verified medicines to your cart.</p>
                <a href="{{ route('contact') }}" class="btn-cd">Contact the pharmacy</a></div>
        @else
            <div class="panel"><div class="table-wrap"><table class="tbl">
                <thead><tr><th>Reference</th><th>Date</th><th>Doctor</th><th>Items</th><th>Status</th><th></th></tr></thead>
                <tbody>@foreach($prescriptions as $rx)
                    <tr>
                        <td><strong>{{ $rx->reference }}</strong></td>
                        <td>{{ $rx->created_at->format('d M Y') }}</td>
                        <td>{{ $rx->doctor_name ?: '—' }}</td>
                        <td>{{ $rx->items_count }}</td>
                        <td><span class="status {{ $rx->status }}">{{ $rx->status }}</span></td>
                        <td style="text-align:right"><a href="{{ route('account.prescriptions.show', $rx) }}" class="btn-cd sm outline">View</a></td>
                    </tr>
                @endforeach</tbody>
            </table></div></div>
            {{ $prescriptions->links('vendor.pagination.custom') }}
        @endif
    </div>
</div>
</div></div>
@endsection

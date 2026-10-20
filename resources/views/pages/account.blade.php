@extends('layouts.app')

@section('meta_title', 'My account — CityDrug')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li>Account</li></ol><h1>Hello, {{ Str::before($user->name, ' ') }}</h1></div></div>
<div class="page-body"><div class="container">
<div class="account-grid">
    @include('pages._account-nav')
    <div>
        <div class="panel panel-pad mb-3">
            <h2>Profile</h2>
            <form action="{{ route('account.update-profile') }}" method="POST" novalidate>
                @csrf
                <div class="cols-2">
                    <div class="field"><label for="name">Full name</label><input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="@error('name') is-invalid @enderror">@error('name')<span class="error">{{ $message }}</span>@enderror</div>
                    <div class="field"><label>Email</label><input type="email" value="{{ $user->email }}" disabled></div>
                    <div class="field"><label for="phone">Phone</label><input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}"></div>
                    <div class="field"><label for="city">City</label><input id="city" type="text" name="city" value="{{ old('city', $user->city) }}"></div>
                </div>
                <div class="field"><label for="address">Delivery address</label><textarea id="address" name="address" rows="2">{{ old('address', $user->address) }}</textarea></div>
                <button class="btn-cd">Save profile</button>
            </form>
        </div>

        <div class="panel panel-pad mb-3">
            <h2>Change password</h2>
            <form action="{{ route('account.update-password') }}" method="POST" novalidate>
                @csrf
                <div class="field"><label for="current_password">Current password</label><input id="current_password" type="password" name="current_password" required autocomplete="current-password" class="@error('current_password') is-invalid @enderror">@error('current_password')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="cols-2">
                    <div class="field"><label for="new_password">New password</label><input id="new_password" type="password" name="password" required minlength="8" autocomplete="new-password" class="@error('password') is-invalid @enderror">@error('password')<span class="error">{{ $message }}</span>@enderror</div>
                    <div class="field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
                </div>
                <button class="btn-cd outline">Update password</button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-pad" style="padding-bottom:0"><h2>Recent orders</h2></div>
            @if($orders->isEmpty())
                <p class="muted panel-pad" style="padding-top:0">You have not placed an order yet. <a href="{{ route('products.index') }}">Start shopping</a></p>
            @else
                <div class="table-wrap"><table class="tbl">
                    <thead><tr><th>Order</th><th>Date</th><th>Status</th><th style="text-align:right">Total</th></tr></thead>
                    <tbody>@foreach($orders as $o)
                        <tr><td><a href="{{ route('account.order-detail', $o->order_number) }}" style="font-weight:700">{{ $o->order_number }}</a></td><td>{{ $o->created_at->format('d M Y') }}</td><td><span class="status {{ $o->status }}">{{ $o->status }}</span></td><td style="text-align:right">Rs. {{ number_format($o->total_amount, 0) }}</td></tr>
                    @endforeach</tbody>
                </table></div>
            @endif
        </div>
    </div>
</div>
</div></div>
@endsection

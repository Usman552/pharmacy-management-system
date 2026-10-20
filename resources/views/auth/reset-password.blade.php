@extends('layouts.app')

@section('meta_title', 'Choose a new password — CityDrug')

@section('content')
<div class="auth" style="grid-template-columns:1fr">
    <div class="auth-main">
        <div class="auth-card">
            <h1>Choose a new password</h1>
            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="field"><label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" required class="@error('email') is-invalid @enderror">
                    @error('email')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="password">New password</label>
                    <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="@error('password') is-invalid @enderror">
                    @error('password')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
                <button class="btn-cd lg block" type="submit">Reset password</button>
            </form>
        </div>
    </div>
</div>
@endsection

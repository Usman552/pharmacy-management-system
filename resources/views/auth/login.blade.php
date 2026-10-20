@extends('layouts.app')

@section('meta_title', 'Sign in — CityDrug')

@section('content')
<div class="auth">
    <div class="auth-aside">
        <img src="{{ asset('images/site/pharmacy-interior.jpg') }}" alt="">
        <div>
            <h2>Welcome back.</h2>
            <p>Track your orders, see your pharmacist-verified prescriptions and check out faster.</p>
        </div>
    </div>
    <div class="auth-main">
        <div class="auth-card">
            <h1>Sign in</h1>
            <p class="muted">Use the email and password for your CityDrug account.</p>

            @include('auth._google-button')

            <form action="{{ route('login') }}" method="POST" novalidate>
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="@error('email') is-invalid @enderror">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="@error('password') is-invalid @enderror">
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
                    <a href="{{ route('password.request') }}" style="font-size:13px;font-weight:700">Forgot password?</a>
                </div>
                <button class="btn-cd lg block" type="submit">Sign in</button>
            </form>
            <p class="muted mt-4 mb-0">New to CityDrug? <a href="{{ route('register') }}" style="font-weight:700">Create an account</a></p>
        </div>
    </div>
</div>
@endsection

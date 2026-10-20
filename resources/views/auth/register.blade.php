@extends('layouts.app')

@section('meta_title', 'Create account — CityDrug')

@section('content')
<div class="auth">
    <div class="auth-aside">
        <img src="{{ asset('images/site/hero-pharmacist.jpg') }}" alt="">
        <div>
            <h2>Care that follows you home.</h2>
            <p>An account keeps your orders and verified prescriptions in one place.</p>
        </div>
    </div>
    <div class="auth-main">
        <div class="auth-card">
            <h1>Create your account</h1>
            <p class="muted">It takes less than a minute.</p>

            @include('auth._google-button')

            <form action="{{ route('register') }}" method="POST" novalidate>
                @csrf
                <div class="field"><label for="name">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="@error('name') is-invalid @enderror">
                    @error('name')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="@error('email') is-invalid @enderror">
                    @error('email')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="cols-2">
                    <div class="field"><label for="phone">Phone <span class="muted">(optional)</span></label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"></div>
                    <div class="field"><label for="city">City <span class="muted">(optional)</span></label>
                        <input id="city" type="text" name="city" value="{{ old('city') }}" autocomplete="address-level2"></div>
                </div>
                <div class="cols-2">
                    <div class="field"><label for="password">Password</label>
                        <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="@error('password') is-invalid @enderror">
                        @error('password')<span class="error">{{ $message }}</span>@enderror</div>
                    <div class="field"><label for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
                </div>
                <p class="hint muted" style="font-size:12.5px">Use at least 8 characters.</p>
                <button class="btn-cd lg block" type="submit">Create account</button>
            </form>
            <p class="muted mt-4 mb-0">Already registered? <a href="{{ route('login') }}" style="font-weight:700">Sign in</a></p>
        </div>
    </div>
</div>
@endsection

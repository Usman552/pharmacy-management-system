@extends('layouts.app')

@section('meta_title', 'Reset password — CityDrug')

@section('content')
<div class="auth" style="grid-template-columns:1fr">
    <div class="auth-main">
        <div class="auth-card">
            <h1>Forgot your password?</h1>
            <p class="muted">Enter your email and we will send you a link to choose a new one.</p>

            @if(session('status'))<div class="alert-cd success mb-3"><i class="fas fa-circle-check"></i><span>{{ session('status') }}</span></div>@endif

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                <div class="field"><label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="@error('email') is-invalid @enderror">
                    @error('email')<span class="error">{{ $message }}</span>@enderror</div>
                <button class="btn-cd lg block" type="submit">Send reset link</button>
            </form>
            <p class="mt-4 mb-0"><a href="{{ route('login') }}" style="font-weight:700"><i class="fas fa-arrow-left"></i> Back to sign in</a></p>
        </div>
    </div>
</div>
@endsection

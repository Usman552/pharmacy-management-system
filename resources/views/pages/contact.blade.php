@extends('layouts.app')

@section('meta_title', 'Contact — CityDrug')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li>Contact</li></ol><h1>Contact the pharmacy</h1><p>Questions about a product, an order or a prescription? Send us a message.</p></div></div>
<div class="page-body"><div class="container">
<div class="cart-layout">
    <div class="panel panel-pad">
        <h2>Send a message</h2>
        <form action="{{ route('contact.submit') }}" method="POST" novalidate>
            @csrf
            <div class="cols-2">
                <div class="field"><label for="name">Name</label><input id="name" type="text" name="name" required value="{{ old('name', auth()->user()?->name) }}" class="@error('name') is-invalid @enderror">@error('name')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" required value="{{ old('email', auth()->user()?->email) }}" class="@error('email') is-invalid @enderror">@error('email')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="phone">Phone <span class="muted">(optional)</span></label><input id="phone" type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}"></div>
                <div class="field"><label for="subject">Topic</label>
                    <select id="subject" name="subject">
                        @foreach(['General question', 'Product question', 'Order or delivery', 'Prescription', 'Other'] as $s)<option @selected(old('subject') === $s)>{{ $s }}</option>@endforeach
                    </select></div>
            </div>
            <div class="field"><label for="message">Message</label><textarea id="message" name="message" rows="5" required minlength="10" class="@error('message') is-invalid @enderror" placeholder="How can we help?">{{ old('message') }}</textarea>@error('message')<span class="error">{{ $message }}</span>@enderror</div>
            <p class="muted" style="font-size:12.5px">Please do not send urgent medical information here. In an emergency contact your local emergency services.</p>
            <button class="btn-cd lg">Send message</button>
        </form>
    </div>

    <aside class="panel panel-pad">
        <h2>Pharmacy details</h2>
        @php
            $rows = array_filter([
                'fa-location-dot' => trim(implode(', ', array_filter([$site['site_address'] ?? '', $site['site_city'] ?? '']))),
                'fa-phone'        => $site['site_phone'] ?? '',
                'fa-envelope'     => $site['site_email'] ?? '',
            ]);
            $wa = preg_replace('/\D+/', '', $site['whatsapp'] ?? '');
        @endphp
        @forelse($rows as $icon => $text)
            <p class="d-flex gap-3"><i class="fas {{ $icon }}" style="color:var(--green);margin-top:5px;width:18px"></i><span>{{ $text }}</span></p>
        @empty
            <p class="muted">Use the form to reach the pharmacy team. We will reply to the email you provide.</p>
        @endforelse
        @if($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn-cd ghost block mt-2"><i class="fab fa-whatsapp"></i> Message on WhatsApp</a>@endif
    </aside>
</div>
</div></div>
@endsection

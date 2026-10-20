@extends('layouts.app')

@section('meta_title', 'About — CityDrug')
@section('meta_desc', 'How CityDrug works: pharmacist-verified prescription medicines, everyday health products and home delivery.')

@section('content')
<div class="page-head"><div class="container"><ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li>About</li></ol><h1>About CityDrug</h1><p>An online pharmacy built around safe dispensing and clear, honest information.</p></div></div>

<div class="page-body"><div class="container">
    <div class="split mb-5">
        <div class="photo"><img src="{{ asset('images/site/pharmacy-interior.jpg') }}" alt="Inside a bright, modern pharmacy" loading="lazy"></div>
        <div>
            <span class="eyebrow">What we do</span>
            <h2>Medicines and health products, delivered</h2>
            <p class="muted">CityDrug lets you order everyday medicines, vitamins and healthcare products online. Prices and stock you see are the same ones used at checkout, and you can follow every order from your account.</p>
            <ul class="ticks">
                <li><i class="fas fa-check"></i><div><strong>Live stock</strong><span>Availability shown on every product is read from our inventory.</span></div></li>
                <li><i class="fas fa-check"></i><div><strong>Clear pricing</strong><span>The total, including delivery and any coupon, is shown before you place an order.</span></div></li>
                <li><i class="fas fa-check"></i><div><strong>Order tracking</strong><span>Status updates are recorded and visible in your account.</span></div></li>
            </ul>
        </div>
    </div>

    <div class="panel panel-pad" id="prescriptions">
        <span class="eyebrow">Prescription medicines</span>
        <h2>How prescriptions are handled</h2>
        <ol class="rx-steps mt-3">
            <li><div><strong>Your prescription is recorded.</strong> Our pharmacy team stores it securely against your customer account. Prescription files are private and visible only to you and authorised pharmacy staff.</div></li>
            <li><div><strong>A pharmacist verifies it.</strong> Each medicine, strength and quantity is checked and matched to the right product. Nothing is assumed from similar names.</div></li>
            <li><div><strong>You review and decide.</strong> Verified items appear under <em>My prescriptions</em>. You add them to your cart, review them and check out. We never place an order or take payment for you.</div></li>
        </ol>
        <p class="muted mt-3 mb-0">Medicines marked <span class="badge-cd rx">Rx</span> cannot be added to a cart without a verified prescription for the quantity requested.</p>
    </div>
</div></div>
@endsection

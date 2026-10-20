@extends('layouts.app')

@section('meta_title', 'Checkout — CityDrug')

@section('content')
<div class="page-head">
    <div class="container">
        <ol class="crumbs"><li><a href="{{ route('home') }}">Home</a></li><li><a href="{{ route('cart.index') }}">Cart</a></li><li>Checkout</li></ol>
        <h1>Checkout</h1>
    </div>
</div>

<div class="page-body">
<div class="container">
<form action="{{ route('checkout.place') }}" method="POST" novalidate>
@csrf
@if($errors->any())
    <div class="alert-cd error mb-3" role="alert"><i class="fas fa-circle-exclamation"></i><span>Please correct the highlighted fields.</span></div>
@endif
<div class="cart-layout">
    <div>
        @guest
        <div class="panel panel-pad mb-3">
            @include('auth._google-button', ['next' => 'checkout'])
            <p class="mb-0" style="font-size:14px">Already have an account? <a href="{{ route('login') }}" style="font-weight:700">Sign in</a>. You can also check out as a guest below.</p>
        </div>
        @endguest

        <div class="panel panel-pad mb-3">
            <h2>Contact and delivery</h2>
            <div class="cols-2">
                <div class="field"><label for="customer_name">Full name</label>
                    <input id="customer_name" type="text" name="customer_name" required maxlength="100" value="{{ old('customer_name', $user?->name) }}" class="@error('customer_name') is-invalid @enderror">
                    @error('customer_name')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="customer_phone">Phone</label>
                    <input id="customer_phone" type="tel" name="customer_phone" required value="{{ old('customer_phone', $user?->phone) }}" class="@error('customer_phone') is-invalid @enderror">
                    @error('customer_phone')<span class="error">{{ $message }}</span>@enderror</div>
            </div>
            <div class="field"><label for="customer_email">Email</label>
                <input id="customer_email" type="email" name="customer_email" required value="{{ old('customer_email', $user?->email) }}" class="@error('customer_email') is-invalid @enderror">
                @error('customer_email')<span class="error">{{ $message }}</span>@enderror</div>
            <div class="cols-2">
                <div class="field"><label for="shipping_city">City</label>
                    <input id="shipping_city" type="text" name="shipping_city" required value="{{ old('shipping_city', $user?->city) }}" class="@error('shipping_city') is-invalid @enderror">
                    @error('shipping_city')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="shipping_area">Area <span class="muted">(optional)</span></label>
                    <input id="shipping_area" type="text" name="shipping_area" value="{{ old('shipping_area') }}"></div>
            </div>
            <div class="field"><label for="shipping_address">Delivery address</label>
                <textarea id="shipping_address" name="shipping_address" rows="2" required class="@error('shipping_address') is-invalid @enderror" placeholder="House, street, landmark">{{ old('shipping_address', $user?->address) }}</textarea>
                @error('shipping_address')<span class="error">{{ $message }}</span>@enderror</div>
            <div class="field mb-0"><label for="customer_note">Note for the pharmacy <span class="muted">(optional)</span></label>
                <textarea id="customer_note" name="customer_note" rows="2" maxlength="1000">{{ old('customer_note') }}</textarea></div>
        </div>

        @guest
        <div class="panel panel-pad mb-3">
            <label class="check">
                <input type="checkbox" name="create_account" value="1" id="createAccount" @checked(old('create_account'))>
                <span><strong>Create an account for faster checkout next time</strong><br><span class="muted" style="font-size:13px">Optional. Track this order and see your order history. We use the details above.</span></span>
            </label>
            <div id="accountFields" class="cols-2 mt-3" style="{{ old('create_account') ? '' : 'display:none' }}">
                <div class="field"><label for="password">Password</label>
                    <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" class="@error('password') is-invalid @enderror">
                    @error('password')<span class="error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"></div>
            </div>
        </div>
        @endguest

        <div class="panel panel-pad">
            <h2>Payment method</h2>
            @foreach($paymentMethods as $value => $label)
                <label class="pay-opt">
                    <input type="radio" name="payment_method" value="{{ $value }}" @checked(old('payment_method', 'cod') === $value)>
                    <div>
                        <strong>{{ $label }}</strong>
                        <span>@if($value === 'cod') Pay the courier when your order arrives. @else We will contact you with the account details; your order is processed once payment is confirmed. @endif</span>
                    </div>
                </label>
            @endforeach
            @error('payment_method')<span class="error">{{ $message }}</span>@enderror
            <p class="muted mb-0" style="font-size:12.5px">Online card payment is not available yet. No payment is taken on this page.</p>
        </div>
    </div>

    <aside class="panel panel-pad summary" aria-label="Order summary">
        <h2>Your order</h2>
        @foreach($lines as $line)
            <div class="mini-item" style="grid-template-columns:56px 1fr auto;padding:10px 0">
                @include('partials.product-image', ['product' => $line->product, 'size' => 'sm'])
                <div><h3 style="font-size:13.5px">{{ $line->product->name }}</h3><span class="muted" style="font-size:12.5px">Qty {{ $line->quantity }}</span></div>
                <span class="price">Rs. {{ number_format($line->line_total, 0) }}</span>
            </div>
        @endforeach
        <div class="sum-row mt-2"><span>Subtotal</span><span>Rs. {{ number_format($subtotal, 0) }}</span></div>
        @if($discount > 0)<div class="sum-row"><span>Discount ({{ $coupon->code }})</span><span>− Rs. {{ number_format($discount, 0) }}</span></div>@endif
        <div class="sum-row"><span>Delivery</span><span>{{ $shipping > 0 ? 'Rs. ' . number_format($shipping, 0) : 'Free' }}</span></div>
        <div class="sum-row total"><span>Total</span><span>Rs. {{ number_format($total, 0) }}</span></div>
        <button type="submit" class="btn-cd lg block mt-3"><i class="fas fa-lock"></i> Place order</button>
        <a href="{{ route('cart.index') }}" class="btn-cd outline block mt-2">Back to cart</a>
    </aside>
</div>
</form>
</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var box = document.getElementById('createAccount'), fields = document.getElementById('accountFields');
    if (!box || !fields) return;
    var pw = document.getElementById('password');
    function sync() { fields.style.display = box.checked ? '' : 'none'; if (pw) pw.required = box.checked; }
    box.addEventListener('change', sync); sync();
})();
</script>
@endpush

<nav class="side-nav" aria-label="Account">
    <a href="{{ route('account.index') }}" class="{{ request()->routeIs('account.index') ? 'active' : '' }}"><i class="far fa-user"></i>Profile</a>
    <a href="{{ route('account.orders') }}" class="{{ request()->routeIs('account.orders', 'account.order-detail') ? 'active' : '' }}"><i class="fas fa-box"></i>My orders</a>
    <a href="{{ route('account.prescriptions') }}" class="{{ request()->routeIs('account.prescriptions*') ? 'active' : '' }}"><i class="fas fa-file-prescription"></i>Prescriptions</a>
    <a href="{{ route('wishlist.index') }}"><i class="far fa-heart"></i>Wishlist</a>
    @if(auth()->user()->isAdmin() || auth()->user()->role === 'pharmacist')
        <a href="{{ auth()->user()->role === 'pharmacist' ? route('admin.prescriptions.index') : route('admin.dashboard') }}"><i class="fas fa-gauge"></i>Staff area</a>
    @endif
    <form action="{{ route('logout') }}" method="POST">@csrf
        <a href="#" onclick="this.closest('form').submit();return false;"><i class="fas fa-right-from-bracket"></i>Sign out</a>
    </form>
</nav>

@php
    $role = auth()->user()->role;
    $isAdmin = in_array($role, ['admin', 'super_admin'], true);
    $roleLabel = ['super_admin' => 'Super admin', 'admin' => 'Administrator', 'pharmacist' => 'Pharmacist'][$role] ?? $role;
    if ($isAdmin) {
        $navPending  = \App\Models\Order::where('status', 'pending')->count();
        $navLowStock = \App\Models\Product::where('stock', '>', 0)->whereColumn('stock', '<', 'min_stock_alert')->count()
                     + \App\Models\Product::where('stock', 0)->count();
        $navUnread   = \App\Models\ContactMessage::whereNull('read_at')->count();
        $navDrafts   = \App\Models\Blog::where('is_published', false)->count();
    }
    $navRx = \App\Models\Prescription::where('status', 'pending')->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('meta_title', 'Admin') — CityDrug Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

@if(session('success'))<span data-toast="{{ session('success') }}" data-type="success" style="display:none"></span>@endif
@if(session('error'))<span data-toast="{{ session('error') }}" data-type="error" style="display:none"></span>@endif

<div id="sidebarOverlay" class="d-lg-none" style="position:fixed;inset:0;background:rgba(14,42,33,.5);z-index:999;display:none;opacity:0;transition:opacity .3s"></div>

<aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
    <a href="{{ $isAdmin ? route('admin.dashboard') : route('admin.prescriptions.index') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="fas fa-staff-snake"></i></div>
        <div class="sidebar-brand-text"><div class="name">CityDrug</div><div class="sub">{{ $isAdmin ? 'Admin' : 'Pharmacy' }}</div></div>
    </a>

    <nav class="sidebar-nav">
        @if($isAdmin)
            <div class="sidebar-section-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-gauge"></i> Dashboard</a>

            <div class="sidebar-section-label">Catalogue</div>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><i class="fas fa-pills"></i> Medicines &amp; products</a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><i class="fas fa-tags"></i> Categories</a>
            <a href="{{ route('admin.inventory.index') }}" class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"><i class="fas fa-boxes-stacked"></i> Inventory @if($navLowStock)<span class="badge-nav">{{ $navLowStock }}</span>@endif</a>
        @endif

        <div class="sidebar-section-label">Pharmacy</div>
        <a href="{{ route('admin.prescriptions.index') }}" class="{{ request()->routeIs('admin.prescriptions.*') ? 'active' : '' }}"><i class="fas fa-file-prescription"></i> Prescriptions @if($navRx)<span class="badge-nav">{{ $navRx }}</span>@endif</a>

        @if($isAdmin)
            <div class="sidebar-section-label">Sales</div>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="fas fa-bag-shopping"></i> Orders @if($navPending)<span class="badge-nav">{{ $navPending }}</span>@endif</a>
            <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> Payments</a>
            <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><i class="fas fa-ticket"></i> Coupons</a>
            <a href="{{ route('admin.receipts.index') }}" class="{{ request()->routeIs('admin.receipts.*') ? 'active' : '' }}"><i class="fas fa-receipt"></i> Receipts</a>

            <div class="sidebar-section-label">People</div>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index', 'admin.users.show') ? 'active' : '' }}"><i class="fas fa-users"></i> Customers</a>
            <a href="{{ route('admin.users.admins') }}" class="{{ request()->routeIs('admin.users.admins') ? 'active' : '' }}"><i class="fas fa-user-shield"></i> Staff</a>
            <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages.*') ? 'active' : '' }}"><i class="fas fa-envelope"></i> Messages @if($navUnread)<span class="badge-nav">{{ $navUnread }}</span>@endif</a>

            <div class="sidebar-section-label">Content</div>
            <a href="{{ route('admin.banners.index') }}" class="{{ request()->routeIs('admin.banners.*') ? 'active' : '' }}"><i class="fas fa-images"></i> Banners</a>
            <a href="{{ route('admin.blogs.index') }}" class="{{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}"><i class="fas fa-newspaper"></i> Blog @if($navDrafts)<span class="badge-nav">{{ $navDrafts }}</span>@endif</a>

            <div class="sidebar-section-label">Settings</div>
            <a href="{{ route('admin.settings.general') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="fas fa-gear"></i> Store settings</a>
        @endif

        <div class="sidebar-section-label">Store</div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i> View website</a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="sidebar-user-info"><div class="name">{{ auth()->user()->name }}</div><div class="role">{{ $roleLabel }}</div></div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0">@csrf
                <button type="submit" class="sidebar-logout" title="Sign out" aria-label="Sign out"><i class="fas fa-right-from-bracket"></i></button>
            </form>
        </div>
    </div>
</aside>

<div class="admin-main">
    <div class="admin-topbar">
        <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle navigation"><i class="fas fa-bars"></i></button>

        @if($isAdmin)
        <form class="topbar-search d-none d-md-block" action="{{ route('admin.products.index') }}" method="GET" role="search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="search" name="search" placeholder="Search medicines by name, SKU or brand" aria-label="Search products">
        </form>
        @endif

        <div class="topbar-spacer"></div>

        @if($isAdmin)
        <div class="topbar-actions">
            <div class="position-relative">
                <button class="topbar-btn" title="Notifications" id="notifBtn" onclick="toggleNotifications()" aria-label="Notifications">
                    <i class="fas fa-bell"></i><span class="notif-dot" id="notifDot" style="display:none"></span>
                </button>
                <div class="notif-dropdown" id="notifDropdown" data-url="{{ route('admin.notifications') }}" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:340px;background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow-lg);z-index:9999;overflow:hidden">
                    <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
                        <strong style="font-size:14px">Notifications</strong><span id="notifCount" class="badge bg-danger"></span>
                    </div>
                    <div id="notifList" style="max-height:350px;overflow-y:auto;padding:6px 0"><div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin"></i></div></div>
                    <div style="padding:10px 16px;border-top:1px solid var(--border);text-align:center"><a href="{{ route('admin.orders.index') }}" style="font-size:13px">View all orders →</a></div>
                </div>
            </div>
            <a class="topbar-btn" href="{{ route('admin.messages.index') }}" title="Messages" aria-label="Messages">
                <i class="fas fa-envelope"></i>@if($navUnread)<span class="msg-dot">{{ $navUnread }}</span>@endif
            </a>
        </div>
        @endif

        <div class="dropdown">
            <div class="topbar-user dropdown-toggle" data-bs-toggle="dropdown" role="button" tabindex="0">
                <div class="topbar-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="topbar-user-info d-none d-md-block"><div class="name">{{ auth()->user()->name }}</div><div class="role">{{ $roleLabel }}</div></div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm py-2" style="min-width:190px;border-radius:12px">
                @if($isAdmin)<li><a class="dropdown-item py-2" href="{{ route('admin.settings.general') }}"><i class="fas fa-gear me-2 text-muted"></i> Settings</a></li>@endif
                <li><a class="dropdown-item py-2" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square me-2 text-muted"></i> View site</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="dropdown-item py-2 text-danger"><i class="fas fa-right-from-bracket me-2"></i> Sign out</button></form></li>
            </ul>
        </div>
    </div>

    <div class="admin-content">
        @if(session('success'))<div class="admin-alert alert-success" role="status"><i class="fas fa-circle-check mt-1"></i><div>{{ session('success') }}</div></div>@endif
        @if(session('error'))<div class="admin-alert alert-danger" role="alert"><i class="fas fa-circle-exclamation mt-1"></i><div>{{ session('error') }}</div></div>@endif
        @yield('content')
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@if($isAdmin)<script src="{{ asset('js/admin-notifications.js') }}"></script>@endif
@stack('scripts')
</body>
</html>

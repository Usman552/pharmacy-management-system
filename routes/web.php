<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Response;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PrescriptionController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController    as AdminProductController;
use App\Http\Controllers\Admin\CategoryController   as AdminCategoryController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\OrderController      as AdminOrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReceiptController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ToggleStatusController;
use App\Http\Controllers\Admin\BlogController       as AdminBlogController;
use App\Http\Controllers\Admin\PrescriptionController as AdminPrescriptionController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\MessageController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// ─── MAIN SITE ───
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/',        [ProductController::class, 'index'])->name('index');
    Route::get('/search',  [ProductController::class, 'search'])->name('search');
    Route::get('/{slug}',  [ProductController::class, 'show'])->name('show');
});

Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/',       [CategoryController::class, 'index'])->name('index');
    Route::get('/{slug}', [CategoryController::class, 'show'])->name('show');
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',        [CartController::class, 'index'])->name('index');
    Route::post('/add',    [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/remove', [CartController::class, 'remove'])->name('remove');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon');
    Route::get('/items',   [CartController::class, 'items'])->name('items');
});

Route::prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/',                   [WishlistController::class, 'index'])->name('index');
    Route::post('/toggle',            [WishlistController::class, 'toggle'])->name('toggle');
    Route::delete('/{id}/remove',     [WishlistController::class, 'remove'])->name('remove');
    Route::post('/{id}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('move-to-cart');
    Route::post('/clear',             [WishlistController::class, 'clear'])->name('clear');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/',       [CheckoutController::class, 'index'])->name('index');
    Route::post('/place', [CheckoutController::class, 'placeOrder'])->name('place');
});
Route::get('/order/success/{orderNumber}', [CheckoutController::class, 'success'])->name('order.success');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/',             [AccountController::class, 'index'])->name('index');
    Route::get('/orders',       [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{num}', [AccountController::class, 'orderDetail'])->name('order-detail');
    Route::get('/prescriptions',                   [PrescriptionController::class, 'index'])->name('prescriptions');
    Route::get('/prescriptions/{prescription}',    [PrescriptionController::class, 'show'])->name('prescriptions.show');
    Route::get('/prescriptions/{prescription}/file', [PrescriptionController::class, 'file'])->name('prescriptions.file');
    Route::post('/prescriptions/{prescription}/add-to-cart', [PrescriptionController::class, 'addToCart'])->name('prescriptions.add-to-cart');
    Route::post('/profile',     [AccountController::class, 'updateProfile'])->name('update-profile');
    Route::post('/password',    [AccountController::class, 'updatePassword'])->name('update-password');
});

// Blog
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/',        [BlogController::class, 'index'])->name('index');
    Route::get('/{slug}',  [BlogController::class, 'show'])->name('show');
});

// Static
Route::get('/about',    [PageController::class, 'about'])->name('about');
Route::get('/contact',  [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');

// Auth
Route::get('/login',    [LoginController::class,    'showLoginForm'])->name('login');
Route::post('/login',   [LoginController::class,    'login']);
Route::post('/logout',  [LoginController::class,    'logout'])->name('logout');
Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register',[RegisterController::class, 'register']);
Route::get('/forgot-password',        [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password',       [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class,  'showResetForm'])->name('password.reset');
Route::post('/reset-password',        [ResetPasswordController::class,  'reset'])->name('password.update');

// Sitemap
Route::get('/sitemap.xml', function () {
    $products   = \App\Models\Product::where('is_active',1)->latest()->get();
    $categories = \App\Models\Category::where('is_active',1)->get();
    $blogs      = \App\Models\Blog::published()->latest('published_at')->get();
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
    // Static
    foreach (['', 'products', 'blog', 'about', 'contact', 'categories'] as $page) {
        $xml .= '<url><loc>'.config('app.url').($page?'/'.$page:'').'</loc><changefreq>'.($page===''?'daily':'weekly').'</changefreq><priority>'.($page===''?'1.0':'0.8').'</priority></url>';
    }
    foreach ($categories as $c) {
        $xml .= '<url><loc>'.route('categories.show',$c->slug).'</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>';
    }
    foreach ($products as $p) {
        $xml .= '<url><loc>'.route('products.show',$p->slug).'</loc><lastmod>'.$p->updated_at->toAtomString().'</lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>';
    }
    foreach ($blogs as $b) {
        $xml .= '<url><loc>'.route('blog.show',$b->slug).'</loc><lastmod>'.$b->updated_at->toAtomString().'</lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>';
    }
    $xml .= '</urlset>';
    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

// ─── ADMIN ───
// Pharmacy staff (pharmacists included) handle prescriptions and see incoming orders.
Route::middleware(['auth', 'role:admin,super_admin,pharmacist'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/prescriptions',                     [AdminPrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/create',              [AdminPrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/prescriptions',                    [AdminPrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::get('/prescriptions/{prescription}',      [AdminPrescriptionController::class, 'show'])->name('prescriptions.show');
    Route::get('/prescriptions/{prescription}/file', [AdminPrescriptionController::class, 'file'])->name('prescriptions.file');
    Route::post('/prescriptions/{prescription}/items',                [AdminPrescriptionController::class, 'addItem'])->name('prescriptions.items.store');
    Route::put('/prescriptions/{prescription}/items/{item}',          [AdminPrescriptionController::class, 'updateItem'])->name('prescriptions.items.update');
    Route::delete('/prescriptions/{prescription}/items/{item}',       [AdminPrescriptionController::class, 'destroyItem'])->name('prescriptions.items.destroy');
    Route::post('/prescriptions/{prescription}/verify',               [AdminPrescriptionController::class, 'verify'])->name('prescriptions.verify');
    Route::post('/prescriptions/{prescription}/reject',               [AdminPrescriptionController::class, 'reject'])->name('prescriptions.reject');
});

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/inventory',                    [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/{product}/adjust',  [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/inventory/{product}/history',  [InventoryController::class, 'movements'])->name('inventory.history');
    Route::resource('coupons', CouponController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    Route::resource('products',   AdminProductController::class);
    Route::resource('categories', AdminCategoryController::class);
    Route::resource('banners',    BannerController::class);
    Route::resource('blogs',      AdminBlogController::class);

    Route::get('/orders',                  [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}',          [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status',  [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/payment', [AdminOrderController::class, 'updatePayment'])->name('orders.payment');
    Route::delete('/orders/{order}',       [AdminOrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/orders/{order}/receipt',  [AdminOrderController::class, 'receipt'])->name('orders.receipt');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::get('/receipts',         [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/receipts/{order}', [ReceiptController::class, 'show'])->name('receipts.show');

    Route::get('/users',           [UserController::class, 'index'])->name('users.index');
    Route::get('/users/admins',    [UserController::class, 'admins'])->name('users.admins');
    Route::post('/users/staff',    [UserController::class, 'storeStaff'])->name('users.staff.store');
    Route::get('/users/{user}',    [UserController::class, 'show'])->name('users.show');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/settings',             [SettingsController::class, 'general'])->name('settings.general');
    Route::post('/settings',            [SettingsController::class, 'saveGeneral'])->name('settings.save');
    Route::get('/settings/appearance',  [SettingsController::class, 'appearance'])->name('settings.appearance');
    Route::post('/settings/appearance', [SettingsController::class, 'saveAppearance'])->name('settings.appearance.save');

    Route::get('/notifications', function () {
        $orders   = \App\Models\Order::where('status','pending')->latest()->take(5)->get();
        $lowStock = \App\Models\Product::whereColumn('stock','<','min_stock_alert')->where('is_active',1)->take(5)->get();
        return response()->json([
            'orders'   => $orders->map(fn($o)=>['id'=>$o->id,'number'=>$o->order_number,'total'=>$o->total,'status'=>$o->status]),
            'lowStock' => $lowStock->map(fn($p)=>['id'=>$p->id,'name'=>$p->name,'stock'=>$p->stock]),
            'count'    => $orders->count() + $lowStock->count(),
        ]);
    })->name('notifications');

    Route::post('/toggle-status', [ToggleStatusController::class, 'toggle'])->name('toggle-status');

    // Products expiring
    Route::get('/products-expiring', [AdminProductController::class, 'expiring'])->name('products.expiring');
});

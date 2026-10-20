<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::defaultView('vendor.pagination.custom');

        // Shared storefront data: site settings, navigation categories, cart count.
        View::composer('layouts.app', function ($view) {
            try {
                $settings = Setting::pluck('value', 'key')->toArray();
                $cats = Category::where('is_active', true)->orderBy('sort_order')->get();
            } catch (\Throwable) {
                $settings = [];
                $cats = collect();
            }
            $view->with([
                'site'          => $settings + ['site_name' => 'CityDrug'],
                'navCategories' => $cats,
                'cartCount'     => app(CartService::class)->count(),
                'wishCount'     => is_array(session('wishlist')) ? count(session('wishlist')) : 0,
            ]);
        });

        // Add fullUrlWithQuery macro to Request
        Request::macro('fullUrlWithQuery', function (array $merge): string {
            $params = array_merge($this->query(), $merge);
            $params = array_filter($params, fn($v) => $v !== null);
            $query  = http_build_query($params);
            return $this->url() . ($query ? '?' . $query : '');
        });
    }
}

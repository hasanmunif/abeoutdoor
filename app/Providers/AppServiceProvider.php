<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repository bindings
        $this->app->bind(
            \App\Repositories\Interfaces\ProductRepositoryInterface::class,
            \App\Repositories\ProductRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\TransactionRepositoryInterface::class,
            \App\Repositories\TransactionRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\StoreRepositoryInterface::class,
            \App\Repositories\StoreRepository::class
        );

        // Register service bindings
        $this->app->bind(
            \App\Services\Interfaces\CheckoutServiceInterface::class,
            \App\Services\CheckoutService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\MidtransServiceInterface::class,
            \App\Services\MidtransService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan event listener untuk redirect setelah login berhasil
    }
}
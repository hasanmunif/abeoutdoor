<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
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
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
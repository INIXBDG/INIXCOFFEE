<?php

namespace App\Providers;

use App\Channels\WebPushChannel;
use App\Services\WebPushService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        Notification::extend('webpush', function ($app) {
            return $app->make(WebPushChannel::class);
        });
    }
}

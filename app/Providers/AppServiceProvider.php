<?php

namespace App\Providers;

use App\Channels\WebPushChannel;
use App\Services\WebPushService;
use App\Support\CacheRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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

        if (request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // Deteksi otomatis N+1 Lazy Loading pada non-production
        Model::preventLazyLoading(!app()->isProduction());
        if (!app()->isProduction() && !config('app.prevent_lazy_loading_strict', false)) {
            Model::handleLazyLoadingViolationUsing(function ($model, string $relation): void {
                Log::warning('N+1 Lazy Loading detected', [
                    'model'    => get_class($model),
                    'relation' => $relation,
                    'url'      => request()->fullUrl(),
                ]);
            });
        }

        // Invalidasi cache badge notifikasi otomatis begitu notifikasi baru dikirim (termasuk via Redis Queue worker)
        Event::listen(NotificationSent::class, function (NotificationSent $event): void {
            if (is_object($event->notifiable) && method_exists($event->notifiable, 'getKey')) {
                CacheRegistry::forgetUnreadNotifCount($event->notifiable->getKey());
            }
        });

        View::composer([
            'layouts.app',
            'layouts.menus',
            'layouts_crm.app',
            'layouts_crm.navbar',
            'layouts_kpi.app',
            'layouts_kpi.navbar',
            'layouts_office.app',
            'layouts_office.navbar',
        ], function ($view) {
            $count = 0;

            if (auth()->check()) {
                $user = auth()->user();
                $req = request();
                if (!$req->attributes->has('_global_unread_notif_count')) {
                    $req->attributes->set(
                        '_global_unread_notif_count',
                        CacheRegistry::rememberUnreadNotifCount(
                            $user->id,
                            fn () => $user->unreadNotifications()->count(),
                            60
                        )
                    );
                }
                $count = (int) $req->attributes->get('_global_unread_notif_count');
            }

            $view->with('globalUnreadNotifCount', $count);
        });
    }
}

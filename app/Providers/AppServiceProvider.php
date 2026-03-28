<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\Payment;
use App\Models\ServiceJob;
use App\Policies\ConversationPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ServiceJobPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if ($this->app->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Gate::policy(ServiceJob::class, ServiceJobPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('admin', function ($request) {

            return Limit::perMinute(120)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}

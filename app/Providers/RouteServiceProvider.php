<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home'; // Atau '/admin/dashboard' jika Anda ingin default ke sana setelah login

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // Ini untuk API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php')); // <<< BARIS INI SANGAT PENTING!

            // Ini untuk Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}

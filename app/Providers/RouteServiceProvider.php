<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();
        $this->registerFailsafeForOtherModelBindings();
        
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
            
            Route::middleware('web')
                ->group(base_path('routes/auth.php'))
                ->group(base_path('routes/web.php'));
            
            $this->registerApplicationRoutes();
        });
        
        $this->registerRouteBindings();
    }

    /**
     * Easily register any new routes for the application.
     *
     * @return void
     */
    private function registerApplicationRoutes()
    {
        collect(File::allFiles(base_path('routes')))
            ->map(fn ($splFileInfo) => $splFileInfo->getFilenameWithoutExtension())
            ->reject(fn($file) => in_array($file, ['api', 'web', 'auth', 'channels']))
            ->each(fn ($file) => Route::middleware('api')
                ->prefix('api')
                ->namespace($this->namespace)
                ->group(base_path("routes/$file.php"))
        );
    }

    /**
     * Register application route bindings.
     *
     * @return void
     */
    private function registerRouteBindings(): void
    {
        // Register route bindings for the application.
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', fn (Request $request) =>
            Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
        );
    }
}

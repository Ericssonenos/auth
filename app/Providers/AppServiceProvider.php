<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use App\Http\Middleware\RhPermissionMiddleware;

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
        // Registrar alias de middleware para RH
        try {
            $router = $this->app->make(Router::class);
            $router->aliasMiddleware('rh.auth', RhPermissionMiddleware::class);
        } catch (\Exception $e) {
            // ambiente sem Router disponível no bootstrap: ignorar com log
            \Illuminate\Support\Facades\Log::info('Não foi possível registrar alias rh.auth: ' . $e->getMessage());
        }
    }
}

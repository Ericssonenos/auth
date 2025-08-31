<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use App\Http\Middleware\RhPermissionMiddleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use App\Services\RH\usuarioServices;
use Illuminate\Support\Facades\Blade;

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
        // Registra singleton que encapsula dados do usuário
        $this->app->singleton(usuarioServices::class, function ($app) {
            return new usuarioServices(
                session("list_Permissoes_session", []),
                session("dados_Usuario", [])
            );
        });

        // View Composer para compartilhar dados da sessão com todas as views
        View::composer('*', function ($view) {
            $view->with('usuarioServices', $this->app->make(usuarioServices::class));
        });

        Blade::if('temPermissao', function ($permissao) {
            return app(usuarioServices::class)->temPermissao($permissao);
        });
    }
}

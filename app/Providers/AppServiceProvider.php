<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use App\Http\Middleware\RhPermissionMiddleware;
use Illuminate\Support\Facades\Gate;
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
        // Registrar alias de middleware para RH

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('rh.auth', RhPermissionMiddleware::class);


        // Gate global: verifica permissões armazenadas na session (ex.: use @can('PERM_X') nas views)
        Gate::before(function ($_user, $ability) {

            $usuario = session('id_Usuario_session') ?: request()->attributes->get('id_Usuario_session');
            if (empty($usuario)) {
                return null; // não decidimos, deixa o fluxo padrão decidir
            }

            $permissao = session("list_Permissoes_session.{$usuario}", []);
            return in_array($ability, $permissao) ? true : null;
        });


        $id_Usuario = session('id_Usuario_session') ?: request()->header('X-id_Usuario');
        // Compatibilidade: algumas views esperam $usuario em vez de $id_Usuario_session
        View::share('usuario', $id_Usuario);
    }
}

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
        try {
            $router = $this->app->make(Router::class);
            $router->aliasMiddleware('rh.auth', RhPermissionMiddleware::class);
        } catch (\Exception $e) {
            // ambiente sem Router disponível no bootstrap: ignorar com log
            \Illuminate\Support\Facades\Log::info('Não foi possível registrar alias rh.auth: ' . $e->getMessage());
        }

        // Gate global: verifica permissões armazenadas na session (ex.: use @can('PERM_X') nas views)
        try {
            Gate::before(function ($_user, $ability) {
                $usuario = session('rh_usuario') ?: request()->attributes->get('rh_usuario');
                if (empty($usuario)) {
                    return null; // não decidimos, deixa o fluxo padrão decidir
                }

                $permissao = session("rh_permissions.{$usuario}", []);
                return in_array($ability, $permissao) ? true : null;
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info('Gate::before not registered: ' . $e->getMessage());
        }

        // Compartilhar permissões e matrícula em todas as views para evitar passar manualmente
        try {
            $mat = session('rh_usuario') ?: request()->header('X-id_Usuario');
            // Não compartilhamos mais a lista completa de permissões; use o Gate/@can nas views
            // para centralizar a autorização. Mantemos apenas a matrícula para exibição.
            View::share('rh_usuario', $mat);
            // Compatibilidade: algumas views esperam $usuario em vez de $rh_usuario
            View::share('usuario', $mat);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info('View::share rh permissao failed: ' . $e->getMessage());
        }
    }
}

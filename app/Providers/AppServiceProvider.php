<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use App\Http\Middleware\RhPermissionMiddleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

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

        // View Composer para compartilhar dados da sessão com todas as views
        View::composer('*', function ($view) {
            $rawPerms = session("list_Permissoes_session", []);

            $permissoes = [];
            // Caso o modelo tenha guardado linhas (array de assoc) com chave 'cod_permissao', extrair valores
            if (is_array($rawPerms)) {
                foreach ($rawPerms as $p) {
                    if (is_array($p) && array_key_exists('cod_permissao', $p)) {
                        $permissoes[] = $p['cod_permissao'];
                    } elseif (is_string($p)) {
                        $permissoes[] = $p;
                    }
                }
            }

            $view->with([
                'permissoes' => $permissoes,
                'dados_Usuario' => session('dados_Usuario')
            ]);
        });
    }
}

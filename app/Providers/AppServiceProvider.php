<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
        // Registra singleton que encapsula dados do usuário logado na sessão
        $this->app->singleton(usuarioServices::class, function ($app) {
            return new usuarioServices(
                session("list_Permissoes_session", []),
                session("dados_Usuario", [])
            );
        });

        // View Composer para compartilhar dados do usuário com todas as views do sistema
        View::composer('*', function ($view) {
            $servicoDoUsuario = $this->app->make(usuarioServices::class);
            $view->with('usuarioServices', $servicoDoUsuario);

            // Para ambiente de desenvolvimento - facilita debugging das permissões
            if (config('app.debug')) {
                $view->with('debug_permissoes_usuario', $servicoDoUsuario->permissoes());
                $view->with('debug_dados_usuario', $servicoDoUsuario->usuario());
            }
        });

        // Blade directive para verificar se usuário possui permissão específica
        Blade::if('temPermissao', function ($permissaoNecessaria) {
            return app(usuarioServices::class)->temPermissao($permissaoNecessaria);
        });

        // Blade directive para verificar se usuário possui qualquer uma das permissões listadas
        Blade::if('possuiQualquerUmaDasPermissoes', function (...$permissoesNecessarias) {
            $servicoDoUsuario = app(usuarioServices::class);
            foreach ($permissoesNecessarias as $permissaoNecessaria) {
                if ($servicoDoUsuario->temPermissao($permissaoNecessaria)) {
                    return true;
                }
            }
            return false;
        });

        // Blade directive para verificar se usuário está autenticado no sistema
        Blade::if('usuarioEstaAutenticado', function () {
            return !empty(app(usuarioServices::class)->usuario());
        });
    }
}

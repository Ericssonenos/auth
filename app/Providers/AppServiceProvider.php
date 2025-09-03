<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\RH\usuarioServices;
use Illuminate\Support\Facades\Blade;
use Illuminate\Routing\Router;
use PhpParser\Node\Expr\Cast\Object_;

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
        // Garantir que o alias do middleware esteja registrado no router
        // Isso evita problemas de timing caso as rotas sejam carregadas
        // antes do registro feito em bootstrap/app.php
        // if ($this->app->bound(Router::class)) {
        //     $this->app->make(Router::class)->aliasMiddleware('usuario', \App\Http\Middleware\UsuarioMiddleware::class);
        // }

        // Registra singleton que encapsula dados do usuário logado na sessão
        $this->app->singleton(usuarioServices::class, function ($app) {
            return new usuarioServices(
                session("dadosUsuarioSession", []),
            );
        });

        // View Composer para compartilhar dados do usuário com todas as views do sistema
        View::composer('*', function ($view) {
            $dadosUsuario = $this->app->make(usuarioServices::class);
            $view->with('dadosUsuario', $dadosUsuario);
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

    }
}

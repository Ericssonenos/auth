<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\rh\usuarioServices;
use Illuminate\Support\Facades\Blade;
use App\Helpers\BladeHelpers;

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

        // JSON_UNESCAPED_UNICODE aplicado pelo middleware JsonUnicodeMiddleware

        // Registra singleton que encapsula dados do usuário logado na sessão
        $this->app->singleton(usuarioServices::class, function ($app) {
            return new usuarioServices(
                session("dados_usuario_sessao", []),
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
        Blade::if('possuiQualquerUmaDasPermissoes', function (...$cod_permissoes_necessarias) {
            $servicoDoUsuario = app(usuarioServices::class);
            foreach ($cod_permissoes_necessarias as $permissaoNecessaria) {
                if ($servicoDoUsuario->temPermissao($permissaoNecessaria)) {
                    return true;
                }
            }
            return false;
        });

        Blade::if('usuarioLogado', function () {
            return app(usuarioServices::class)->estaLogado();
        });

        // diretiva simples que passa os 3 parâmetros para o helper
        Blade::directive('href_permissa', function ($expression) {
            return "<?php echo \\App\\Helpers\\BladeHelpers::hrefPermissa({$expression}); ?>";
        });

    }
}

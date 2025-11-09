<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registra middleware personalizado para controle de acesso de usuários
        $middleware->alias([
            'usuarioMiddleware' => \App\Http\Middleware\UsuarioMiddleware::class,
        ]);

        $middleware->append([
            \App\Http\Middleware\JsonUnicodeMiddleware::class,
        ]);

        // Em modo debug, ignora CSRF para rotas de API
        if (env('APP_DEBUG', true)) {
            $middleware->validateCsrfTokens(except: [
                'api/*',
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

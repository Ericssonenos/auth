<?php

/*
|--------------------------------------------------------------------------
| Exemplos de Uso do Middleware UsuarioMiddleware
|--------------------------------------------------------------------------
|
| Este arquivo demonstra as diferentes formas de utilizar o middleware
| de controle de acesso de usuários no sistema.
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;

// Exemplo 1: Detecção automática de permissões baseada no nome da rota
Route::get('/usuarios', [UserController::class, 'index'])
    ->name('usuarios.listar')
    ->middleware('usuario'); // Vai procurar permissão 'usuarios.listar'

// Exemplo 2: Detecção automática baseada em controller.action
Route::post('/usuarios', [UserController::class, 'store'])
    ->middleware('usuario'); // Vai procurar permissão 'user.store'

// Exemplo 3: Permissão específica definida manualmente
Route::get('/admin/painel', [AdminController::class, 'dashboard'])
    ->middleware('usuario:administrador.painel.principal');

// Exemplo 4: Múltiplas permissões aceitas (operação OR lógico)
Route::get('/relatorios', [AdminController::class, 'relatorios'])
    ->middleware('usuario:relatorios.visualizar,administrador.completo,gerente.relatorios');

// Exemplo 5: Grupo de rotas com middleware aplicado
Route::middleware(['usuario'])->group(function () {
    Route::resource('produtos', ProductController::class);
    Route::get('/dashboard', [UserController::class, 'dashboard']);
});

// Exemplo 6: Combinando com outros middlewares
Route::middleware(['web', 'usuario:usuarios.gerenciar'])->group(function () {
    Route::get('/usuarios/gerenciar', [UserController::class, 'manage']);
    Route::post('/usuarios/ativar/{id}', [UserController::class, 'activate']);
    Route::post('/usuarios/desativar/{id}', [UserController::class, 'deactivate']);
});

/*
|--------------------------------------------------------------------------
| Estratégias de Detecção Automática de Permissões
|--------------------------------------------------------------------------
|
| Quando não especificada, o middleware tentará detectar automaticamente
| a permissão necessária na seguinte ordem de prioridade:
|
| 1. Nome da rota (route()->getName())
|    - Exemplo: ->name('produtos.criar') = permissão 'produtos.criar'
|
| 2. Controller.Action formatado
|    - Exemplo: ProductController@store = permissão 'product.store'
|
| 3. Método HTTP + URI formatada
|    - Exemplo: POST /produtos = permissão 'post.produtos'
|
| 4. Apenas URI formatada
|    - Exemplo: /produtos/criar = permissão 'produtos/criar'
|
*/

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\rh\LoginController;
use App\Http\Controllers\rh\UsuarioController;
use App\Http\Controllers\rh\GrupoController;
use App\Http\Controllers\orcamento\OrcamentoController;


Route::get('/', function () {
    return view('welcome');
})->name('home.view');

Route::view('em-desenvolvimento', 'pages.em-desenvolvimento')->name('em-desenvolvimento');


// rotas para login
Route::get('login', [LoginController::class, 'exibirFormularioLogin'])->name('login');

Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::post('alterar-senha', [LoginController::class, 'processarAlterarSenha'])->name('alterar.senha');
Route::post('logar', [LoginController::class, 'processarLogin'])->name('logar');

// alteração de senha obrigatória (exibir formulário e processar)
Route::get('alterar-senha', [LoginController::class, 'exibirAlterarSenha'])->name('alterar.senha.view');


// Rotas rh - Views
Route::prefix('rh')->group(function () {
    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuario.view')->middleware('usuarioMiddleware');
    Route::post('usuario/{id}/gerar-senha', [LoginController::class, 'GerarNovaSenha'])->name('usuario.gerar_senha')->middleware('usuarioMiddleware');

    Route::get('grupos', [GrupoController::class, 'index'])
        ->name('grupos.view')
        ->middleware('usuarioMiddleware');
});

 // Rotas orcamento - Views
 Route::prefix('orcamento')->group(function () {
     Route::get('/', [OrcamentoController::class, 'listarOrcamentosDisponiveis'])->name('orcamento.listar')->middleware('usuarioMiddleware');
 });

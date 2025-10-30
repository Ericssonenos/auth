<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\rh\LoginController;
use App\Http\Controllers\rh\UsuarioController;
use App\Http\Controllers\rh\GrupoController;


Route::get('/', function () {
    return view('welcome');
})->name('home.view');





// rotas para login
Route::get('login', [LoginController::class, 'exibirFormularioLogin'])->name('login');



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

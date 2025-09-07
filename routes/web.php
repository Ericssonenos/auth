<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RH\LoginController;
use App\Http\Controllers\RH\UsuarioController;

Route::get('/', function () {
    return view('welcome');
})->name('home.view');



// rotas para login
Route::get('login', [LoginController::class, 'exibirFormularioLogin'])->name('login');
Route::post('login', [LoginController::class, 'processarLogin']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// alteração de senha obrigatória (exibir formulário e processar)
Route::get('alterar-senha', [LoginController::class, 'exibirAlterarSenha'])->name('alterar.senha.view');
Route::post('alterar-senha', [LoginController::class, 'processarAlterarSenha'])->name('alterar.senha');

// Rotas RH - Usuários
Route::prefix('rh')->group(function () {
    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuario.view')->middleware('usuarioMiddleware');
    Route::post('api/usuarios/dados', [UsuarioController::class, 'ObterDadosUsuarios'])->name('usuarios.dados')->middleware('usuarioMiddleware'); // para DataTable AJAX
    Route::post('api/usuario/cadastrar', [UsuarioController::class, 'CadastrarUsuarios'])->name('usuario.cadastrar')->middleware('usuarioMiddleware');
    Route::put('api/usuario/atualizar/{id}', [UsuarioController::class, 'AtualizarUsuarios'])->name('usuario.atualizar')->middleware('usuarioMiddleware');
    Route::post('usuario/{id}/gerar-senha', [UsuarioController::class, 'GerarNovaSenha'])->name('usuario.gerar_senha')->middleware('usuarioMiddleware');


});

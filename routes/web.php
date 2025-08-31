<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RH\LoginController;
use App\Http\Controllers\RH\UsuarioController;
use App\Http\Middleware\UsuarioMiddleware;

Route::get('/', function () {
    return view('welcome');
});


// Painel protegido exemplo
Route::get('painel', function () {
    return view('welcome');
})->name('painel');//->middleware('RH')->name('painel');

// rotas para login
Route::get('login', [LoginController::class, 'exibirFormularioLogin'])->name('login');
Route::post('login', [LoginController::class, 'processarLogin']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Rotas RH - Usuários
Route::prefix('rh')->group(function () {
    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarioView');
    Route::post('api/usuarios', [UsuarioController::class, 'ObterDadosUsuarios'])->name('usuariosAPI'); // para DataTable AJAX
    Route::post('usuarios', [UsuarioController::class, 'store'])->name('rh.usuarios.store');
    Route::put('usuarios/{id}', [UsuarioController::class, 'update'])->name('rh.usuarios.update');
    // endpoints adicionais serão adicionados conforme necessidade
})->middleware(UsuarioMiddleware::class); // Aplica middleware de controle de acesso

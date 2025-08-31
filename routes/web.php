<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RH\LoginController;


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

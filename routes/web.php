<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rotas do módulo RH
Route::prefix('rh')->group(function () {
    // Usuários
    Route::get('usuarios', [\App\Http\Controllers\RH\UsuarioController::class, 'ListaUsuarios']);
    Route::get('usuarios/{matricula}/permissoes', [\App\Http\Controllers\RH\UsuarioController::class, 'ObterPermissoesMatricula']);
    Route::post('usuarios/permissoes', [\App\Http\Controllers\RH\UsuarioController::class, 'AtribuirPermissoes']);
    Route::post('usuarios/grupos', [\App\Http\Controllers\RH\UsuarioController::class, 'AtribuirGrupo']);
    Route::delete('usuarios/permissoes', [\App\Http\Controllers\RH\UsuarioController::class, 'RemoverPermissoes']);
    Route::delete('usuarios/grupos', [\App\Http\Controllers\RH\UsuarioController::class, 'RemoverGrupo']);

    // Grupos
    Route::get('grupos', [\App\Http\Controllers\RH\GrupoController::class, 'ListaGrupos']);
    Route::get('grupos/{id}', [\App\Http\Controllers\RH\GrupoController::class, 'ObterGrupoPorId']);
    Route::post('grupos', [\App\Http\Controllers\RH\GrupoController::class, 'CriarGrupo']);
    Route::put('grupos/{id}', [\App\Http\Controllers\RH\GrupoController::class, 'AtualizarGrupo']);
    Route::delete('grupos/{id}', [\App\Http\Controllers\RH\GrupoController::class, 'RemoverGrupo']);
    Route::post('grupos/permissoes', [\App\Http\Controllers\RH\GrupoController::class, 'AtribuirPermissaoGrupo']);
    Route::delete('grupos/permissoes', [\App\Http\Controllers\RH\GrupoController::class, 'RemoverPermissaoGrupo']);
    Route::post('grupos/relacoes', [\App\Http\Controllers\RH\GrupoController::class, 'AtribuirGrupoGrupo']);
    Route::delete('grupos/relacoes', [\App\Http\Controllers\RH\GrupoController::class, 'RemoverGrupoGrupo']);

    // Permissões
    Route::get('permissoes', [\App\Http\Controllers\RH\PermissaoController::class, 'ListaPermissoes']);
    Route::get('permissoes/{id}', [\App\Http\Controllers\RH\PermissaoController::class, 'ObterPermissaoPorId']);
    Route::post('permissoes', [\App\Http\Controllers\RH\PermissaoController::class, 'CriarPermissao']);
    Route::put('permissoes/{id}', [\App\Http\Controllers\RH\PermissaoController::class, 'AtualizarPermissao']);
    Route::delete('permissoes/{id}', [\App\Http\Controllers\RH\PermissaoController::class, 'RemoverPermissao']);

    // Categorias
    Route::get('categorias', [\App\Http\Controllers\RH\CategoriaController::class, 'ListaCategorias']);
    Route::get('categorias/{id}', [\App\Http\Controllers\RH\CategoriaController::class, 'ObterCategoriaPorId']);
    Route::post('categorias', [\App\Http\Controllers\RH\CategoriaController::class, 'CriarCategoria']);
    Route::put('categorias/{id}', [\App\Http\Controllers\RH\CategoriaController::class, 'AtualizarCategoria']);
    Route::delete('categorias/{id}', [\App\Http\Controllers\RH\CategoriaController::class, 'RemoverCategoria']);

    // Demo de acesso (usando middleware rh.auth)
    Route::get('/', [\App\Http\Controllers\RH\AccessDemoController::class, 'Demo'])->middleware('rh.auth');
});

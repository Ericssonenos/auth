<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RH\UsuarioController;
use App\Http\Controllers\RH\GrupoController;
use App\Http\Controllers\RH\PermissaoController;
use App\Http\Controllers\RH\CategoriaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas de API sem proteção CSRF (middleware 'api' por padrão)
| Prefix automático: /api
|
*/

Route::middleware(['web', 'usuarioMiddleware'])->prefix('rh')->group(function () {
    // Usuários
    Route::post('usuarios/dados', [UsuarioController::class, 'ObterDadosUsuarios']);
    Route::post('usuario/cadastrar', [UsuarioController::class, 'CadastrarUsuarios']);
    Route::put('usuario/atualizar/{usuario_id}', [UsuarioController::class, 'AtualizarUsuarios']);
    Route::delete('usuario/deletar/{usuario_id}', [UsuarioController::class, 'DeletarUsuarios']);

    // Permissões
    Route::post('permissoes/dados', [PermissaoController::class, 'ObterRHPermissoes']);
    Route::post('usuario/permissao/adicionar', [UsuarioController::class, 'AtribuirPermissoes']);
    Route::delete('usuario/permissao/remover/{id_rel_usuario_permissao}', [UsuarioController::class, 'RemoverPermissoes']);

    // Grupos
    Route::post('grupos/dados', [GrupoController::class, 'ObterDadosGrupo']);
    Route::post('usuario/grupo/adicionar', [UsuarioController::class, 'AtribuirGrupo']);
    Route::delete('usuario/grupo/remover/{id_rel_usuario_grupo}', [UsuarioController::class, 'RemoverGrupo']);
    Route::post('grupo/cadastrar', [GrupoController::class, 'CadastrarGrupo']);
    Route::put('grupo/atualizar/{grupo_id}', [GrupoController::class, 'AtualizarGrupo']);
    Route::delete('grupo/deletar/{grupo_id}', [GrupoController::class, 'DeletarGrupo']);
    Route::post('grupo/permissao/adicionar', [GrupoController::class, 'AtribuirPermissaoGrupo']);
    Route::delete('grupo/permissao/remover/{id_rel_grupo_permissao}', [GrupoController::class, 'RemoverPermissaoGrupo']);

    // Categorias
    Route::post('categorias/dados', [CategoriaController::class, 'ObterCategorias']);
});

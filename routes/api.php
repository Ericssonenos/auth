<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\rh\UsuarioController;
use App\Http\Controllers\rh\GrupoController;
use App\Http\Controllers\rh\PermissaoController;
use App\Http\Controllers\rh\CategoriaController;
use App\Http\Controllers\rh\LoginController;
use App\Http\Controllers\orcamento\OrcamentoController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas de API sem proteção CSRF (middleware 'api' por padrão)
| Prefix automático: /api
|
*/




Route::middleware('web')->post('logar', [LoginController::class, 'processarLogin'])->name('logar');

Route::middleware(['web', 'usuarioMiddleware'])->prefix('rh')->group(function () {

    // Usuários
    Route::prefix('usuario')->group(function () {
        // Usuários
        Route::post('dados', [UsuarioController::class, 'ObterDadosUsuarios']);
        Route::post('cadastrar', [UsuarioController::class, 'CadastrarUsuarios']);
        Route::put('atualizar/{usuario_id}', [UsuarioController::class, 'AtualizarUsuarios']);
        Route::delete('deletar/{usuario_id}', [UsuarioController::class, 'DeletarUsuarios']);
        // Permissões
        Route::post('permissao/adicionar', [UsuarioController::class, 'AtribuirPermissoes']);
        Route::delete('permissao/remover/{id_rel_usuario_permissao}', [UsuarioController::class, 'RemoverPermissoes']);
        // Grupos
        Route::post('grupo/adicionar', [UsuarioController::class, 'AtribuirGrupo']);
        Route::delete('grupo/remover/{id_rel_usuario_grupo}', [UsuarioController::class, 'RemoverGrupo']);
    });

    // Permissões
    Route::prefix('permissao')->group(function () {
        // Permissões
        Route::post('dados', [PermissaoController::class, 'ObterPermissoes']);
        Route::post('cadastrar', [PermissaoController::class, 'CadastrarPermissao']);
        Route::put('atualizar/{permissao_id}', [PermissaoController::class, 'AtualizarPermissao']);
        Route::delete('deletar/{permissao_id}', [PermissaoController::class, 'DeletarPermissao']);
    });

    // Grupos
    Route::prefix('grupo')->group(function () {
        // Grupos
        Route::post('dados', [GrupoController::class, 'ObterDadosGrupo']);

        Route::post('cadastrar', [GrupoController::class, 'CadastrarGrupo']);
        Route::put('atualizar/{grupo_id}', [GrupoController::class, 'AtualizarGrupo']);
        Route::delete('deletar/{grupo_id}', [GrupoController::class, 'DeletarGrupo']);
        Route::post('permissao/adicionar', [GrupoController::class, 'AtribuirPermissaoGrupo']);
        Route::delete('permissao/remover/{id_rel_grupo_permissao}', [GrupoController::class, 'RemoverPermissaoGrupo']);
    });

    // Categorias
    Route::prefix('categoria')->group(function () {
        Route::post('dados', [CategoriaController::class, 'ObterCategorias']);
    });
});

Route::middleware(['web', 'usuarioMiddleware'])->prefix('orcamento')->group(function () {
    Route::post('obter-dados-orcamentos', [OrcamentoController::class, 'obterDadosOrcamentos']);
    Route::post('salvar', [OrcamentoController::class, 'salvarOrcamento']);
    Route::post('obter-detalhes/{id}', [OrcamentoController::class, 'obterDetalhesOrcamento']);
    Route::post('excluir/{id}', [OrcamentoController::class, 'excluirOrcamento']);
    Route::post('enviar-workflow/{id}', [OrcamentoController::class, 'enviarOrcamentoParaWorkflow']);
    Route::post('duplicar/{id}', [OrcamentoController::class, 'duplicarOrcamento']);
    Route::get('gerar-pdf/{id}', [OrcamentoController::class, 'gerarPdfOrcamento']);
});

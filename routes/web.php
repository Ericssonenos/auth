<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RH\LoginController;
use App\Http\Controllers\RH\UsuarioController;
use App\Http\Controllers\RH\PermissaoController;
use App\Http\Controllers\RH\GrupoController;

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
    Route::put('api/usuario/atualizar/{usuario_id}', [UsuarioController::class, 'AtualizarUsuarios'])->name('usuario.atualizar')->middleware('usuarioMiddleware');
    Route::delete('api/usuario/deletar/{usuario_id}', [UsuarioController::class, 'DeletarUsuarios'])->name('usuario.deletar')->middleware('usuarioMiddleware');
    // permissões: obter lista com flag (possui), e endpoints para adicionar/remover
    Route::post('api/permissoes/dados', [PermissaoController::class, 'ObterRHPermissoes'])->name('permissoes.dados')->middleware('usuarioMiddleware');
    Route::post('api/usuario/permissao/adicionar', [UsuarioController::class, 'AtribuirPermissoes'])->name('usuario.permissao.adicionar')->middleware('usuarioMiddleware');
    Route::delete('api/usuario/permissao/remover/{id_rel_usuario_permissao}', [UsuarioController::class, 'RemoverPermissoes'])->name('usuario.permissao.remover')->middleware('usuarioMiddleware');
    Route::post('usuario/{id}/gerar-senha', [UsuarioController::class, 'GerarNovaSenha'])->name('usuario.gerar_senha')->middleware('usuarioMiddleware');

    // Grupos: listagem com associação por usuário e permissões do grupo
    Route::post('api/grupos/dados', [GrupoController::class, 'ObterDadosGrupo'])->name('grupos.dados')->middleware('usuarioMiddleware');

    // Atribuir/remover grupo do usuário
    Route::post('api/usuario/grupo/adicionar', [UsuarioController::class, 'AtribuirGrupo'])->name('usuario.grupo.adicionar')->middleware('usuarioMiddleware');
    Route::delete('api/usuario/grupo/remover/{id_rel_usuario_grupo}', [UsuarioController::class, 'RemoverGrupo'])->name('usuario.grupo.remover')->middleware('usuarioMiddleware');


});

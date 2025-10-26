<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RH\usuarioModel;
use Illuminate\Support\Facades\Cache;

class UsuarioController extends Controller
{
    private usuarioModel $usuarioModel;

    public function __construct()
    {
        // [x] validar uso
        $this->usuarioModel = new usuarioModel();
    }
    /**
     * Página de listagem de usuários (exibe DataTable)
     */
    public function index()
    {
        // [x] validar uso
        return view('RH.rh');
    }
    // corresponde a usuario->CadastrarUsuarios($dados)
    public function CadastrarUsuarios(Request $request)
    {
        $payload = $request->all();
        $respostaStatusCadastro = $this->usuarioModel->CadastrarUsuarios($payload); // [x] validar uso
        return response()->json($respostaStatusCadastro, $respostaStatusCadastro['status']);
    }

    // corresponde a usuario->ObterDadosUsuario(['usuario_id' => $usuario])
    public function ObterDadosUsuarios(Request $request)
    {
        $respostaDadosUsuario = $this->usuarioModel->ObterDadosUsuarios($request->all());
        return response()->json($respostaDadosUsuario, $respostaDadosUsuario['status']);
    }

    // atribui permissão direta ao usuário
    public function AtribuirPermissoes(Request $request)
    {
        $payload = $request->all();
        $respostaStatusAtribuicao = $this->usuarioModel->AtribuirPermissoes($payload);

        // [x] validar uso
        // Se atribuição foi bem sucedida, atualiza versão de permissões para o usuário afetado
        if ($respostaStatusAtribuicao['status']==201) {
            Cache::put("Permissao_versao", time());
        }

        return response()->json($respostaStatusAtribuicao, $respostaStatusAtribuicao['status']);
    }

    // atribui grupo ao usuário
    public function AtribuirGrupo(Request $request)
    {
        $respostaStatusAtribuicao = $this->usuarioModel->AtribuirGrupo($request->all());

        // [ ] validar uso
        if ($respostaStatusAtribuicao['status'] == 201) {
            Cache::put("Permissao_versao", time());
        }

        return response()->json($respostaStatusAtribuicao, $respostaStatusAtribuicao['status']);
    }

    // remove vínculo permissão->usuário
    public function RemoverPermissoes(Request $request, $id_rel_usuario_permissao)
    {
        $payload = $request->all();
        $payload['id_rel_usuario_permissao'] = $id_rel_usuario_permissao;
        $respostaStatusRemocao = $this->usuarioModel->RemoverPermissoes($payload);

        // [x] validar uso
        if ($respostaStatusRemocao['status'] === 201) {
            Cache::put("Permissao_versao", time());
        }

        return response()->json($respostaStatusRemocao, $respostaStatusRemocao['status']);
    }

    // remove vínculo grupo->usuário
    public function RemoverGrupo(Request $request, $id_rel_usuario_grupo)
    {
        $payload = $request->all();
        $payload['id_rel_usuario_grupo'] = $id_rel_usuario_grupo;
        $respostaStatusRemocao = $this->usuarioModel->RemoverGrupo($payload);

        // [ ] validar uso
        if ($respostaStatusRemocao['status'] === 201) {
            Cache::put("Permissao_versao", time());
        }

        return response()->json($respostaStatusRemocao, $respostaStatusRemocao['status']);
    }



    /**
     * Atualiza apenas o nome_Completo do usuário identificado por id.
     */
    public function AtualizarUsuarios(Request $request, $usuario_id)
    {
        $payload = $request->all();
        $payload['usuario_id'] = $usuario_id;
        $respostaStatusUsuario = $this->usuarioModel->AtualizarUsuarios($payload);
        return response()->json($respostaStatusUsuario, $respostaStatusUsuario['status']);
    }

    /**
     * Excluir (logicamente) usuário identificado por id.
     */
    public function DeletarUsuarios(Request $request, $usuario_id)
    {
        $payload = $request->all();
        $payload['usuario_id'] = $usuario_id;

        $respostaStatusDeletar = $this->usuarioModel->DeletarUsuarios($payload);

        if($respostaStatusDeletar['status'] === 201) {
            Cache::put("Permissao_versao", time());
        }

        return response()->json($respostaStatusDeletar, $respostaStatusDeletar['status']);

    }
}


<?php

namespace App\Http\Controllers\rh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\rh\permissaoModel;

class PermissaoController extends Controller
{
    private permissaoModel $permissaoModel;

    public function __construct()
    {
        $this->permissaoModel = new permissaoModel();
    }

    /**
     * Retorna todas as permissões com flag indicando se o usuário já possui cada uma.
     */
    public function ObterPermissoes(Request $request)
    {
        $respostaDadosPermissao = $this->permissaoModel->ObterPermissoes($request->all());
        return response()->json($respostaDadosPermissao, $respostaDadosPermissao['status']);
    }

    /**
     * Cadastrar nova permissão.
     */
    public function CadastrarPermissao(Request $request)
    {
        $payload = $request->all();
        $respostaCadastro = $this->permissaoModel->CriarPermissao($payload);

        return response()->json($respostaCadastro, $respostaCadastro['status'] ?? 400);
    }

    /**
     * Atualizar permissão existente.
     */
    public function AtualizarPermissao(Request $request, $permissao_id)
    {
        $payload = $request->all();
        $payload['id_permissao'] = $permissao_id;

        $respostaAtualizacao = $this->permissaoModel->AtualizarPermissao($payload);

        return response()->json($respostaAtualizacao, $respostaAtualizacao['status'] ?? 400);
    }

    /**
     * Remover (logicamente) uma permissão.
     */
    public function DeletarPermissao(Request $request, $permissao_id)
    {
        $payload = $request->all();
        $payload['id_permissao'] = $permissao_id;

        $respostaRemocao = $this->permissaoModel->RemoverPermissao($payload);

        return response()->json($respostaRemocao, $respostaRemocao['status'] ?? 400);
    }
}

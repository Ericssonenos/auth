<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RH\permissaoModel;

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
    public function ObterRHPermissoes(Request $request)
    {
        $respostaDadosPermissao = $this->permissaoModel->ObterRHPermissoes($request->all());
        return response()->json($respostaDadosPermissao['dados'], $respostaDadosPermissao['status']);
    }



    // corresponde a permissao->CriarPermissao()
    public function CriarPermissao(Request $request)
    {
        $payload = $request->all();
        $respostaStatusCriacao = $this->permissaoModel->CriarPermissao($payload);

        // [ ] validar uso
        return response()->json($respostaStatusCriacao);
    }

    // corresponde a permissao->AtualizarPermissao()
    public function AtualizarPermissao(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_permissao'] = $id;
        $respostaStatusAtualizacao = $this->permissaoModel->AtualizarPermissao($payload);

        // [ ] validar uso
        return response()->json($respostaStatusAtualizacao);
    }

    // corresponde a permissao->RemoverPermissao()
    public function RemoverPermissao(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_permissao'] = $id;
        $respostaStatusRemocao = $this->permissaoModel->RemoverPermissao($payload);

        // [ ] validar uso
        return response()->json($respostaStatusRemocao);
    }
}

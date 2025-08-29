<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\RH\permissao;

class PermissaoController extends Controller
{
    private permissao $permissaoModel;

    public function __construct()
    {
        // [ ] validar uso
        $this->permissaoModel = new permissao();
    }

    // corresponde a permissao->ListaPermissoes()
    public function ListaPermissoes()
    {
        // [ ] validar uso
        return response()->json($this->permissaoModel->ListaPermissoes());
    }

    // corresponde a permissao->ObterPermissaoPorId()
    public function ObterPermissaoPorId($id)
    {
        // [ ] validar uso
        return response()->json($this->permissaoModel->ObterPermissaoPorId($id));
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

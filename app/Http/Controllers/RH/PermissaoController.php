<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RH\permissao;

class PermissaoController extends Controller
{
    private permissao $permissaoModel;

    public function __construct()
    {
        $this->permissaoModel = new permissao();
    }

    // corresponde a permissao->ListaPermissoes()
    public function ListaPermissoes()
    {
        return response()->json($this->permissaoModel->ListaPermissoes());
    }

    // corresponde a permissao->ObterPermissaoPorId()
    public function ObterPermissaoPorId($id)
    {
        return response()->json($this->permissaoModel->ObterPermissaoPorId($id));
    }

    // corresponde a permissao->CriarPermissao()
    public function CriarPermissao(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->permissaoModel->CriarPermissao($payload));
    }

    // corresponde a permissao->AtualizarPermissao()
    public function AtualizarPermissao(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_permissao'] = $id;
        return response()->json($this->permissaoModel->AtualizarPermissao($payload));
    }

    // corresponde a permissao->RemoverPermissao()
    public function RemoverPermissao(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_permissao'] = $id;
        return response()->json($this->permissaoModel->RemoverPermissao($payload));
    }
}

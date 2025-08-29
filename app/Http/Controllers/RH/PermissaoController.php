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
    $res = $this->permissaoModel->CriarPermissao($payload);
    // permissão criada: invalidar cache global
    $current = Session::get('rh_usuario');
    if ($current) {
        Session::forget("rh_permissions.{$current}");
    }
    return response()->json($res);
    }

    // corresponde a permissao->AtualizarPermissao()
    public function AtualizarPermissao(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_permissao'] = $id;
    $res = $this->permissaoModel->AtualizarPermissao($payload);
    $current = Session::get('rh_usuario');
    if ($current) {
        Session::forget("rh_permissions.{$current}");
    }
    return response()->json($res);
    }

    // corresponde a permissao->RemoverPermissao()
    public function RemoverPermissao(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_permissao'] = $id;
    $res = $this->permissaoModel->RemoverPermissao($payload);
    $current = Session::get('rh_usuario');
    if ($current) {
        Session::forget("rh_permissions.{$current}");
    }
    return response()->json($res);
    }
}

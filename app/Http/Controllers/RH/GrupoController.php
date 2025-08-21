<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\Rh;
use App\Models\RH\grupo;

class GrupoController extends Controller
{
    private grupo $grupoModel;

    public function __construct()
    {
        $this->grupoModel = new grupo();
    }

    // corresponde a grupo->ListaGrupos()
    public function ListaGrupos()
    {
        return response()->json($this->grupoModel->ListaGrupos());
    }

    // corresponde a grupo->ObterGrupoPorId()
    public function ObterGrupoPorId($id)
    {
        return response()->json($this->grupoModel->ObterGrupoPorId($id));
    }

    // corresponde a grupo->CriarGrupo()
    public function CriarGrupo(Request $request)
    {
        $payload = $request->all();
    $res = $this->grupoModel->CriarGrupo($payload);
    Rh::invalidate(null);
    return response()->json($res);
    }

    // corresponde a grupo->AtualizarGrupo()
    public function AtualizarGrupo(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_grupo'] = $id;
    $res = $this->grupoModel->AtualizarGrupo($payload);
    Rh::invalidate(null);
    return response()->json($res);
    }

    // corresponde a grupo->RemoverGrupo()
    public function RemoverGrupo(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_grupo'] = $id;
    $res = $this->grupoModel->RemoverGrupo($payload);
    Rh::invalidate(null);
    return response()->json($res);
    }

    // atribui permissão a um grupo
    public function AtribuirPermissaoGrupo(Request $request)
    {
        $payload = $request->all();
    $res = $this->grupoModel->AtribuirPermissaoGrupo($payload);
    $mat = $payload['matricula_cod'] ?? null;
    Rh::invalidate($mat);
    return response()->json($res);
    }

    // remove permissão de um grupo
    public function RemoverPermissaoGrupo(Request $request)
    {
        $payload = $request->all();
    $res = $this->grupoModel->RemoverPermissaoGrupo($payload);
    $mat = $payload['matricula_cod'] ?? null;
    Rh::invalidate($mat);
    return response()->json($res);
    }

    // cria relação pai->filho entre grupos
    public function AtribuirGrupoGrupo(Request $request)
    {
        $payload = $request->all();
    $res = $this->grupoModel->AtribuirGrupoGrupo($payload);
    Rh::invalidate(null);
    return response()->json($res);
    }

    // remove relação entre grupos
    public function RemoverGrupoGrupo(Request $request)
    {
        $payload = $request->all();
    $res = $this->grupoModel->RemoverGrupoGrupo($payload);
    Rh::invalidate(null);
    return response()->json($res);
    }
}

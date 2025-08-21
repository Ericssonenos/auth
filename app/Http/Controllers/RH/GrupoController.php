<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        return response()->json($this->grupoModel->CriarGrupo($payload));
    }

    // corresponde a grupo->AtualizarGrupo()
    public function AtualizarGrupo(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_grupo'] = $id;
        return response()->json($this->grupoModel->AtualizarGrupo($payload));
    }

    // corresponde a grupo->RemoverGrupo()
    public function RemoverGrupo(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_grupo'] = $id;
        return response()->json($this->grupoModel->RemoverGrupo($payload));
    }

    // atribui permissão a um grupo
    public function AtribuirPermissaoGrupo(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->grupoModel->AtribuirPermissaoGrupo($payload));
    }

    // remove permissão de um grupo
    public function RemoverPermissaoGrupo(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->grupoModel->RemoverPermissaoGrupo($payload));
    }

    // cria relação pai->filho entre grupos
    public function AtribuirGrupoGrupo(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->grupoModel->AtribuirGrupoGrupo($payload));
    }

    // remove relação entre grupos
    public function RemoverGrupoGrupo(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->grupoModel->RemoverGrupoGrupo($payload));
    }
}

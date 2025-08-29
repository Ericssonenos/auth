<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\RH\grupo;

class GrupoController extends Controller
{
    private grupo $grupoModel;

    public function __construct()
    {
        // [ ] validar uso
        $this->grupoModel = new grupo();
    }

    // corresponde a grupo->ListaGrupos()
    public function ListaGrupos()
    {
        // [ ] validar uso
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
        $respostaStatusCriacao = $this->grupoModel->CriarGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusCriacao);
    }

    // corresponde a grupo->AtualizarGrupo()
    public function AtualizarGrupo(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_Grupo'] = $id;
        $respostaStatusAtualizacao = $this->grupoModel->AtualizarGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusAtualizacao);
    }

    // corresponde a grupo->RemoverGrupo()
    public function RemoverGrupo(Request $request, $id)
    {
        $payload = $request->all();
        $payload['id_Grupo'] = $id;
        $respostaStatusRemocao = $this->grupoModel->RemoverGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusRemocao);
    }

    // atribui permissão a um grupo
    public function AtribuirPermissaoGrupo(Request $request)
    {
        $payload = $request->all();
        $respostaStatusAtribuicao = $this->grupoModel->AtribuirPermissaoGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusAtribuicao);
    }

    // remove permissão de um grupo
    public function RemoverPermissaoGrupo(Request $request)
    {
        $payload = $request->all();
        $respostaStatusRemocao = $this->grupoModel->RemoverPermissaoGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusRemocao);
    }

    // cria relação pai->filho entre grupos
    public function AtribuirGrupoGrupo(Request $request)
    {
        $payload = $request->all();
        $respostaStatusAtribuicao = $this->grupoModel->AtribuirGrupoGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusAtribuicao);
    }

    // remove relação entre grupos
    public function RemoverGrupoGrupo(Request $request)
    {
        $payload = $request->all();
        $respostaStatusRemocao = $this->grupoModel->RemoverGrupoGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusRemocao);
    }
}

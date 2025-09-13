<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RH\grupoModel;


class GrupoController extends Controller
{
    private grupoModel $grupoModel;

    public function __construct()
    {
        // [x] validar uso
        $this->grupoModel = new grupoModel();
    }


    // endpoint para DataTable: lista grupos com indicação se usuário pertence (usa usuario_id no POST)
    public function ObterDadosGrupo(Request $request)
    {

        $respostaDadosGrupo = $this->grupoModel->ObterDadosGrupo($request->all());
        if(!empty($respostaDadosGrupo['status']) && $respostaDadosGrupo['status'] === true) {
            return response()->json($respostaDadosGrupo, 200);
        }
        return response()->json($respostaDadosGrupo, 400);
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
        $respostaStatusAtribuicao = $this->grupoModel->AtribuirGrupoGrupo($request->all());

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

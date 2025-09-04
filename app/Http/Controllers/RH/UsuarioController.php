<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\RH\usuarioModel;

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
        // so acessa quem tiver a permissão GESTAO_USUARIOS
        //[ ] Criar uma regra global igual o @can
        return view('RH.usuario');
    }

    // corresponde a usuario->ObterDadosUsuario(['Usuario_id' => $usuario])
    public function ObterDadosUsuarios(Request $request)
    {

        $respostaDadosUsuario = $this->usuarioModel->ObterDadosUsuarios($request->all());
        return response()->json($respostaDadosUsuario);
    }

    // corresponde a usuario->ObterPermissoesUsuario(['Usuario_id' => $usuario])
    public function ObterPermissoesUsuario($usuario)
    {
        // [ ] validar uso
        $respostaPermissoesUsuario = $this->usuarioModel->ObterPermissoesUsuario(['Usuario_id' => $usuario]);
        return response()->json($respostaPermissoesUsuario);
    }



    // atribui permissão direta ao usuário
    public function AtribuirPermissoes(Request $request)
    {
        $payload = $request->all();
        $respostaStatusAtribuicao = $this->usuarioModel->AtribuirPermissoes($payload);

        // [ ] validar uso
        return response()->json($respostaStatusAtribuicao);
    }

    // atribui grupo ao usuário
    public function AtribuirGrupo(Request $request)
    {
        $payload = $request->all();
        $respostaStatusAtribuicao = $this->usuarioModel->AtribuirGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusAtribuicao);
    }

    // remove vínculo permissão->usuário
    public function RemoverPermissoes(Request $request)
    {
        $payload = $request->all();
        $respostaStatusRemocao = $this->usuarioModel->RemoverPermissoes($payload);

        // [ ] validar uso
        return response()->json($respostaStatusRemocao);
    }

    // remove vínculo grupo->usuário
    public function RemoverGrupo(Request $request)
    {
        $payload = $request->all();
        $respostaStatusRemocao = $this->usuarioModel->RemoverGrupo($payload);

        // [ ] validar uso
        return response()->json($respostaStatusRemocao);
    }
}

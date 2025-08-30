<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\RH\usuario;

class UsuarioController extends Controller
{
    private usuario $usuarioModel;

    public function __construct()
    {
        // [ ] validar uso
        $this->usuarioModel = new usuario();
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

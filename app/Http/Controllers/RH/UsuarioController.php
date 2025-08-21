<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RH\usuario;

class UsuarioController extends Controller
{
    private usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new usuario();
    }

    // corresponde a usuario->ListaUsuarios()
    public function ListaUsuarios()
    {
        return response()->json($this->usuarioModel->ListaUsuarios());
    }

    // corresponde a usuario->ObterPermissoesMatricula(['matricula_cod' => $matricula])
    public function ObterPermissoesMatricula($matricula)
    {
        $res = $this->usuarioModel->ObterPermissoesMatricula(['matricula_cod' => $matricula]);
        return response()->json($res);
    }

    // atribui permissão direta ao usuário
    public function AtribuirPermissoes(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->usuarioModel->AtribuirPermissoes($payload));
    }

    // atribui grupo ao usuário
    public function AtribuirGrupo(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->usuarioModel->AtribuirGrupo($payload));
    }

    // remove vínculo permissão->usuário
    public function RemoverPermissoes(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->usuarioModel->RemoverPermissoes($payload));
    }

    // remove vínculo grupo->usuário
    public function RemoverGrupo(Request $request)
    {
        $payload = $request->all();
        return response()->json($this->usuarioModel->RemoverGrupo($payload));
    }
}

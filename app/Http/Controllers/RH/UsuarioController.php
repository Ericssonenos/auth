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
        $this->usuarioModel = new usuario();
    }

    // corresponde a usuario->ListaUsuarios()
    public function ListaUsuarios()
    {
        return response()->json($this->usuarioModel->ListaUsuarios());
    }

    // corresponde a usuario->ObterPermissoesMatricula(['Usuario_id' => $usuario])
    public function ObterPermissoesMatricula($usuario)
    {
        $res = $this->usuarioModel->ObterPermissoesMatricula(['Usuario_id' => $usuario]);
        return response()->json($res);
    }

    // atribui permissão direta ao usuário
    public function AtribuirPermissoes(Request $request)
    {
        $payload = $request->all();
    $res = $this->usuarioModel->AtribuirPermissoes($payload);
    // invalidar cache para a matrícula afetada se informado
    $mat = $payload['Usuario_id'] ?? null;
    if ($mat) {
        Session::forget("rh_permissions.{$mat}");
    } else {
        $current = Session::get('rh_usuario');
        if ($current) {
            Session::forget("rh_permissions.{$current}");
        }
    }
    return response()->json($res);
    }

    // atribui grupo ao usuário
    public function AtribuirGrupo(Request $request)
    {
        $payload = $request->all();
    $res = $this->usuarioModel->AtribuirGrupo($payload);
    $mat = $payload['Usuario_id'] ?? null;
    if ($mat) {
        Session::forget("rh_permissions.{$mat}");
    } else {
        $current = Session::get('rh_usuario');
        if ($current) {
            Session::forget("rh_permissions.{$current}");
        }
    }
    return response()->json($res);
    }

    // remove vínculo permissão->usuário
    public function RemoverPermissoes(Request $request)
    {
        $payload = $request->all();
    $res = $this->usuarioModel->RemoverPermissoes($payload);
    $mat = $payload['Usuario_id'] ?? null;
    if ($mat) {
        Session::forget("rh_permissions.{$mat}");
    } else {
        $current = Session::get('rh_usuario');
        if ($current) {
            Session::forget("rh_permissions.{$current}");
        }
    }
    return response()->json($res);
    }

    // remove vínculo grupo->usuário
    public function RemoverGrupo(Request $request)
    {
        $payload = $request->all();
    $res = $this->usuarioModel->RemoverGrupo($payload);
    $mat = $payload['Usuario_id'] ?? null;
    if ($mat) {
        Session::forget("rh_permissions.{$mat}");
    } else {
        $current = Session::get('rh_usuario');
        if ($current) {
            Session::forget("rh_permissions.{$current}");
        }
    }
    return response()->json($res);
    }
}

<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use App\Models\RH\usuario;

class AccessDemoController extends Controller
{
    private usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new usuario();
    }

    public function Demo(Request $request)
    {
        // Ler permissões/matrícula da session (middleware grava em session)
        $usuario = Session::get('rh_usuario', $request->header('X-id_Usuario'));
        $permissao = Session::get("rh_permissions.{$usuario}", []);

        // Expor um boolean via Gate também para conveniência na view
        $canManage = Gate::check('PERM_GERENCIAR_PERMISSOES');

        return view('rh.access_demo', [
            'can_manage' => $canManage,
        ]);
    }
}

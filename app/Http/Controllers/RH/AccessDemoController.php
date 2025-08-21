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
        $matricula = Session::get('rh_matricula', $request->header('X-Matricula'));
        $perms = Session::get("rh_permissions.{$matricula}", []);

        // Expor um boolean via Gate também para conveniência na view
        $canManage = Gate::check('PERM_GERENCIAR_PERMISSOES');

        return view('rh.access_demo', [
            'perms' => $perms,
            'matricula' => $matricula,
            'can_manage' => $canManage,
        ]);
    }
}

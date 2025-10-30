<?php

namespace App\Http\Controllers\rh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\rh\permissaoModel;

class PermissaoController extends Controller
{
    private permissaoModel $permissaoModel;

    public function __construct()
    {
        $this->permissaoModel = new permissaoModel();
    }

    /**
     * Retorna todas as permissões com flag indicando se o usuário já possui cada uma.
     */
    public function ObterPermissoes(Request $request)
    {
        $respostaDadosPermissao = $this->permissaoModel->ObterPermissoes($request->all());
        return response()->json($respostaDadosPermissao, $respostaDadosPermissao['status']);
    }
}

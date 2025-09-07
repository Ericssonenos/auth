<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
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
    // corresponde a usuario->CadastrarUsuarios($dados)
    public function CadastrarUsuarios(Request $request)
    {



        $payload = $request->all();
        $respostaStatusCadastro = $this->usuarioModel->CadastrarUsuarios($payload); // [x] validar uso
        if (!empty($respostaStatusCadastro['status']) && $respostaStatusCadastro['status'] === true) {
            $status = 200;
        }
        return response()->json($respostaStatusCadastro, $status ?? 400);
    }

    // corresponde a usuario->ObterDadosUsuario(['Usuario_id' => $usuario])
    public function ObterDadosUsuarios(Request $request)
    {

        $respostaDadosUsuario = $this->usuarioModel->ObterDadosUsuarios($request->all());
        if ($respostaDadosUsuario['status']) {
            $status = 200;
        }
        return response()->json($respostaDadosUsuario, $status ?? 400);
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

    /**
     * Gera uma nova senha temporária para o usuário e retorna a senha gerada (JSON).
     */
    public function GerarNovaSenha(Request $request, $id)
    {
        // privilégio: este endpoint deve ser protegido por middleware/permissão
        $res = $this->usuarioModel->GerarSenhaTemporaria(['Usuario_id' => $id]);
        if (!empty($res['status']) && $res['status'] === true) {
            return response()->json($res, 200);
        }
        return response()->json($res, 400);
    }

    /**
     * Atualiza apenas o nome_Completo do usuário identificado por id.
     */
    public function AtualizarUsuarios(Request $request, $id)
    {
        $payload = $request->all();
        $payload['Usuario_id'] = $id;

        $res = $this->usuarioModel->AtualizarUsuarios($payload);
        if (!empty($res['status']) && $res['status'] === true) {
            return response()->json($res, 200);
        }
        return response()->json($res, 400);
    }
}

<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RH\usuarioModel;
use Illuminate\Support\Facades\Cache;

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

    // corresponde a usuario->ObterDadosUsuario(['usuario_id' => $usuario])
    public function ObterDadosUsuarios(Request $request)
    {

        $respostaDadosUsuario = $this->usuarioModel->ObterDadosUsuarios($request->all());
        if ($respostaDadosUsuario['status']) {
            $status = 200;
        }
        return response()->json($respostaDadosUsuario, $status ?? 400);
    }

    // atribui permissão direta ao usuário
    public function AtribuirPermissoes(Request $request)
    {
        $payload = $request->all();
        $respostaStatusAtribuicao = $this->usuarioModel->AtribuirPermissoes($payload);

        // [x] validar uso
        // Se atribuição foi bem sucedida, atualiza versão de permissões para o usuário afetado
        if (!empty($respostaStatusAtribuicao['status']) && $respostaStatusAtribuicao['status'] === true) {
            $usuarioId = $payload['usuario_id'] ?? ($respostaStatusAtribuicao['data']['usuario_id'] ?? null) ?? null;
            if (!empty($usuarioId)) {
                $cacheKey = "perms_version_user_{$usuarioId}";
                Cache::put($cacheKey, time());
            }
        }

        return response()->json($respostaStatusAtribuicao);
    }

    // atribui grupo ao usuário
    public function AtribuirGrupo(Request $request)
    {
        $payload = $request->all();
        $respostaStatusAtribuicao = $this->usuarioModel->AtribuirGrupo($payload);

        // [ ] validar uso
        if (!empty($respostaStatusAtribuicao['status']) && $respostaStatusAtribuicao['status'] === true) {
            $usuarioId = $payload['usuario_id'] ?? ($respostaStatusAtribuicao['data']['usuario_id'] ?? null) ?? null;
            if (!empty($usuarioId)) {
                $cacheKey = "perms_version_user_{$usuarioId}";
                Cache::put($cacheKey, time());
            }
        }

        return response()->json($respostaStatusAtribuicao);
    }

    // remove vínculo permissão->usuário
    public function RemoverPermissoes(Request $request, $id_rel_usuario_permissao)
    {
        $payload = $request->all();
        $payload['id_rel_usuario_permissao'] = $id_rel_usuario_permissao;
        $respostaStatusRemocao = $this->usuarioModel->RemoverPermissoes($payload);

        // [x] validar uso
        if (!empty($respostaStatusRemocao['status']) && $respostaStatusRemocao['status'] === true) {
            $usuarioId = $payload['usuario_id'] ?? ($respostaStatusRemocao['data']['usuario_id'] ?? null) ?? null;
            if (!empty($usuarioId)) {
                $cacheKey = "perms_version_user_{$usuarioId}";
                Cache::put($cacheKey, time());
            }
        }

        return response()->json($respostaStatusRemocao);
    }

    // remove vínculo grupo->usuário
    public function RemoverGrupo(Request $request)
    {
        $payload = $request->all();
        $respostaStatusRemocao = $this->usuarioModel->RemoverGrupo($payload);

        // [ ] validar uso
        if (!empty($respostaStatusRemocao['status']) && $respostaStatusRemocao['status'] === true) {
            $usuarioId = $payload['usuario_id'] ?? ($respostaStatusRemocao['data']['usuario_id'] ?? null) ?? null;
            if (!empty($usuarioId)) {
                $cacheKey = "perms_version_user_{$usuarioId}";
                Cache::put($cacheKey, time());
            }
        }

        return response()->json($respostaStatusRemocao);
    }

    /**
     * Gera uma nova senha temporária para o usuário e retorna a senha gerada (JSON).
     */
    public function GerarNovaSenha(Request $request, $id)
    {
        // privilégio: este endpoint deve ser protegido por middleware/permissão
        $res = $this->usuarioModel->GerarSenhaTemporaria(['usuario_id' => $id]);
        if (!empty($res['status']) && $res['status'] === true) {
            return response()->json($res, 200);
        }
        return response()->json($res, 400);
    }

    /**
     * Atualiza apenas o nome_Completo do usuário identificado por id.
     */
    public function AtualizarUsuarios(Request $request, $usuario_id)
    {
        $payload = $request->all();
        $payload['usuario_id'] = $usuario_id;

        $res = $this->usuarioModel->AtualizarUsuarios($payload);
        if (!empty($res['status']) && $res['status'] === true) {
            return response()->json($res, 200);
        }
        return response()->json($res, 400);
    }
}

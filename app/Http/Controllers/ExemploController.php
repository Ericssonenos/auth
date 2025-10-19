<?php

namespace App\Http\Controllers;

use App\Models\RH\usuarioModel;
use App\Models\RH\permissaoModel;
use Illuminate\Http\Request;

class ExemploController extends Controller
{
    /**
     * Exibe a página principal com tabs
     */
    public function index()
    {
        return view('exemplo.index');
    }

    /**
     * Carrega a tab de usuários via AJAX
     */
    public function tabUsuarios(Request $request)
    {
        try {
            $params = [
                'limit' => $request->input('limit', 50),
                'offset' => $request->input('offset', 0),
                'order_by' => 'id_Usuario',
                'order_direction' => 'DESC'
            ];

            $usuarioModel = new usuarioModel();
            $resultado = $usuarioModel->ObterDadosUsuarios($params);

            return view('exemplo.tabs.usuarios', [
                'usuarios' => $resultado['data'] ?? [],
                'mensagem' => $resultado['mensagem'] ?? '',
                'status' => $resultado['status'] ?? 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'mensagem' => 'Erro ao carregar usuários: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Carrega a tab de permissões via AJAX
     */
    public function tabPermissoes(Request $request)
    {
        try {
            $params = [
                'fn' => 'btn-permissoes-grupo',
                'grupo_id' => $request->input('grupo_id', 1),
                'limit' => $request->input('limit', 100),
                'offset' => $request->input('offset', 0),
                'order_by' => 'cod_permissao',
                'order_direction' => 'ASC'
            ];

            $permissaoModel = new permissaoModel();
            $resultado = $permissaoModel->ObterRHPermissoes($params);

            return view('exemplo.tabs.permissoes', [
                'permissoes' => $resultado['data'] ?? [],
                'mensagem' => $resultado['mensagem'] ?? '',
                'status' => $resultado['status'] ?? 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'mensagem' => 'Erro ao carregar permissões: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Carrega a tab de estatísticas via AJAX
     */
    public function tabEstatisticas(Request $request)
    {
        try {
            // Buscar dados de usuários
            $usuarioModel = new usuarioModel();
            $usuarios = $usuarioModel->ObterDadosUsuarios(['limit' => 1000]);

            // Buscar dados de permissões
            $permissaoModel = new permissaoModel();
            $permissoes = $permissaoModel->ObterRHPermissoes([
                'fn' => 'btn-permissoes-grupo',
                'grupo_id' => 1,
                'limit' => 1000
            ]);

            $estatisticas = [
                'total_usuarios' => count($usuarios['data'] ?? []),
                'total_permissoes' => count($permissoes['data'] ?? []),
                'usuarios_ativos' => count($usuarios['data'] ?? []),
                'ultima_atualizacao' => now()->format('d/m/Y H:i:s')
            ];

            return view('exemplo.tabs.estatisticas', [
                'estatisticas' => $estatisticas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'mensagem' => 'Erro ao carregar estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}

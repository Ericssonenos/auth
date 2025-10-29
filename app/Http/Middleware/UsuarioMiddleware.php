<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\RH\permissaoModel;

class UsuarioMiddleware
{

    public function handle(Request $request, Closure $next, ...$cod_permissoes_necessarias)
    {
        $dados = session('dados_usuario_sessao', null);
        // Verifica se o usuário está devidamente autenticado no sistema
        //[ ] testar acesso dia api ajax sem estar logado
        if (empty($dados['id_Usuario'])) {

            // Se a requisição espera JSON (API), retorna resposta JSON
            // ou se na rota contem api/
            // e estiver no modo debug
            if ( str_starts_with($request->path(), 'api/') && env('APP_DEBUG', true)) {
                 return $next($request);
            }

            // redirecionar para o login
            return redirect()->route('login')
                ->with('erro', [
                    'mensagem' => "Você precisa estar autenticado para acessar esta página."
                ]);
        }

        // Verifica se existe uma versão de permissões global para este usuário
        // se existir e for diferente da versão armazenada na sessão, recarrega as permissões

        $versao_permissao_global = Cache::get("versao_permissao_global", null);
        $versao_permissao_sessao = $dados['versao_permissao_sessao'] ?? null;

        if ($versao_permissao_global !== null && $versao_permissao_global !== $versao_permissao_sessao) {
            // Recarregar permissões do usuário via model (mesma lógica do LoginController)
            try {
                $permissaoModel = new permissaoModel();
                // id_Usuario traz as permissões ativas
                // usuario_id traz todas as permissões com flag (possui ou não)
                $permissoes_usuario = $permissaoModel->ObterPermissoes(
                    [
                        'id_Usuario' => $dados['id_Usuario'],
                        'fn' => 'fn-do-usuario'
                    ]
                );

                // Verificar se a resposta contém permissões
                if ($permissoes_usuario['status'] == 200) {
                    $dados['permissoes_usuario'] = $permissoes_usuario['data'];
                } else {
                    $dados['permissoes_usuario'] = [];
                }

                // Atualiza a versão na sessão para marcar que já sincronizamos
                $dados['versao_permissao_sessao'] = $versao_permissao_global;
                session(['dados_usuario_sessao' => $dados]);
            } catch (\Throwable $e) {
                // Em caso de erro, limpa as permissões do usuário para evitar inconsistências
                $dados['permissoes_usuario'] = [];
                $dados['versao_permissao_sessao'] = null;
                session(['dados_usuario_sessao' => $dados]);
            }
        }


        // Se nenhuma permissão foi passada, tenta detectar automaticamente
        if (!$cod_permissoes_necessarias) {
            // Obter as permissões necessárias
            $cod_permissoes_necessarias = $this->DetectarCodPermissoesNecessariasPelaRota($request);
        }

        $permissoes_usuario = $dados['permissoes_usuario'] ?? [];

        // verrificar se o array servicoDoUsuario comtem alguma permssiao de permissaoNecessaria
        foreach ($permissoes_usuario as $permissao) {
            if (in_array($permissao['cod_permissao'], $cod_permissoes_necessarias)) {
                return $next($request);
            }
        }
        // se estiver no modo debug e não tiver permissão, cadastrar no banco automaticamente
        if (env('APP_DEBUG', true)) {
            try {
                $permissaoModel = new permissaoModel();
                foreach ($cod_permissoes_necessarias as $cod_permissao) {
                    // Verifica se a permissão já existe
                    $permissaoExistente = $permissaoModel->ObterPermissoes([
                        'cod_permissao' => $cod_permissao,
                        'fn' => 'middleware-se-existe'
                    ])['data'] ?? null;
                    if (!$permissaoExistente) {
                        // Cria a permissão automaticamente
                        $params = [
                            'cod_permissao' => $cod_permissao,
                            'descricao_permissao' => 'Permissão criada automaticamente em modo debug.',
                            'criado_Usuario_id' => 1 // $dados['id_Usuario'] ??

                        ];
                        $permissaoModel->CriarPermissao($params);
                    }
                }
            } catch (\Throwable $e) {
                // Ignorar erros na criação automática de permissões
                dd($e);
            }
        }


        // Retorna resposta de acesso negado

        // Se a requisição espera JSON (API), retorna resposta JSON
        // ou se na rota contem api/
        if (request()->expectsJson() || str_contains($request->path(), '/api/')) {

            return response()
                ->json(
                    // retirar os dados sensíveis do path
                    [
                        'mensagem' => "Você não possui permissão para acessar API: " . str_replace('_', '/', $this->formatarUriDaRequisicao($request->path())),
                        'cod_permissoes_necessarias' => $cod_permissoes_necessarias
                    ],
                    403
                );
        }

        // Para requisições web, redireciona com mensagens na sessão
        return redirect()->back()
            ->with('erro', [
                'mensagem' => "Você não possui permissão para acessar esta página.",
                'cod_permissoes_necessarias' => $cod_permissoes_necessarias
            ]);
    }

    /**
     * Detecta automaticamente as permissões necessárias baseado na rota atual
     */
    private function DetectarCodPermissoesNecessariasPelaRota(Request $request): array
    {
        $rotaAtual = $request->route();
        $permissoesPossiveis = [];

        // 1. Prioridade máxima: Nome da rota (mais semântico e declarativo)
        if ($rotaAtual->getName()) {
            $permissoesPossiveis[] = strtoupper("N_{$rotaAtual->getName()}");
        }

        // 3. Método HTTP combinado com URI (fallback para casos específicos)
        $metodoHttp = $request->method();
        $uriFormatada = $this->formatarUriDaRequisicao($request->path());
        // deixar upcase
        $permissoesPossiveis[] = strtoupper("R_{$metodoHttp}_{$uriFormatada}");

        return array_unique(array_filter($permissoesPossiveis));
    }


    /**
     * Formatar URI da requisição substituindo parâmetros dinâmicos por placeholders
     */
    private function formatarUriDaRequisicao(string $caminhoUri): string
    {
        // normaliza e remove barras extremas
        $uriLimpa = trim($caminhoUri, '/');

        if ($uriLimpa === '') {
            return '';
        }

        $partes = preg_split('#/#', $uriLimpa);

        $partesFormatadas = array_map(function (string $segmento) {
            // segmento numérico -> VALOR
            if (preg_match('/^\d+$/', $segmento)) {
                return 'VALOR';
            }

            // UUID -> VALOR (aceita letras maiúsculas/minúsculas)
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $segmento)) {
                return 'VALOR';
            }

            // normaliza: remove caracteres não alfanuméricos e converte para underscore
            $limpo = preg_replace('/[^a-z0-9]+/i', '_', $segmento);
            $limpo = trim($limpo, '_');

            return strtoupper($limpo);
        }, $partes);

        // junta com underscore para ficar no formato desejado: USER_CADASTRO_VALOR
        return implode('_', array_filter($partesFormatadas, fn($v) => $v !== ''));
    }
}

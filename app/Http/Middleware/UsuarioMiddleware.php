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
        $dados = session('dadosUsuarioSession', null);
        // Verifica se o usuário está devidamente autenticado no sistema
        //[ ] testar acesso dia api ajax sem estar logado
        if (empty($dados['id_Usuario'])) {

            // Atribuir a Session a rota que o usuário tentou acessar
            session(['url_intentada' => $request->fullUrl()]);

            // redirecionar para o login
            return redirect()->route('login')
                ->with('erro', [
                    'mensagem' => "Você precisa estar autenticado para acessar esta página."
                ]);
        }

        // Verifica se existe uma versão de permissões global para este usuário
        // se existir e for diferente da versão armazenada na sessão, recarrega as permissões

        $globalVersion = Cache::get("Permissao_versao", null);
        $sessionVersion = $dados['perms_version'] ?? null;

        if ($globalVersion !== null && $globalVersion !== $sessionVersion) {
            // Recarregar permissões do usuário via model (mesma lógica do LoginController)
            try {
                $permissaoModel = new permissaoModel();
                // id_Usuario traz as permissões ativas
                // usuario_id traz todas as permissões com flag (possui ou não)
                $permissoesUsuario = $permissaoModel->ObterLoginPermissoes(['id_Usuario' => $dados['id_Usuario']]);

                // Verificar se a resposta contém permissões
                if ($permissoesUsuario['status'] == 200) {
                    $dados['permissoesUsuario'] = $permissoesUsuario['data'];
                } else {
                    $dados['permissoesUsuario'] = [];
                }

                // Atualiza a versão na sessão para marcar que já sincronizamos
                $dados['perms_version'] = $globalVersion;
                session(['dadosUsuarioSession' => $dados]);
            } catch (\Throwable $e) {
                // Em caso de erro, limpa as permissões do usuário para evitar inconsistências
                $dados['permissoesUsuario'] = [];
                $dados['perms_version'] = null;
                session(['dadosUsuarioSession' => $dados]);
            }
        }


        // Se nenhuma permissão foi passada, tenta detectar automaticamente
        if (!$cod_permissoes_necessarias) {
            // Obter as permissões necessárias
            $cod_permissoes_necessarias = $this->detectarcod_permissoesNecessariasPelaRota($request);
        }

        $permissoesUsuario = $dados['permissoesUsuario'] ?? [];

        // verrificar se o array servicoDoUsuario comtem alguma permssiao de permissaoNecessaria
        foreach ($permissoesUsuario as $permissao) {
            if (in_array($permissao['cod_permissao'], $cod_permissoes_necessarias)) {
                return $next($request);
            }
        }


        // Retorna resposta de acesso negado

        // Se a requisição espera JSON (API), retorna resposta JSON
        if (request()->expectsJson()) {

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
    private function detectarcod_permissoesNecessariasPelaRota(Request $request): array
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

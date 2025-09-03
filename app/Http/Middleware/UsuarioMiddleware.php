<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RH\usuarioServices;

class UsuarioMiddleware
{
    private usuarioServices $servicoDoUsuario;

    public function __construct(usuarioServices $servicoDoUsuario)
    {
        $this->servicoDoUsuario = $servicoDoUsuario;
    }

    public function handle(Request $request, Closure $next, ...$permissoesNecessarias)
    {

        // Verifica se o usuário está devidamente autenticado no sistema
        //[ ] testar acesso dia api ajax sem estar logado
        if (empty($this->servicoDoUsuario->id_Usuario)) {

            // Adiciona mensagem de erro ao array de resposta
           $this->servicoDoUsuario->mensagem = "Você precisa estar autenticado para acessar esta página.";

            // redirecionar para o login
            return redirect()->route('login')
                ->with('dadosUsuario', $this->servicoDoUsuario);
        }

        // Se nenhuma permissão foi passada, tenta detectar automaticamente
        if(!$permissoesNecessarias) {
            // Obter as permissões necessárias
            $permissoesNecessarias = $this->detectarPermissoesNecessariasPelaRota($request);
        }


        // Verifica permissões necessárias
        // permissaoNecessaria ira ser ou o nome da rota: R_USUARIO.LISTA ou o metodo E http: R_RH/USUARIO
        foreach ($permissoesNecessarias as $permissaoNecessaria) {
            if ($this->servicoDoUsuario->temPermissao($permissaoNecessaria)) {
                // Adiciona informações do usuário logado à requisição para uso posterior
                $request
                    ->merge(['dadosUsuario' => $this->servicoDoUsuario]);

                return $next($request);
            }
        }

        // Retorna resposta de acesso negado

        // Adiciona permissões necessárias
        $this->servicoDoUsuario->permissoesNecessarias = $permissoesNecessarias;

        // Se a requisição espera JSON (API), retorna resposta JSON
        if (request()->expectsJson()) {

            // Adiciona mensagem de erro
            $this->servicoDoUsuario->mensagem = "Você não possui permissão para acessar estes dados da API: {$request->path()}";

            return response()
                ->json($this->servicoDoUsuario, 403);
        }

        // Para requisições web, redireciona com mensagens na sessão
        $this->servicoDoUsuario->mensagem = "Você não possui permissão para acessar esta página.";

        return redirect()->back()
            ->with('dadosUsuario', $this->servicoDoUsuario);
    }

    /**
     * Detecta automaticamente as permissões necessárias baseado na rota atual
     */
    private function detectarPermissoesNecessariasPelaRota(Request $request): array
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

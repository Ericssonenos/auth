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

    public function handle(Request $request, Closure $next, string ...$permissoesNecessarias)
    {
        // Verifica se o usuário está devidamente autenticado no sistema
        if (empty($this->servicoDoUsuario->usuario())) {
            return $this->retornarAcessoNegado('Usuário não está autenticado no sistema');
        }

        // Se não foram especificadas permissões, tenta detectar automaticamente baseado na rota
        if (empty($permissoesNecessarias)) {
            $permissoesNecessarias = $this->detectarPermissoesNecessariasPelaRota($request);
        }

        // Verifica se o usuário possui pelo menos uma das permissões necessárias
        $usuarioPossuiPermissaoNecessaria = false;
        $listaDePermissoesVerificadas = [];
        
        foreach ($permissoesNecessarias as $permissaoNecessaria) {
            $listaDePermissoesVerificadas[] = $permissaoNecessaria;
            if ($this->servicoDoUsuario->temPermissao($permissaoNecessaria)) {
                $usuarioPossuiPermissaoNecessaria = true;
                break;
            }
        }

        if (!$usuarioPossuiPermissaoNecessaria) {
            return $this->retornarAcessoNegado(
                'Usuário não possui permissão necessária para acessar este recurso', 
                $listaDePermissoesVerificadas
            );
        }

        // Adiciona informações do usuário logado à requisição para uso posterior
        $request->merge([
            'dados_do_usuario_logado' => $this->servicoDoUsuario->usuario(),
            'permissoes_do_usuario_logado' => $this->servicoDoUsuario->permissoes()
        ]);

        return $next($request);
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
            $permissoesPossiveis[] = $rotaAtual->getName();
        }

        // 2. Controller e Action (padrão REST bem definido)
        if ($rotaAtual->getController()) {
            $nomeDoController = class_basename($rotaAtual->getController());
            $nomeDoAction = $rotaAtual->getActionMethod();
            $nomeDoControllerFormatado = $this->formatarNomeDoController($nomeDoController);
            $permissoesPossiveis[] = "{$nomeDoControllerFormatado}.{$nomeDoAction}";
        }

        // 3. Método HTTP combinado com URI (fallback para casos específicos)
        $metodoHttp = strtolower($request->method());
        $uriFormatada = $this->formatarUriDaRequisicao($request->path());
        $permissoesPossiveis[] = "{$metodoHttp}.{$uriFormatada}";

        // 4. Apenas a URI formatada (para casos mais simples)
        if ($uriFormatada !== $request->path()) {
            $permissoesPossiveis[] = $uriFormatada;
        }

        return array_unique(array_filter($permissoesPossiveis));
    }

    /**
     * Formatar nome do controller removendo sufixo e convertendo para snake_case
     */
    private function formatarNomeDoController(string $nomeDoController): string
    {
        // Remove o sufixo 'Controller' e converte PascalCase para snake_case
        $nomeLimpo = str_replace('Controller', '', $nomeDoController);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $nomeLimpo));
    }

    /**
     * Formatar URI da requisição substituindo parâmetros dinâmicos por placeholders
     */
    private function formatarUriDaRequisicao(string $caminhoUri): string
    {
        // Remove barras extras e formata a URI
        $uriLimpa = trim($caminhoUri, '/');
        
        // Substitui números por placeholder genérico (ex: users/123 -> users/{id})
        $uriLimpa = preg_replace('/\/\d+/', '/{id}', $uriLimpa);
        
        // Substitui UUIDs por placeholder específico
        $uriLimpa = preg_replace('/\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', '/{uuid}', $uriLimpa);
        
        return $uriLimpa;
    }

    /**
     * Retorna resposta apropriada quando o acesso é negado
     */
    private function retornarAcessoNegado(string $mensagemDeErro, array $permissoesNecessarias = [])
    {
        $dadosDaResposta = [
            'mensagem' => $mensagemDeErro,
            'permissoes_do_usuario_atual' => $this->servicoDoUsuario->permissoes(),
            'status_autenticacao' => !empty($this->servicoDoUsuario->usuario()) ? 'autenticado' : 'nao_autenticado',
        ];

        if (!empty($permissoesNecessarias)) {
            $dadosDaResposta['permissoes_necessarias_para_acesso'] = $permissoesNecessarias;
        }

        // Se a requisição espera JSON (API), retorna resposta JSON
        if (request()->expectsJson()) {
            return response()->json($dadosDaResposta, 403);
        }

        // Para requisições web, redireciona com mensagens na sessão
        return redirect()->back()
            ->with('erro_de_acesso', $mensagemDeErro)
            ->with('permissoes_necessarias', $permissoesNecessarias)
            ->with('usuario_nao_autorizado', true);
    }
}

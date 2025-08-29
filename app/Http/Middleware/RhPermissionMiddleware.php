<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\RH\usuario;

class RhPermissionMiddleware
{
    /**
     * Simula obter is_usuario via LDAP: prioriza header X-id_Usuario, depois fallback C000000
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $usuario = $request->header('X-id_Usuario');
            if (empty($usuario)) {
                // fallback: usuário local de teste
                $usuario = '1';
            }

            $sessionKey = "list_Permissoes_session.{$usuario}";

            if (!Session::has($sessionKey)) {
                // buscar do DB e armazenar
                $usuarioModel = new usuario();
                $respostaStatus = $usuarioModel->ObterPermissoesUsuario(['Usuario_id' => $usuario]);

                if (isset($respostaStatus['status']) && $respostaStatus['status'] === true && is_array($respostaStatus['data'])) {
                    // normalizar para lista simples de códigos
                    $permissao = []; // array destino

                    // percorre cada item retornado e extrai 'cod_permissao' quando presente
                    foreach ($respostaStatus['data'] as $item) {
                        // válida estrutura esperada antes de acessar a chave
                        if (is_array($item) && array_key_exists('cod_permissao', $item)) {
                            $permissao[] = $item['cod_permissao'];
                        }
                    }

                    Session::put($sessionKey, $permissao);
                    // também guardar a is_usuario na session para uso pelos Gates
                    Session::put('id_Usuario_session', $usuario);
                } else {
                    Session::put($sessionKey, []);
                }
            }

            // permissões e is_usuario já estão na session; o Gate e controllers devem ler da session

            return $next($request);
        } catch (\Exception $e) {
            Log::error('RhPermissionMiddleware error: ' . $e->getMessage());
            // para segurança, permitir a requisição — ou retornar 403 se preferir negar
            return $next($request);
        }
    }
}

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
     * Simula obter matrícula via LDAP: prioriza header X-id_Usuario, depois fallback C000000
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $usuario = $request->header('X-id_Usuario');
            if (empty($usuario)) {
                // fallback: usuário local de teste
                $usuario = 'C000000';
            }

            $sessionKey = "rh_permissions.{$usuario}";

            if (!Session::has($sessionKey)) {
                // buscar do DB e armazenar
                $usuarioModel = new usuario();
                $res = $usuarioModel->ObterPermissoesMatricula(['Usuario_id' => $usuario]);
                if (isset($res['status']) && $res['status'] === true && is_array($res['data'])) {
                    // normalizar para lista simples de códigos
                    $permissao = array_map(function ($p) { return $p['cod_permissao']; }, $res['data']);
                    Session::put($sessionKey, $permissao);
                    // também guardar a matrícula na session para uso pelos Gates
                    Session::put('rh_usuario', $usuario);
                } else {
                    Session::put($sessionKey, []);
                }
            }

            // permissões e matrícula já estão na session; o Gate e controllers devem ler da session

            return $next($request);
        } catch (\Exception $e) {
            Log::error('RhPermissionMiddleware error: ' . $e->getMessage());
            // para segurança, permitir a requisição — ou retornar 403 se preferir negar
            return $next($request);
        }
    }
}

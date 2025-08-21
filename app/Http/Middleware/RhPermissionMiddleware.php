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
     * Simula obter matrícula via LDAP: prioriza header X-Matricula, depois fallback C000000
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $matricula = $request->header('X-Matricula');
            if (empty($matricula)) {
                // fallback: usuário local de teste
                $matricula = 'C000000';
            }

            $sessionKey = "rh_permissions.{$matricula}";

            if (!Session::has($sessionKey)) {
                // buscar do DB e armazenar
                $usuarioModel = new usuario();
                $res = $usuarioModel->ObterPermissoesMatricula(['matricula_cod' => $matricula]);
                if (isset($res['status']) && $res['status'] === true && is_array($res['data'])) {
                    // normalizar para lista simples de códigos
                    $perms = array_map(function ($p) { return $p['txt_cod_permissao']; }, $res['data']);
                    Session::put($sessionKey, $perms);
                } else {
                    Session::put($sessionKey, []);
                }
            }

            // anexar ao request as permissões (facilita uso nos controllers)
            $permsFromSession = Session::get($sessionKey, []);
            $request->attributes->set('rh_permissions', $permsFromSession);
            $request->attributes->set('rh_matricula', $matricula);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('RhPermissionMiddleware error: ' . $e->getMessage());
            // para segurança, permitir a requisição — ou retornar 403 se preferir negar
            return $next($request);
        }
    }
}

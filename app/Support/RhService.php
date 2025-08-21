<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

class RhService
{
    /**
     * Verifica se a permissão existe para a matrícula atual (via session)
     */
    public function can(string $perm): bool
    {
        $mat = Session::get('rh_matricula') ?: request()->header('X-Matricula');
        if (empty($mat)) {
            return false;
        }
        $perms = Session::get("rh_permissions.{$mat}", []);
        return in_array($perm, $perms);
    }

    /**
     * Invalida cache de permissões para uma matrícula ou todas se null
     */
    public function invalidate(?string $matricula = null): void
    {
        if ($matricula) {
            Session::forget("rh_permissions.{$matricula}");
        } else {
            // remover chave principal caso exista
            $current = Session::get('rh_matricula');
            if ($current) {
                Session::forget("rh_permissions.{$current}");
            }
        }
    }
}

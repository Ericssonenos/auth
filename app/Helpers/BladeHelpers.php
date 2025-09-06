<?php

namespace App\Helpers;

use App\Services\RH\usuarioServices;
use Illuminate\Support\Facades\Route;

class BladeHelpers
{
    /**
     * Gera a tag <a> condicionada à permissão da rota.
     * Recebe: nomeRota (string), innerHtml (string|null), icon (string|null)
     */
    public static function hrefPermissa(string $nomeRota, $innerHtml = null, $icon = null): string
    {
        $dadosUsuario = app(usuarioServices::class);

        // prepara HTML do ícone se passado
        $iconHtml = $icon ? '<i class="' . e($icon) . '" aria-hidden="true"></i> ' : '';

        // se innerHtml não foi fornecido, gera um texto amigável a partir do nome da rota
        if ($innerHtml === null) {
            $label = ucwords(str_replace(['.', '_'], ' ', $nomeRota));
            $innerHtml = $iconHtml . e($label);
        } else {
            // assume que caller fornece HTML intencionalmente (ex.: ícone + texto)
            $innerHtml = $iconHtml . $innerHtml;
        }

        // detecta permissões possíveis para esta rota
        $permissoesPossiveis = self::detectarcod_permissoesNecessariasPelaRota($nomeRota);

        // verifica se o usuário possui qualquer uma das permissões detectadas
        $autorizado = false;
        foreach ($permissoesPossiveis as $cod) {
            if ($dadosUsuario->temPermissao($cod)) {
                $autorizado = true;
                break;
            }
        }

        try {
            $href = route($nomeRota);
        } catch (\Throwable $e) {
            $href = '#';
        }

        if ($autorizado) {
            return '<a href="' . e($href) . '" role="menuitem">' . $innerHtml . '</a>';
        }

        // se não autorizado, mas expoem a rota mesmo assim. obs Não tire a rota da tag, pois pode ser útil para SEO e acessibilidade
        return '<a href="' . e($href) . '" title="Acesso Negado" class="nao-autorizado" aria-disabled="true" role="menuitem">' . $innerHtml . '</a>';
    }

    /**
     * Retorna lista de códigos de permissão possíveis para a rota nomeada.
     * Segue a lógica: N_<nome_da_rota> e R_<METODO>_<URI_FORMATADA> (se a rota existir).
     */
    public static function detectarcod_permissoesNecessariasPelaRota(string $nomeRota): array
    {
        $permissoesPossiveis = [];

        if ($nomeRota) {
            $permissoesPossiveis[] = strtoupper('N_' . $nomeRota);
        }

        // tenta resolver rota pelo nome para obter método e URI
        try {
            $route = Route::getRoutes()->getByName($nomeRota);
        } catch (\Throwable $e) {
            $route = null;
        }

        if ($route) {
            $methods = $route->methods();
            $metodo = 'GET';
            foreach ($methods as $m) {
                if ($m !== 'HEAD') {
                    $metodo = $m;
                    break;
                }
            }

            $uri = $route->uri();
            $uriFormatada = self::formatarUriDaRota($uri);
            $permissoesPossiveis[] = strtoupper('R_' . $metodo . '_' . $uriFormatada);
        }

        return array_unique(array_filter($permissoesPossiveis));
    }

    /**
     * Formata a URI da rota para um token simples: remove slashes, params e normaliza para underscore/upper.
     */
    private static function formatarUriDaRota(string $uri): string
    {
        $s = trim($uri, '/');
        // replace route parameters {id} => param
        $s = preg_replace('/\{[^}]+\}/', 'param', $s);
        // replace non-alphanumeric with underscore
        $s = preg_replace('/[^A-Za-z0-9]+/', '_', $s);
        $s = trim($s, '_');
        return strtoupper($s ?: '');
    }
}

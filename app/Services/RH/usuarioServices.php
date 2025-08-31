<?php

namespace App\Services\RH;

class usuarioServices
{
    private array $permissoes = [];
    private array $dadosDoUsuario = [];

    public function __construct(array $permissoesBrutas = [], ?array $dadosDoUsuario = null)
    {
        foreach ($permissoesBrutas as $permissao) {
            if (is_array($permissao) && array_key_exists('cod_permissao', $permissao)) {
                $this->permissoes[] = $permissao['cod_permissao'];
            } elseif (is_string($permissao)) {
                $this->permissoes[] = $permissao;
            }
        }

        $this->permissoes = array_values(array_unique($this->permissoes));
        $this->dadosDoUsuario = $dadosDoUsuario ?? [];
    }

    public function permissoes(): array
    {
        return $this->permissoes;
    }

    public function usuario(): array
    {
        return $this->dadosDoUsuario;
    }

    public function temPermissao(string $permissao): bool
    {
        return in_array($permissao, $this->permissoes, true);
    }

    public function toArray(): array
    {
        return [
            'permissoes' => $this->permissoes,
            'usuario' => $this->dadosDoUsuario,
        ];
    }
}

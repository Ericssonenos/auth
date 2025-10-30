<?php

namespace App\Services\RH;

class usuarioServices
{
    public array $cod_permissoes = [];
    public int $id_usuario = 0;
    public int $id_Usuario = 0;
    public string $nome_completo = '';
    public string $nome_Completo = '';
    public string $email = '';
    public string $mensagem = '';
    public array $cod_permissoes_necessarias = [];

    public function __construct($dadosDoUsuario = null)
    {
        if (empty($dadosDoUsuario)) {
            return;
        }

        foreach ($dadosDoUsuario['permissoes_usuario'] as $cod_permissao) {
            if (is_array($cod_permissao) && array_key_exists('cod_permissao', $cod_permissao)) {
                $this->cod_permissoes[] = $cod_permissao['cod_permissao'];
            } elseif (is_string($cod_permissao)) {
                $this->cod_permissoes[] = $cod_permissao;
            }
        }

    $this->cod_permissoes = array_values(array_unique($this->cod_permissoes));
    $this->id_usuario = $dadosDoUsuario['id_usuario'] ?? $dadosDoUsuario['id_Usuario'] ?? 0;
    $this->id_Usuario = $this->id_usuario;
    $this->nome_completo = $dadosDoUsuario['nome_completo'] ?? $dadosDoUsuario['nome_Completo'] ?? '';
    $this->nome_Completo = $this->nome_completo;
    $this->email = $dadosDoUsuario['email'] ?? '';
    }


    public function temPermissao(string $permissao): bool
    {
        return in_array($permissao, $this->cod_permissoes, true);
    }
    public function estaLogado(): bool
    {
        return !empty($this->id_usuario);
    }

}

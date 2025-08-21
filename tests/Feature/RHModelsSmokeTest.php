<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RHModelsSmokeTest extends TestCase
{
    // Se preferir limpar DB entre testes, ative RefreshDatabase e configure um connection de teste.
    // use RefreshDatabase;

    public function test_full_rh_flow_create_and_assign()
    {
        // propósito deste teste: criar categoria, permissoes, grupo; atribuir permissoes->grupo; grupo->usuario; verificar leituras
        $this->markTestSkipped('Smoke test que altera DB — execute manualmente localmente quando quiser.');

        // Exemplo de passos (descomentando o teste):
        // 1) Criar categoria
        // 2) Criar 2 permissoes
        // 3) Criar grupo vinculado à categoria
        // 4) Atribuir permissoes ao grupo
        // 5) Atribuir grupo ao usuario (usar Matricula existente C000000)
        // 6) Verificar ObterPermissoesMatricula retorna as permissoes atribuídas via grupo

        // Recomenda-se executar manualmente o arquivo tests/smoke/rh_models_smoke.php para inspeção interativa.
    }
}

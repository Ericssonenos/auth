<?php
// Smoke test de escrita para RH: cria categoria, permissões, grupo, atribui permissões ao grupo e grupo ao usuário.
// Uso (rodar manualmente): php tests/smoke/rh_models_smoke_write.php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$pdo = DB::connection()->getPdo();
try {
    $pdo->beginTransaction();

    $categoriaModel = new \App\Models\RH\categoria();
    $permModel = new \App\Models\RH\permissao();
    $groupModel = new \App\Models\RH\grupo();
    $userModel = new \App\Models\RH\usuario();

    $matricula = 'C000000';
    $matricula_criado_por = 'C000000';

    // 1) criar categoria
    $resCat = $categoriaModel->CriarCategoria([
        'txt_nome_categoria' => 'TesteSmokeCat',
        'txt_descricao_categoria' => 'Categoria criada pelo smoke test',
        'matricula_criado_por' => $matricula_criado_por
    ]);
    echo "CriarCategoria: "; print_r($resCat); echo "\n";

    // 2) criar permissoes
    $resP1 = $permModel->CriarPermissao([
        'txt_cod_permissao' => 'SMOKE_TEST_P1',
        'txt_descricao_permissao' => 'Permissao smoke 1',
        'matricula_criado_por' => $matricula_criado_por
    ]);
    $resP2 = $permModel->CriarPermissao([
        'txt_cod_permissao' => 'SMOKE_TEST_P2',
        'txt_descricao_permissao' => 'Permissao smoke 2',
        'matricula_criado_por' => $matricula_criado_por
    ]);
    echo "CriarPermissoes: "; print_r($resP1); print_r($resP2); echo "\n";

    // recuperar IDs das permissoes criadas
    $p1 = DB::table('RH.Tbl_Permissoes')->where('txt_cod_permissao', 'SMOKE_TEST_P1')->first();
    $p2 = DB::table('RH.Tbl_Permissoes')->where('txt_cod_permissao', 'SMOKE_TEST_P2')->first();

    // 3) criar grupo
    $resG = $groupModel->CriarGrupo([
        'txt_nome_grupo' => 'SmokeGroup',
        'txt_descricao_grupo' => 'Grupo criado para smoke test',
        'categoria_id' => null,
        'matricula_criado_por' => $matricula_criado_por
    ]);
    echo "CriarGrupo: "; print_r($resG); echo "\n";

    $g = DB::table('RH.Tbl_Grupos')->where('txt_nome_grupo', 'SmokeGroup')->first();

    // 4) atribuir permissoes ao grupo
    $groupModel->AtribuirPermissaoGrupo(['grupo_id' => $g->id_grupo, 'permissao_id' => $p1->id_permissao, 'matricula_criado_por' => $matricula_criado_por]);
    $groupModel->AtribuirPermissaoGrupo(['grupo_id' => $g->id_grupo, 'permissao_id' => $p2->id_permissao, 'matricula_criado_por' => $matricula_criado_por]);
    echo "Permissoes atribuídas ao grupo.\n";

    // 5) atribuir grupo ao usuario
    $userModel->AtribuirGrupo(['matricula_cod' => $matricula, 'grupo_id' => $g->id_grupo, 'matricula_criado_por' => $matricula_criado_por]);
    echo "Grupo atribuído ao usuário.\n";

    // 6) verificar permissões do usuário (deverá retornar as SMOKE_TEST_P1 e SMOKE_TEST_P2)
    $perms = $userModel->ObterPermissoesMatricula(['matricula_cod' => $matricula]);
    echo "Permissoes do usuario apos atribuicao: "; print_r($perms); echo "\n";

    // rollback para não deixar alterações
    $pdo->rollBack();
    echo "Rollback executado - alterações revertidas.\n";
} catch (\Exception $e) {
    echo "Erro no smoke write: " . $e->getMessage() . "\n";
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    exit(1);
}

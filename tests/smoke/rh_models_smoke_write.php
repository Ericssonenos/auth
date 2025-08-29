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

    $usuario = 'C000000';
    $criado_Usuario_id = 'C000000';

    // 1) criar categoria
    $resCat = $categoriaModel->CriarCategoria([
        'nome_Categoria' => 'TesteSmokeCat',
        'descricao_Categoria' => 'Categoria criada pelo smoke test',
        'criado_Usuario_id' => $criado_Usuario_id
    ]);
    echo "CriarCategoria: "; print_r($resCat); echo "\n";

    // 2) criar permissoes
    $resP1 = $permModel->CriarPermissao([
        'cod_permissao' => 'SMOKE_TEST_P1',
        'descricao_permissao' => 'Permissao smoke 1',
        'criado_Usuario_id' => $criado_Usuario_id
    ]);
    $resP2 = $permModel->CriarPermissao([
        'cod_permissao' => 'SMOKE_TEST_P2',
        'descricao_permissao' => 'Permissao smoke 2',
        'criado_Usuario_id' => $criado_Usuario_id
    ]);
    echo "CriarPermissoes: "; print_r($resP1); print_r($resP2); echo "\n";

    // recuperar IDs das permissoes criadas
    $p1 = DB::table('RH.Tbl_Permissoes')->where('cod_permissao', 'SMOKE_TEST_P1')->first();
    $p2 = DB::table('RH.Tbl_Permissoes')->where('cod_permissao', 'SMOKE_TEST_P2')->first();

    // 3) criar grupo
    $resG = $groupModel->CriarGrupo([
        'nome_Grupo' => 'SmokeGroup',
        'descricao_Grupo' => 'Grupo criado para smoke test',
        'categoria_id' => null,
        'criado_Usuario_id' => $criado_Usuario_id
    ]);
    echo "CriarGrupo: "; print_r($resG); echo "\n";

    $g = DB::table('RH.Tbl_Grupos')->where('nome_Grupo', 'SmokeGroup')->first();

    // 4) atribuir permissoes ao grupo
    $groupModel->AtribuirPermissaoGrupo(['grupo_id' => $g->id_Grupo, 'permissao_id' => $p1->id_permissao, 'criado_Usuario_id' => $criado_Usuario_id]);
    $groupModel->AtribuirPermissaoGrupo(['grupo_id' => $g->id_Grupo, 'permissao_id' => $p2->id_permissao, 'criado_Usuario_id' => $criado_Usuario_id]);
    echo "Permissoes atribuídas ao grupo.\n";

    // 5) atribuir grupo ao usuario
    $userModel->AtribuirGrupo(['Usuario_id' => $usuario, 'grupo_id' => $g->id_Grupo, 'criado_Usuario_id' => $criado_Usuario_id]);
    echo "Grupo atribuído ao usuário.\n";

    // 6) verificar permissões do usuário (deverá retornar as SMOKE_TEST_P1 e SMOKE_TEST_P2)
    $permissao = $userModel->ObterPermissoesMatricula(['Usuario_id' => $usuario]);
    echo "Permissoes do usuario apos atribuicao: "; print_r($permissao); echo "\n";

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

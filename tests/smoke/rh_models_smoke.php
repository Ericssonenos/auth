<?php
// Smoke test para os models RH (usuario, grupo, permissao, categoria)
// Como usar (execute da raiz do projeto):
// php tests/smoke/rh_models_smoke.php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function dumpResult($title, $result)
{
    echo "=== $title ===\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";
}

try {
    // Instanciar modelos
    $userModel = new \App\Models\RH\usuario();
    $groupModel = new \App\Models\RH\grupo();
    $permModel = new \App\Models\RH\permissao();
    $catModel = new \App\Models\RH\categoria();

    // Lista basica (read-only)
    $resUsers = $userModel->ListaUsuarios();
    dumpResult('ListaUsuarios', $resUsers);

    $resGroups = $groupModel->ListaGrupos();
    dumpResult('ListaGrupos', $resGroups);

    $resPerms = $permModel->ListaPermissoes();
    dumpResult('ListaPermissoes', $resPerms);

    $resCats = $catModel->ListaCategorias();
    dumpResult('ListaCategorias', $resCats);

    // Se houver permissões, obter detalhes da primeira
    if (!empty($resPerms['data']) && is_array($resPerms['data'])) {
        $first = $resPerms['data'][0];
        if (isset($first['id_permissao'])) {
            $det = $permModel->ObterPermissaoPorId($first['id_permissao']);
            dumpResult('ObterPermissaoPorId('.$first['id_permissao'].')', $det);
        }
    }

    // Exemplo de consulta de permissões por matrícula (usar matrícula existente ou a que você seedou)
    $usuarioExemplo = 'C000000';
    $permsByMat = $userModel->ObterPermissoesMatricula(['Usuario_id' => $usuarioExemplo]);
    dumpResult('ObterPermissoesMatricula('.$usuarioExemplo.')', $permsByMat);

    echo "Smoke test concluído.\n";
    exit(0);
} catch (\Exception $e) {
    echo "Erro durante o smoke test: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(2);
}

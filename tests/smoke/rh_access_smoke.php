<?php
// Smoke test para fluxo de acesso: cria dados, atribui permissão e abre a view demo
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

try {
    // garantir ambiente
    $matricula = 'C000000';
    $matricula_criado_por = 'C000000';

    echo "Executando smoke access flow...\n";

    // 1) garantir que permissão PERM_GERENCIAR_PERMISSOES exista
    $exists = DB::table('RH.Tbl_Permissoes')->where('txt_cod_permissao', 'PERM_GERENCIAR_PERMISSOES')->exists();
    if (!$exists) {
        DB::table('RH.Tbl_Permissoes')->insert([
            'txt_cod_permissao' => 'PERM_GERENCIAR_PERMISSOES',
            'txt_descricao_permissao' => 'Permissão para gerenciar permissões (smoke)',
            'matricula_criado_por' => $matricula_criado_por,
            'dat_criado_em' => DB::raw('GETDATE()')
        ]);
        echo "Permissão criada.\n";
    }

    // 2) garantir que o usuário tenha a permissão via vínculo direto
    $perm = DB::table('RH.Tbl_Permissoes')->where('txt_cod_permissao', 'PERM_GERENCIAR_PERMISSOES')->first();
    $relExists = DB::table('RH.Tbl_Rel_Usuarios_Permissoes')
        ->where('matricula_cod', $matricula)
        ->where('permissao_id', $perm->id_permissao)
        ->whereNull('dat_cancelamento_em')
        ->exists();
    if (!$relExists) {
        DB::table('RH.Tbl_Rel_Usuarios_Permissoes')->insert([
            'matricula_cod' => $matricula,
            'permissao_id' => $perm->id_permissao,
            'matricula_criado_por' => $matricula_criado_por,
            'dat_criado_em' => DB::raw('GETDATE()')
        ]);
        echo "Permissão atribuída ao usuário.\n";
    }

    // 3) chamar a rota demo com header X-Matricula e inspecionar resposta HTML
    $ch = curl_init('http://localhost:8000/rh/demo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Matricula: $matricula"]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Request /rh/demo returned HTTP $code\n";
    echo substr($html, 0, 1000) . "\n";

    echo "Smoke access flow concluído.\n";
} catch (\Exception $e) {
    echo "Erro smoke access: " . $e->getMessage() . "\n";
    exit(1);
}

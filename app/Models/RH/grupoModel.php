<?php

namespace App\Models\RH;

use App\Services\Operacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\RH\usuarioServices;

class grupoModel extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    public function ObterDadosGrupo($params)
    {
        $fn = $params['fn'] ?? null;

        if ($fn === 'fn-usuario-status') {
            // este traz os grupos vinculados a um usuário específico
            $execParams[':usuario_id'] = $params['usuario_id'];
            $consultaSql = "SELECT
                                g.id_grupo
                            ,   g.nome_grupo
                            ,   g.descricao_grupo
                            ,   g.categoria_id
                            ,   rug.id_rel_usuario_grupo
                            ,   rh.fn_getpermissoesgrupoxml(g.id_grupo) AS permissoes_grupo
                            FROM rh.tb_grupos g
                            LEFT JOIN rh.tr_usuarios_grupos rug
                                ON rug.grupo_id = g.id_grupo
                                AND rug.usuario_id = :usuario_id
                                AND rug.dat_cancelamento_em IS NULL
                            WHERE g.dat_cancelamento_em IS NULL";
        } else {

            $parametrizacao = Operacao::Parametrizar($params);
            // Verifica se houve erro na parametrização
            if ($parametrizacao['status'] === 422) {
                return [
                    'status' => $parametrizacao['status'],
                    'mensagem' => $parametrizacao['mensagem'],
                    'data' => []
                ];
            }

            $whereParams = $parametrizacao['whereParams'];
            $optsParams = $parametrizacao['optsParams'];
            $execParams = $parametrizacao['execParams'];

            $consultaSql = "SELECT
                            g.id_grupo,
                            g.nome_grupo,
                            g.descricao_grupo,
                            g.categoria_id,
                            c.nome_categoria,
                            rh.fn_getpermissoesgrupoxml(g.id_grupo) as permissoes_xml
                        FROM rh.tb_grupos g
                        LEFT JOIN rh.tb_categorias c ON g.categoria_id = c.id_categoria
                        WHERE g.dat_cancelamento_em IS NULL
                        AND c.dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? ' ORDER BY g.nome_grupo')
                . ($optsParams['limit'] ?? '')
                . ($optsParams['offset'] ?? '');
        }

        try {
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute($execParams);
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'mensagem' => 'Grupos recuperados.',
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mensagem' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function CriarGrupo($params)
    {
        return $this->CadastrarGrupo($params);
    }

    public function CadastrarGrupo($params)
    {
        try {
            $nomeGrupo = trim($params['nome_grupo'] ?? $params['nome_Grupo']);
            $descricaoGrupo = trim($params['descricao_grupo'] ?? $params['descricao_Grupo'] ?? '');
            $categoriaId = !empty($params['categoria_id']) ? $params['categoria_id'] : null;
            $criadoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "INSERT INTO rh.tb_grupos (
                            nome_grupo, descricao_grupo, categoria_id, criado_usuario_id
                        ) VALUES (:nome_grupo, :descricao_grupo, :categoria_id, :criado_usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':nome_grupo' => $nomeGrupo,
                ':descricao_grupo' => $descricaoGrupo,
                ':categoria_id' => $categoriaId,
                ':criado_usuario_id' => $criadoUsuarioId
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'mensagem' => $rows > 0 ? 'Grupo criado com sucesso!' : 'Nenhuma linha inserida.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mensagem' => 'Erro ao criar grupo: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function AtualizarGrupo($params)
    {
        try {
            $grupoId = $params['grupo_id'] ?? $params['id_Grupo'];
            $nomeGrupo = trim($params['nome_grupo'] ?? $params['nome_Grupo']);
            $descricaoGrupo = trim($params['descricao_grupo'] ?? $params['descricao_Grupo'] ?? '');
            $categoriaId = !empty($params['categoria_id']) ? $params['categoria_id'] : null;
            $atualizadoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE rh.tb_grupos
                            SET nome_grupo = :nome_grupo,
                                descricao_grupo = :descricao_grupo,
                                categoria_id = :categoria_id,
                                atualizado_usuario_id = :atualizado_usuario_id,
                                dat_atualizado_em = :dat_atualizado_em
                            WHERE id_grupo = :grupo_id
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':nome_grupo' => $nomeGrupo,
                ':descricao_grupo' => $descricaoGrupo,
                ':categoria_id' => $categoriaId,
                ':atualizado_usuario_id' => $atualizadoUsuarioId,
                ':dat_atualizado_em' => date('Y-m-d H:i:s'),
                ':grupo_id' => $grupoId
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'mensagem' => $rows > 0 ? 'Grupo atualizado com sucesso!' : 'Nenhuma linha atualizada.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mensagem' => 'Erro ao atualizar grupo: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function RemoverGrupo($params)
    {
        return $this->DeletarGrupo($params);
    }

    public function DeletarGrupo($params)
    {
        try {
            $grupoId = $params['grupo_id'] ?? $params['id_Grupo'];
            $cancelamentoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE rh.tb_grupos
                            SET cancelamento_usuario_id = :cancelamento_usuario_id,
                                dat_cancelamento_em = :dat_cancelamento_em
                            WHERE id_grupo = :grupo_id
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cancelamento_usuario_id' => $cancelamentoUsuarioId,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s'),
                ':grupo_id' => $grupoId
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'mensagem' => $rows > 0 ? 'Grupo excluído com sucesso!' : 'Grupo não encontrado ou já excluído.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mensagem' => 'Erro ao excluir grupo: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function AtribuirPermissaoGrupo($params)
    {
        try {
            $grupoId = $params['grupo_id'];
            $permissaoId = $params['permissao_id'];
            $criadoUsuarioId = $this->obterUsuarioAutenticadoId();

            // verificar se já existe vínculo ativo
            $checkSql = "SELECT 1 FROM rh.tr_grupos_permissoes WHERE grupo_id = :grupo_id AND permissao_id = :permissao_id AND dat_cancelamento_em IS NULL";
            $check = $this->conexao->prepare($checkSql);
            $check->execute([':grupo_id' => $grupoId, ':permissao_id' => $permissaoId]);
            $exists = $check->fetchColumn();
            $check->closeCursor();

            if ($exists) {
                return [
                    'status' => 400,
                    'mensagem' => 'Permissão já atribuída a este grupo.'
                ];
            }

            $consultaSql = "INSERT INTO rh.tr_grupos_permissoes (grupo_id, permissao_id, criado_usuario_id) VALUES (:grupo_id, :permissao_id, :criado_usuario_id)";
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':grupo_id' => $grupoId,
                ':permissao_id' => $permissaoId,
                ':criado_usuario_id' => $criadoUsuarioId
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => 200,
                'mensagem' => $rows > 0 ? 'Permissão atribuída ao grupo com sucesso!' : 'Nenhuma permissão foi atribuída.'
            ];
        } catch (\Exception $e) {
             return Operacao::mapearExcecaoPDO($e, $params);
        }
    }

    public function RemoverPermissaoGrupo($params)
    {
        try {
            $idRelGrupoPermissao = $params['id_rel_grupo_permissao'];
            $cancelamentoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE rh.tr_grupos_permissoes
                            SET cancelamento_usuario_id = :cancelamento_usuario_id,
                                dat_cancelamento_em = :dat_cancelamento_em
                            WHERE id_rel_grupo_permissao = :id_rel_grupo_permissao
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':id_rel_grupo_permissao' => $idRelGrupoPermissao,
                ':cancelamento_usuario_id' => $cancelamentoUsuarioId,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s')
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => 200,
                'mensagem' => $rows > 0 ? 'Permissão removida do grupo com sucesso!' : 'Relacionamento não encontrado ou já removido.'
            ];
        } catch (\Exception $e) {
             return Operacao::mapearExcecaoPDO($e, $params);
        }
    }

    // cria relação pai->filho entre grupos (evita duplicata ativa e self-link)
    public function AtribuirGrupoGrupo($params)
    {
        $grupoPaiId = $params['grupo_pai_id'];
        $grupoFilhoId = $params['grupo_filho_id'];
        $criadoUsuarioId = $params['criado_usuario_id'] ?? $params['criado_Usuario_id'] ?? $this->obterUsuarioAutenticadoId();

        if ($grupoPaiId == $grupoFilhoId) {
            return; // evita self-link
        }

        $checkSql = "SELECT 1 FROM rh.tr_grupos_grupos WHERE grupo_pai_id = :pai AND grupo_filho_id = :filho AND dat_cancelamento_em IS NULL";
        $check = $this->conexao->prepare($checkSql);
        $check->execute([':pai' => $grupoPaiId, ':filho' => $grupoFilhoId]);
        $exists = $check->fetchColumn();
        $check->closeCursor();

        if ($exists) {
            return; // já existe vínculo ativo
        }

        $consultaSql = "INSERT INTO rh.tr_grupos_grupos (grupo_pai_id, grupo_filho_id, criado_usuario_id) VALUES (:pai, :filho, :criado_usuario_id)";
        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':pai' => $grupoPaiId,
            ':filho' => $grupoFilhoId,
            ':criado_usuario_id' => $criadoUsuarioId
        ]);
        $comando->closeCursor();
    }

    // remove (marca cancelamento) relação entre grupos
    public function RemoverGrupoGrupo($params)
    {
        $idRelGrupoGrupo = $params['id_rel_grupo_grupo'];
        $cancelamentoUsuarioId = $params['cancelamento_usuario_id'] ?? $params['cancelamento_Usuario_id'] ?? $this->obterUsuarioAutenticadoId();

        $consultaSql = "UPDATE rh.tr_grupos_grupos
                        SET cancelamento_usuario_id = :cancelamento_usuario_id,
                            dat_cancelamento_em = :dat_cancelamento_em
                        WHERE id_rel_grupo_grupo = :id_rel_grupo_grupo
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':id_rel_grupo_grupo' => $idRelGrupoGrupo,
            ':cancelamento_usuario_id' => $cancelamentoUsuarioId,
            ':dat_cancelamento_em' => date('Y-m-d H:i:s')
        ]);
        $comando->closeCursor();
    }



    public function __destruct()
    {
        $this->conexao = null;
    }

    private function obterUsuarioAutenticadoId(): int
    {
        $usuarioService = app(usuarioServices::class);

        if (property_exists($usuarioService, 'id_usuario') && !empty($usuarioService->id_usuario)) {
            return (int) $usuarioService->id_usuario;
        }

        return (int) ($usuarioService->id_Usuario ?? 0);
    }
}

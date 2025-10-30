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
                                g.id_Grupo
                            ,   g.nome_Grupo
                            ,   g.descricao_Grupo
                            ,   g.categoria_id
                            ,   rug.id_rel_usuario_grupo
                            ,   permissoes_Grupo = RH.Fn_GetPermissoesGrupoXML(g.id_Grupo)
                            FROM RH.Tbl_Grupos g
                            LEFT JOIN RH.Tbl_Rel_Usuarios_Grupos rug
                                ON rug.grupo_id = g.id_Grupo
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
                            g.id_Grupo,
                            g.nome_Grupo,
                            g.descricao_Grupo,
                            g.categoria_id,
                            c.nome_Categoria,
                            RH.Fn_GetPermissoesGrupoXML(g.id_Grupo) as permissoes_XML
                        FROM RH.Tbl_Grupos g
                        LEFT JOIN RH.Tbl_Categorias c ON g.categoria_id = c.id_Categoria
                        WHERE g.dat_cancelamento_em IS NULL
                        AND c.dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? ' ORDER BY g.nome_Grupo')
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
            $nome_Grupo = trim($params['nome_Grupo']);
            $descricao_Grupo = trim($params['descricao_Grupo'] ?? '');
            $categoria_id = !empty($params['categoria_id']) ? $params['categoria_id'] : null;
            $criado_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "INSERT INTO RH.Tbl_Grupos (
                            nome_Grupo, descricao_Grupo, categoria_id, criado_Usuario_id
                        ) VALUES (:nome_Grupo, :descricao_Grupo, :categoria_id, :criado_Usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':nome_Grupo' => $nome_Grupo,
                ':descricao_Grupo' => $descricao_Grupo,
                ':categoria_id' => $categoria_id,
                ':criado_Usuario_id' => $criado_Usuario_id
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
            $grupo_id = $params['grupo_id'] ?? $params['id_Grupo'];
            $nome_Grupo = trim($params['nome_Grupo']);
            $descricao_Grupo = trim($params['descricao_Grupo'] ?? '');
            $categoria_id = !empty($params['categoria_id']) ? $params['categoria_id'] : null;
            $atualizado_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "UPDATE RH.Tbl_Grupos
                            SET nome_Grupo = :nome_Grupo,
                                descricao_Grupo = :descricao_Grupo,
                                categoria_id = :categoria_id,
                                atualizado_Usuario_id = :atualizado_Usuario_id,
                                dat_atualizado_em = GETDATE()
                            WHERE id_Grupo = :grupo_id
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':nome_Grupo' => $nome_Grupo,
                ':descricao_Grupo' => $descricao_Grupo,
                ':categoria_id' => $categoria_id,
                ':atualizado_Usuario_id' => $atualizado_Usuario_id,
                ':grupo_id' => $grupo_id
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
            $grupo_id = $params['grupo_id'] ?? $params['id_Grupo'];
            $cancelamento_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "UPDATE RH.Tbl_Grupos
                            SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                                dat_cancelamento_em = GETDATE()
                            WHERE id_Grupo = :grupo_id
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id,
                ':grupo_id' => $grupo_id
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
            $grupo_id = $params['grupo_id'];
            $permissao_id = $params['permissao_id'];
            // classe singleton para obter id_Usuario logado
            $criado_Usuario_id = app(usuarioServices::class)->id_Usuario;

            // verificar se já existe vínculo ativo
            $checkSql = "SELECT 1 FROM RH.Tbl_Rel_Grupos_Permissoes WHERE grupo_id = :grupo_id AND permissao_id = :permissao_id AND dat_cancelamento_em IS NULL";
            $check = $this->conexao->prepare($checkSql);
            $check->execute([':grupo_id' => $grupo_id, ':permissao_id' => $permissao_id]);
            $exists = $check->fetchColumn();
            $check->closeCursor();

            if ($exists) {
                return [
                    'status' => 400,
                    'mensagem' => 'Permissão já atribuída a este grupo.'
                ];
            }

            $consultaSql = "INSERT INTO RH.Tbl_Rel_Grupos_Permissoes (grupo_id, permissao_id, criado_Usuario_id) VALUES (:grupo_id, :permissao_id, :criado_Usuario_id)";
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':grupo_id' => $grupo_id,
                ':permissao_id' => $permissao_id,
                ':criado_Usuario_id' => $criado_Usuario_id
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
            $id_rel_grupo_permissao = $params['id_rel_grupo_permissao'];
            $cancelamento_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "UPDATE RH.Tbl_Rel_Grupos_Permissoes
                            SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                                dat_cancelamento_em = GETDATE()
                            WHERE id_rel_grupo_permissao = :id_rel_grupo_permissao
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':id_rel_grupo_permissao' => $id_rel_grupo_permissao,
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id
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
        $grupo_pai_id = $params['grupo_pai_id'];
        $grupo_filho_id = $params['grupo_filho_id'];
        $criado_Usuario_id = $params['criado_Usuario_id'];

        if ($grupo_pai_id == $grupo_filho_id) {
            return; // evita self-link
        }

        $checkSql = "SELECT 1 FROM RH.Tbl_Rel_Grupos_Grupos WHERE grupo_pai_id = :pai AND grupo_filho_id = :filho AND dat_cancelamento_em IS NULL";
        $check = $this->conexao->prepare($checkSql);
        $check->execute([':pai' => $grupo_pai_id, ':filho' => $grupo_filho_id]);
        $exists = $check->fetchColumn();
        $check->closeCursor();

        if ($exists) {
            return; // já existe vínculo ativo
        }

        $consultaSql = "INSERT INTO RH.Tbl_Rel_Grupos_Grupos (grupo_pai_id, grupo_filho_id, criado_Usuario_id) VALUES (:pai, :filho, :criado_Usuario_id)";
        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':pai' => $grupo_pai_id,
            ':filho' => $grupo_filho_id,
            ':criado_Usuario_id' => $criado_Usuario_id
        ]);
        $comando->closeCursor();
    }

    // remove (marca cancelamento) relação entre grupos
    public function RemoverGrupoGrupo($params)
    {
        $id_rel_grupo_grupo = $params['id_rel_grupo_grupo'];
        $cancelamento_Usuario_id = $params['cancelamento_Usuario_id'];

        $consultaSql = "UPDATE RH.Tbl_Rel_Grupos_Grupos
                        SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                            dat_cancelamento_em = GETDATE()
                        WHERE id_rel_grupo_grupo = :id_rel_grupo_grupo
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':id_rel_grupo_grupo' => $id_rel_grupo_grupo,
            ':cancelamento_Usuario_id' => $cancelamento_Usuario_id
        ]);
        $comando->closeCursor();
    }



    public function __destruct()
    {
        $this->conexao = null;
    }
}

<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class grupo extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    public function ListaGrupos()
    {
        try {
            $consultaSql = "SELECT id_grupo, txt_nome_grupo, txt_descricao_grupo, categoria_id
                            FROM RH.Tbl_Grupos
                            WHERE txt_nome_grupo IS NOT NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute();
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'message' => 'Lista de grupos recuperada.',
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function ObterGrupoPorId($id_grupo)
    {
        try {
            $consultaSql = "SELECT id_grupo, txt_nome_grupo, txt_descricao_grupo, categoria_id,
                                    matricula_criado_por, dat_criado_em, matricula_cancelamento_em, dat_cancelamento_em
                            FROM RH.Tbl_Grupos
                            WHERE id_grupo = :id_grupo";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([':id_grupo' => $id_grupo]);
            $data = $comando->fetch(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'message' => 'Grupo recuperado.',
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function CriarGrupo($params)
    {
        try {
            $txt_nome_grupo = $params['txt_nome_grupo'];
            $txt_descricao_grupo = $params['txt_descricao_grupo'] ?? null;
            $categoria_id = $params['categoria_id'] ?? null;
            $matricula_criado_por = $params['matricula_criado_por'];

            $consultaSql = "INSERT INTO RH.Tbl_Grupos (
                            txt_nome_grupo, txt_descricao_grupo, categoria_id, matricula_criado_por
                        ) VALUES (:txt_nome_grupo, :txt_descricao_grupo, :categoria_id, :matricula_criado_por)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':txt_nome_grupo' => $txt_nome_grupo,
                ':txt_descricao_grupo' => $txt_descricao_grupo,
                ':categoria_id' => $categoria_id,
                ':matricula_criado_por' => $matricula_criado_por
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Grupo criado.' : 'Nenhuma linha inserida.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function AtualizarGrupo($params)
    {
        try {
            $id_grupo = $params['id_grupo'];
            $txt_nome_grupo = $params['txt_nome_grupo'];
            $txt_descricao_grupo = $params['txt_descricao_grupo'] ?? null;
            $categoria_id = $params['categoria_id'] ?? null;
            $matricula_atualizado_por = $params['matricula_atualizado_por'];

            $consultaSql = "UPDATE RH.Tbl_Grupos
                            SET txt_nome_grupo = :txt_nome_grupo,
                                txt_descricao_grupo = :txt_descricao_grupo,
                                categoria_id = :categoria_id,
                                matricula_atualizado_em = :matricula_atualizado_por,
                                dat_atualizado_em = GETDATE()
                            WHERE id_grupo = :id_grupo
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':txt_nome_grupo' => $txt_nome_grupo,
                ':txt_descricao_grupo' => $txt_descricao_grupo,
                ':categoria_id' => $categoria_id,
                ':matricula_atualizado_por' => $matricula_atualizado_por,
                ':id_grupo' => $id_grupo
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Grupo atualizado.' : 'Nenhuma linha atualizada.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function RemoverGrupo($params)
    {
        try {
            $id_grupo = $params['id_grupo'];
            $matricula_cancelamento_em = $params['matricula_cancelamento_em'];

            $consultaSql = "UPDATE RH.Tbl_Grupos
                            SET matricula_cancelamento_em = :matricula_cancelamento_em,
                                dat_cancelamento_em = GETDATE()
                            WHERE id_grupo = :id_grupo
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':matricula_cancelamento_em' => $matricula_cancelamento_em,
                ':id_grupo' => $id_grupo
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Grupo removido (cancelado).' : 'Nenhuma linha atualizada.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    // atribui permissão a um grupo (evita duplicata ativa)
    public function AtribuirPermissaoGrupo($params)
    {
        $grupo_id = $params['grupo_id'];
        $permissao_id = $params['permissao_id'];
        $matricula_criado_por = $params['matricula_criado_por'];

        // verificar se já existe vínculo ativo
        $checkSql = "SELECT 1 FROM RH.Tbl_Rel_Grupos_Permissoes WHERE grupo_id = :grupo_id AND permissao_id = :permissao_id AND dat_cancelamento_em IS NULL";
        $check = $this->conexao->prepare($checkSql);
        $check->execute([':grupo_id' => $grupo_id, ':permissao_id' => $permissao_id]);
        $exists = $check->fetchColumn();
        $check->closeCursor();

        if ($exists) {
            return; // já existe vínculo ativo
        }

        $consultaSql = "INSERT INTO RH.Tbl_Rel_Grupos_Permissoes (grupo_id, permissao_id, matricula_criado_por) VALUES (:grupo_id, :permissao_id, :matricula_criado_por)";
        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':grupo_id' => $grupo_id,
            ':permissao_id' => $permissao_id,
            ':matricula_criado_por' => $matricula_criado_por
        ]);
        $comando->closeCursor();
    }

    // remove (marca cancelamento) vínculo permissão->grupo
    public function RemoverPermissaoGrupo($params)
    {
        $id_rel_grupo_permissao = $params['id_rel_grupo_permissao'];
        $matricula_cancelamento_em = $params['matricula_cancelamento_em'];

        $consultaSql = "UPDATE RH.Tbl_Rel_Grupos_Permissoes
                        SET matricula_cancelamento_em = :matricula_cancelamento_em,
                            dat_cancelamento_em = GETDATE()
                        WHERE id_rel_grupo_permissao = :id_rel_grupo_permissao
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':id_rel_grupo_permissao' => $id_rel_grupo_permissao,
            ':matricula_cancelamento_em' => $matricula_cancelamento_em
        ]);
        $comando->closeCursor();
    }

    // cria relação pai->filho entre grupos (evita duplicata ativa e self-link)
    public function AtribuirGrupoGrupo($params)
    {
        $grupo_pai_id = $params['grupo_pai_id'];
        $grupo_filho_id = $params['grupo_filho_id'];
        $matricula_criado_por = $params['matricula_criado_por'];

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

        $consultaSql = "INSERT INTO RH.Tbl_Rel_Grupos_Grupos (grupo_pai_id, grupo_filho_id, matricula_criado_por) VALUES (:pai, :filho, :matricula_criado_por)";
        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':pai' => $grupo_pai_id,
            ':filho' => $grupo_filho_id,
            ':matricula_criado_por' => $matricula_criado_por
        ]);
        $comando->closeCursor();
    }

    // remove (marca cancelamento) relação entre grupos
    public function RemoverGrupoGrupo($params)
    {
        $id_rel_grupo_grupo = $params['id_rel_grupo_grupo'];
        $matricula_cancelamento_em = $params['matricula_cancelamento_em'];

        $consultaSql = "UPDATE RH.Tbl_Rel_Grupos_Grupos
                        SET matricula_cancelamento_em = :matricula_cancelamento_em,
                            dat_cancelamento_em = GETDATE()
                        WHERE id_rel_grupo_grupo = :id_rel_grupo_grupo
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':id_rel_grupo_grupo' => $id_rel_grupo_grupo,
            ':matricula_cancelamento_em' => $matricula_cancelamento_em
        ]);
        $comando->closeCursor();
    }

    public function __destruct()
    {
        $this->conexao = null;
    }

}

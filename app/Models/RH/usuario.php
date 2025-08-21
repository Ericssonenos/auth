<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class usuario extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    public function ListaUsuarios()
    {
        try {
            $consultaSql = "SELECT Matricula, Nome_Completo FROM RH.Users WHERE Matricula IS NOT NULL AND Nome_Completo IS NOT NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute();
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'message' => 'Lista recuperada com sucesso.',
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

    public function ObterPermissoesMatricula($params)
    {
        try {
            $matricula = $params['matricula_cod'];

                        $consultaSql = "SELECT DISTINCT txt_cod_permissao FROM (
                                -- Permissões diretas do usuário
                                SELECT p.txt_cod_permissao
                                FROM RH.Tbl_Permissoes p
                                INNER JOIN RH.Tbl_Rel_Usuarios_Permissoes rup ON rup.permissao_id = p.id_permissao
                                WHERE rup.matricula_cod = :matricula1
                                    AND rup.dat_cancelamento_em IS NULL
                                    AND p.dat_cancelamento_em IS NULL

                                UNION

                                -- Permissões via grupos
                                SELECT p.txt_cod_permissao
                                FROM RH.Tbl_Permissoes p
                                INNER JOIN RH.Tbl_Rel_Grupos_Permissoes gp ON gp.permissao_id = p.id_permissao
                                INNER JOIN RH.Tbl_Grupos g ON g.id_grupo = gp.grupo_id
                                INNER JOIN RH.Tbl_Rel_Usuarios_Grupos ug ON ug.grupo_id = g.id_grupo
                                WHERE ug.matricula_cod = :matricula2
                                    AND ug.dat_cancelamento_em IS NULL
                                    AND gp.dat_cancelamento_em IS NULL
                                    AND g.dat_cancelamento_em IS NULL
                                    AND p.dat_cancelamento_em IS NULL
                        ) AS perms
                        ORDER BY txt_cod_permissao";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([':matricula1' => $matricula, ':matricula2' => $matricula]);
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'message' => 'Permissões recuperadas.',
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

    public function AtribuirPermissoes($params)
    {
        try {
            $matricula_cod = $params['matricula_cod'];
            $permissao_id = $params['permissao_id'];
            $matricula_criado_por = $params['matricula_criado_por'];

            $consultaSql = "INSERT INTO RH.Tbl_Rel_Usuarios_Permissoes (
                        matricula_cod
                    ,   permissao_id
                    ,   matricula_criado_por
                    ) VALUES (:matricula_cod, :permissao_id, :matricula_criado_por)";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':matricula_cod' => $matricula_cod,
                ':permissao_id' => $permissao_id,
                ':matricula_criado_por' => $matricula_criado_por
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Permissão atribuída.' : 'Nenhuma linha inserida.',
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

    public function AtribuirGrupo($params)
    {
        try {
            $matricula_cod = $params['matricula_cod'];
            $grupo_id = $params['grupo_id'];
            $matricula_criado_por = $params['matricula_criado_por'];

            $consultaSql = "INSERT INTO RH.Tbl_Rel_Usuarios_Grupos (
                        matricula_cod
                    ,   grupo_id
                    ,   matricula_criado_por
                    ) VALUES (:matricula_cod, :grupo_id, :matricula_criado_por)";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':matricula_cod' => $matricula_cod,
                ':grupo_id' => $grupo_id,
                ':matricula_criado_por' => $matricula_criado_por
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Grupo atribuído ao usuário.' : 'Nenhuma linha inserida.',
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

    public function RemoverPermissoes($params)
    {
        try {
            $id_rel_usuario_permissao = $params['id_rel_usuario_permissao'];
            $matricula_cancelamento_em = $params['matricula_cancelamento_em'];

            $consultaSql = "UPDATE RH.Tbl_Rel_Usuarios_Permissoes
                            SET matricula_cancelamento_em = :matricula_cancelamento_em,
                                data_cancelamento_em = GETDATE()
                            WHERE id_rel_usuario_permissao = :id_rel_usuario_permissao
                            AND data_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':id_rel_usuario_permissao' => $id_rel_usuario_permissao,
                ':matricula_cancelamento_em' => $matricula_cancelamento_em
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Permissão removida (cancelada).' : 'Nenhuma linha atualizada.',
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
            $id_rel_usuario_grupo = $params['id_rel_usuario_grupo'];
            $matricula_cancelamento_em = $params['matricula_cancelamento_em'];

            $consultaSql = "UPDATE RH.Tbl_Rel_Usuarios_Grupos
                            SET matricula_cancelamento_em = :matricula_cancelamento_em,
                                data_cancelamento_em = GETDATE()
                            WHERE id_rel_usuario_grupo = :id_rel_usuario_grupo
                            AND data_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':id_rel_usuario_grupo' => $id_rel_usuario_grupo,
                ':matricula_cancelamento_em' => $matricula_cancelamento_em
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Vínculo de grupo cancelado.' : 'Nenhuma linha atualizada.',
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

    public function __destruct()
    {
        $this->conexao = null;
    }

}

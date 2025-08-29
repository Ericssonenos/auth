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
            $consultaSql = "SELECT id_Usuario, Nome_Completo FROM RH.Users WHERE id_Usuario IS NOT NULL AND Nome_Completo IS NOT NULL";

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
            $usuario = $params['Usuario_id'];

                        $consultaSql = "SELECT DISTINCT cod_permissao FROM (
                                -- Permissões diretas do usuário
                                SELECT p.cod_permissao
                                FROM RH.Tbl_Permissoes p
                                INNER JOIN RH.Tbl_Rel_Usuarios_Permissoes rup ON rup.permissao_id = p.id_permissao
                                WHERE rup.Usuario_id = :usuario1
                                    AND rup.dat_cancelamento_em IS NULL
                                    AND p.dat_cancelamento_em IS NULL

                                UNION

                                -- Permissões via grupos
                                SELECT p.cod_permissao
                                FROM RH.Tbl_Permissoes p
                                INNER JOIN RH.Tbl_Rel_Grupos_Permissoes gp ON gp.permissao_id = p.id_permissao
                                INNER JOIN RH.Tbl_Grupos g ON g.id_Grupo = gp.grupo_id
                                INNER JOIN RH.Tbl_Rel_Usuarios_Grupos ug ON ug.grupo_id = g.id_Grupo
                                WHERE ug.Usuario_id = :usuario2
                                    AND ug.dat_cancelamento_em IS NULL
                                    AND gp.dat_cancelamento_em IS NULL
                                    AND g.dat_cancelamento_em IS NULL
                                    AND p.dat_cancelamento_em IS NULL
                        ) AS permissao
                        ORDER BY cod_permissao";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([':usuario1' => $usuario, ':usuario2' => $usuario]);
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
            $Usuario_id = $params['Usuario_id'];
            $permissao_id = $params['permissao_id'];
            $criado_Usuario_id = $params['criado_Usuario_id'];

            $consultaSql = "INSERT INTO RH.Tbl_Rel_Usuarios_Permissoes (
                        Usuario_id
                    ,   permissao_id
                    ,   criado_Usuario_id
                    ) VALUES (:Usuario_id, :permissao_id, :criado_Usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':Usuario_id' => $Usuario_id,
                ':permissao_id' => $permissao_id,
                ':criado_Usuario_id' => $criado_Usuario_id
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
            $Usuario_id = $params['Usuario_id'];
            $grupo_id = $params['grupo_id'];
            $criado_Usuario_id = $params['criado_Usuario_id'];

            $consultaSql = "INSERT INTO RH.Tbl_Rel_Usuarios_Grupos (
                        Usuario_id
                    ,   grupo_id
                    ,   criado_Usuario_id
                    ) VALUES (:Usuario_id, :grupo_id, :criado_Usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':Usuario_id' => $Usuario_id,
                ':grupo_id' => $grupo_id,
                ':criado_Usuario_id' => $criado_Usuario_id
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
            $cancelamento_Usuario_id = $params['cancelamento_Usuario_id'];

            $consultaSql = "UPDATE RH.Tbl_Rel_Usuarios_Permissoes
                            SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                                data_cancelamento_em = GETDATE()
                            WHERE id_rel_usuario_permissao = :id_rel_usuario_permissao
                            AND data_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':id_rel_usuario_permissao' => $id_rel_usuario_permissao,
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id
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
            $cancelamento_Usuario_id = $params['cancelamento_Usuario_id'];

            $consultaSql = "UPDATE RH.Tbl_Rel_Usuarios_Grupos
                            SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                                data_cancelamento_em = GETDATE()
                            WHERE id_rel_usuario_grupo = :id_rel_usuario_grupo
                            AND data_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':id_rel_usuario_grupo' => $id_rel_usuario_grupo,
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id
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

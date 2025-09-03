<?php

namespace App\Models\RH;

use App\Services\Operacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class usuarioModel extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    public function ObterDadosUsuarios($params)
    {

        $parametrizacao = Operacao::Parametrizar($params);
        // Verifica se houve erro na parametrização
        if ($parametrizacao['statusParams'] !== 200) {
            return [
                'pdo_status' => $parametrizacao['statusParams'],
                'message' => $parametrizacao['message'],
                'data' => []
            ];
        }

        $whereParams = $parametrizacao['whereParams'];
        $optsParams  = $parametrizacao['optsParams'];
        $execParams  = $parametrizacao['execParams'];

        //montar a consulta SQL
        $consultaSql = "SELECT
                        id_Usuario,
                        nome_Completo,
                        email
                    FROM RH.Tbl_Usuarios
                    WHERE dat_cancelamento_em IS NULL"
            . implode(' ', $whereParams)
            . ($optsParams['order_by']   ?? '')
            . ($optsParams['limit']      ?? '')
            . ($optsParams['offset']     ?? '');

        $comando = $this->conexao->prepare($consultaSql);

        try {
            $comando->execute($execParams);
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            if( empty($data) ){
                return [
                    'status' => false,
                    'message' => 'Nenhum usuário encontrado com os critérios fornecidos.',
                    'data' => []
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Erro ao executar consulta: ' . $e->getMessage(),
                'data' => null
            ];
        }

        return [
            'status' => true,
            'message' => 'Dados do usuário recuperados.',
            'data' => $data
        ];
    }

    public function ObterPermissoesUsuario($params)
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
    public function CriarUsuario($params)
    {
        try {
            $nome = $params['nome_Completo'];
            $email = $params['email'];
            $senha = $params['senha'];
            $criadoId = $params['criado_Usuario_id'] ?? 1;

            $consultaSql = "INSERT INTO RH.Tbl_Usuarios (nome_Completo, email, senha, criado_Usuario_id, dat_criado_em) VALUES (:nome, :email, :senha, :criadoId, GETDATE())";
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senha,
                ':criadoId' => $criadoId
            ]);

            $rows = $comando->rowCount();

            // Tentar obter id inserido quando possível
            $lastId = null;
            try {
                $lastId = $this->conexao->lastInsertId();
            } catch (\Exception $e) {
                $lastId = null;
            }

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Usuário criado.' : 'Nenhuma linha inserida.',
                'data' => ['affected' => $rows, 'id' => $lastId]
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

<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class permissao extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    public function ListaPermissoes()
    {
        try {
            $consultaSql = "SELECT id_permissao, cod_permissao, descricao_permissao
                            FROM RH.Tbl_Permissoes
                            WHERE cod_permissao IS NOT NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute();
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'mensagem' => 'Lista de permissoes recuperada.',
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

    public function ObterPermissaoPorId($id_permissao)
    {
        try {
        $consultaSql = "SELECT id_permissao, cod_permissao, descricao_permissao,
                    criado_Usuario_id, dat_criado_em, cancelamento_Usuario_id, dat_cancelamento_em
                FROM RH.Tbl_Permissoes
                WHERE id_permissao = :id_permissao";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([':id_permissao' => $id_permissao]);
            $data = $comando->fetch(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'mensagem' => 'Permissao recuperada.',
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

    public function CriarPermissao($params)
    {
        try {
            $cod_permissao = $params['cod_permissao'];
            $descricao_permissao = $params['descricao_permissao'] ?? null;
            $criado_Usuario_id = $params['criado_Usuario_id'];

            $consultaSql = "INSERT INTO RH.Tbl_Permissoes (
                            cod_permissao, descricao_permissao, criado_Usuario_id
                        ) VALUES (:cod_permissao, :descricao_permissao, :criado_Usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cod_permissao' => $cod_permissao,
                ':descricao_permissao' => $descricao_permissao,
                ':criado_Usuario_id' => $criado_Usuario_id
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'mensagem' => $rows > 0 ? 'Permissao criada.' : 'Nenhuma linha inserida.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mensagem' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function AtualizarPermissao($params)
    {
        try {
            $id_permissao = $params['id_permissao'];
            $cod_permissao = $params['cod_permissao'];
            $descricao_permissao = $params['descricao_permissao'] ?? null;
            $usuario_atualizado_por = $params['usuario_atualizado_por'];

            $consultaSql = "UPDATE RH.Tbl_Permissoes
                            SET cod_permissao = :cod_permissao,
                                descricao_permissao = :descricao_permissao,
                                atualizado_Usuario_id = :usuario_atualizado_por,
                                dat_atualizado_em = GETDATE()
                            WHERE id_permissao = :id_permissao
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cod_permissao' => $cod_permissao,
                ':descricao_permissao' => $descricao_permissao,
                ':usuario_atualizado_por' => $usuario_atualizado_por,
                ':id_permissao' => $id_permissao
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'mensagem' => $rows > 0 ? 'Permissao atualizada.' : 'Nenhuma linha atualizada.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mensagem' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function RemoverPermissao($params)
    {
        try {
            $id_permissao = $params['id_permissao'];
            $cancelamento_Usuario_id = $params['cancelamento_Usuario_id'];

            $consultaSql = "UPDATE RH.Tbl_Permissoes
                            SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                                dat_cancelamento_em = GETDATE()
                            WHERE id_permissao = :id_permissao
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id,
                ':id_permissao' => $id_permissao
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'mensagem' => $rows > 0 ? 'Permissao removida (cancelada).' : 'Nenhuma linha atualizada.',
                'data' => ['affected' => $rows]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mensagem' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function __destruct()
    {
        $this->conexao = null;
    }

}

<?php

namespace App\Models;

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
            $consultaSql = "SELECT id_permissao, txt_cod_permissao, txt_descricao_permissao, categoria_id
                            FROM RH.Tbl_Permissoes
                            WHERE txt_cod_permissao IS NOT NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute();
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'message' => 'Lista de permissoes recuperada.',
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

    public function ObterPermissaoPorId($id_permissao)
    {
        try {
            $consultaSql = "SELECT id_permissao, txt_cod_permissao, txt_descricao_permissao, categoria_id,
                                    matricula_criado_por, dat_criado_em, matricula_cancelamento_em, dat_cancelamento_em
                            FROM RH.Tbl_Permissoes
                            WHERE id_permissao = :id_permissao";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([':id_permissao' => $id_permissao]);
            $data = $comando->fetch(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'message' => 'Permissao recuperada.',
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

    public function CriarPermissao($params)
    {
        try {
            $txt_cod_permissao = $params['txt_cod_permissao'];
            $txt_descricao_permissao = $params['txt_descricao_permissao'] ?? null;
            $categoria_id = $params['categoria_id'] ?? null;
            $matricula_criado_por = $params['matricula_criado_por'];

            $consultaSql = "INSERT INTO RH.Tbl_Permissoes (
                            txt_cod_permissao, txt_descricao_permissao, categoria_id, matricula_criado_por
                        ) VALUES (:txt_cod_permissao, :txt_descricao_permissao, :categoria_id, :matricula_criado_por)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':txt_cod_permissao' => $txt_cod_permissao,
                ':txt_descricao_permissao' => $txt_descricao_permissao,
                ':categoria_id' => $categoria_id,
                ':matricula_criado_por' => $matricula_criado_por
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Permissao criada.' : 'Nenhuma linha inserida.',
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

    public function AtualizarPermissao($params)
    {
        try {
            $id_permissao = $params['id_permissao'];
            $txt_cod_permissao = $params['txt_cod_permissao'];
            $txt_descricao_permissao = $params['txt_descricao_permissao'] ?? null;
            $categoria_id = $params['categoria_id'] ?? null;
            $matricula_atualizado_por = $params['matricula_atualizado_por'];

            $consultaSql = "UPDATE RH.Tbl_Permissoes
                            SET txt_cod_permissao = :txt_cod_permissao,
                                txt_descricao_permissao = :txt_descricao_permissao,
                                categoria_id = :categoria_id,
                                matricula_atualizado_em = :matricula_atualizado_por,
                                dat_atualizado_em = GETDATE()
                            WHERE id_permissao = :id_permissao
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':txt_cod_permissao' => $txt_cod_permissao,
                ':txt_descricao_permissao' => $txt_descricao_permissao,
                ':categoria_id' => $categoria_id,
                ':matricula_atualizado_por' => $matricula_atualizado_por,
                ':id_permissao' => $id_permissao
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Permissao atualizada.' : 'Nenhuma linha atualizada.',
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

    public function RemoverPermissao($params)
    {
        try {
            $id_permissao = $params['id_permissao'];
            $matricula_cancelamento_em = $params['matricula_cancelamento_em'];

            $consultaSql = "UPDATE RH.Tbl_Permissoes
                            SET matricula_cancelamento_em = :matricula_cancelamento_em,
                                dat_cancelamento_em = GETDATE()
                            WHERE id_permissao = :id_permissao
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':matricula_cancelamento_em' => $matricula_cancelamento_em,
                ':id_permissao' => $id_permissao
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'message' => $rows > 0 ? 'Permissao removida (cancelada).' : 'Nenhuma linha atualizada.',
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

<?php

namespace App\Models\RH;

use App\Services\Operacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class categoriaModal extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    /**
     * Obter dados das categorias para select
     */
    public function ObterCategorias($params = [])
    {
        try {
            $consultaSql = "SELECT
                id_categoria,
                nome_Categoria,
                descricao_Categoria
            FROM RH.Tbl_Categorias
            WHERE dat_cancelamento_em IS NULL
            ORDER BY nome_Categoria";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute();
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);
            // verificar se encontrou dados
            if (!$data) {
                return [
                    'status' => 204,// Sucesso porem sem dados
                    'mensagem' => 'Nenhuma categoria encontrada.',
                    'data' => []
                ];
            }

            return [
                'status' => 200,
                'mensagem' => 'Categorias recuperadas com sucesso.',
                'data' => $data
            ];
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e,$params);
        }
    }


    public function CriarCategoria($params)
    {
        $nome_Categoria = $params['nome_Categoria'];
        $descricao_Categoria = $params['descricao_Categoria'] ?? null;
        $criado_Usuario_id = $params['criado_Usuario_id'];

        $consultaSql = "INSERT INTO RH.Tbl_Categorias (nome_Categoria, descricao_Categoria, criado_Usuario_id) VALUES (:nome_Categoria, :descricao_Categoria, :criado_Usuario_id)";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':nome_Categoria' => $nome_Categoria,
            ':descricao_Categoria' => $descricao_Categoria,
            ':criado_Usuario_id' => $criado_Usuario_id
        ]);

        $comando->closeCursor();
    }

    public function AtualizarCategoria($params)
    {
        $id_categoria = $params['id_categoria'];
        $nome_Categoria = $params['nome_Categoria'];
        $descricao_Categoria = $params['descricao_Categoria'] ?? null;
        $usuario_atualizado_por = $params['usuario_atualizado_por'];

        $consultaSql = "UPDATE RH.Tbl_Categorias
                        SET nome_Categoria = :nome_Categoria,
                            descricao_Categoria = :descricao_Categoria,
                            atualizado_Usuario_id = :usuario_atualizado_por,
                            dat_atualizado_em = GETDATE()
                        WHERE id_categoria = :id_categoria
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':nome_Categoria' => $nome_Categoria,
            ':descricao_Categoria' => $descricao_Categoria,
            ':usuario_atualizado_por' => $usuario_atualizado_por,
            ':id_categoria' => $id_categoria
        ]);

        $comando->closeCursor();
    }

    public function RemoverCategoria($params)
    {
        $id_categoria = $params['id_categoria'];
        $cancelamento_Usuario_id = $params['cancelamento_Usuario_id'];

        $consultaSql = "UPDATE RH.Tbl_Categorias
                        SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                            dat_cancelamento_em = GETDATE()
                        WHERE id_categoria = :id_categoria
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':cancelamento_Usuario_id' => $cancelamento_Usuario_id,
            ':id_categoria' => $id_categoria
        ]);

        $comando->closeCursor();
    }

    public function __destruct()
    {
        $this->conexao = null;
    }

}

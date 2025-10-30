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
                nome_categoria,
                descricao_categoria
            FROM rh.tb_categorias
            WHERE dat_cancelamento_em IS NULL
            ORDER BY nome_categoria";

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
        $nomeCategoria = $params['nome_categoria'] ?? $params['nome_Categoria'] ?? null;
        $descricaoCategoria = $params['descricao_categoria'] ?? $params['descricao_Categoria'] ?? null;
        $criadoUsuarioId = $params['criado_usuario_id'] ?? $params['criado_Usuario_id'] ?? null;

        $consultaSql = "INSERT INTO rh.tb_categorias (nome_categoria, descricao_categoria, criado_usuario_id) VALUES (:nome_categoria, :descricao_categoria, :criado_usuario_id)";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':nome_categoria' => $nomeCategoria,
            ':descricao_categoria' => $descricaoCategoria,
            ':criado_usuario_id' => $criadoUsuarioId
        ]);

        $comando->closeCursor();
    }

    public function AtualizarCategoria($params)
    {
        $idCategoria = $params['id_categoria'] ?? $params['id_Categoria'];
        $nomeCategoria = $params['nome_categoria'] ?? $params['nome_Categoria'];
        $descricaoCategoria = $params['descricao_categoria'] ?? $params['descricao_Categoria'] ?? null;
        $usuarioAtualizadoPor = $params['usuario_atualizado_por'] ?? $params['atualizado_usuario_id'] ?? null;

        $consultaSql = "UPDATE rh.tb_categorias
                        SET nome_categoria = :nome_categoria,
                            descricao_categoria = :descricao_categoria,
                            atualizado_usuario_id = :usuario_atualizado_por,
                            dat_atualizado_em = :dat_atualizado_em
                        WHERE id_categoria = :id_categoria
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':nome_categoria' => $nomeCategoria,
            ':descricao_categoria' => $descricaoCategoria,
            ':usuario_atualizado_por' => $usuarioAtualizadoPor,
            ':dat_atualizado_em' => date('Y-m-d H:i:s'),
            ':id_categoria' => $idCategoria
        ]);

        $comando->closeCursor();
    }

    public function RemoverCategoria($params)
    {
        $idCategoria = $params['id_categoria'] ?? $params['id_Categoria'];
        $cancelamentoUsuarioId = $params['cancelamento_usuario_id'] ?? $params['cancelamento_Usuario_id'];

        $consultaSql = "UPDATE rh.tb_categorias
                        SET cancelamento_usuario_id = :cancelamento_usuario_id,
                            dat_cancelamento_em = :dat_cancelamento_em
                        WHERE id_categoria = :id_categoria
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':cancelamento_usuario_id' => $cancelamentoUsuarioId,
            ':dat_cancelamento_em' => date('Y-m-d H:i:s'),
            ':id_categoria' => $idCategoria
        ]);

        $comando->closeCursor();
    }

    public function __destruct()
    {
        $this->conexao = null;
    }

}

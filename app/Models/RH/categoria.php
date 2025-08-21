<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class categoria extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    public function ListaCategorias()
    {
        $consultaSql = "SELECT id_categoria, txt_nome_categoria, txt_descricao_categoria FROM RH.Tbl_Categorias WHERE dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute();
        return $comando->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function ObterCategoriaPorId($id_categoria)
    {
        $consultaSql = "SELECT id_categoria, txt_nome_categoria, txt_descricao_categoria, matricula_criado_por, dat_criado_em, matricula_cancelamento_em, dat_cancelamento_em FROM RH.Tbl_Categorias WHERE id_categoria = :id_categoria";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([':id_categoria' => $id_categoria]);
        return $comando->fetch(\PDO::FETCH_ASSOC);
    }

    public function CriarCategoria($params)
    {
        $txt_nome_categoria = $params['txt_nome_categoria'];
        $txt_descricao_categoria = $params['txt_descricao_categoria'] ?? null;
        $matricula_criado_por = $params['matricula_criado_por'];

        $consultaSql = "INSERT INTO RH.Tbl_Categorias (txt_nome_categoria, txt_descricao_categoria, matricula_criado_por) VALUES (:txt_nome_categoria, :txt_descricao_categoria, :matricula_criado_por)";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':txt_nome_categoria' => $txt_nome_categoria,
            ':txt_descricao_categoria' => $txt_descricao_categoria,
            ':matricula_criado_por' => $matricula_criado_por
        ]);

        $comando->closeCursor();
    }

    public function AtualizarCategoria($params)
    {
        $id_categoria = $params['id_categoria'];
        $txt_nome_categoria = $params['txt_nome_categoria'];
        $txt_descricao_categoria = $params['txt_descricao_categoria'] ?? null;
        $matricula_atualizado_por = $params['matricula_atualizado_por'];

        $consultaSql = "UPDATE RH.Tbl_Categorias
                        SET txt_nome_categoria = :txt_nome_categoria,
                            txt_descricao_categoria = :txt_descricao_categoria,
                            matricula_atualizado_em = :matricula_atualizado_por,
                            dat_atualizado_em = GETDATE()
                        WHERE id_categoria = :id_categoria
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':txt_nome_categoria' => $txt_nome_categoria,
            ':txt_descricao_categoria' => $txt_descricao_categoria,
            ':matricula_atualizado_por' => $matricula_atualizado_por,
            ':id_categoria' => $id_categoria
        ]);

        $comando->closeCursor();
    }

    public function RemoverCategoria($params)
    {
        $id_categoria = $params['id_categoria'];
        $matricula_cancelamento_em = $params['matricula_cancelamento_em'];

        $consultaSql = "UPDATE RH.Tbl_Categorias
                        SET matricula_cancelamento_em = :matricula_cancelamento_em,
                            dat_cancelamento_em = GETDATE()
                        WHERE id_categoria = :id_categoria
                          AND dat_cancelamento_em IS NULL";

        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':matricula_cancelamento_em' => $matricula_cancelamento_em,
            ':id_categoria' => $id_categoria
        ]);

        $comando->closeCursor();
    }

    public function __destruct()
    {
        $this->conexao = null;
    }

}

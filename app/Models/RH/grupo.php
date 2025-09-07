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
            $consultaSql = "SELECT id_Grupo, nome_Grupo, descricao_Grupo, categoria_id
                            FROM RH.Tbl_Grupos
                            WHERE nome_Grupo IS NOT NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute();
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'mensagem' => 'Lista de grupos recuperada.',
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

    public function ObterGrupoPorId($id_Grupo)
    {
        try {
            $consultaSql = "SELECT id_Grupo, nome_Grupo, descricao_Grupo, categoria_id,
                                    criado_Usuario_id, dat_criado_em, cancelamento_Usuario_id, dat_cancelamento_em
                            FROM RH.Tbl_Grupos
                            WHERE id_Grupo = :id_Grupo";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([':id_Grupo' => $id_Grupo]);
            $data = $comando->fetch(\PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'mensagem' => 'Grupo recuperado.',
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
        try {
            $nome_Grupo = $params['nome_Grupo'];
            $descricao_Grupo = $params['descricao_Grupo'] ?? null;
            $categoria_id = $params['categoria_id'] ?? null;
            $criado_Usuario_id = $params['criado_Usuario_id'];

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
                'mensagem' => $rows > 0 ? 'Grupo criado.' : 'Nenhuma linha inserida.',
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

    public function AtualizarGrupo($params)
    {
        try {
            $id_Grupo = $params['id_Grupo'];
            $nome_Grupo = $params['nome_Grupo'];
            $descricao_Grupo = $params['descricao_Grupo'] ?? null;
            $categoria_id = $params['categoria_id'] ?? null;
            $usuario_atualizado_por = $params['usuario_atualizado_por'];

            $consultaSql = "UPDATE RH.Tbl_Grupos
                            SET nome_Grupo = :nome_Grupo,
                                descricao_Grupo = :descricao_Grupo,
                                categoria_id = :categoria_id,
                                atualizado_Usuario_id = :usuario_atualizado_por,
                                dat_atualizado_em = GETDATE()
                            WHERE id_Grupo = :id_Grupo
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':nome_Grupo' => $nome_Grupo,
                ':descricao_Grupo' => $descricao_Grupo,
                ':categoria_id' => $categoria_id,
                ':usuario_atualizado_por' => $usuario_atualizado_por,
                ':id_Grupo' => $id_Grupo
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'mensagem' => $rows > 0 ? 'Grupo atualizado.' : 'Nenhuma linha atualizada.',
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

    public function RemoverGrupo($params)
    {
        try {
            $id_Grupo = $params['id_Grupo'];
            $cancelamento_Usuario_id = $params['cancelamento_Usuario_id'];

            $consultaSql = "UPDATE RH.Tbl_Grupos
                            SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                                dat_cancelamento_em = GETDATE()
                            WHERE id_Grupo = :id_Grupo
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id,
                ':id_Grupo' => $id_Grupo
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            return [
                'status' => $rows > 0,
                'mensagem' => $rows > 0 ? 'Grupo removido (cancelado).' : 'Nenhuma linha atualizada.',
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

    // atribui permissão a um grupo (evita duplicata ativa)
    public function AtribuirPermissaoGrupo($params)
    {
        $grupo_id = $params['grupo_id'];
        $permissao_id = $params['permissao_id'];
        $criado_Usuario_id = $params['criado_Usuario_id'];

        // verificar se já existe vínculo ativo
        $checkSql = "SELECT 1 FROM RH.Tbl_Rel_Grupos_Permissoes WHERE grupo_id = :grupo_id AND permissao_id = :permissao_id AND dat_cancelamento_em IS NULL";
        $check = $this->conexao->prepare($checkSql);
        $check->execute([':grupo_id' => $grupo_id, ':permissao_id' => $permissao_id]);
        $exists = $check->fetchColumn();
        $check->closeCursor();

        if ($exists) {
            return; // já existe vínculo ativo
        }

        $consultaSql = "INSERT INTO RH.Tbl_Rel_Grupos_Permissoes (grupo_id, permissao_id, criado_Usuario_id) VALUES (:grupo_id, :permissao_id, :criado_Usuario_id)";
        $comando = $this->conexao->prepare($consultaSql);
        $comando->execute([
            ':grupo_id' => $grupo_id,
            ':permissao_id' => $permissao_id,
            ':criado_Usuario_id' => $criado_Usuario_id
        ]);
        $comando->closeCursor();
    }

    // remove (marca cancelamento) vínculo permissão->grupo
    public function RemoverPermissaoGrupo($params)
    {
        $id_rel_grupo_permissao = $params['id_rel_grupo_permissao'];
        $cancelamento_Usuario_id = $params['cancelamento_Usuario_id'];

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
        $comando->closeCursor();
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

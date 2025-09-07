<?php

namespace App\Models\RH;

use App\Services\Operacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class permissaoModel extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }


    /**
     * Retorna todas as permissões com flag indicando se o usuário possui cada permissão (vínculo ativo).
     */
    public function ObterDadosPermissoes($params)
    {


        $parametrizacao = Operacao::Parametrizar($params);
        // Verifica se houve erro na parametrização
        if ($parametrizacao['status'] === false) {
            return [
                'status' => $parametrizacao['status'],
                'mensagem' => $parametrizacao['mensagem'],
                'data' => []
            ];
        }

        $whereParams = $parametrizacao['whereParams'];
        $optsParams = $parametrizacao['optsParams'];
        $execParams = $parametrizacao['execParams'];

        // filtros de execução específicos
        $On_id_usuario = " ";
        if (isset($params['usuario_id'])) {
            $On_id_usuario = "AND rup.usuario_id = :usuario ";
            $execParams[':usuario'] = $params['usuario_id'];
        }

        $left_Usuario = " ";
        if (isset($params['id_Usuario'])) {
            $left_Usuario = " LEFT JOIN RH.Tbl_Usuarios u
                            ON rup.usuario_id = u.id_Usuario";
        }


        $consultaSql = "SELECT
                            p.id_permissao
                        ,   p.cod_permissao
                        ,   p.descricao_permissao
                        ,   rup.id_rel_usuario_permissao
                        FROM RH.Tbl_Permissoes p
                        LEFT JOIN RH.Tbl_Rel_Usuarios_Permissoes rup
                            ON rup.permissao_id = p.id_permissao
                            $On_id_usuario
                            AND rup.dat_cancelamento_em IS NULL
                        $left_Usuario
                        WHERE p.dat_cancelamento_em IS NULL"
            . implode(' ', $whereParams)
            . ($optsParams['order_by'] ?? "  ")
            . ($optsParams['limit'] ?? "  ")
            . ($optsParams['offset'] ?? "  ");

        try {
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute($execParams);
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($data)) {
                return [
                    'status' => false,
                    'mensagem' => 'Nenhuma permissão encontrada com os critérios fornecidos.',
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'mensagem' => $e->getMessage(),
                'data' => null
            ];
        }

        return [
            'status' => true,
            'mensagem' => 'Permissões carregadas.',
            'data' => $data
        ];
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

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
    // para login usar o maiximo de segurança possivel
    public function ObterLoginPermissoes($params)
    {
        $execParams[':id_Usuario_Permissao'] = $params['id_Usuario'];
        $execParams[':id_Usuario_Grupo'] = $params['id_Usuario'];

        // 1 select no Union
        $SqlUnionPermissao = "SELECT
                               PRM.cod_permissao
                            FROM RH.Tbl_Permissoes PRM
                            LEFT JOIN RH.Tbl_Rel_Usuarios_Permissoes REL_USUARIO_P
                                ON REL_USUARIO_P.permissao_id = PRM.id_permissao
                            WHERE   REL_USUARIO_P.dat_cancelamento_em IS NULL
                            AND     PRM.dat_cancelamento_em IS NULL
                            AND     REL_USUARIO_P.usuario_id = :id_Usuario_Permissao
                            UNION
                            SELECT
                               PRM.cod_permissao
                            FROM RH.Tbl_Permissoes PRM
                            LEFT JOIN RH.Tbl_Rel_Grupos_Permissoes REL_GRUPO_P
                                ON REL_GRUPO_P.permissao_id = PRM.id_permissao
                            LEFT JOIN RH.Tbl_Rel_Usuarios_Grupos REL_USUARIO_G
                                ON REL_USUARIO_G.grupo_id = REL_GRUPO_P.grupo_id
                            WHERE   REL_USUARIO_G.dat_cancelamento_em IS NULL
                            AND     PRM.dat_cancelamento_em IS NULL
                            AND     REL_USUARIO_G.usuario_id = :id_Usuario_Grupo
                            ";
        try {
            $comando = $this->conexao->prepare($SqlUnionPermissao);
            $comando->execute($execParams);
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($data)) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma permissão encontrada para o usuário.',
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e,$params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Permissões do usuário carregadas.',
            'data' => $data
        ];
    }

    /**
     * Retorna todas as permissões com flag indicando se o usuário possui cada permissão (vínculo ativo).
     */
    public function ObterRHPermissoes($params)
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


        if($params['fn'] == 'btn-permissoes'){
            $execParams[':usuario_id'] = $params['usuario_id'];
            $execParams[':usuario_id_Sub'] = $params['usuario_id'];
            $consultaSql = "SELECT
                                p.id_permissao
                            ,   p.cod_permissao
                            ,   p.descricao_permissao
                            ,   rup.id_rel_usuario_permissao
                            ,   ativo_Grupo = (SELECT TOP 1 1
                                            FROM RH.Tbl_Rel_Grupos_Permissoes rgp
                                            INNER JOIN RH.Tbl_Rel_Usuarios_Grupos rug
                                                ON rug.grupo_id = rgp.grupo_id
                                                AND rug.dat_cancelamento_em IS NULL
                                            WHERE rgp.permissao_id = p.id_permissao
                                            AND rug.usuario_id = :usuario_id_Sub
                                            AND rgp.dat_cancelamento_em IS NULL)
                            FROM RH.Tbl_Permissoes p
                            LEFT JOIN RH.Tbl_Rel_Usuarios_Permissoes rup
                                ON rup.permissao_id = p.id_permissao
                                AND rup.usuario_id = :usuario_id
                                AND rup.dat_cancelamento_em IS NULL
                            WHERE p.dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? "  ")
                . ($optsParams['limit'] ?? "  ")
                . ($optsParams['offset'] ?? "  ");

        }else if($params['fn'] == 'btn-permissoes-grupo'){
            $execParams[':grupo_id'] = $params['grupo_id'];
            $consultaSql = "SELECT
                                p.id_permissao
                            ,   p.cod_permissao
                            ,   p.descricao_permissao
                            ,   rgp.id_rel_grupo_permissao
                            FROM RH.Tbl_Permissoes p
                            LEFT JOIN RH.Tbl_Rel_Grupos_Permissoes rgp
                                ON rgp.permissao_id = p.id_permissao
                                AND rgp.grupo_id = :grupo_id
                                AND rgp.dat_cancelamento_em IS NULL
                            WHERE p.dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? "  ")
                . ($optsParams['limit'] ?? "  ")
                . ($optsParams['offset'] ?? "  ");

        }else if($params['fn'] == 'btn-expand-grupo'){
            $execParams[':grupo_id'] = $params['grupo_id'];
            $consultaSql = "SELECT
                                p.id_permissao
                            ,   p.cod_permissao
                            ,   p.descricao_permissao
                            FROM RH.Tbl_Permissoes p
                            LEFT JOIN RH.Tbl_Rel_Grupos_Permissoes rgp
                                ON rgp.permissao_id = p.id_permissao
                                AND rgp.dat_cancelamento_em IS NULL
                            WHERE p.dat_cancelamento_em IS NULL
                            AND rgp.grupo_id = :grupo_id"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? "  ")
                . ($optsParams['limit'] ?? "  ")
                . ($optsParams['offset'] ?? "  ");
        }

        try {
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute($execParams);
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($data)) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma permissão encontrada com os critérios fornecidos.',
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
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

            if( $rows == 0){
                return [
                    'status' => 400,
                    'mensagem' => 'Nenhuma permissão criada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e,$params);
        }
        return [
            'status' => 201,
            'mensagem' => 'Permissão criada com sucesso.',
            'data' => ['affected' => $rows]
        ];
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

            if( $rows == 0){
                return [
                    'status' => 400,
                    'mensagem' => 'Nenhuma permissão atualizada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e,$params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Permissão atualizada com sucesso.',
            'data' => ['affected' => $rows]
        ];
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

            if( $rows == 0){
                return [
                    'status' => 400,
                    'mensagem' => 'Nenhuma permissão removida.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e,$params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Permissão removida com sucesso.',
            'data' => ['affected' => $rows]
        ];
    }

    public function __destruct()
    {
        $this->conexao = null;
    }
}

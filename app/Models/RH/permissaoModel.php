<?php

namespace App\Models\RH;

use App\Services\Operacao;
use App\Services\RH\usuarioServices;
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
    public function ObterPermissoes($params)
    {

        $fn = $params['fn'] ?? null;

        $parametrizacao = Operacao::Parametrizar($params);
        // Verifica se houve erro na parametrização
        if ($parametrizacao['status'] === 422) {
            return [
                'status' => $parametrizacao['status'],
                'mensagem' => $parametrizacao['mensagem'],
                'data' => []
            ];
        }




        if ($fn == 'fn-do-usuario') {
            // este traz as permissões ativas de um usuário para o middleware
            // seja diretamente ou via grupo

            // id_usuario pra se relacionar com permissões diretas
            $execParams[':id_usuario_permissao'] = $params['id_usuario'] ?? $params['id_Usuario'];
            // id_usuario pra se relacionar com permissões via grupo
            $execParams[':id_usuario_grupo'] = $params['id_usuario'] ?? $params['id_Usuario'];

            // 1 select no Union
            $consultaSql = "SELECT
                               prm.cod_permissao
                            FROM rh.tb_permissoes prm
                            LEFT JOIN rh.tr_usuarios_permissoes rel_usuario_p
                                ON rel_usuario_p.permissao_id = prm.id_permissao
                            WHERE   rel_usuario_p.dat_cancelamento_em IS NULL
                            AND     prm.dat_cancelamento_em IS NULL
                            AND     rel_usuario_p.usuario_id = :id_usuario_permissao
                            UNION
                            SELECT
                               prm.cod_permissao
                            FROM rh.tb_permissoes prm
                            LEFT JOIN rh.tr_grupos_permissoes rel_grupo_p
                                ON rel_grupo_p.permissao_id = prm.id_permissao
                            LEFT JOIN rh.tr_usuarios_grupos rel_usuario_g
                                ON rel_usuario_g.grupo_id = rel_grupo_p.grupo_id
                            WHERE   rel_usuario_g.dat_cancelamento_em IS NULL
                            AND     prm.dat_cancelamento_em IS NULL
                            AND     rel_usuario_g.usuario_id = :id_usuario_grupo
                            ";

        } else if ($fn == 'fn-usuario-status') {
            // traz todas as permissões com flag indicando se o usuário possui cada permissão

            // Adicionar  o usuario_id
            $execParams[':usuario_id'] = $params['usuario_id'];
            $execParams[':usuario_id_Sub'] = $params['usuario_id'];
            $consultaSql = "SELECT
                                p.id_permissao,
                                p.cod_permissao,
                                p.descricao_permissao,
                                rup.id_rel_usuario_permissao,
                                CASE
                                    WHEN EXISTS (
                                        SELECT 1
                                        FROM rh.tr_grupos_permissoes rgp
                                        INNER JOIN rh.tr_usuarios_grupos rug
                                            ON rug.grupo_id = rgp.grupo_id
                                            AND rug.dat_cancelamento_em IS NULL
                                        WHERE rgp.permissao_id = p.id_permissao
                                          AND rug.usuario_id = :usuario_id_Sub
                                          AND rgp.dat_cancelamento_em IS NULL
                                    ) THEN 1 ELSE 0
                                END AS ativo_grupo
                            FROM rh.tb_permissoes p
                            LEFT JOIN rh.tr_usuarios_permissoes rup
                                ON rup.permissao_id = p.id_permissao
                                AND rup.usuario_id = :usuario_id
                                AND rup.dat_cancelamento_em IS NULL
                            WHERE p.dat_cancelamento_em IS NULL";

        } else if ($fn == 'fn-grupo-status') {
            $execParams[':grupo_id'] = $params['grupo_id'];
            $consultaSql = "SELECT
                                p.id_permissao,
                                p.cod_permissao,
                                p.descricao_permissao,
                                rgp.id_rel_grupo_permissao
                            FROM rh.tb_permissoes p
                            LEFT JOIN rh.tr_grupos_permissoes rgp
                                ON rgp.permissao_id = p.id_permissao
                                AND rgp.grupo_id = :grupo_id
                                AND rgp.dat_cancelamento_em IS NULL
                            WHERE p.dat_cancelamento_em IS NULL";
        } else if ($fn == 'middleware-se-existe') {
            // Verifica se a permissão existe (para o middleware)
            // este não tem a trava de dat_cancelamento_em IS NULL

            // Deixar execParams apenas com o cod_permissao
            $execParams = [];
            // Adicionar apenas o cod_permissao
            $execParams[':cod_permissao'] = $params['cod_permissao'];
            $consultaSql = "SELECT
                                p.id_permissao,
                                p.cod_permissao,
                                p.descricao_permissao
                            FROM rh.tb_permissoes p
                            WHERE p.dat_cancelamento_em IS NULL
                            AND p.cod_permissao = :cod_permissao";
        } else {
            // Consulta padrão para obter todas as permissões

            $execParams = $parametrizacao['execParams'];
            $whereParams = $parametrizacao['whereParams'];
            $optsParams = $parametrizacao['optsParams'];

            $consultaSql = "SELECT
                                p.id_permissao,
                                p.cod_permissao,
                                p.descricao_permissao
                            FROM rh.tb_permissoes p
                            LEFT JOIN rh.tr_grupos_permissoes rgp
                                ON rgp.permissao_id = p.id_permissao
                                AND rgp.dat_cancelamento_em IS NULL
                            WHERE p.dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? " order by p.cod_permissao ")
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
            $codPermissao = $params['cod_permissao'];
            $descricaoPermissao = $params['descricao_permissao'] ?? null;
            $criadoUsuarioId = $params['criado_usuario_id'] ?? $params['criado_Usuario_id'] ?? $this->obterUsuarioAutenticadoId();

            $consultaSql = "INSERT INTO rh.tb_permissoes (
                            cod_permissao, descricao_permissao, criado_usuario_id
                        ) VALUES (:cod_permissao, :descricao_permissao, :criado_usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cod_permissao' => $codPermissao,
                ':descricao_permissao' => $descricaoPermissao,
                ':criado_usuario_id' => $criadoUsuarioId
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 400,
                    'mensagem' => 'Nenhuma permissão criada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Permissão criada com sucesso.',
            'data' => ['affected' => $rows]
        ];
    }

    public function AtualizarPermissao($params)
    {
        try {
            $idPermissao = $params['id_permissao'];
            $codPermissao = $params['cod_permissao'];
            $descricaoPermissao = $params['descricao_permissao'] ?? null;
            $usuarioAtualizadoPor = $params['usuario_atualizado_por'] ?? $params['atualizado_usuario_id'] ?? $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE rh.tb_permissoes
                            SET cod_permissao = :cod_permissao,
                                descricao_permissao = :descricao_permissao,
                                atualizado_usuario_id = :usuario_atualizado_por,
                                dat_atualizado_em = :dat_atualizado_em
                            WHERE id_permissao = :id_permissao
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cod_permissao' => $codPermissao,
                ':descricao_permissao' => $descricaoPermissao,
                ':usuario_atualizado_por' => $usuarioAtualizadoPor,
                ':dat_atualizado_em' => date('Y-m-d H:i:s'),
                ':id_permissao' => $idPermissao
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 400,
                    'mensagem' => 'Nenhuma permissão atualizada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
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
            $idPermissao = $params['id_permissao'];
            $cancelamentoUsuarioId = $params['cancelamento_usuario_id'] ?? $params['cancelamento_Usuario_id'] ?? $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE rh.tb_permissoes
                            SET cancelamento_usuario_id = :cancelamento_usuario_id,
                                dat_cancelamento_em = :dat_cancelamento_em
                            WHERE id_permissao = :id_permissao
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cancelamento_usuario_id' => $cancelamentoUsuarioId,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s'),
                ':id_permissao' => $idPermissao
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 400,
                    'mensagem' => 'Nenhuma permissão removida.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
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

    private function obterUsuarioAutenticadoId(): int
    {
        $usuarioService = app(usuarioServices::class);

        if (property_exists($usuarioService, 'id_usuario') && !empty($usuarioService->id_usuario)) {
            return (int) $usuarioService->id_usuario;
        }

        return (int) ($usuarioService->id_Usuario ?? 0);
    }
}

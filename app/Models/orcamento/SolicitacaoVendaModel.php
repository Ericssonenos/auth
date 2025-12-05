<?php

namespace App\Models\orcamento;

use App\Services\Operacao;
use App\Services\rh\usuarioServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SolicitacaoVendaModel extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    /**
     * Obter dados de solicitações de venda com diferentes modalidades
     */
    public function ObterDadosSolicitacoesVenda($params)
    {
        $fn = $params['fn'] ?? null;

        if ($fn === 'fn-com-itens') {
            // Solicitações com seus itens
            $execParams[':id_solicitacao_venda'] = $params['id_solicitacao_venda'] ?? null;

            $whereClause = '';
            if (!is_null($execParams[':id_solicitacao_venda'])) {
                $whereClause = ' AND sv.id_solicitacao_venda = :id_solicitacao_venda';
            } else {
                unset($execParams[':id_solicitacao_venda']);
            }

            $consultaSql = "SELECT
                        sv.id_solicitacao_venda,
                        sv.cod_solicitacao,
                        sv.cliente_id,
                        sv.descricao,
                        sv.criado_usuario_id,
                        sv.dat_criado_em,
                        u.nome_completo as criado_por_nome,
                        i.id_item,
                        i.produto_id,
                        i.quantidade,
                        i.id_instancia,
                        i.observacao as item_observacao
                    FROM sv.tb_solicitacao_venda sv
                    LEFT JOIN sv.tb_solicitacao_venda_item i
                        ON sv.id_solicitacao_venda = i.id_solicitacao_venda
                        AND i.dat_cancelamento_em IS NULL
                    LEFT JOIN rh.tb_usuarios u
                        ON sv.criado_usuario_id = u.id_usuario
                    WHERE sv.dat_cancelamento_em IS NULL"
                . $whereClause
                . " ORDER BY sv.dat_criado_em DESC, i.id_item ASC";
        }
        else if ($fn === 'fn-resumo-orcamentos') {
            // Resumo com contagem de itens por solicitação
            $consultaSql = "SELECT
                        sv.id_solicitacao_venda,
                        sv.cod_solicitacao,
                        sv.cliente_id,
                        sv.descricao,
                        sv.criado_usuario_id,
                        sv.dat_criado_em,
                        u.nome_completo as criado_por_nome,
                        COUNT(i.id_item) as total_itens,
                        COUNT(CASE WHEN i.id_instancia IS NOT NULL THEN 1 END) as itens_no_workflow
                    FROM sv.tb_solicitacao_venda sv
                    LEFT JOIN sv.tb_solicitacao_venda_item i
                        ON sv.id_solicitacao_venda = i.id_solicitacao_venda
                        AND i.dat_cancelamento_em IS NULL
                    LEFT JOIN rh.tb_usuarios u
                        ON sv.criado_usuario_id = u.id_usuario
                    WHERE sv.dat_cancelamento_em IS NULL
                    GROUP BY sv.id_solicitacao_venda, sv.cod_solicitacao, sv.cliente_id,
                             sv.descricao, sv.criado_usuario_id, sv.dat_criado_em, u.nome_completo
                    ORDER BY sv.dat_criado_em DESC";
            $execParams = [];
        }
        else if ($fn === 'fn-por-cliente') {
            // Solicitações de um cliente específico
            $execParams[':cliente_id'] = $params['cliente_id'];
            $consultaSql = "SELECT
                        sv.*,
                        u.nome_completo as criado_por_nome
                    FROM sv.tb_solicitacao_venda sv
                    LEFT JOIN rh.tb_usuarios u ON sv.criado_usuario_id = u.id_usuario
                    WHERE sv.dat_cancelamento_em IS NULL
                      AND sv.cliente_id = :cliente_id
                    ORDER BY sv.dat_criado_em DESC";
        }
        else {
            // Pesquisa padrão usando Operacao::Parametrizar
            $parametrizacao = Operacao::Parametrizar($params);

            if ($parametrizacao['status'] !== 200) {
                return [
                    'status' => $parametrizacao['status'],
                    'mensagem' => $parametrizacao['mensagem'],
                    'data' => []
                ];
            }

            $whereParams = $parametrizacao['whereParams'];
            $optsParams  = $parametrizacao['optsParams'];
            $execParams  = $parametrizacao['execParams'];

            $consultaSql = "SELECT
                        sv.id_solicitacao_venda,
                        sv.cod_solicitacao,
                        sv.cliente_id,
                        sv.descricao,
                        sv.criado_usuario_id,
                        sv.dat_criado_em,
                        u.nome_completo as criado_por_nome
                    FROM sv.tb_solicitacao_venda sv
                    LEFT JOIN rh.tb_usuarios u ON sv.criado_usuario_id = u.id_usuario
                    WHERE sv.dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? ' ORDER BY sv.dat_criado_em DESC')
                . ($optsParams['limit'] ?? '')
                . ($optsParams['offset'] ?? '');
        }

        try {
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute($execParams);
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($data)) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma solicitação encontrada com os critérios fornecidos.',
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Solicitações recuperadas com sucesso.',
            'data' => $data
        ];
    }

    /**
     * Criar nova solicitação de venda
     */
    public function CriarSolicitacaoVenda($params)
    {
        try {
            $cod_solicitacao = $params['cod_solicitacao'] ?? null;
            $cliente_id = $params['cliente_id'];
            $descricao = $params['descricao'] ?? null;
            $criado_usuario_id = $this->obterUsuarioAutenticadoId();

            $consultaSql = "INSERT INTO sv.tb_solicitacao_venda
                            (cod_solicitacao, cliente_id, descricao, criado_usuario_id)
                            VALUES (:cod_solicitacao, :cliente_id, :descricao, :criado_usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cod_solicitacao' => $cod_solicitacao,
                ':cliente_id' => $cliente_id,
                ':descricao' => $descricao,
                ':criado_usuario_id' => $criado_usuario_id
            ]);

            $rows = $comando->rowCount();

            // Obter ID da solicitação criada
            $id_solicitacao_venda = null;
            try {
                $id_solicitacao_venda = $this->conexao->lastInsertId();
            } catch (\Exception $e) {
                $id_solicitacao_venda = null;
            }

            if ($rows == 0) {
                return [
                    'status' => 404,
                    'mensagem' => 'Solicitação não criada.',
                    'data' => ['afetadas' => $rows, 'id_solicitacao_venda' => null]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Solicitação criada com sucesso.',
            'data' => ['afetadas' => $rows, 'id_solicitacao_venda' => $id_solicitacao_venda]
        ];
    }

    /**
     * Atualizar solicitação de venda
     */
    public function AtualizarSolicitacaoVenda($params)
    {
        try {
            $id_solicitacao_venda = $params['id_solicitacao_venda'];
            $cod_solicitacao = $params['cod_solicitacao'] ?? null;
            $cliente_id = $params['cliente_id'] ?? null;
            $descricao = $params['descricao'] ?? null;
            $atualizado_usuario_id = $this->obterUsuarioAutenticadoId();

            // Construir SQL dinamicamente baseado nos campos fornecidos
            $updateFields = [];
            $execParams = [':id_solicitacao_venda' => $id_solicitacao_venda];

            if (!is_null($cod_solicitacao)) {
                $updateFields[] = 'cod_solicitacao = :cod_solicitacao';
                $execParams[':cod_solicitacao'] = $cod_solicitacao;
            }

            if (!is_null($cliente_id)) {
                $updateFields[] = 'cliente_id = :cliente_id';
                $execParams[':cliente_id'] = $cliente_id;
            }

            if (!is_null($descricao)) {
                $updateFields[] = 'descricao = :descricao';
                $execParams[':descricao'] = $descricao;
            }

            if (empty($updateFields)) {
                return [
                    'status' => 400,
                    'mensagem' => 'Nenhum campo fornecido para atualização.',
                    'data' => ['afetadas' => 0]
                ];
            }

            // Sempre atualizar campos de auditoria
            $updateFields[] = 'atualizado_usuario_id = :atualizado_usuario_id';
            $updateFields[] = 'dat_atualizado_em = :dat_atualizado_em';
            $execParams[':atualizado_usuario_id'] = $atualizado_usuario_id;
            $execParams[':dat_atualizado_em'] = date('Y-m-d H:i:s');

            $consultaSql = "UPDATE sv.tb_solicitacao_venda
                            SET " . implode(', ', $updateFields) . "
                            WHERE id_solicitacao_venda = :id_solicitacao_venda
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute($execParams);

            $rows = $comando->rowCount();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma alteração realizada.',
                    'data' => ['afetadas' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Solicitação atualizada com sucesso.',
            'data' => ['afetadas' => $rows]
        ];
    }

    /**
     * Cancelar solicitação de venda (soft-delete)
     */
    public function CancelarSolicitacaoVenda($params)
    {
        try {
            $id_solicitacao_venda = $params['id_solicitacao_venda'];
            $cancelamento_usuario_id = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE sv.tb_solicitacao_venda
                            SET cancelamento_usuario_id = :cancelamento_usuario_id,
                                dat_cancelamento_em = :dat_cancelamento_em
                            WHERE id_solicitacao_venda = :id_solicitacao_venda
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':id_solicitacao_venda' => $id_solicitacao_venda,
                ':cancelamento_usuario_id' => $cancelamento_usuario_id,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s')
            ]);

            $rows = $comando->rowCount();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma solicitação foi cancelada.',
                    'data' => ['afetadas' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Solicitação cancelada com sucesso.',
            'data' => ['afetadas' => $rows]
        ];
    }

    private function obterUsuarioAutenticadoId(): int
    {
        $usuarioService = app(usuarioServices::class);

        if (property_exists($usuarioService, 'id_usuario') && !empty($usuarioService->id_usuario)) {
            return (int) $usuarioService->id_usuario;
        }

        return (int) ($usuarioService->id_usuario ?? 0);
    }

    public function __destruct()
    {
        $this->conexao = null;
    }
}

<?php

namespace App\Models\orcamento;

use App\Services\Operacao;
use App\Services\rh\usuarioServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SolicitacaoVendaItemModel extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    /**
     * Obter dados de itens de solicitação com diferentes modalidades
     */
    public function ObterDadosItensVenda($params)
    {
        $fn = $params['fn'] ?? null;

        if ($fn === 'fn-com-status-workflow') {
            // Itens com status atual do workflow
            $execParams[':id_solicitacao_venda'] = $params['id_solicitacao_venda'] ?? null;

            $whereClause = '';
            if (!is_null($execParams[':id_solicitacao_venda'])) {
                $whereClause = ' AND i.id_solicitacao_venda = :id_solicitacao_venda';
            } else {
                unset($execParams[':id_solicitacao_venda']);
            }

            $consultaSql = "SELECT
                        i.id_item,
                        i.id_solicitacao_venda,
                        i.produto_id,
                        i.quantidade,
                        i.id_instancia,
                        i.observacao,
                        i.criado_usuario_id,
                        i.dat_criado_em,
                        -- Status atual do workflow (último movimento)
                        ultimo_mov.nome_acao as ultima_acao,
                        ultimo_mov.etapa_atual,
                        ultimo_mov.texto_status,
                        ultimo_mov.dat_movimento,
                        -- Dados da solicitação
                        sv.cod_solicitacao,
                        sv.cliente_id,
                        sv.descricao as descricao_solicitacao
                    FROM sv.tb_solicitacao_venda_item i
                    LEFT JOIN sv.tb_solicitacao_venda sv ON i.id_solicitacao_venda = sv.id_solicitacao_venda
                    LEFT JOIN (
                        SELECT
                            m.id_instancia,
                            a.nome_acao,
                            e.nome_etapa as etapa_atual,
                            s.texto_status,
                            m.dat_criado_em as dat_movimento,
                            ROW_NUMBER() OVER (PARTITION BY m.id_instancia ORDER BY m.dat_criado_em DESC, m.id_movimento DESC) as rn
                        FROM wf.tw_movimento m
                        INNER JOIN wf.tr_fluxo f ON m.id_fluxo = f.id_fluxo
                        INNER JOIN wf.tb_acao a ON f.id_acao = a.id_acao
                        INNER JOIN wf.tb_etapa e ON a.id_etapa_destino = e.id_etapa
                        LEFT JOIN wf.tb_status s ON f.id_status_destino = s.id_status
                        WHERE m.dat_cancelamento_em IS NULL
                    ) ultimo_mov ON i.id_instancia = ultimo_mov.id_instancia AND ultimo_mov.rn = 1
                    WHERE i.dat_cancelamento_em IS NULL"
                . $whereClause
                . " ORDER BY i.id_item ASC";
        }
        else if ($fn === 'fn-por-solicitacao') {
            // Itens de uma solicitação específica
            $execParams[':id_solicitacao_venda'] = $params['id_solicitacao_venda'];
            $consultaSql = "SELECT
                        i.*,
                        u.nome_completo as criado_por_nome
                    FROM sv.tb_solicitacao_venda_item i
                    LEFT JOIN rh.tb_usuarios u ON i.criado_usuario_id = u.id_usuario
                    WHERE i.dat_cancelamento_em IS NULL
                      AND i.id_solicitacao_venda = :id_solicitacao_venda
                    ORDER BY i.id_item ASC";
        }
        else if ($fn === 'fn-sem-workflow') {
            // Itens que ainda não foram enviados para o workflow
            $consultaSql = "SELECT
                        i.*,
                        sv.cod_solicitacao,
                        sv.cliente_id,
                        u.nome_completo as criado_por_nome
                    FROM sv.tb_solicitacao_venda_item i
                    LEFT JOIN sv.tb_solicitacao_venda sv ON i.id_solicitacao_venda = sv.id_solicitacao_venda
                    LEFT JOIN rh.tb_usuarios u ON i.criado_usuario_id = u.id_usuario
                    WHERE i.dat_cancelamento_em IS NULL
                      AND i.id_instancia IS NULL
                    ORDER BY i.dat_criado_em DESC";
            $execParams = [];
        }
        else if ($fn === 'fn-por-produto') {
            // Itens de um produto específico
            $execParams[':produto_id'] = $params['produto_id'];
            $consultaSql = "SELECT
                        i.*,
                        sv.cod_solicitacao,
                        sv.cliente_id,
                        u.nome_completo as criado_por_nome
                    FROM sv.tb_solicitacao_venda_item i
                    LEFT JOIN sv.tb_solicitacao_venda sv ON i.id_solicitacao_venda = sv.id_solicitacao_venda
                    LEFT JOIN rh.tb_usuarios u ON i.criado_usuario_id = u.id_usuario
                    WHERE i.dat_cancelamento_em IS NULL
                      AND i.produto_id = :produto_id
                    ORDER BY i.dat_criado_em DESC";
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
                        i.id_item,
                        i.id_solicitacao_venda,
                        i.produto_id,
                        i.quantidade,
                        i.id_instancia,
                        i.observacao,
                        i.criado_usuario_id,
                        i.dat_criado_em,
                        sv.cod_solicitacao,
                        u.nome_completo as criado_por_nome
                    FROM sv.tb_solicitacao_venda_item i
                    LEFT JOIN sv.tb_solicitacao_venda sv ON i.id_solicitacao_venda = sv.id_solicitacao_venda
                    LEFT JOIN rh.tb_usuarios u ON i.criado_usuario_id = u.id_usuario
                    WHERE i.dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? ' ORDER BY i.dat_criado_em DESC')
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
                    'mensagem' => 'Nenhum item encontrado com os critérios fornecidos.',
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Itens recuperados com sucesso.',
            'data' => $data
        ];
    }

    /**
     * Criar novo item de solicitação
     */
    public function CriarItemVenda($params)
    {
        try {
            $id_solicitacao_venda = $params['id_solicitacao_venda'];
            $produto_id = $params['produto_id'];
            $quantidade = $params['quantidade'] ?? 1;
            $observacao = $params['observacao'] ?? null;
            $criado_usuario_id = $this->obterUsuarioAutenticadoId();

            $consultaSql = "INSERT INTO sv.tb_solicitacao_venda_item
                            (id_solicitacao_venda, produto_id, quantidade, observacao, criado_usuario_id)
                            VALUES (:id_solicitacao_venda, :produto_id, :quantidade, :observacao, :criado_usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':id_solicitacao_venda' => $id_solicitacao_venda,
                ':produto_id' => $produto_id,
                ':quantidade' => $quantidade,
                ':observacao' => $observacao,
                ':criado_usuario_id' => $criado_usuario_id
            ]);

            $rows = $comando->rowCount();

            // Obter ID do item criado
            $id_item = null;
            try {
                $id_item = $this->conexao->lastInsertId();
            } catch (\Exception $e) {
                $id_item = null;
            }

            if ($rows == 0) {
                return [
                    'status' => 404,
                    'mensagem' => 'Item não criado.',
                    'data' => ['afetadas' => $rows, 'id_item' => null]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Item criado com sucesso.',
            'data' => ['afetadas' => $rows, 'id_item' => $id_item]
        ];
    }

    /**
     * Atualizar item de solicitação
     */
    public function AtualizarItemVenda($params)
    {
        try {
            $id_item = $params['id_item'];
            $produto_id = $params['produto_id'] ?? null;
            $quantidade = $params['quantidade'] ?? null;
            $observacao = $params['observacao'] ?? null;
            $atualizado_usuario_id = $this->obterUsuarioAutenticadoId();

            // Construir SQL dinamicamente baseado nos campos fornecidos
            $updateFields = [];
            $execParams = [':id_item' => $id_item];

            if (!is_null($produto_id)) {
                $updateFields[] = 'produto_id = :produto_id';
                $execParams[':produto_id'] = $produto_id;
            }

            if (!is_null($quantidade)) {
                $updateFields[] = 'quantidade = :quantidade';
                $execParams[':quantidade'] = $quantidade;
            }

            if (!is_null($observacao)) {
                $updateFields[] = 'observacao = :observacao';
                $execParams[':observacao'] = $observacao;
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

            $consultaSql = "UPDATE sv.tb_solicitacao_venda_item
                            SET " . implode(', ', $updateFields) . "
                            WHERE id_item = :id_item
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
            'mensagem' => 'Item atualizado com sucesso.',
            'data' => ['afetadas' => $rows]
        ];
    }

    /**
     * Vincular item com instância do workflow
     */
    public function VincularComWorkflow($params)
    {
        try {
            $id_item = $params['id_item'];
            $id_instancia = $params['id_instancia'];
            $atualizado_usuario_id = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE sv.tb_solicitacao_venda_item
                            SET id_instancia = :id_instancia,
                                atualizado_usuario_id = :atualizado_usuario_id,
                                dat_atualizado_em = :dat_atualizado_em
                            WHERE id_item = :id_item
                              AND dat_cancelamento_em IS NULL
                              AND id_instancia IS NULL"; // Só vincula se não estiver já vinculado

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':id_item' => $id_item,
                ':id_instancia' => $id_instancia,
                ':atualizado_usuario_id' => $atualizado_usuario_id,
                ':dat_atualizado_em' => date('Y-m-d H:i:s')
            ]);

            $rows = $comando->rowCount();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Item não foi vinculado (pode já estar vinculado ou não existe).',
                    'data' => ['afetadas' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Item vinculado ao workflow com sucesso.',
            'data' => ['afetadas' => $rows]
        ];
    }

    /**
     * Enviar itens de uma solicitação para o workflow
     * Cria instâncias e vincula itens
     */
    public function EnviarItensParaWorkflow($params)
    {
        try {
            $this->conexao->beginTransaction();

            $id_solicitacao_venda = $params['id_solicitacao_venda'];
            $id_fluxo_inicial = $params['id_fluxo_inicial']; // Fluxo da primeira etapa
            $criado_usuario_id = $this->obterUsuarioAutenticadoId();

            // 1. Buscar itens que ainda não estão no workflow
            $buscarItensSql = "SELECT id_item FROM sv.tb_solicitacao_venda_item
                               WHERE id_solicitacao_venda = :id_solicitacao_venda
                                 AND id_instancia IS NULL
                                 AND dat_cancelamento_em IS NULL";

            $cmdItens = $this->conexao->prepare($buscarItensSql);
            $cmdItens->execute([':id_solicitacao_venda' => $id_solicitacao_venda]);
            $itens = $cmdItens->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($itens)) {
                $this->conexao->rollBack();
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhum item disponível para envio ao workflow.',
                    'data' => ['itens_processados' => 0]
                ];
            }

            $itens_processados = 0;

            // 2. Para cada item, criar instância e movimento inicial
            foreach ($itens as $item) {
                // Criar instância
                $criarInstanciaSql = "INSERT INTO wf.tw_instancia (criado_usuario_id)
                                     VALUES (:criado_usuario_id)";
                $cmdInstancia = $this->conexao->prepare($criarInstanciaSql);
                $cmdInstancia->execute([':criado_usuario_id' => $criado_usuario_id]);

                $id_instancia = $this->conexao->lastInsertId();

                // Criar movimento inicial
                $criarMovimentoSql = "INSERT INTO wf.tw_movimento (id_instancia, id_fluxo, criado_usuario_id)
                                     VALUES (:id_instancia, :id_fluxo, :criado_usuario_id)";
                $cmdMovimento = $this->conexao->prepare($criarMovimentoSql);
                $cmdMovimento->execute([
                    ':id_instancia' => $id_instancia,
                    ':id_fluxo' => $id_fluxo_inicial,
                    ':criado_usuario_id' => $criado_usuario_id
                ]);

                // Vincular item com instância
                $vincularSql = "UPDATE sv.tb_solicitacao_venda_item
                               SET id_instancia = :id_instancia,
                                   atualizado_usuario_id = :atualizado_usuario_id,
                                   dat_atualizado_em = :dat_atualizado_em
                               WHERE id_item = :id_item";
                $cmdVincular = $this->conexao->prepare($vincularSql);
                $cmdVincular->execute([
                    ':id_item' => $item['id_item'],
                    ':id_instancia' => $id_instancia,
                    ':atualizado_usuario_id' => $criado_usuario_id,
                    ':dat_atualizado_em' => date('Y-m-d H:i:s')
                ]);

                $itens_processados++;
            }

            $this->conexao->commit();

        } catch (\Exception $e) {
            $this->conexao->rollBack();
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Itens enviados para o workflow com sucesso.',
            'data' => ['itens_processados' => $itens_processados]
        ];
    }

    /**
     * Cancelar item de solicitação (soft-delete)
     */
    public function CancelarItemVenda($params)
    {
        try {
            $id_item = $params['id_item'];
            $cancelamento_usuario_id = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE sv.tb_solicitacao_venda_item
                            SET cancelamento_usuario_id = :cancelamento_usuario_id,
                                dat_cancelamento_em = :dat_cancelamento_em
                            WHERE id_item = :id_item
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':id_item' => $id_item,
                ':cancelamento_usuario_id' => $cancelamento_usuario_id,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s')
            ]);

            $rows = $comando->rowCount();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhum item foi cancelado.',
                    'data' => ['afetadas' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Item cancelado com sucesso.',
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

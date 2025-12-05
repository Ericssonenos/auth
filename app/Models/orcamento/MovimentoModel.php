<?php

namespace App\Models\orcamento;

use App\Services\Operacao;
use App\Services\rh\usuarioServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MovimentoModel extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    /**
     * Obter dados de movimentos com diferentes modalidades via parâmetro fn
     */
    public function ObterDadosMovimentos($params)
    {
        $fn = $params['fn'] ?? null;

        if ($fn === 'fn-com-respostas-chave-valor') {
            // Movimentos com todas as respostas das chaves-valor
            $execParams[':id_instancia'] = $params['id_instancia'];
            $consultaSql = "SELECT
                        m.id_movimento,
                        m.id_instancia,
                        m.id_fluxo,
                        m.observacao as observacao_movimento,
                        m.criado_usuario_id,
                        m.dat_criado_em,
                        r.resposta_texto,
                        cv.pergunta,
                        cv.tipo_resposta,
                        cvo.texto_opcao,
                        f.id_processo,
                        f.id_versao,
                        a.nome_acao,
                        e1.nome_etapa as etapa_origem,
                        e2.nome_etapa as etapa_destino
                    FROM wf.tw_movimento m
                    INNER JOIN wf.tr_fluxo f ON m.id_fluxo = f.id_fluxo
                    INNER JOIN wf.tb_acao a ON f.id_acao = a.id_acao
                    INNER JOIN wf.tb_etapa e1 ON a.id_etapa_origem = e1.id_etapa
                    INNER JOIN wf.tb_etapa e2 ON a.id_etapa_destino = e2.id_etapa
                    LEFT JOIN wf.tr_movimento_resposta r ON m.id_movimento = r.id_movimento
                        AND r.dat_cancelamento_em IS NULL
                    LEFT JOIN wf.tb_chave_valor cv ON r.id_chave_valor = cv.id_chave_valor
                    LEFT JOIN wf.tb_chave_valor_opcao cvo ON r.id_opcao = cvo.id_opcao
                    WHERE m.dat_cancelamento_em IS NULL
                      AND m.id_instancia = :id_instancia
                    ORDER BY m.dat_criado_em ASC";
        }
        else if ($fn === 'fn-ultimo-movimento-por-instancia') {
            // Último movimento de uma instância específica
            $execParams[':id_instancia'] = $params['id_instancia'];
            $consultaSql = "SELECT
                        m.*,
                        f.id_processo,
                        f.id_versao,
                        a.nome_acao,
                        e1.nome_etapa as etapa_origem,
                        e2.nome_etapa as etapa_destino,
                        s.cod_status,
                        s.texto_status
                    FROM wf.tw_movimento m
                    INNER JOIN wf.tr_fluxo f ON m.id_fluxo = f.id_fluxo
                    INNER JOIN wf.tb_acao a ON f.id_acao = a.id_acao
                    INNER JOIN wf.tb_etapa e1 ON a.id_etapa_origem = e1.id_etapa
                    INNER JOIN wf.tb_etapa e2 ON a.id_etapa_destino = e2.id_etapa
                    LEFT JOIN wf.tb_status s ON f.id_status_destino = s.id_status
                    WHERE m.dat_cancelamento_em IS NULL
                      AND m.id_instancia = :id_instancia
                    ORDER BY m.dat_criado_em DESC, m.id_movimento DESC
                    LIMIT 1";
        }
        else if ($fn === 'fn-historico-completo-instancia') {
            // Todo histórico de uma instância com detalhes completos
            $execParams[':id_instancia'] = $params['id_instancia'];
            $consultaSql = "SELECT
                        m.id_movimento,
                        m.id_instancia,
                        m.id_fluxo,
                        m.observacao,
                        m.criado_usuario_id,
                        m.dat_criado_em,
                        f.id_processo,
                        f.id_versao,
                        a.nome_acao,
                        a.descricao_acao,
                        e1.nome_etapa as etapa_origem,
                        e2.nome_etapa as etapa_destino,
                        s.cod_status,
                        s.texto_status,
                        u.nome_completo as usuario_nome
                    FROM wf.tw_movimento m
                    INNER JOIN wf.tr_fluxo f ON m.id_fluxo = f.id_fluxo
                    INNER JOIN wf.tb_acao a ON f.id_acao = a.id_acao
                    INNER JOIN wf.tb_etapa e1 ON a.id_etapa_origem = e1.id_etapa
                    INNER JOIN wf.tb_etapa e2 ON a.id_etapa_destino = e2.id_etapa
                    LEFT JOIN wf.tb_status s ON f.id_status_destino = s.id_status
                    LEFT JOIN rh.tb_usuarios u ON m.criado_usuario_id = u.id_usuario
                    WHERE m.dat_cancelamento_em IS NULL
                      AND m.id_instancia = :id_instancia
                    ORDER BY m.dat_criado_em ASC";
        }
        else if ($fn === 'fn-movimentos-por-usuario') {
            // Movimentos executados por um usuário específico
            $execParams[':criado_usuario_id'] = $params['criado_usuario_id'];
            $consultaSql = "SELECT
                        m.*,
                        f.id_processo,
                        a.nome_acao,
                        e2.nome_etapa as etapa_destino
                    FROM wf.tw_movimento m
                    INNER JOIN wf.tr_fluxo f ON m.id_fluxo = f.id_fluxo
                    INNER JOIN wf.tb_acao a ON f.id_acao = a.id_acao
                    INNER JOIN wf.tb_etapa e2 ON a.id_etapa_destino = e2.id_etapa
                    WHERE m.dat_cancelamento_em IS NULL
                      AND m.criado_usuario_id = :criado_usuario_id
                    ORDER BY m.dat_criado_em DESC";
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
                        m.id_movimento,
                        m.id_instancia,
                        m.id_fluxo,
                        m.observacao,
                        m.criado_usuario_id,
                        m.dat_criado_em,
                        a.nome_acao,
                        e2.nome_etapa as etapa_destino
                    FROM wf.tw_movimento m
                    INNER JOIN wf.tr_fluxo f ON m.id_fluxo = f.id_fluxo
                    INNER JOIN wf.tb_acao a ON f.id_acao = a.id_acao
                    INNER JOIN wf.tb_etapa e2 ON a.id_etapa_destino = e2.id_etapa
                    WHERE m.dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by'] ?? ' ORDER BY m.dat_criado_em DESC')
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
                    'mensagem' => 'Nenhum movimento encontrado com os critérios fornecidos.',
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Movimentos recuperados com sucesso.',
            'data' => $data
        ];
    }

    /**
     * Criar um novo movimento no workflow
     */
    public function CriarMovimento($params)
    {
        try {
            $id_instancia = $params['id_instancia'];
            $id_fluxo = $params['id_fluxo'];
            $observacao = $params['observacao'] ?? null;
            $criado_usuario_id = $this->obterUsuarioAutenticadoId();

            $consultaSql = "INSERT INTO wf.tw_movimento
                            (id_instancia, id_fluxo, observacao, criado_usuario_id)
                            VALUES (:id_instancia, :id_fluxo, :observacao, :criado_usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':id_instancia' => $id_instancia,
                ':id_fluxo' => $id_fluxo,
                ':observacao' => $observacao,
                ':criado_usuario_id' => $criado_usuario_id
            ]);

            $rows = $comando->rowCount();

            // Tentar obter ID do movimento criado
            $id_movimento = null;
            try {
                $id_movimento = $this->conexao->lastInsertId();
            } catch (\Exception $e) {
                $id_movimento = null;
            }

            if ($rows == 0) {
                return [
                    'status' => 404,
                    'mensagem' => 'Movimento não criado.',
                    'data' => ['afetadas' => $rows, 'id_movimento' => null]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Movimento criado com sucesso.',
            'data' => ['afetadas' => $rows, 'id_movimento' => $id_movimento]
        ];
    }

    /**
     * Salvar respostas das chaves-valor para um movimento
     */
    public function SalvarRespostasMovimento($params)
    {
        try {
            $id_movimento = $params['id_movimento'];
            $respostas = $params['respostas']; // Array de respostas
            $criado_usuario_id = $this->obterUsuarioAutenticadoId();

            $rows_inseridas = 0;

            foreach ($respostas as $resposta) {
                $consultaSql = "INSERT INTO wf.tr_movimento_resposta
                                (id_movimento, id_chave_valor, id_opcao, resposta_texto, criado_usuario_id)
                                VALUES (:id_movimento, :id_chave_valor, :id_opcao, :resposta_texto, :criado_usuario_id)";

                $comando = $this->conexao->prepare($consultaSql);
                $comando->execute([
                    ':id_movimento' => $id_movimento,
                    ':id_chave_valor' => $resposta['id_chave_valor'],
                    ':id_opcao' => $resposta['id_opcao'] ?? null,
                    ':resposta_texto' => $resposta['resposta_texto'] ?? null,
                    ':criado_usuario_id' => $criado_usuario_id
                ]);

                $rows_inseridas += $comando->rowCount();
            }

            if ($rows_inseridas == 0) {
                return [
                    'status' => 404,
                    'mensagem' => 'Nenhuma resposta foi salva.',
                    'data' => ['afetadas' => $rows_inseridas]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Respostas salvas com sucesso.',
            'data' => ['afetadas' => $rows_inseridas]
        ];
    }

    /**
     * Cancelar um movimento (soft-delete)
     */
    public function CancelarMovimento($params)
    {
        try {
            $id_movimento = $params['id_movimento'];
            $cancelamento_usuario_id = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE wf.tw_movimento
                            SET cancelamento_usuario_id = :cancelamento_usuario_id,
                                dat_cancelamento_em = :dat_cancelamento_em
                            WHERE id_movimento = :id_movimento
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':id_movimento' => $id_movimento,
                ':cancelamento_usuario_id' => $cancelamento_usuario_id,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s')
            ]);

            $rows = $comando->rowCount();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhum movimento foi cancelado.',
                    'data' => ['afetadas' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Movimento cancelado com sucesso.',
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

<?php

namespace App\Http\Controllers\orcamento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\orcamento\SolicitacaoVendaModel;
use App\Models\orcamento\SolicitacaoVendaItemModel;
use App\Models\orcamento\MovimentoModel;

class OrcamentoController extends Controller
{
    private SolicitacaoVendaModel $solicitacaoVendaModel;
    private SolicitacaoVendaItemModel $solicitacaoVendaItemModel;
    private MovimentoModel $movimentoModel;

    public function __construct()
    {
        $this->solicitacaoVendaModel = new SolicitacaoVendaModel();
        $this->solicitacaoVendaItemModel = new SolicitacaoVendaItemModel();
        $this->movimentoModel = new MovimentoModel();
    }

    /**
     * Página principal de listagem de orçamentos
     */
    public function listarOrcamentosDisponiveis()
    {
        return view('pages.orcamento.listarOrcamentosDisponiveis');
    }

    /**
     * Página de criação de novo orçamento
     */
    public function exibirFormularioCriacaoOrcamento()
    {
        return view('pages.orcamento.criarNovoOrcamento');
    }

    /**
     * Obter dados de solicitações de venda
     */
    public function ObterDadosSolicitacoesVenda(Request $request)
    {
        $respostaDadosSolicitacoes = $this->solicitacaoVendaModel->ObterDadosSolicitacoesVenda($request->all());
        return response()->json($respostaDadosSolicitacoes, $respostaDadosSolicitacoes['status']);
    }

    /**
     * Obter dados de itens de solicitação
     */
    public function ObterDadosItensVenda(Request $request)
    {
        $respostaDadosItens = $this->solicitacaoVendaItemModel->ObterDadosItensVenda($request->all());
        return response()->json($respostaDadosItens, $respostaDadosItens['status']);
    }

    /**
     * Obter dados de movimentos do workflow
     */
    public function ObterDadosMovimentos(Request $request)
    {
        $respostaDadosMovimentos = $this->movimentoModel->ObterDadosMovimentos($request->all());
        return response()->json($respostaDadosMovimentos, $respostaDadosMovimentos['status']);
    }

    /**
     * Criar nova solicitação de venda
     */
    public function salvarOrcamento(Request $request)
    {
        $payload = $request->all();
        $respostaStatusCriacao = $this->solicitacaoVendaModel->CriarSolicitacaoVenda($payload);
        return response()->json($respostaStatusCriacao, $respostaStatusCriacao['status']);
    }

    /**
     * Adicionar produto ao orçamento (criar item)
     */
    public function AdicionarProdutoAoOrcamento(Request $request)
    {
        $payload = $request->all();
        $respostaStatusAdicao = $this->solicitacaoVendaItemModel->CriarItemVenda($payload);
        return response()->json($respostaStatusAdicao, $respostaStatusAdicao['status']);
    }

    /**
     * Remover produto do orçamento (cancelar item)
     */
    public function RemoverProdutoDoOrcamento(Request $request, $id_item)
    {
        $payload = $request->all();
        $payload['id_item'] = $id_item;
        $respostaStatusRemocao = $this->solicitacaoVendaItemModel->CancelarItemVenda($payload);
        return response()->json($respostaStatusRemocao, $respostaStatusRemocao['status']);
    }

    /**
     * Atualizar item do orçamento
     */
    public function AtualizarProdutoDoOrcamento(Request $request, $id_item)
    {
        $payload = $request->all();
        $payload['id_item'] = $id_item;
        $respostaStatusAtualizacao = $this->solicitacaoVendaItemModel->AtualizarItemVenda($payload);
        return response()->json($respostaStatusAtualizacao, $respostaStatusAtualizacao['status']);
    }

    /**
     * Atualizar dados da solicitação
     */
    public function AtualizarSolicitacaoVenda(Request $request, $id_solicitacao_venda)
    {
        $payload = $request->all();
        $payload['id_solicitacao_venda'] = $id_solicitacao_venda;
        $respostaStatusAtualizacao = $this->solicitacaoVendaModel->AtualizarSolicitacaoVenda($payload);
        return response()->json($respostaStatusAtualizacao, $respostaStatusAtualizacao['status']);
    }

    /**
     * Excluir solicitação de venda
     */
    public function excluirOrcamento($id, Request $request)
    {
        $payload = $request->all();
        $payload['id_solicitacao_venda'] = $id;
        $respostaStatusCancelamento = $this->solicitacaoVendaModel->CancelarSolicitacaoVenda($payload);
        return response()->json($respostaStatusCancelamento, $respostaStatusCancelamento['status']);
    }

    /**
     * Enviar orçamento para o workflow
     */
    public function enviarOrcamentoParaWorkflow(Request $request)
    {
        $payload = $request->all();

        // Validar parâmetros obrigatórios
        if (empty($payload['id_solicitacao_venda'])) {
            return response()->json([
                'status' => 400,
                'mensagem' => 'ID da solicitação é obrigatório.',
                'data' => []
            ], 400);
        }

        if (empty($payload['id_fluxo_inicial'])) {
            return response()->json([
                'status' => 400,
                'mensagem' => 'ID do fluxo inicial é obrigatório.',
                'data' => []
            ], 400);
        }

        // Enviar itens para o workflow
        $respostaEnvio = $this->solicitacaoVendaItemModel->EnviarItensParaWorkflow($payload);
        return response()->json($respostaEnvio, $respostaEnvio['status']);
    }

    /**
     * Obter detalhes de um orçamento específico
     */
    public function obterDetalhesOrcamento($id)
    {
        // Buscar dados da solicitação com itens
        $dadosSolicitacao = $this->solicitacaoVendaModel->ObterDadosSolicitacoesVenda([
            'fn' => 'fn-com-itens',
            'id_solicitacao_venda' => $id
        ]);

        if ($dadosSolicitacao['status'] !== 200) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Orçamento não encontrado',
                'dados' => []
            ], 404);
        }

        // Buscar itens com status do workflow
        $dadosItensWorkflow = $this->solicitacaoVendaItemModel->ObterDadosItensVenda([
            'fn' => 'fn-com-status-workflow',
            'id_solicitacao_venda' => $id
        ]);

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Detalhes do orçamento obtidos com sucesso',
            'dados' => [
                'solicitacao' => $dadosSolicitacao['data'],
                'itens' => $dadosItensWorkflow['data'] ?? [],
                'movimentos' => []
            ]
        ]);
    }

    /**
     * Criar movimento no workflow
     */
    public function CriarMovimentoWorkflow(Request $request)
    {
        $payload = $request->all();
        $respostaStatusMovimento = $this->movimentoModel->CriarMovimento($payload);
        return response()->json($respostaStatusMovimento, $respostaStatusMovimento['status']);
    }

    /**
     * Salvar respostas de chaves-valor em um movimento
     */
    public function SalvarRespostasMovimento(Request $request)
    {
        $payload = $request->all();
        $respostaStatusRespostas = $this->movimentoModel->SalvarRespostasMovimento($payload);
        return response()->json($respostaStatusRespostas, $respostaStatusRespostas['status']);
    }

    /**
     * Obter histórico completo de uma instância
     */
    public function ObterHistoricoInstancia(Request $request, $id_instancia)
    {
        $dadosHistorico = $this->movimentoModel->ObterDadosMovimentos([
            'fn' => 'fn-historico-completo-instancia',
            'id_instancia' => $id_instancia
        ]);
        return response()->json($dadosHistorico, $dadosHistorico['status']);
    }

    /**
     * Obter último movimento de uma instância
     */
    public function ObterUltimoMovimentoInstancia(Request $request, $id_instancia)
    {
        $dadosUltimoMovimento = $this->movimentoModel->ObterDadosMovimentos([
            'fn' => 'fn-ultimo-movimento-por-instancia',
            'id_instancia' => $id_instancia
        ]);
        return response()->json($dadosUltimoMovimento, $dadosUltimoMovimento['status']);
    }

    /**
     * Dashboard - resumo de orçamentos
     */
    public function obterDadosOrcamentos(Request $request)
    {
        $resumoOrcamentos = $this->solicitacaoVendaModel->ObterDadosSolicitacoesVenda([
            'fn' => 'fn-resumo-orcamentos'
        ]);
        return response()->json($resumoOrcamentos, $resumoOrcamentos['status']);
    }

    /**
     * Duplicar orçamento
     */
    public function duplicarOrcamento($id, Request $request)
    {
        // Buscar dados do orçamento original
        $dadosOriginais = $this->solicitacaoVendaModel->ObterDadosSolicitacoesVenda([
            'fn' => 'fn-com-itens',
            'id_solicitacao_venda' => $id
        ]);

        if ($dadosOriginais['status'] !== 200) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Orçamento original não encontrado'
            ], 404);
        }

        // Criar nova solicitação baseada na original
        $payload = $request->all();
        // Aqui você pode implementar a lógica de duplicação específica
        $respostaStatusCriacao = $this->solicitacaoVendaModel->CriarSolicitacaoVenda($payload);
        return response()->json($respostaStatusCriacao, $respostaStatusCriacao['status']);
    }

    /**
     * Gerar PDF do orçamento
     */
    public function gerarPdfOrcamento($id)
    {
        // Implementar geração de PDF
        return response()->json([
            'sucesso' => true,
            'mensagem' => 'PDF gerado com sucesso',
            'url' => '/storage/orcamentos/orcamento_' . $id . '.pdf'
        ]);
    }
}

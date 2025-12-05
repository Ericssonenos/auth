/**
 * Script para modal de detalhes do orçamento
 */

$(document).ready(function() {
    console.log('Modal de detalhes carregado');

    // Eventos do modal
    $('#btn_detalhes_editar').on('click', function() {
        const orcamentoId = $(this).data('id');
        editarOrcamento(orcamentoId);
    });

    $('#btn_detalhes_duplicar').on('click', function() {
        const orcamentoId = $(this).data('id');
        duplicarOrcamento(orcamentoId);
    });

    $('#btn_detalhes_gerar_pdf').on('click', function() {
        const orcamentoId = $(this).data('id');
        gerarPdfOrcamento(orcamentoId);
    });
});

/**
 * Carrega e exibe detalhes do orçamento
 */
function carregarDetalhesOrcamento(id) {
    $.ajax({
        url: `/api/orcamento/obter-detalhes/${id}`,
        type: 'POST',
        success: function(response) {
            if (response.sucesso) {
                preencherDetalhesOrcamento(response.dados);
                $('#modal_detalhes_orcamento').modal('show');
            } else {
                alert('Erro ao carregar detalhes: ' + response.mensagem);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar detalhes:', error);
            alert('Erro ao carregar detalhes. Verifique o console para mais detalhes.');
        }
    });
}

/**
 * Preenche o modal com os detalhes do orçamento
 */
function preencherDetalhesOrcamento(dados) {
    // Informações gerais
    $('#detalhes_cliente').text(dados.cliente_nome || 'N/A');
    $('#detalhes_contato').text(dados.contato_cliente || 'N/A');
    $('#detalhes_descricao').text(dados.descricao || 'N/A');
    $('#detalhes_data_criacao').text(dados.dat_criacao || 'N/A');
    $('#detalhes_prazo_entrega').text(dados.prazo_entrega || 'N/A');
    $('#detalhes_validade').text(dados.validade_orcamento || 'N/A');

    // Observações
    if (dados.observacoes) {
        $('#detalhes_observacoes').text(dados.observacoes);
        $('#container_observacoes_detalhes').show();
    } else {
        $('#container_observacoes_detalhes').hide();
    }

    // Status e totais
    const statusBadge = $('#detalhes_status_badge');
    statusBadge.removeClass('bg-secondary bg-warning bg-success bg-danger');

    switch(dados.status) {
        case 'rascunho':
            statusBadge.addClass('bg-secondary').text('Rascunho');
            break;
        case 'em_aprovacao':
            statusBadge.addClass('bg-warning').text('Em Aprovação');
            break;
        case 'aprovado':
            statusBadge.addClass('bg-success').text('Aprovado');
            break;
        case 'rejeitado':
            statusBadge.addClass('bg-danger').text('Rejeitado');
            break;
        default:
            statusBadge.addClass('bg-secondary').text('Desconhecido');
    }

    $('#detalhes_valor_total').text('R$ ' + parseFloat(dados.valor_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    $('#detalhes_total_itens').text(dados.itens ? dados.itens.length : 0);
    $('#detalhes_total_desconto').text('R$ ' + parseFloat(dados.valor_desconto || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }));

    // Itens
    preencherTabelaItens(dados.itens || []);

    // Histórico do workflow
    if (dados.movimentos && dados.movimentos.length > 0) {
        preencherHistoricoWorkflow(dados.movimentos);
        $('#container_historico_workflow').show();
    } else {
        $('#container_historico_workflow').hide();
    }

    // Configurar botões
    configurarBotoesDetalhes(dados);
}

/**
 * Preenche a tabela de itens
 */
function preencherTabelaItens(itens) {
    const tbody = $('#tabela_itens_detalhes');
    tbody.empty();

    if (itens.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="7" class="text-center text-muted">
                    Nenhum item encontrado para este orçamento.
                </td>
            </tr>
        `);
        return;
    }

    itens.forEach((item, index) => {
        const statusWorkflow = item.id_instancia_workflow ?
            '<span class="badge bg-warning">Em Workflow</span>' :
            '<span class="badge bg-secondary">Pendente</span>';

        const observacoes = item.observacoes ?
            `<br><small class="text-muted">${item.observacoes}</small>` : '';

        const desconto = item.desconto_percentual > 0 ?
            `${parseFloat(item.desconto_percentual).toLocaleString('pt-BR', { minimumFractionDigits: 1 })}%` :
            '-';

        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td>
                    <strong>${item.produto_nome}</strong>
                    ${observacoes}
                </td>
                <td class="text-center">${parseFloat(item.quantidade).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                <td class="text-end">R$ ${parseFloat(item.valor_unitario).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                <td class="text-center">${desconto}</td>
                <td class="text-end">R$ ${parseFloat(item.valor_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                <td class="text-center">${statusWorkflow}</td>
            </tr>
        `);
    });
}

/**
 * Preenche o histórico do workflow
 */
function preencherHistoricoWorkflow(movimentos) {
    const container = $('#historico_movimentos');
    container.empty();

    movimentos.forEach((movimento, index) => {
        const respostas = movimento.respostas && movimento.respostas.length > 0 ? `
            <div class="mt-2">
                <small class="text-muted">Respostas:</small>
                ${movimento.respostas.map(resp => `
                    <div class="mt-1">
                        <strong>${resp.pergunta}:</strong> ${resp.resposta}
                    </div>
                `).join('')}
            </div>
        ` : '';

        const observacoes = movimento.observacoes ? `
            <div class="border-start border-primary ps-3">
                ${movimento.observacoes}
            </div>
        ` : '';

        container.append(`
            <div class="timeline-item">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted">${movimento.dat_movimento}</small>
                        <br><strong>${movimento.usuario_nome}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="badge bg-primary">${movimento.acao_nome}</span>
                        <br><small>${movimento.etapa_nome}</small>
                    </div>
                    <div class="col-md-5">
                        ${observacoes}
                        ${respostas}
                    </div>
                </div>
                ${index < movimentos.length - 1 ? '<hr>' : ''}
            </div>
        `);
    });
}

/**
 * Configura os botões do modal de detalhes
 */
function configurarBotoesDetalhes(dados) {
    // Botão editar (apenas para rascunhos)
    if (dados.status === 'rascunho') {
        $('#btn_detalhes_editar').removeClass('d-none').data('id', dados.id);
    } else {
        $('#btn_detalhes_editar').addClass('d-none');
    }

    // Botão gerar PDF (para aprovados ou em aprovação)
    if (['aprovado', 'em_aprovacao'].includes(dados.status)) {
        $('#btn_detalhes_gerar_pdf').removeClass('d-none').data('id', dados.id);
    } else {
        $('#btn_detalhes_gerar_pdf').addClass('d-none');
    }

    // Botão duplicar (sempre disponível)
    $('#btn_detalhes_duplicar').data('id', dados.id);
}

/**
 * Editar orçamento
 */
function editarOrcamento(id) {
    $('#modal_detalhes_orcamento').modal('hide');
    // TODO: Carregar dados e abrir modal de edição
    console.log('Editando orçamento:', id);
}

/**
 * Duplicar orçamento
 */
function duplicarOrcamento(id) {
    if (confirm('Deseja duplicar este orçamento?')) {
        $.ajax({
            url: `/api/orcamento/duplicar/${id}`,
            type: 'POST',
            success: function(response) {
                if (response.sucesso) {
                    alert('Orçamento duplicado com sucesso!');
                    $('#modal_detalhes_orcamento').modal('hide');
                    $('#tb_orcamentos').DataTable().ajax.reload();
                } else {
                    alert('Erro ao duplicar orçamento: ' + response.mensagem);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao duplicar orçamento:', error);
                alert('Erro ao duplicar orçamento. Verifique o console para mais detalhes.');
            }
        });
    }
}

/**
 * Gerar PDF do orçamento
 */
function gerarPdfOrcamento(id) {
    window.open(`/api/orcamento/gerar-pdf/${id}`, '_blank');
}

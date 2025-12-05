/**
 * Script para listagem de orçamentos
 */

$(document).ready(function() {
    console.log('Lista de orçamentos carregada');

    // Inicializar DataTable
    inicializarDataTable();

    // Eventos dos botões
    $('#btn_novo_orcamento').on('click', function() {
        abrirModalNovoOrcamento();
    });
});

/**
 * Inicializa a DataTable de orçamentos
 */
function inicializarDataTable() {
    $('#tb_orcamentos').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '/api/orcamento/obter-dados-orcamentos',
            type: 'POST',
            error: function(xhr) {
                 window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            }
        },
        columns: [
            { data: 'id', title: 'ID' },
            { data: 'cliente_nome', title: 'Cliente' },
            { data: 'descricao', title: 'Descrição' },
            {
                data: 'valor_total',
                title: 'Valor Total',
                render: function(data, type, row) {
                    return 'R$ ' + parseFloat(data).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                }
            },
            {
                data: 'status',
                title: 'Status',
                render: function(data, type, row) {
                    const statusMap = {
                        'rascunho': '<span class="badge bg-secondary">Rascunho</span>',
                        'em_aprovacao': '<span class="badge bg-warning">Em Aprovação</span>',
                        'aprovado': '<span class="badge bg-success">Aprovado</span>',
                        'rejeitado': '<span class="badge bg-danger">Rejeitado</span>'
                    };
                    return statusMap[data] || '<span class="badge bg-light">Desconhecido</span>';
                }
            },
            { data: 'dat_criacao', title: 'Data Criação' },
            {
                data: 'dat_ultima_atualizacao',
                title: 'Última Atualização',
                render: function(data, type, row) {
                    return data || 'N/A';
                }
            },
            {
                data: null,
                title: 'Ações',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button type="button" class="btn btn-sm btn-outline-info btn-detalhes" data-id="${row.id}">
                            <i class="bi bi-eye"></i> Detalhes
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning btn-editar" data-id="${row.id}">
                            <i class="bi bi-pencil"></i> Editar
                        </button>
                    `;
                }
            }
        ],
        responsive: true,
        order: [[ 0, 'desc' ]]
    });

    // Eventos dos botões da tabela
    $('#tb_orcamentos').on('click', '.btn-detalhes', function() {
        const id = $(this).data('id');
        abrirModalDetalhes(id);
    });

    $('#tb_orcamentos').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        abrirModalEdicaoOrcamento(id);
    });
}

/**
 * Abre o modal para criar novo orçamento
 */
function abrirModalNovoOrcamento() {
    console.log('Abrindo modal para novo orçamento');
    // TODO: Implementar abertura do modal de orçamento
    $('#modal_orcamento').modal('show');
}

/**
 * Abre o modal para editar orçamento
 */
function abrirModalEdicaoOrcamento(id) {
    console.log('Abrindo modal para editar orçamento:', id);
    // TODO: Implementar carregamento e abertura do modal de edição
    $('#modal_orcamento').modal('show');
}

/**
 * Abre o modal de detalhes do orçamento
 */
function abrirModalDetalhes(id) {
    console.log('Abrindo modal de detalhes para orçamento:', id);
    carregarDetalhesOrcamento(id);
}

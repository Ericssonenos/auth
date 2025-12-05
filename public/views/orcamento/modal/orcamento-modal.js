/**
 * Script para modal de orçamento (criar/editar)
 */

$(document).ready(function() {
    console.log('Modal de orçamento carregado');

    // Eventos do modal
    $('#btn_modal_orcamento_salvar').on('click', function() {
        salvarOrcamento();
    });

    $('#btn_adicionar_item_modal').on('click', function() {
        adicionarItemOrcamento();
    });

    // Evento para remover item
    $(document).on('click', '.btn-remover-item-modal', function() {
        removerItemOrcamento(this);
    });
});

/**
 * Salva o orçamento
 */
function salvarOrcamento() {
    console.log('Salvando orçamento...');

    const formData = $('#fm_modal_orcamento').serialize();

    $.ajax({
        url: '/api/orcamento/salvar',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.sucesso) {
                alert('Orçamento salvo com sucesso!');
                $('#modal_orcamento').modal('hide');
                $('#tb_orcamentos').DataTable().ajax.reload();
            } else {
                alert('Erro ao salvar orçamento: ' + response.mensagem);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao salvar orçamento:', error);
            alert('Erro ao salvar orçamento. Verifique o console para mais detalhes.');
        }
    });
}

/**
 * Adiciona um novo item ao orçamento
 */
function adicionarItemOrcamento() {
    const template = document.getElementById('template_item_orcamento_modal');
    const clone = template.content.cloneNode(true);

    // Substituir INDEX por um número único
    const itemIndex = $('#container_itens_modal .item-orcamento-modal').length;
    const html = clone.firstElementChild.outerHTML.replace(/INDEX/g, itemIndex);

    $('#container_itens_modal').append(html);

    // Recalcular totais
    recalcularTotais();
}

/**
 * Remove um item do orçamento
 */
function removerItemOrcamento(button) {
    $(button).closest('.item-orcamento-modal').remove();
    recalcularTotais();
}

/**
 * Recalcula os totais do orçamento
 */
function recalcularTotais() {
    let subtotal = 0;
    let totalDesconto = 0;

    $('#container_itens_modal .item-orcamento-modal').each(function() {
        const quantidade = parseFloat($(this).find('.quantidade-input-modal').val()) || 0;
        const valorUnitario = parseFloat($(this).find('.valor-unitario-input-modal').val()) || 0;
        const descontoPercentual = parseFloat($(this).find('.desconto-input-modal').val()) || 0;

        const subtotalItem = quantidade * valorUnitario;
        const descontoItem = (subtotalItem * descontoPercentual) / 100;
        const totalItem = subtotalItem - descontoItem;

        // Atualizar display do item
        $(this).find('.total-item-display-modal').val('R$ ' + totalItem.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
        $(this).find('.total-item-hidden-modal').val(totalItem);

        subtotal += subtotalItem;
        totalDesconto += descontoItem;
    });

    const total = subtotal - totalDesconto;

    // Atualizar displays dos totais
    $('#total_subtotal_modal').text('R$ ' + subtotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    $('#total_desconto_modal').text('R$ ' + totalDesconto.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
    $('#total_geral_modal').text('R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
}

// Eventos para recalcular totais quando valores mudam
$(document).on('input', '.quantidade-input-modal, .valor-unitario-input-modal, .desconto-input-modal', function() {
    recalcularTotais();
});

// -------------------------
// Autocomplete de Cliente (simulado)
// -------------------------
(function(){
    const clientes = [
        { id: 1, nome: 'Cliente Exemplo A', cnpj: '12.345.678/0001-90', endereco: 'Rua A, 100 - Centro, Cidade X - SP', telefone: '+55 (11) 1111-1111', email: 'contatoA@exemplo.com', contato: 'João Silva' },
        { id: 2, nome: 'Cliente Exemplo B', cnpj: '98.765.432/0001-10', endereco: 'Av. B, 200 - Bairro Y, Cidade Z - SP', telefone: '+55 (11) 2222-2222', email: 'vendasB@exemplo.com', contato: 'Maria Santos' }
    ];

    function renderSuggestions(term) {
        const $container = $('#client_suggestions');
        $container.empty();
        const q = (term || '').trim().toLowerCase();
        if (!q) { $container.hide(); return; }
        const matches = clientes.filter(c => c.nome.toLowerCase().includes(q) || c.cnpj.replace(/\D/g,'').includes(q.replace(/\D/g,'')));
        matches.slice(0,10).forEach(c => {
            const $item = $('<button type="button" class="list-group-item list-group-item-action"></button>');
            $item.text(c.nome + ' — ' + c.cnpj);
            $item.data('client', c);
            $item.on('click', function(){ selectClient($(this).data('client')); });
            $container.append($item);
        });
        if (matches.length) $container.show(); else $container.hide();
    }

    function selectClient(c) {
        if (!c) return;
        $('#id_cliente_modal').val(c.id);
        $('#id_cliente_modal_search').val(c.nome);
        $('#cli_nome').val(c.nome);
        $('#cli_cnpj').val(c.cnpj);
        $('#cli_endereco').val(c.endereco);
        $('#cli_telefone').val(c.telefone);
        $('#cli_email').val(c.email);
        $('#cli_contato').val(c.contato || '');
        $('#cliente_info_display').show();
        $('#client_suggestions').hide();
    }

    function createTestClient() {
        const novoId = clientes.length + 1;
        const novo = { id: novoId, nome: 'Cliente Teste ' + novoId, cnpj: '00.000.000/0000-' + String(novoId).padStart(2,'0'), endereco: 'Endereço teste ' + novoId, telefone: '+55 (11) 9999-000' + novoId, email: 'teste' + novoId + '@exemplo.com', contato: 'Contato Teste ' + novoId };
        clientes.push(novo);
        selectClient(novo);
    }

    $(document).ready(function(){
        // input typing
        $(document).on('input', '#id_cliente_modal_search', function(){
            renderSuggestions($(this).val());
        });

        // create test client
        $(document).on('click', '#btn_add_cliente_teste', function(){
            createTestClient();
        });

        // click outside suggestions -> hide
        $(document).on('click', function(e){
            const $target = $(e.target);
            if (!$target.closest('#client_suggestions').length && !$target.is('#id_cliente_modal_search')) {
                $('#client_suggestions').hide();
            }
        });
    });
})();

// -------------------------
// Autocomplete de Vendedor (simulado)
// -------------------------
(function(){
    const vendedores = [
        { id: 1, nome: 'Ericsson Sousa' },
        { id: 2, nome: 'Eliezer Begio' },
        { id: 3, nome: 'Ricardo Oliveira' }
    ];

    function renderVendedorSuggestions(term) {
        const $container = $('#vendedor_suggestions');
        $container.empty();
        const q = (term || '').trim().toLowerCase();
        if (!q) { $container.hide(); return; }
        const matches = vendedores.filter(v => v.nome.toLowerCase().includes(q));
        matches.slice(0,10).forEach(v => {
            const $item = $('<button type="button" class="list-group-item list-group-item-action"></button>');
            $item.text(v.nome);
            $item.data('vendedor', v);
            $item.on('click', function(){ selectVendedor($(this).data('vendedor')); });
            $container.append($item);
        });
        if (matches.length) $container.show(); else $container.hide();
    }

    function selectVendedor(v) {
        if (!v) return;
        $('#id_vendedor_modal').val(v.id);
        $('#id_vendedor_modal_search').val(v.nome);
        $('#vendedor_suggestions').hide();
    }

    $(document).ready(function(){
        // input typing
        $(document).on('input', '#id_vendedor_modal_search', function(){
            renderVendedorSuggestions($(this).val());
        });

        // click outside suggestions -> hide
        $(document).on('click', function(e){
            const $target = $(e.target);
            if (!$target.closest('#vendedor_suggestions').length && !$target.is('#id_vendedor_modal_search')) {
                $('#vendedor_suggestions').hide();
            }
        });
    });
})();

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
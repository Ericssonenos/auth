/**
 * Script para modal de orçamento (criar/editar)
 */

(function() {
    const BRL_FORMATTER = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    const clientes = [
        { id: 1, nome: 'Cliente Exemplo A', cnpj: '12.345.678/0001-90', endereco: 'Rua A, 100 - Centro, Cidade X - SP', telefone: '+55 (11) 1111-1111', email: 'contatoA@exemplo.com', contato: 'João Silva' },
        { id: 2, nome: 'Cliente Exemplo B', cnpj: '98.765.432/0001-10', endereco: 'Av. B, 200 - Bairro Y, Cidade Z - SP', telefone: '+55 (11) 2222-2222', email: 'vendasB@exemplo.com', contato: 'Maria Santos' }
    ];

    const vendedores = [
        { id: 1, nome: 'Ericsson Sousa' },
        { id: 2, nome: 'Eliezer Begio' },
        { id: 3, nome: 'Ricardo Oliveira' }
    ];

    const produtos = [
        {
            id: 1,
            nome: 'Máquina de Solda MIG 350i',
            codigo: 'FW-MIG350I',
            descricao: 'Máquina de solda MIG 350A trifásica com alimentador integrado',
            ncm: '8515.10.11',
            marca: 'FluxWeld',
            preco_compra: 7000.00,
            impostos: { icms: 12, ipi: 5, pis: 1.65, cofins: 7.6 }
        },
        {
            id: 2,
            nome: 'Máquina de Solda TIG 200 AC/DC',
            codigo: 'FW-TIG200AC',
            descricao: 'Fonte TIG 200A AC/DC com tocha refrigerada a água',
            ncm: '8515.39.10',
            marca: 'FluxWeld',
            preco_compra: 5400.00,
            impostos: { icms: 12, ipi: 4.5, pis: 1.65, cofins: 7.6 }
        },
        {
            id: 3,
            nome: 'Inversora de Solda MMA 160 Compact',
            codigo: 'FW-MMA160C',
            descricao: 'Inversora portátil MMA 160A, bivolt automático',
            ncm: '8515.80.00',
            marca: 'FluxWeld',
            preco_compra: 980.00,
            impostos: { icms: 12, ipi: 3.5, pis: 1.65, cofins: 7.6 }
        }
    ];

    function formatCurrency(value) {
        if (Number.isNaN(value) || !isFinite(value)) {
            return BRL_FORMATTER.format(0);
        }
        return BRL_FORMATTER.format(value);
    }

    function sumPercent(obj) {
        return Object.values(obj || {}).reduce((acc, val) => acc + (parseFloat(val) || 0), 0);
    }

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

    function adicionarItemOrcamento() {
        const template = document.getElementById('template_item_orcamento_modal');
        const clone = template.content.cloneNode(true);
        const itemIndex = $('#container_itens_modal .item-orcamento-modal').length;
        const html = clone.firstElementChild.outerHTML.replace(/INDEX/g, itemIndex);
        const $element = $(html);
        $('#container_itens_modal').append($element);
        recalcularTotais();
        $element.find('.produto-search-modal').trigger('focus');
    }

    function removerItemOrcamento(button) {
        $(button).closest('.item-orcamento-modal').remove();
        recalcularTotais();
    }

    function calcularValoresItem($item) {
        const quantidade = parseFloat($item.find('.quantidade-input-modal').val()) || 0;
        const descontoPercentual = parseFloat($item.find('.desconto-input-modal').val()) || 0;
        const margemPercentual = parseFloat($item.find('.margem-input-modal').val()) || 0;
        const comissaoPercentual = parseFloat($('#comissao_modal').val()) || 0;
        const precoCompra = parseFloat($item.find('.produto-preco-compra-hidden').val()) || 0;
        const impostosPercentual = parseFloat($item.find('.produto-impostos-percent-hidden').val()) || 0;
        const impostosBreakdown = $item.data('produtoImpostosBreakdown') || '';

        if (!precoCompra || !quantidade) {
            $item.find('.valor-unitario-display-modal').val('');
            $item.find('.valor-unitario-hidden-modal').val('');
            $item.find('.total-item-display-modal').val('');
            $item.find('.total-item-hidden-modal').val('');
            $item.find('.produto-impostos-display').val('');
            $item.find('.produto-margem-valor-display').val('');
            $item.find('.produto-comissao-valor-display').val('');
            return { subtotal: 0, desconto: 0, total: 0 };
        }

        const percentualTotal = impostosPercentual + margemPercentual + comissaoPercentual;
        const divisor = 1 - (percentualTotal / 100);

        if (divisor <= 0) {
            $item.find('.valor-unitario-display-modal').val('Percentual inválido');
            $item.find('.valor-unitario-hidden-modal').val('');
            $item.find('.total-item-display-modal').val('');
            $item.find('.total-item-hidden-modal').val('');
            return { subtotal: 0, desconto: 0, total: 0 };
        }

        const valorUnitario = precoCompra / divisor;
        const impostoValor = valorUnitario * (impostosPercentual / 100);
        const margemValor = valorUnitario * (margemPercentual / 100);
        const comissaoValor = valorUnitario * (comissaoPercentual / 100);

        const subtotalItem = valorUnitario * quantidade;
        const totalItemSemDesconto = subtotalItem;
        const totalItemComDesconto = totalItemSemDesconto * (1 - (descontoPercentual / 100));
        const descontoValor = totalItemSemDesconto - totalItemComDesconto;

        $item.find('.valor-unitario-display-modal').val(formatCurrency(valorUnitario));
        $item.find('.valor-unitario-hidden-modal').val(valorUnitario.toFixed(2));
        $item.find('.total-item-display-modal').val(formatCurrency(totalItemComDesconto));
        $item.find('.total-item-hidden-modal').val(totalItemComDesconto.toFixed(2));
        $item.find('.produto-impostos-display').val(`${impostosPercentual.toFixed(2)}% / ${formatCurrency(impostoValor)}${impostosBreakdown ? ` (${impostosBreakdown})` : ''}`);
        $item.find('.produto-margem-valor-display').val(formatCurrency(margemValor));
        $item.find('.produto-comissao-valor-display').val(formatCurrency(comissaoValor));

        return {
            subtotal: totalItemSemDesconto,
            desconto: descontoValor,
            total: totalItemComDesconto
        };
    }

    function recalcularTotais() {
        let subtotal = 0;
        let totalDesconto = 0;
        let total = 0;

        $('#container_itens_modal .item-orcamento-modal').each(function() {
            const resultado = calcularValoresItem($(this));
            subtotal += resultado.subtotal;
            totalDesconto += resultado.desconto;
            total += resultado.total;
        });

        $('#total_subtotal_modal').text(formatCurrency(subtotal));
        $('#total_desconto_modal').text(formatCurrency(totalDesconto));
        $('#total_geral_modal').text(formatCurrency(total));
    }

    function renderClientSuggestions(term) {
        const $container = $('#client_suggestions');
        $container.empty();
        const q = (term || '').trim().toLowerCase();
        if (!q) { $container.hide(); return; }
        const matches = clientes.filter(c => c.nome.toLowerCase().includes(q) || c.cnpj.replace(/\D/g,'').includes(q.replace(/\D/g,'')));
        matches.slice(0, 10).forEach(c => {
            const $item = $('<button type="button" class="list-group-item list-group-item-action"></button>');
            $item.text(`${c.nome} — ${c.cnpj}`);
            $item.data('client', c);
            $item.on('click', function(){ selectClient($(this).data('client')); });
            $container.append($item);
        });
        $container.toggle(matches.length > 0);
    }

    function selectClient(client) {
        if (!client) return;
        $('#id_cliente_modal').val(client.id);
        $('#id_cliente_modal_search').val(client.nome);
        $('#cli_nome').val(client.nome);
        $('#cli_cnpj').val(client.cnpj);
        $('#cli_endereco').val(client.endereco);
        $('#cli_telefone').val(client.telefone);
        $('#cli_email').val(client.email);
        $('#cli_contato').val(client.contato || '');
        $('#cliente_info_display').show();
        $('#client_suggestions').hide();
    }

    function criarClienteTeste() {
        const novoId = clientes.length + 1;
        const novo = {
            id: novoId,
            nome: `Cliente Teste ${novoId}`,
            cnpj: `00.000.000/0000-${String(novoId).padStart(2, '0')}`,
            endereco: `Endereço teste ${novoId}`,
            telefone: `+55 (11) 9999-00${String(novoId).padStart(2, '0')}`,
            email: `teste${novoId}@exemplo.com`,
            contato: `Contato Teste ${novoId}`
        };
        clientes.push(novo);
        selectClient(novo);
    }

    function renderVendedorSuggestions(term) {
        const $container = $('#vendedor_suggestions');
        $container.empty();
        const q = (term || '').trim().toLowerCase();
        if (!q) { $container.hide(); return; }
        const matches = vendedores.filter(v => v.nome.toLowerCase().includes(q));
        matches.slice(0, 10).forEach(v => {
            const $item = $('<button type="button" class="list-group-item list-group-item-action"></button>');
            $item.text(v.nome);
            $item.data('vendedor', v);
            $item.on('click', function(){ selectVendedor($(this).data('vendedor')); });
            $container.append($item);
        });
        $container.toggle(matches.length > 0);
    }

    function selectVendedor(vendedor) {
        if (!vendedor) return;
        $('#id_vendedor_modal').val(vendedor.id);
        $('#id_vendedor_modal_search').val(vendedor.nome);
        $('#vendedor_suggestions').hide();
    }

    function renderProdutoSuggestions($input, term) {
        const $item = $input.closest('.item-orcamento-modal');
        const $container = $item.find('.produto-suggestions');
        $container.empty();
        const q = (term || '').trim().toLowerCase();
        if (!q) { $container.hide(); return; }
        const matches = produtos.filter(produto => {
            const codigo = (produto.codigo || '').toLowerCase();
            return produto.nome.toLowerCase().includes(q) || codigo.includes(q) || (produto.descricao || '').toLowerCase().includes(q);
        });
        matches.slice(0, 10).forEach(produto => {
            const $button = $('<button type="button" class="list-group-item list-group-item-action"></button>');
            $button.text(`${produto.nome} — ${produto.codigo}`);
            $button.data('produto', produto);
            $button.on('click', function(){
                selectProduto($item, $(this).data('produto'));
            });
            $container.append($button);
        });
        $container.toggle(matches.length > 0);
    }

    function selectProduto($item, produto) {
        if (!produto) return;
        const impostosPercentual = sumPercent(produto.impostos);
        const impostosDescricao = Object.entries(produto.impostos || {})
            .map(([chave, valor]) => `${chave.toUpperCase()}: ${parseFloat(valor).toFixed(2)}%`)
            .join(' | ');

        $item.find('.produto-id-modal').val(produto.id);
        $item.find('.produto-search-modal').val(produto.nome);
        $item.find('.produto-info').show();

        $item.find('.produto-codigo-display').val(produto.codigo || '');
        $item.find('.produto-codigo-hidden').val(produto.codigo || '');

        $item.find('.produto-descricao-display').val(produto.descricao || produto.nome || '');
        $item.find('.produto-descricao-hidden').val(produto.descricao || produto.nome || '');

        $item.find('.produto-ncm-display').val(produto.ncm || '');
        $item.find('.produto-ncm-hidden').val(produto.ncm || '');

        $item.find('.produto-marca-display').val(produto.marca || '');
        $item.find('.produto-marca-hidden').val(produto.marca || '');

        $item.find('.produto-preco-compra-display').val(formatCurrency(produto.preco_compra || 0));
        $item.find('.produto-preco-compra-hidden').val((produto.preco_compra || 0).toFixed(2));

        $item.find('.produto-impostos-json-hidden').val(JSON.stringify(produto.impostos || {}));
        $item.find('.produto-impostos-percent-hidden').val(impostosPercentual.toFixed(4));
        $item.data('produtoImpostosBreakdown', impostosDescricao);
        $item.find('.produto-impostos-display').val(`${impostosPercentual.toFixed(2)}% / ${formatCurrency(0)}${impostosDescricao ? ` (${impostosDescricao})` : ''}`);

        recalcularTotais();
        $item.find('.produto-suggestions').hide();
    }

    $(document).ready(function() {
        console.log('Modal de orçamento carregado');

        $('#btn_modal_orcamento_salvar').on('click', salvarOrcamento);
        $('#btn_adicionar_item_modal').on('click', adicionarItemOrcamento);

        $(document).on('click', '.btn-remover-item-modal', function() {
            removerItemOrcamento(this);
        });

        $(document).on('input', '.quantidade-input-modal, .desconto-input-modal, .margem-input-modal', function() {
            recalcularTotais();
        });

        $(document).on('input', '#comissao_modal', function() {
            recalcularTotais();
        });

        $(document).on('input', '#id_cliente_modal_search', function() {
            renderClientSuggestions($(this).val());
        });

        $(document).on('click', '#btn_add_cliente_teste', function() {
            criarClienteTeste();
        });

        $(document).on('input', '#id_vendedor_modal_search', function() {
            renderVendedorSuggestions($(this).val());
        });

        $(document).on('input', '.produto-search-modal', function() {
            renderProdutoSuggestions($(this), $(this).val());
        });

        $(document).on('click', function(e) {
            const $target = $(e.target);
            if (!$target.closest('#client_suggestions, #id_cliente_modal_search').length) {
                $('#client_suggestions').hide();
            }
            if (!$target.closest('#vendedor_suggestions, #id_vendedor_modal_search').length) {
                $('#vendedor_suggestions').hide();
            }
            if (!$target.closest('.produto-suggestions, .produto-search-modal').length) {
                $('.produto-suggestions').hide();
            }
        });

        recalcularTotais();
    });
})();

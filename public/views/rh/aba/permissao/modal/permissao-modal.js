let id_permissao_selecionada = null;

// Abrir formulário de permissões no modal
Abrir_Modal_Nova_Permissao = function () {
    // Resetar variáveis globais
    id_permissao_selecionada = null;

    // Resetar formulário
    $('#fm_modal_permissao')[0].reset();

    // Atualizar título do modal
    $('#titulo_modal_permissao').text('Nova Permissão');

    // Exibir modal
    new bootstrap.Modal(document.getElementById('modal_permissao')).show();
};



// Abre modal de edição preenchendo os campos
Abrir_Modal_Editar_Permissao = function (dados_permissao = {}) {
    id_permissao_selecionada = dados_permissao.id_permissao ?? dados_permissao.id_Permissao ?? null;

    $('#titulo_modal_permissao').text('Editar Permissão');
    $('#nome_modal_permissao').val(dados_permissao.cod_permissao ?? dados_permissao.nome_permissao ?? '');
    $('#descricao_modal_permissao').val(dados_permissao.descricao_permissao ?? dados_permissao.descricao ?? '');

    new bootstrap.Modal(document.getElementById('modal_permissao')).show();
};

// Persiste permissão (criação ou edição)
Salvar_Permissao = function () {
    const codPermissao = $('#nome_modal_permissao').val();
    const descricao = $('#descricao_modal_permissao').val();
    const categoriaId = $('#categoria_id_modal_permissao').val();

    const dadosPermissao = {
        cod_permissao: codPermissao,
        descricao_permissao: descricao,
        categoria_id: categoriaId
    };

    let url = '/api/rh/permissao/cadastrar';
    let method = 'POST';

    if (id_permissao_selecionada) {
        url = '/api/rh/permissao/atualizar/' + encodeURIComponent(id_permissao_selecionada);
        method = 'PUT';
    }

    $.ajax({
        type: method,
        method: method,
        url: url,
        contentType: 'application/json',
        data: JSON.stringify(dadosPermissao),
        success: function (resposta) {
            window.alerta.sucesso(resposta.mensagem);

            tb_permissoes.ajax.reload( () => {
                tb_permissoes.searchPanes.clearSelections();
                tb_permissoes.searchPanes.rebuildPane();
            });

            const modalElement = document.getElementById('modal_permissao');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        },
        error: function (xhr) {
            window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
        }
    });
};

// Remove permissão selecionada
Excluir_Permissao = function (id_permissao) {
    if (!id_permissao) {
        return;
    }

    if (!confirm('Tem certeza que deseja excluir esta permissão?')) {
        return;
    }

    $.ajax({
        type: 'DELETE',
        url: '/api/rh/permissao/deletar/' + encodeURIComponent(id_permissao),
        contentType: 'application/json',
        success: function (resposta) {
            window.alerta.sucesso(resposta.mensagem ?? 'Permissão removida.');
            if (typeof tb_permissoes !== 'undefined') {
                tb_permissoes.ajax.reload();
            }
        },
        error: function (xhr) {
            window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
        }
    });
};

// Iteração dos botões da tabela de permissões
$('#tb_permissao').on('click', '.btn-abrir-modal-editar-permissao', function () {
    // Obter dados da permissão a partir do atributo data
    let dados_permissao = $(this).data('permissao');

    if (typeof tb_permissoes !== 'undefined' && (!dados_permissao || $.isEmptyObject(dados_permissao))) {
        const $linha = $(this).closest('tr');
        const linhaPrincipal = $linha.hasClass('child') ? $linha.prev() : $linha;
        dados_permissao = tb_permissoes.row(linhaPrincipal).data();
    }

    // Chamar função para abrir o modal de edição
    Abrir_Modal_Editar_Permissao(dados_permissao);
});

// Botões do modal de permissão
$('#modal_permissao').on('click', '#btn_modal_permissao_salvar', function () {
    Salvar_Permissao();
});

$('#modal_permissao').on('click', '#btn_modal_permissao_excluir', function () {
    Excluir_Permissao(id_permissao_selecionada);
});

$('#modal_permissao').on('click', '#btn_modal_permissao_imprimir', function () {
    const modal = document.getElementById('modal_permissao');
    if (window.impressao && typeof window.impressao.imprimirConteudoModal === 'function') {
        window.impressao.imprimirConteudoModal(modal);
    }
});


//Abrir formulario modal de grupo

Abrir_Modal_Novo_Grupo = function () {


    // Resetar variáveis globais
    id_grupo_selecionado = null;

    // Resetar formulário
    $('#fm_modal_grupo')[0].reset();

    // Definir título do modal
    $('#titulo_modal_grupo').text('Novo Grupo');

    // carregar Categorias no select
    Carregar_Categorias_No_Select();

    // Exibir modal
    new bootstrap.Modal(document.getElementById('modal_grupo')).show();
}

// Função para carregar categorias no select do modal de grupo
Carregar_Categorias_No_Select = function ( id_Categoria_ativo = null) {

    // Requisição AJAX para obter categorias
    $.ajax({
        type: 'POST',
        url: '/api/rh/categoria/dados',
        success: function (resp) {
            // Adicionar uma opção padrão
            const select = $('#categoria_id_modal_grupo');

            // Limpar opções existentes
            // e adicionar a opção padrão
            select.empty().append(new Option('Selecione uma categoria', ''));

            // Iterar sobre os dados e adicionar opções ao select
            resp.data.forEach(function (categoria) {
                const option = new Option(categoria.nome_Categoria, categoria.id_Categoria);
                if (categoria.id_Categoria === id_Categoria_ativo) {
                    option.selected = true;
                }
                select.append(option);
            });
        },
        error: function (xhr) {
            window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
        }
    });
}

// Função para abrir o modal de edição de grupo
Abrir_Modal_Editar_Grupo = function (dados_grupo) {

    // Resetar variáveis globais
    id_grupo_selecionado = dados_grupo.id_Grupo;

    // Atualizar título do modal
    $('#titulo_modal_grupo').text('Editar Grupo');

    // Preencher o formulário com os dados do grupo
    $('#nome_modal_grupo').val(dados_grupo.nome_Grupo);
    $('#descricao_modal_grupo').val(dados_grupo.descricao_Grupo);

    // carregar Categorias no select
    Carregar_Categorias_No_Select(dados_grupo.categoria_id);

    // Exibir o modal
    new bootstrap.Modal(document.getElementById('modal_grupo')).show();
}

// Salvar Grupo (novo ou editado)
Salvar_Grupo = function () {
    // Obter dados do formulário
    const nome_Grupo = $('#nome_modal_grupo').val();
    const descricao_Grupo = $('#descricao_modal_grupo').val();
    const categoria_id = $('#categoria_id_modal_grupo').val();

    // Construir objeto de dados
    const dados_grupo = {
        nome_Grupo: nome_Grupo,
        descricao_Grupo: descricao_Grupo,
        categoria_id: categoria_id
    };

    let url = '/api/rh/grupo/cadastrar';
    let method = 'POST';

    // Se estivermos editando um grupo existente
    if (id_grupo_selecionado) {
        url = '/api/rh/grupo/atualizar/' + encodeURIComponent(id_grupo_selecionado);
        method = 'PUT';
    }

    // Requisição AJAX para salvar o grupo
    $.ajax({
        type: method,
        method: method,
        url: url,
        contentType: 'application/json',
        data: JSON.stringify(dados_grupo),
        success: function (resp) {
            window.alerta.sucesso(resp.mensagem);
            tb_grupo.ajax.reload( () => {
                tb_grupo.searchPanes.clearSelections();
                tb_grupo.searchPanes.rebuildPane();
            });
            // Fechar o modal
            const modalElement = document.getElementById('modal_grupo');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();
        },
        error: function (xhr) {
            window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
        }
    });
}

// Função para excluir um grupo
Excluir_Grupo = function (id_Grupo) {
    // Confirmar exclusão
    if (!confirm('Tem certeza que deseja excluir este grupo?')) {
        return;
    }

    // Requisição AJAX para excluir o grupo
    $.ajax({
        type: 'delete',
        url: '/api/rh/grupo/deletar/' + encodeURIComponent(id_Grupo),
        contentType: 'application/json',
        success: function (resp) {
            window.alerta.sucesso('Grupo excluído com sucesso.');
            tb_grupo.ajax.reload();
        },
        error: function (xhr) {
            window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
        }
    });
}



// Interaçao dos botões da tabela de grupos

// botão Editar grupo
$("#tb_grupo").on('click', '.btn-abrir-modal-editar-grupo', function () {
    const dados_grupo = tb_grupo.row($(this).closest('tr')).data();
    Abrir_Modal_Editar_Grupo(dados_grupo);
});

// botão Salvar grupo
$("#modal_grupo").on('click', '#btn_modal_grupo_salvar', function () {
    Salvar_Grupo();
});

// botão Excluir grupo
$("#modal_grupo").on('click', '#btn_modal_grupo_excluir', function () {
    // id_grupo_selecionado é definido ao abrir o modal de edição
    Excluir_Grupo(id_grupo_selecionado);
});

// botão de imprimir modal grupo
$("#modal_grupo").on('click', '#btn_modal_grupo_imprimir', function () {
    const modal_grupo = document.getElementById('modal_grupo');
    window.impressao.imprimirConteudoModal(modal_grupo);
});

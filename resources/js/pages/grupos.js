// JS específico para a página de Grupos
// Este arquivo é importado pelo bundle principal (resources/js/app.js)

$(function () {

    // variáveis globais para controle de modais e DataTables
    let dataTable_Permissoes_Modal = null;
    let grupos_id_Selecionado = null;

    // se a tabela não existir nesta página, aborta
    if (!document.querySelector('#dataTable_Grupos')) return;

    $('#btnNovoGrupo').off('click').on('click', function () {
        grupos_id_Selecionado = null; // resetar variável global
        $('#modalGrupoTitulo').text('Novo grupo');
        $('#formGrupo')[0].reset();
        $('#btnExcluirGrupo').addClass('d-none');
        new bootstrap.Modal(document.getElementById('modalGrupo')).show();
        carregarCategorias(); // carregar categorias no select
    });

    // inicializar DataTable principal de grupos
    const table = $('#dataTable_Grupos').DataTable({
        ajax: {
            method: 'POST',
            url: '/rh/api/grupos/dados', // rota para buscar os dados
            data: function (requestData) {
                requestData.fn = 'listar-grupos';
                return requestData;
            },
            dataSrc: function (json) {
                try {
                    if (!json) {
                        window.alerta.erroPermissoes('Acesso negado');
                        return [];
                    }
                    if (Array.isArray(json.data)) return json.data;
                    if (Array.isArray(json)) return json;
                    return [];
                } catch (e) {
                    window.alerta.erroPermissoes(String(e));
                    return [];
                }
            },
            error: function (xhr, status, error) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            }
        },
        columns: [
            { data: 'nome_Grupo', title: 'Nome do Grupo' },
            { data: 'descricao_Grupo', title: 'Descrição' },
            { data: 'nome_Categoria', title: 'Categoria' },
            {
                data: null,
                orderable: false,
                render: function (row) {
                    return `
                        <button class="btn btn-sm btn-primary btn-edit" data-id="${row.id_Grupo}">Editar</button>
                        <button class="btn btn-sm btn-info btn-permissoes" data-id="${row.id_Grupo}">Permissões</button>
                    `;
                }
            },
            // coluna oculta para filtros
            { data: 'permissoes_XML', title: 'Permissões (XML)', visible: false }
        ]
    });

    // handler para editar grupo
    $('#dataTable_Grupos').off('click', '.btn-edit').on('click', '.btn-edit', function () {
        $('#modalGrupoTitulo').text('Editar grupo');

        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        grupos_id_Selecionado = rowData.id_Grupo;

        $('#nome_Grupo_Modal').val(rowData.nome_Grupo);
        $('#descricao_Grupo_Modal').val(rowData.descricao_Grupo);
        $('#categoria_id_Modal').val(rowData.categoria_id);
        $('#btnExcluirGrupo').removeClass('d-none');

        new bootstrap.Modal(document.getElementById('modalGrupo')).show();
        carregarCategorias(); // carregar categorias no select
    });

    // handler para gerenciar permissões do grupo
    $('#dataTable_Grupos').off('click', '.btn-permissoes').on('click', '.btn-permissoes', function () {
        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        grupos_id_Selecionado = rowData.id_Grupo;

        // abrir modal
        const modal = new bootstrap.Modal(document.getElementById('modalPermissoes'));
        modal.show();

        $('#modalPermissoesTitulo').text('Permissões do grupo: ' + (rowData?.nome_Grupo || '??'));

        // inicializar ou recarregar DataTable de permissões
        if (!dataTable_Permissoes_Modal) {
            dataTable_Permissoes_Modal = $('#dataTable_Permissoes_Modal').DataTable({
                ajax: {
                    method: 'POST',
                    url: '/rh/api/permissoes/dados',
                    data: function (requestData) {
                        requestData.grupo_id = grupos_id_Selecionado; // enviar grupo_id ao invés de usuario_id
                        requestData.fn = 'btn-permissoes-grupo';
                        requestData.order_by = 'CASE WHEN rgp.id_rel_grupo_permissao IS NOT NULL THEN 1 ELSE 0 END, p.cod_permissao';
                        return requestData;
                    },
                    dataSrc: function (json) {
                        try {
                            if (!json) {
                                window.alerta.erroPermissoes('Acesso negado');
                                return [];
                            }
                            if (Array.isArray(json.data)) return json.data;
                            if (Array.isArray(json)) return json;
                            return [];
                        } catch (e) {
                            window.alerta.erroPermissoes({ mensagem: String(e) });
                            return [];
                        }
                    },
                    error: function (xhr, status, error) {
                        window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
                    }
                },
                columns: [
                    { data: 'cod_permissao', title: 'Código' },
                    { data: 'descricao_permissao', title: 'Descrição' },
                    {
                        data: 'id_rel_grupo_permissao',
                        render: function (data, type, row) {
                            if (row.id_rel_grupo_permissao) {
                                return `<button class="btn btn-sm btn-danger btn-permissao-toggle" data-id="${row.id_rel_grupo_permissao}" data-action="remover">Remover</button>`;
                            } else {
                                return `<button class="btn btn-sm btn-success btn-permissao-toggle" data-id="${row.id_permissao}" data-action="adicionar">Adicionar</button>`;
                            }
                        }
                    }
                ]
            });
        } else {
            // limpar e recarregar tabela de permissões
            $('#dataTable_Permissoes_Modal').DataTable().clear().draw();
            dataTable_Permissoes_Modal.ajax.reload(null, false);
            dataTable_Permissoes_Modal.columns.adjust().draw();
        }
    });

    // handler para adicionar/remover permissões do grupo
    $('#dataTable_Permissoes_Modal').off('click', '.btn-permissao-toggle').on('click', '.btn-permissao-toggle', function () {
        const tr = $(this).closest('tr');
        const rowData = dataTable_Permissoes_Modal.row(tr).data();
        const grupo_id = grupos_id_Selecionado;
        const id_rel_grupo_permissao = rowData.id_rel_grupo_permissao;
        const permissao_id = rowData.id_permissao;
        const action = $(this).data('action');

        const $btn = $(this);
        $btn.prop('disabled', true).text('...');

        if (action === 'adicionar') {
            // adicionar permissão ao grupo
            $.ajax({
                url: '/rh/api/grupo/permissao/adicionar',
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                data: {
                    grupo_id: grupo_id,
                    permissao_id: permissao_id
                },
                success: function (resp) {
                    if (resp.status) {
                        window.alerta.sucesso(resp.mensagem || 'Permissão adicionada com sucesso!');
                        dataTable_Permissoes_Modal.ajax.reload(null, false);
                        table.ajax.reload(null, false); // atualizar tabela principal
                    } else {
                        window.alerta.erro(resp.mensagem || 'Erro ao adicionar permissão');
                        $btn.prop('disabled', false).text('Adicionar');
                    }
                },
                error: function (xhr) {
                    window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
                    $btn.prop('disabled', false).text('Adicionar');
                }
            });
        } else if (action === 'remover') {
            // remover permissão do grupo
            $.ajax({
                url: '/rh/api/grupo/permissao/remover/' + encodeURIComponent(id_rel_grupo_permissao),
                method: 'DELETE',
                type: 'POST',
                dataType: 'json',
                success: function (resp) {
                    if (resp.status) {
                        window.alerta.sucesso(resp.mensagem || 'Permissão removida com sucesso!');
                        dataTable_Permissoes_Modal.ajax.reload(null, false);
                        table.ajax.reload(null, false); // atualizar tabela principal
                    } else {
                        window.alerta.erro(resp.mensagem || 'Erro ao remover permissão');
                        $btn.prop('disabled', false).text('Remover');
                    }
                },
                error: function (xhr) {
                    window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
                    $btn.prop('disabled', false).text('Remover');
                }
            });
        }
    });

    // handler para excluir grupo
    $('#btnExcluirGrupo').off('click').on('click', function (e) {
        e.preventDefault();
        if (!grupos_id_Selecionado) {
            window.alerta.erro('Nenhum grupo selecionado');
            return;
        }

        if (!confirm('Deseja realmente excluir este grupo? Esta ação é irreversível (soft-delete).')) {
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('Excluindo...');

        $.ajax({
            url: '/rh/api/grupo/deletar/' + encodeURIComponent(grupos_id_Selecionado),
            method: 'DELETE',
            type: 'POST',
            dataType: 'json',
            success: function (resp) {
                if (resp.status) {
                    window.alerta.sucesso(resp.mensagem || 'Grupo excluído com sucesso!');
                    table.ajax.reload(null, false);
                    bootstrap.Modal.getInstance(document.getElementById('modalGrupo')).hide();
                } else {
                    window.alerta.erro(resp.mensagem || 'Erro ao excluir grupo');
                }
            },
            error: function (xhr) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            },
            complete: function () {
                $btn.prop('disabled', false).text('Excluir');
            }
        });
    });

    // handler para salvar grupo (criar/editar)
    $('#formGrupo').on('submit', function (e) {
        e.preventDefault();

        const formData = {
            nome_Grupo: $('#nome_Grupo_Modal').val(),
            descricao_Grupo: $('#descricao_Grupo_Modal').val(),
            categoria_id: $('#categoria_id_Modal').val() || null
        };

        const isEdit = grupos_id_Selecionado !== null;
        const url = isEdit ? '/rh/api/grupo/atualizar/' + encodeURIComponent(grupos_id_Selecionado) : '/rh/api/grupo/cadastrar';
        const method = isEdit ? 'PUT' : 'POST';
        const type = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            dataType: 'json',
            data: formData,
            success: function (resp) {
                if (resp.status) {
                    window.alerta.sucesso(resp.mensagem || 'Grupo salvo com sucesso!');
                    table.ajax.reload(null, false);
                    bootstrap.Modal.getInstance(document.getElementById('modalGrupo')).hide();
                } else {
                    window.alerta.erro(resp.mensagem || 'Erro ao salvar grupo');
                }
            },
            error: function (xhr) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            }
        });
    });

    // resetar grupos_id_Selecionado quando modais fecharem
    $('#modalGrupo, #modalPermissoes').on('hidden.bs.modal', function () {
        grupos_id_Selecionado = null;

        // cleanup: destruir DataTable de permissões para evitar sobreposição
        try {
            if (dataTable_Permissoes_Modal) {
                dataTable_Permissoes_Modal.destroy();
                dataTable_Permissoes_Modal = null;
                $('#dataTable_Permissoes_Modal').empty();
            }
        } catch (e) { }
    });

    // função auxiliar para carregar categorias no select
    function carregarCategorias() {
        $.ajax({
            url: '/rh/api/categorias/dados',
            method: 'POST',
            dataType: 'json',
            success: function (resp) {
                const $select = $('#categoria_id_Modal');
                $select.empty().append('<option value="">Selecione uma categoria</option>');

                if (resp.data && Array.isArray(resp.data)) {
                    resp.data.forEach(function (categoria) {
                        $select.append(`<option value="${categoria.id_categoria}">${categoria.nome_Categoria}</option>`);
                    });
                }
            },
            error: function (xhr, status, error) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            }
        });
    }
});

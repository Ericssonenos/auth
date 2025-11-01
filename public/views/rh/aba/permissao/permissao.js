// DataTable de Permissões
let tb_permissoes = null;

$(function () {
    const Carregar_Tb_Permissoes = function () {
        if (tb_permissoes) {
            return;
        }

        tb_permissoes = $('#tb_permissao').DataTable({
            ajax: {
                type: 'POST',
                url: '/api/rh/permissao/dados',
                data: function (requestData) {
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
                error: function (xhr) {
                    window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
                }
            },
            columns: [
                {
                    data: function (row) {
                        return row.descricao_permissao || row.descricao || '';
                    },
                    title: 'Descrição',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: function (row) {
                        return row.cod_permissao || row.nome_permissao || '';
                    },
                    title: 'Código',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },

                {
                    data: null,
                    title: 'Ações ',
                    className: 'text-center',
                    orderable: false,

                    render: function () {
                        return (
                            '<div class="d-flex flex-wrap justify-content-center gap-1">' +
                            '<button type="button" class="btn btn-sm btn-primary btn-abrir-modal-editar-permissao" title="Editar"><i class="bi bi-pencil"></i> Editar</button>' +
                            '<button type="button" class="btn btn-sm btn-secondary btn-abrir-modal-permissao-grupo" title="Grupos"><i class="bi bi-people"></i> Grupos</button>' +
                            '<button type="button" class="btn btn-sm btn-info btn-abrir-modal-permissao-usuario" title="Usuários"><i class="bi bi-person-check"></i> Usuários</button>' +
                            '</div>'
                        );
                    }
                },
                {
                    data: function (row) {
                        return row.id_permissao || row.id_Permissao || null;
                    },
                    visible: false
                }
            ],
            dom: "<''<'d-flex justify-content-between flex-wrap'P>>" +
                "<'d-flex justify-content-between flex-wrap'<''f><''B>>" +
                "<''<''t>>" +
                "<'d-flex justify-content-between flex-wrap'<''l><''i><''p>>",
            buttons: [
                { extend: 'copy', titleAttr: 'Copiar', text: '<i class="bi bi-copy"></i>', className: 'btn btn-outline-dark', exportOptions: { columns: ':visible' } },
                { extend: 'excel', titleAttr: 'Exportar Excel', text: '<i class="bi bi-filetype-xls"></i>', className: 'btn btn-outline-success', exportOptions: { columns: ':visible' } },
                { extend: 'csv', titleAttr: 'Exportar CSV', text: '<i class="bi bi-filetype-csv"></i>', className: 'btn btn-outline-secondary', exportOptions: { columns: ':visible' } },
                { extend: 'pdf', titleAttr: 'Exportar PDF', text: '<i class="bi bi-file-earmark-pdf"></i>', className: 'btn btn-outline-danger', exportOptions: { columns: ':visible' } },
                { extend: 'print', titleAttr: 'Imprimir', text: '<i class="bi bi-printer"></i>', className: 'btn btn-outline-warning', exportOptions: { columns: ':visible' } },
                { extend: 'spacer', style: 'bar' },
                {
                    text: '<i class="bi bi-arrow-clockwise"></i>',
                    titleAttr: 'Atualizar Lista',
                    className: 'btn btn-warning',
                    action: function () {
                        tb_permissoes.ajax.reload(function () {
                            if (tb_permissoes.searchPanes && typeof tb_permissoes.searchPanes.clearSelections === 'function') {
                                tb_permissoes.searchPanes.clearSelections();
                            }
                            if (tb_permissoes.searchPanes && typeof tb_permissoes.searchPanes.rebuildPane === 'function') {
                                tb_permissoes.searchPanes.rebuildPane();
                            }
                        }, false);
                    }
                },
                { extend: 'spacer', style: 'bar' },
                {
                    text: '<i class="bi bi-shield-plus"></i>',
                    titleAttr: 'Nova Permissão',
                    className: 'btn btn-success',
                    action: function () {
                        if (typeof Abrir_Modal_Nova_Permissao === 'function') {
                            Abrir_Modal_Nova_Permissao();
                        }
                    }
                },
                { extend: 'pageLength', titleAttr: 'Linhas', text: '<i class="bi bi-list-ol"></i>', className: 'btn btn-info' },
                { extend: 'colvis', titleAttr: 'Visibilidade de colunas', text: '<i class="bi bi-eye"></i>', className: 'btn btn-primary' }
            ],
            responsive: true,
            colReorder: true,
            select: true,
            scroller: true,
            stateSave: true,
            processing: true,
            serverSide: false,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Todos']],
            pageLength: 10,
            pagingType: 'simple_numbers',
            columnDefs: [
                {
                    targets: 'no-sort',
                    orderable: false
                }
            ],
            searchPanes: {
                threshold: 0,
                dtOpts: {
                    paging: true,
                    searching: true
                },
                cascadePanes: true,
                viewTotal: true,
                initCollapsed: true,
                emptyMessage: 'Sem opções',
                controls: true
            }
        });
    };

    Carregar_Tb_Permissoes();
});

// DataTable de usuários
let tb_usuario = null;
let id_usuario_selecionado = null; // variável global para armazenar o ID do usuário selecionado


// inicializar DataTable de usuários ao carregar a aba
$(function () {

    const Carregar_Tb_Usuarios = function () {

        if (!tb_usuario) {
            tb_usuario = $('#tb_usuario').DataTable({
                // AJAX (note usar 'type' para compatibilidade com DataTables)
                ajax: {
                    type: 'POST',
                    url: '/api/rh/usuarios/dados',
                    contentType: 'application/json',
                    data: function (d) {
                        // d.fn = 'listar-usuarios'; // se precisar enviar algo extra
                        return JSON.stringify(d);
                    },
                    dataSrc: function (json) {
                        try {
                            if (!json) { window.alerta.erroPermissoes('Acesso negado'); return []; }
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

                // COLUNAS / DEFINIÇÕES
                columns: [

                    {
                        data: 'nome_Completo'
                        , title: 'Nome'
                        , orderable: true
                        , className: 'text-start'
                        , searchPanes: { show: true }
                    },
                    {
                        data: 'dat_criado_em',
                        title: 'Data de Criação',
                        orderable: true,
                        className: 'text-start',
                        searchPanes: { show: true }
                    },
                    {
                        data: 'email',
                        title: 'Email ',
                        orderable: true,
                        searchPanes: { show: true }
                    },

                    {
                        data: null,
                        title: 'Ações <i class="bi bi-gear"></i>',
                        className: 'text-center',
                        orderable: false,
                        render: function (row) {
                            return `
                                <button class="btn btn-sm btn-primary btn-abrir-modal-editar-usuario" title="Editar"><i class="bi bi-person-gear"></i>Editar</button>
                                <button class="btn btn-sm btn-secondary btn-abrir-modal-tb-grupo" title="Grupo"><i class="bi bi-person-check"></i>Grupo</button>
                                <button class="btn btn-sm btn-info btn-abrir-modal-tb-permissoes" title="Permissões"><i class="bi bi-shield-lock"></i>Permissões</button>
                            `;
                        }
                    },

                ],

                // 'B' = Buttons, 'l' = length, 'f' = filter, 't' = table, 'i' = info, 'p' = pagination
                dom: "<''<'d-flex justify-content-between'P>>" +
                    "<'d-flex justify-content-between'<''f><''B>>" +
                    "<''<''t>>" +
                    "<'d-flex justify-content-between'<''l><''i><''p>>",
                // EXTENSÕES / PLUGINS
                buttons: [
                    {
                        extend: 'copy',
                        titleAttr: 'Copiar para área de transferência',
                        text: '<i class="bi bi-copy"></i>',
                        className: 'btn btn-outline-dark',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'excel',
                        titleAttr: 'Exportar para Excel',
                        text: '<i class="bi bi-filetype-xls"></i>',
                        className: 'btn btn-outline-success',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'csv',
                        titleAttr: 'Exportar para CSV',
                        text: '<i class="bi bi-filetype-csv"></i>',
                        className: 'btn btn-outline-secondary',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'pdf',
                        titleAttr: 'Exportar para PDF',
                        text: '<i class="bi bi-file-earmark-pdf"></i>',
                        className: 'btn btn-outline-danger',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'print',
                        titleAttr: 'Imprimir',
                        text: '<i class="bi bi-printer"></i>',
                        className: 'btn btn-outline-warning',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'spacer',
                        style: 'bar'
                    },
                    {
                        extend: 'colvis',
                        titleAttr: 'Visibilidade de colunas',
                        text: '<i class="bi bi-eye"></i>',
                        className: 'btn btn-primary',

                    },
                    {
                        extend: 'pageLength',
                        titleAttr: 'Linhas',
                        text: '<i class="bi bi-list-ol"></i>',
                        className: 'btn btn-info',
                    },
                    // criar um botão para atualizar o painel
                    {
                        text: '<i class="bi bi-arrow-clockwise"></i>',
                        titleAttr: 'Atualizar Filtros',
                        className: 'btn btn-warning',
                        action: function () {
                            tb_usuario.ajax.reload(() => {
                                tb_usuario.searchPanes.clearSelections();
                                tb_usuario.searchPanes.rebuildPane();
                            }, false);
                        }
                    },
                    { extend: 'spacer', style: 'bar' },
                    {
                        text: '<i class="bi bi-person-fill-add"></i>',
                        titleAttr: 'Novo Usuário',
                        className: 'btn btn-success',
                        action: function () {
                            Abrir_Modal_Novo_Usuario();
                        }
                    }

                ],
                responsive: true,     // adapta colunas para telas pequenas
                //fixedHeader: true,  // cabeçalho fixo ao rolar a página
                colReorder: true,      // arrastar e reordenar colunas

                select: true,          // seleção de linhas/colunas
                scroller: true,        // virtual scroll para grandes datasets
                stateSave: true,       // salva estado (página, order, coluna visível)
                processing: true,      // mostra indicador de processamento

                // PAGINAÇÃO / TAMANHO / ORDENAÇÃO
                serverSide: false,     // true se a paginação/filtragem for no servidor
                //autoWidth: true,
                lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todos"]],
                pageLength: 10,
                pagingType: 'simple_numbers',
                columnDefs: [
                    {
                        targets: 'no-sort',
                        orderable: false
                    }
                ],
                // HOOKS / CALLBACKS
                createdRow: function (row, data, dataIndex) {
                },

                drawCallback: function (settings) {
                },
                searchPanes: {
                    threshold: 0,             // mostra todas as opções
                    dtOpts: {
                        paging: true,
                        searching: true,
                    },
                    cascadePanes: true,       // panes “dependem” uns dos outros
                    viewTotal: true,          // mostra contadores
                    initCollapsed: true,     // abre visível
                    emptyMessage: 'Sem opções',
                    controls: true,           // botões limpar/colapsar
                },




            });
        }
    }

    // carregar usuários ao abrir a aba
    $('a[data-tab="usuarios"]').on('shown.bs.tab', Carregar_Tb_Usuarios);
    // carregar usuários se a aba já estiver ativa ao carregar a página
    if ($('a[data-tab="usuarios"]').hasClass('active')) {
        Carregar_Tb_Usuarios();
    }


});

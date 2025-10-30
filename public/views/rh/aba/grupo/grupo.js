// DataTable de Grupos
let tb_grupo = null;
// variável global para armazenar o ID do grupo selecionado
let id_grupo_selecionado = null;

// inicializar DataTable de grupos ao carregar a aba
$(function () {

    const Carregar_Tb_Grupos = function () {

        if (!tb_grupo) {
            tb_grupo = $('#tb_grupo').DataTable({
                ajax: {
                    type: 'POST',
                    url: '/api/rh/grupo/dados',
                    data: function (requestData) {
                       // requestData.fn = 'lista-grupo'; // se precisar enviar algo extra
                        return requestData;
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
                        data: 'nome_grupo'
                        , title: 'Nome do Grupo'
                        , orderable: true
                        , className: 'text-start'
                        , searchPanes: { show: true }
                    },
                    {
                        data: 'descricao_grupo',
                        title: 'Descrição do Grupo',
                        orderable: true,
                        className: 'text-start',
                        searchPanes: { show: true }
                    },
                    {
                        data: 'nome_categoria',
                        title: 'Nome da Categoria',
                        orderable: true,
                        className: 'text-start',
                        searchPanes: { show: true }
                    },
                    {
                        data: null,
                        title: 'Ações <i class="bi bi-gear"></i>',
                        className: 'text-center',
                        orderable: false,
                        render: function (row) {
                            return `
                                <button class="btn btn-sm btn-primary btn-abrir-modal-editar-grupo" title="Editar"><i class="bi bi-people"></i></i>Editar</button>
                                <button class="btn btn-sm btn-secondary btn-abrir-modal-tb-usuario" title="Usuários"><i class="bi bi-person-check"></i>Usuários</button>
                                <button class="btn btn-sm btn-info aba-usuario-tb-permissao" title="Permissões"><i class="bi bi-shield-lock"></i>Permissões</button>
                            `;
                        }
                    },
                    {
                        data: 'permissoes_xml',
                        title: 'Permissões XML',
                        orderable: true,
                        className: 'text-start',
                        searchPanes: { show: true },
                        visible: false
                    },
                ],
                // 'B' = Buttons, 'l' = length, 'f' = filter, 't' = table, 'i' = info, 'p' = pagination
                dom: "<''<'d-flex justify-content-between flex-wrap'P>>" +
                    "<'d-flex justify-content-between flex-wrap'<''f><''B>>" +
                    "<''<''t>>" +
                    "<'d-flex justify-content-between flex-wrap'<''l><''i><''p>>",
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
                            tb_grupo.ajax.reload(() => {
                                tb_grupo.searchPanes.clearSelections();
                                tb_grupo.searchPanes.rebuildPane();
                            }, false);
                        }
                    },
                    { extend: 'spacer', style: 'bar' },
                    {
                        text: '<i class="bi bi-person-fill-add"></i>',
                        titleAttr: 'Novo Usuário',
                        className: 'btn btn-success',
                        action: function () {
                            Abrir_Modal_Novo_Grupo()
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
    };

    // carregar a tabela de grupos ao abrir a aba
    $('a[data-tab="grupo"]').on('shown.bs.tab', function () {
        Carregar_Tb_Grupos();
    });


    // carregar a tabela de grupos se a aba já estiver ativa ao carregar a página
    if ($('a[data-tab="grupo"]').hasClass('active')) {
        Carregar_Tb_Grupos();
    }
});

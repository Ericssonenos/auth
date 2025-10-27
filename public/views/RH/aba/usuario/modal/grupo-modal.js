

// gerenciamento de grupos: inicializa DataTable de grupos + subtabelas de permissões por grupo
let tb_modal_usuario_permissao = null;
let tb_modal_usuario_grupo_permissoes = {}; // cache de DataTables por id_Grupo

// abrir Tabelas - de grupos no modal
$('#tb_usuario').off('click', '.btn-abrir-modal-tb-grupo').on('click', '.btn-abrir-modal-tb-grupo', function () {

    // obter dados da linha selecionada
    const $tr = $(this).closest('tr');
    const rowData = tb_usuario.row($tr).data();

    // atualizar título do modal com o email do usuário
    $('#modalGruposTitulo').text('Grupos do usuário: ' + (rowData?.email || '??'));

    // atualizar variável global
    id_usuario_selecionado = rowData.id_Usuario;


    // inicializar ou recarregar DataTable de grupos
    if (!tb_modal_usuario_permissao) {
        tb_modal_usuario_permissao = $('#tb_modal_usuario_permissao').DataTable({
            ajax: {
                method: 'POST',
                url: '/rh/api/grupos/dados',
                data: function (d) {
                    d.usuario_id = rowData.id_Usuario;
                    d.fn = 'btn-abrir-modal-tb-grupo';
                    d.order_by = 'CASE WHEN rug.id_rel_usuario_grupo IS NOT NULL THEN 1 ELSE 0 END, g.nome_Grupo';
                    return d;
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
            columns: [
                { data: 'id_Grupo', title: 'ID' },
                { data: 'nome_Grupo', title: 'Grupo' },
                {
                    data: null,
                    render: function (row) {
                        const assigned = row.id_rel_usuario_grupo;
                        const toggleBtn = assigned
                            ? `<button class="btn btn-sm btn-danger btn-modal-grupo-toggle"  data-action="remover">Remover</button>`
                            : `<button class="btn btn-sm btn-success btn-modal-grupo-toggle"  data-action="adicionar">Adicionar</button>`;
                        const expandBtn = `<button class="btn btn-sm btn-light btn-expand-grupo" >Permissões</button>`;
                        return expandBtn + ' ' + toggleBtn;
                    }
                },
                // coluna oculta para armazenar as permissões em XML usado no filtro
                { data: 'permissoes_Grupo', title: 'Permissões (XML)', visible: false }

            ],
            dom: "<'row'<'col-sm-12 col-md-5'f><'col-sm-12 col-md-7'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'d-flex justify-content-between'<l><i><p>>",
            buttons: [
                {
                    extend: 'copy',
                    titleAttr: 'Copiar para área de transferência',
                    text: '<i class="bi bi-copy"></i>',
                    className: 'btn btn-outline-dark',
                    exportOptions: { columns: ':visible' }//exclui a última coluna (geralmente a de ações como botões)
                },
                {
                    extend: 'excel',
                    titleAttr: 'Exportar para Excel',
                    text: '<i class="bi bi-filetype-xls"></i>',
                    className: 'btn btn-outline-success',
                    exportOptions: { columns: ':visible' }//exclui a última coluna (geralmente a de ações como botões)
                },
                {
                    extend: 'csv',
                    titleAttr: 'Exportar para CSV',
                    text: '<i class="bi bi-filetype-csv"></i>',
                    className: 'btn btn-outline-secondary',
                    exportOptions: { columns: ':visible' } //exclui a última coluna (geralmente a de ações como botões)
                },
                {
                    extend: 'pdf',
                    titleAttr: 'Exportar para PDF',
                    text: '<i class="bi bi-file-earmark-pdf"></i>',
                    className: 'btn btn-outline-danger',
                    exportOptions: { columns: ':visible' } //exclui a última coluna (geralmente a de ações como botões)
                },
                {
                    extend: 'print',
                    titleAttr: 'Imprimir',
                    text: '<i class="bi bi-printer"></i>',
                    className: 'btn btn-outline-warning',
                    exportOptions: { columns: ':visible' } //exclui a última coluna (geralmente a de ações como botões)
                },
                {
                    extend: 'spacer',
                    style: 'bar'
                },
                // criar um botão para atualizar o painel
                {
                    text: '<i class="bi bi-arrow-clockwise"></i>',
                    titleAttr: 'Atualizar Filtros',
                    className: 'btn btn-warning',
                    action: function () {
                        tb_modal_usuario_permissoes.clear().draw();
                        tb_modal_usuario_permissoes.ajax.reload(null, false); // false mantém a página atual
                        tb_modal_usuario_permissoes.columns.adjust().draw();
                    }
                },
                {
                    extend: 'colvis',
                    titleAttr: 'Visibilidade de colunas',
                    text: '<i class="bi bi-eye"></i>',
                    className: 'btn btn-primary',

                }


            ],
            select: true,          // seleção de linhas/colunas
            colReorder: true,      // arrastar e reordenar colunas
            responsive: true,     // adapta colunas para telas pequenas
        });

        // expandir/mostrar subtabela de permissões do grupo
        $('#tb_modal_usuario_permissao tbody').off('click', '.btn-expand-grupo').on('click', '.btn-expand-grupo', function () {

            // obter dados da linha selecionada
            const $btn = $(this);
            const tr = $btn.closest('tr');
            const row = tb_modal_usuario_permissao.row(tr);
            const rowData = row.data();
            const grupo_id = rowData.id_Grupo;

            // alterna child row
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                return;
            }

            // garante id único e evita reuse de elemento antigo
            const childId = 'subPermissoes_' + grupo_id + '_' + Date.now();
            const childHtml = `<div style="padding:10px;"><table id="${childId}" class="table table-sm w-100"></table></div>`;
            row.child(childHtml).show();
            tr.addClass('shown');

            // destruir cache antigo referente ao mesmo grupo (se existir) para evitar sobreposição
            if (tb_modal_usuario_grupo_permissoes[grupo_id]) {
                try { tb_modal_usuario_grupo_permissoes[grupo_id].destroy(); } catch (e) { }
                delete tb_modal_usuario_grupo_permissoes[grupo_id];
            }

            tb_modal_usuario_grupo_permissoes[grupo_id] = $('#' + childId).DataTable({
                ajax: {
                    method: 'POST',
                    url: '/rh/api/permissoes/dados',
                    data: function (requestData) {
                        requestData.grupo_id = grupo_id;
                        requestData.fn = 'btn-expand-grupo';
                        return requestData;
                    },
                    dataSrc: function (json) {
                        if (!json) { window.alerta.erroPermissoes('Erro ao ler permissões do grupo'); return []; }
                        if (Array.isArray(json.data)) return json.data;
                        if (Array.isArray(json)) return json;
                        return [];
                    },
                    error: function (xhr) {
                        window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
                    }
                },
                // ocultar tb_modal_usuario_permissao_length
                lengthChange: false,
                pageLength: 3,
                paging: true,
                searching: false,
                info: false,
                columns: [
                    { data: 'cod_permissao', title: 'Código' },
                    { data: 'descricao_permissao', title: 'Descrição' }
                ]
            });
        });

    } else {
        // recarregar existente, antes fechar child rows e destruir subtabelas para evitar sobreposição
        // fechar todos child rows abertos
        tb_modal_usuario_permissao.rows().every(function () {
            if (this.child.isShown()) {
                this.child.hide();
            }
        });
        // destruir subtabelas cacheadas
        Object.keys(tb_modal_usuario_grupo_permissoes).forEach(k => {
            try { tb_modal_usuario_grupo_permissoes[k].destroy(); } catch (e) { }
            delete tb_modal_usuario_grupo_permissoes[k];
        });

        $('#tb_modal_usuario_permissao').DataTable().clear().draw();
        tb_modal_usuario_permissao.ajax.reload(null, false);
        tb_modal_usuario_permissao.columns.adjust().draw();
    }

    const modalEl = document.getElementById('modalGrupos');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
});




//toggle atribuir ou remover grupo
$('#tb_modal_usuario_permissao').off('click', '.btn-modal-grupo-toggle').on('click', '.btn-modal-grupo-toggle', function () {

    // obter dados da linha selecionada
    const tr = $(this).closest('tr');
    const rowData = tb_modal_usuario_permissao.row(tr).data();
    const grupo_id = rowData.id_Grupo; // id do grupo
    const id_rel_usuario_grupo = rowData.id_rel_usuario_grupo; // id do relacionamento (se existir)

    // desabilitar botão e mostrar spinner
    const $btn = $(this);
    $btn
        .prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

    if (!id_rel_usuario_grupo) {
        $.ajax({
            url: '/rh/api/usuario/grupo/adicionar',
            method: 'POST',
            data: {
                usuario_id: id_usuario_selecionado,
                grupo_id: grupo_id
            },
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.status) {
                    window.alerta.sucesso?.(resp.mensagem || 'Grupo adicionado.');
                    tb_modal_usuario_permissao.ajax.reload(null, false);
                } else {
                    window.alerta.erroPermissoes(resp?.mensagem || 'Erro ao adicionar grupo');
                    $btn.prop('disabled', false).text('Adicionar');
                }
            },
            error: function (xhr) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            }
        });
    } else {
        $.ajax({
            url: '/rh/api/usuario/grupo/remover/' + encodeURIComponent(id_rel_usuario_grupo),
            method: 'DELETE',
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.status) {
                    window.alerta.sucesso?.(resp.mensagem || 'Grupo removido.');
                    tb_modal_usuario_permissao.ajax.reload(null, false);
                } else {
                    window.alerta.erroPermissoes(resp?.mensagem || 'Erro ao remover grupo');
                }

            },
            error: function (xhr) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);

            },
            complete: function () {
                $btn.prop('disabled', false).text('Remover');
            }
        });
    }
});

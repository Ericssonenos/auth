
// abrir modal de permissões
let tb_modal_usuario_permissao = null;

// abrir Tabelas - de permissões no modal
$('#tb_usuario').on('click', '.btn-abrir-modal-tb-permissoes', function () {

    // obter dados da linha selecionada
    const $tr = $(this).closest('tr');
    const rowData = tb_usuario.row($tr).data();

    // atualizar título do modal com o email do usuário
    $('#titulo_modal_usuario_permissao').text('Permissões do usuário: ' + (rowData?.email || '??'));

    // atualizar variável global
    id_usuario_selecionado = rowData.id_Usuario;

    // inicializar ou recarregar DataTable de permissões
    // passar o id_Usuario pelo body da requisição POST
    if (!tb_modal_usuario_permissao) {
        tb_modal_usuario_permissao = $('#tb_modal_usuario_permissao').DataTable({
            ajax: {
                method: 'POST',
                type: 'POST',
                url: '/rh/api/permissoes/dados',
                // enviar parametros dinamicamente a cada requisição
                data: function (requestData) {
                    requestData.usuario_id = rowData.id_Usuario; // variável atualizada antes do reload
                    requestData.fn = 'btn-abrir-modal-tb-permissao';
                    requestData.order_by = 'CASE WHEN rup.id_rel_usuario_permissao IS NOT NULL THEN 1 ELSE 0 END, p.cod_permissao';
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
                error: function (xhr, status, error) {
                    window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
                }
            },
            columns: [
                { data: 'cod_permissao', title: 'Código' },
                { data: 'descricao_permissao', title: 'Descrição' },
                {
                    data: 'id_rel_usuario_permissao',
                    title: 'Ação <i class="bi bi-gear"></i>',
                    className: 'text-center',
                    render: function (data, type, row) {
                        let retorno = '<div class="d-flex align-items-center gap-1">';
                        if (row.ativo_Grupo) {
                            // criar um bolinha pequena com title "Vinculado por grupo"
                            retorno += '<span class="badge bg-success" title="Vinculado por grupo">G</span> ';
                        }
                        if (row.id_rel_usuario_permissao) {
                            retorno
                                += `<button class="btn btn-sm btn-danger btn-modal-permissao-toggle"  data-action="remover">Remover</button>`;
                        } else {
                            retorno += `<button class="btn btn-sm btn-success btn-modal-permissao-toggle"  data-action="adicionar">Adicionar</button>`;
                        }
                        retorno += '</div>';


                        return retorno;
                    }
                }
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
                        tb_modal_usuario_permissao.clear().draw();
                        tb_modal_usuario_permissao.ajax.reload(null, false); // false mantém a página atual
                        tb_modal_usuario_permissao.columns.adjust().draw();
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
        });
    } else {

        // (não é necessário mudar a URL)
        //limpar a tabela de permissões
        tb_modal_usuario_permissao.clear().draw();
        tb_modal_usuario_permissao.ajax.reload(null, false); // false mantém a página atual
        tb_modal_usuario_permissao.columns.adjust().draw();

    }

    // abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modal_usuario_permissao'));
    modal.show();


});


// toggle atribuir ou remover permissão
$('#tb_modal_usuario_permissao').off('click', '.btn-modal-permissao-toggle').on('click', '.btn-modal-permissao-toggle', function () {

    // obter dados da linha selecionada
    const tr = $(this).closest('tr');
    const rowData = tb_modal_usuario_permissao.row(tr).data();


    // desabilitar botão e mostrar spinner
    const $btn = $(this);
    $btn
        .prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

    // obter id da permissão e id do relacionamento
    const id_rel_usuario_permissao = rowData.id_rel_usuario_permissao;
    const permissao_id = rowData.id_permissao;

    // se não existir id_rel_usuario_permissao é porque o usuário não tem a permissão, então adicionar
    if (!id_rel_usuario_permissao) {
        $.ajax({
            url: '/rh/api/usuario/permissao/adicionar',
            type: 'POST',
            data: {
                usuario_id: id_usuario_selecionado,
                permissao_id: permissao_id
            },
            dataType: 'json',
            success: function (resp) {
                if (resp?.status == 201) {
                    window.alerta?.sucesso?.(resp.mensagem);
                    tb_modal_usuario_permissao.ajax.reload(null, false);
                } else {
                    window.alerta?.erro?.(resp.mensagem);
                    $btn.prop('disabled', false).text('Adicionar');
                }
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                    window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoes_necessarias);
                } else {
                    window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem), 'Erro', 7000);
                }
                $btn.prop('disabled', false).text('Adicionar');
            }
        });
    } else {
        // remover usa id_rel_usuario_permissao
        $.ajax({
            url: '/rh/api/usuario/permissao/remover/' + encodeURIComponent(id_rel_usuario_permissao),
            method: 'DELETE',
            dataType: 'json',
            success: function (resp) {
                if (resp?.status == 200) {
                    window.alerta?.sucesso?.(resp.mensagem || 'Permissão removida.');
                    tb_modal_usuario_permissao.ajax.reload(null, false);
                } else {
                    window.alerta?.erro?.(resp.mensagem || 'Erro ao remover permissão.');

                }
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                    window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoes_necessarias);
                } else {
                    window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem), 'Erro', 7000);
                }
                $btn.prop('disabled', false).text('Remover');
            }
        });
    }
});

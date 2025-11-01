// Tabela de grupos vinculados a uma permissao
let tb_modal_permissao_grupo;

// Abre o modal e carrega a lista de grupos relacionados a uma permissao
Carregar_Tb_Modal_Permissao_Grupo = function (dados_permissao = {}) {
    const nomePermissao = dados_permissao.cod_permissao || dados_permissao.nome_permissao || '';
    id_permissao_selecionada = dados_permissao.id_permissao || dados_permissao.id_Permissao || id_permissao_selecionada || null;

    $('#titulo_modal_permissao_grupo').text('Grupos com a permissão: ' + nomePermissao);

    if (!tb_modal_permissao_grupo) {
        tb_modal_permissao_grupo = $('#tb_modal_permissao_grupo').DataTable({
            ajax: {
                type: 'POST',
                url: '/api/rh/grupo/dados',
                contentType: 'application/json',
                data: function (payload) {
                    payload.permissao_id = id_permissao_selecionada;
                    payload.fn = 'fn-permissao-status';
                    return JSON.stringify(payload);
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
                        return row.nome_grupo || row.nomeGrupo || '';
                    },
                    title: 'Grupo',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: function (row) {
                        return row.nome_categoria || row.descricao_grupo || row.descricaoGrupo || '';
                    },
                    title: 'Categoria / Descrição',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: function (row) {
                        return row.id_rel_grupo_permissao || row.id_relGrupoPermissao || null;
                    },
                    title: 'Ação',
                    className: 'text-center',
                    render: function (data, type, row) {
                        let retorno = '<div class="d-flex align-items-center gap-1">';
                        if (row.id_rel_grupo_permissao || row.id_relGrupoPermissao) {
                            retorno += '<button class="btn btn-sm btn-danger btn-modal-grupo-toggle" data-action="remover">Remover</button>';
                        } else {
                            retorno += '<button class="btn btn-sm btn-success btn-modal-grupo-toggle" data-action="adicionar">Adicionar</button>';
                        }
                        retorno += '</div>';
                        return retorno;
                    }
                }
            ],
            dom: "<'row'<'col-sm-12 col-md-5'f><'col-sm-12 col-md-7'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'d-flex justify-content-between'<p>>" +
                "<'d-flex justify-content-between'<i>>",
            buttons: [
                { extend: 'copy', titleAttr: 'Copiar', text: '<i class="bi bi-copy"></i>', className: 'btn btn-outline-dark', exportOptions: { columns: ':visible' } },
                { extend: 'excel', titleAttr: 'Exportar Excel', text: '<i class="bi bi-filetype-xls"></i>', className: 'btn btn-outline-success', exportOptions: { columns: ':visible' } },
                { extend: 'csv', titleAttr: 'Exportar CSV', text: '<i class="bi bi-filetype-csv"></i>', className: 'btn btn-outline-secondary', exportOptions: { columns: ':visible' } },
                { extend: 'pdf', titleAttr: 'Exportar PDF', text: '<i class="bi bi-file-earmark-pdf"></i>', className: 'btn btn-outline-danger', exportOptions: { columns: ':visible' } },
                { extend: 'print', titleAttr: 'Imprimir', text: '<i class="bi bi-printer"></i>', className: 'btn btn-outline-warning', exportOptions: { columns: ':visible' } },
                { extend: 'spacer', style: 'bar' },
                {
                    text: '<i class="bi bi-arrow-clockwise"></i>',
                    titleAttr: 'Atualizar',
                    className: 'btn btn-warning',
                    action: function () {
                        tb_modal_permissao_grupo.clear().draw();
                        tb_modal_permissao_grupo.ajax.reload(null, false);
                        tb_modal_permissao_grupo.columns.adjust().draw();
                    }
                },
                { extend: 'pageLength', titleAttr: 'Linhas', text: '<i class="bi bi-list-ol"></i>', className: 'btn btn-info' },
                { extend: 'colvis', titleAttr: 'Visibilidade de colunas', text: '<i class="bi bi-eye"></i>', className: 'btn btn-primary' }
            ],
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Todos']],
            select: true,
            colReorder: true,
            responsive: true,
            processing: true
        });
    } else {
        tb_modal_permissao_grupo.ajax.reload(null, false);
    }

    const modalElement = document.getElementById('modal_permissao_grupo');
    const modalInstance = new bootstrap.Modal(modalElement);
    modalInstance.show();
};

function Permissao_Atribuir_Grupo(grupo_id, btn) {
    $.ajax({
        type: 'POST',
        url: '/api/rh/grupo/permissao/adicionar',
        data: {
            grupo_id: grupo_id,
            permissao_id: id_permissao_selecionada
        },
        success: function (resposta) {
            if (resposta?.status === 200) {
                window.alerta?.sucesso(resposta.mensagem);
                tb_modal_permissao_grupo.ajax.reload(null, false);
            } else {
                window.alerta.erro(resposta?.mensagem || 'Não foi possível atribuir a permissão.');
                btn.prop('disabled', false).text('Adicionar');
            }
        },
        error: function (xhr) {
            if (xhr.status === 403) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            } else {
                window.alerta.erro('Erro ao atribuir a permissão ao grupo: ' + (xhr.responseJSON?.mensagem || ''));
            }
            btn.prop('disabled', false).text('Adicionar');
        }
    });
}

function Permissao_Remover_Grupo(id_rel_grupo_permissao, btn) {
    $.ajax({
        type: 'DELETE',
        url: '/api/rh/grupo/permissao/remover/' + encodeURIComponent(id_rel_grupo_permissao),
        success: function (resposta) {
            if (resposta?.status === 200) {
                window.alerta?.sucesso(resposta.mensagem);
                tb_modal_permissao_grupo.ajax.reload(null, false);
            } else {
                window.alerta.erro(resposta?.mensagem || 'Não foi possível remover a permissão.');
                btn.prop('disabled', false).text('Remover');
            }
        },
        error: function (xhr) {
            if (xhr.status === 403) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            } else {
                window.alerta.erro('Erro ao remover a permissão do grupo: ' + (xhr.responseJSON?.mensagem || ''));
            }
            btn.prop('disabled', false).text('Remover');
        }
    });
}

$('#tb_permissao').on('click', '.btn-abrir-modal-permissao-grupo', function () {
    let dados_permissao = $(this).data('permissao');
    if (typeof tb_permissoes !== 'undefined' && (!dados_permissao || $.isEmptyObject(dados_permissao))) {
        const $linha = $(this).closest('tr');
        const linhaPrincipal = $linha.hasClass('child') ? $linha.prev() : $linha;
        dados_permissao = tb_permissoes.row(linhaPrincipal).data();
    }
    Carregar_Tb_Modal_Permissao_Grupo(dados_permissao || {});
});

$('#tb_modal_permissao_grupo').on('click', '.btn-modal-grupo-toggle', function () {
    const btn = $(this);
    const dados = tb_modal_permissao_grupo.row($(this).closest('tr')).data();
    btn.prop('disabled', true);

    if (dados.id_rel_grupo_permissao || dados.id_relGrupoPermissao) {
        Permissao_Remover_Grupo(dados.id_rel_grupo_permissao || dados.id_relGrupoPermissao, btn);
    } else {
        const idGrupo = dados.id_grupo || dados.id_Grupo || dados.idGrupo;
        Permissao_Atribuir_Grupo(idGrupo, btn);
    }
});

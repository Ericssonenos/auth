// Tabela de usuarios vinculados a uma permissao
let tb_modal_permissao_usuario;

Carregar_Tb_Modal_Permissao_Usuario = function (dados_permissao = {}) {
    const nomePermissao = dados_permissao.cod_permissao || dados_permissao.nome_permissao || '';
    id_permissao_selecionada = dados_permissao.id_permissao || dados_permissao.id_Permissao || id_permissao_selecionada || null;

    $('#titulo_modal_permissao_usuario').text('Usuários com a permissão: ' + nomePermissao);

    if (!tb_modal_permissao_usuario) {
        tb_modal_permissao_usuario = $('#tb_modal_permissao_usuario').DataTable({
            ajax: {
                type: 'POST',
                url: '/api/rh/usuario/dados',
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
                        return row.nome_completo || row.nomeCompleto || '';
                    },
                    title: 'Nome',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: function (row) {
                        return row.email || row.email_usuario || '';
                    },
                    title: 'E-mail',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: function (row) {
                        return row.id_rel_usuario_permissao || row.id_relUsuarioPermissao || null;
                    },
                    title: 'Ação ',
                    className: 'text-center',
                    render: function (data, type, row) {
                        let retorno = '<div class="d-flex align-items-center gap-1">';
                        if (row.ativo_grupo || row.ativoGrupo) {
                            retorno += '<span class="badge bg-success" title="Vinculado por grupo">G</span> ';
                        }
                        if (row.id_rel_usuario_permissao || row.id_relUsuarioPermissao) {
                            retorno += '<button class="btn btn-sm btn-danger btn-modal-permissao-usuario-toggle" data-action="remover">Remover</button>';
                        } else {
                            retorno += '<button class="btn btn-sm btn-success btn-modal-permissao-usuario-toggle" data-action="adicionar">Adicionar</button>';
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
                        tb_modal_permissao_usuario.clear().draw();
                        tb_modal_permissao_usuario.ajax.reload(null, false);
                        tb_modal_permissao_usuario.columns.adjust().draw();
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
        tb_modal_permissao_usuario.ajax.reload(null, false);
    }

    const modalElement = document.getElementById('modal_permissao_usuario');
    const modalInstance = new bootstrap.Modal(modalElement);
    modalInstance.show();
};

function Permissao_Atribuir_Usuario(usuario_id, btn) {
    $.ajax({
        type: 'POST',
        url: '/api/rh/usuario/permissao/adicionar',
        data: {
            usuario_id: usuario_id,
            permissao_id: id_permissao_selecionada
        },
        success: function (resposta) {
            if (resposta?.status === 200) {
                window.alerta?.sucesso(resposta.mensagem);
                tb_modal_permissao_usuario.ajax.reload(null, false);
            } else {
                window.alerta.erro(resposta?.mensagem || 'Não foi possível atribuir a permissão.');
                btn.prop('disabled', false).text('Adicionar');
            }
        },
        error: function (xhr) {
            if (xhr.status === 403) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            } else {
                window.alerta.erro('Erro ao atribuir a permissão ao usuário: ' + (xhr.responseJSON?.mensagem || ''));
            }
            btn.prop('disabled', false).text('Adicionar');
        }
    });
}

function Permissao_Remover_Usuario(id_rel_usuario_permissao, btn) {
    $.ajax({
        type: 'DELETE',
        url: '/api/rh/usuario/permissao/remover/' + encodeURIComponent(id_rel_usuario_permissao),
        success: function (resposta) {
            if (resposta?.status === 200) {
                window.alerta?.sucesso(resposta.mensagem);
                tb_modal_permissao_usuario.ajax.reload(null, false);
            } else {
                window.alerta.erro(resposta?.mensagem || 'Não foi possível remover a permissão.');
                btn.prop('disabled', false).text('Remover');
            }
        },
        error: function (xhr) {
            if (xhr.status === 403) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            } else {
                window.alerta.erro('Erro ao remover a permissão do usuário: ' + (xhr.responseJSON?.mensagem || ''));
            }
            btn.prop('disabled', false).text('Remover');
        }
    });
}

$('#tb_permissao').on('click', '.btn-abrir-modal-permissao-usuario', function () {
    let dados_permissao = $(this).data('permissao');
    if (typeof tb_permissoes !== 'undefined' && (!dados_permissao || $.isEmptyObject(dados_permissao))) {
        const $linha = $(this).closest('tr');
        const linhaPrincipal = $linha.hasClass('child') ? $linha.prev() : $linha;
        dados_permissao = tb_permissoes.row(linhaPrincipal).data();
    }
    Carregar_Tb_Modal_Permissao_Usuario(dados_permissao || {});
});

$('#tb_modal_permissao_usuario').on('click', '.btn-modal-permissao-usuario-toggle', function () {
    const btn = $(this);
    const dados = tb_modal_permissao_usuario.row($(this).closest('tr')).data();
    btn.prop('disabled', true);

    if (dados.id_rel_usuario_permissao || dados.id_relUsuarioPermissao) {
        Permissao_Remover_Usuario(dados.id_rel_usuario_permissao || dados.id_relUsuarioPermissao, btn);
    } else {
        const idUsuario = dados.id_usuario || dados.id_Usuario || dados.idUsuario;
        Permissao_Atribuir_Usuario(idUsuario, btn);
    }
});

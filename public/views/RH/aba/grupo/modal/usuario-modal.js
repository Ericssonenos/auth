// tabela permissões de grupo
let tb_modal_grupo_usuario;



// abrir Tabelas - de permissões no modal
function Carregar_Tb_Modal_Grupo_usuario(dados_permissoes) {


    // Atualizar o titulo do modal
    $('#titulo_modal_grupo_usuario').text('Permissões do Grupo: ' + (dados_permissoes?.nome_grupo ?? dados_permissoes?.nome_Grupo ?? dados_permissoes?.nomeGrupo ?? ''));

    // Atualizar variável global do id do grupo selecionado
    id_grupo_selecionado = dados_permissoes?.id_grupo ?? dados_permissoes?.id_Grupo ?? dados_permissoes?.idGrupo ?? null;

    // Inicializar DataTable de permissões no modal, se ainda não estiver inicializado
    if (!tb_modal_grupo_usuario) {
        tb_modal_grupo_usuario = $('#tb_modal_grupo_usuario').DataTable({
            ajax: {
                type: 'POST',
                url: '/api/rh/usuario/dados',
                contentType: 'application/json',
                data: function (d) {
                    // Adicionar o ID do grupo selecionado aos dados da requisição
                    d.grupo_id = id_grupo_selecionado;
                    d.fn = 'fn-grupo-status';
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
                    data: function (row) {
                        return row.nome_completo ?? row.nomeCompleto ?? row.nome_completo_usuario ?? '';
                    },
                    title: 'Nome',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: function (row) {
                        return row.email ?? row.email_usuario ?? '';
                    },
                    title: 'E-mail',
                    orderable: true,
                    className: 'text-start',
                    searchPanes: { show: true }
                },
                {
                    data: function (row) {
                        return row.id_rel_usuario_grupo ?? row.id_relUsuarioGrupo ?? row.idRelUsuarioGrupo ?? null;
                    },
                    title: 'Ação <i class="bi bi-gear"></i>',
                    className: 'text-center',
                    render: function (data, type, row) {
                        let retorno = '<div class="d-flex align-items-center gap-1">';
                        if (row.ativo_grupo ?? row.ativo_Grupo ?? row.ativoGrupo) {
                            // criar um bolinha pequena com title "Vinculado por grupo"
                            retorno += '<span class="badge bg-success" title="Vinculado por grupo">G</span> ';
                        }
                        if (row.id_rel_usuario_grupo ?? row.id_relUsuarioGrupo ?? row.idRelUsuarioGrupo) {
                            retorno
                                += `<button class="btn btn-sm btn-danger btn-modal-usuario-toggle"  data-action="remover">Remover</button>`;
                        } else {
                            retorno += `<button class="btn btn-sm btn-success btn-modal-usuario-toggle"  data-action="adicionar">Adicionar</button>`;
                        }
                        retorno += '</div>';


                        return retorno;
                    }
                }
            ], dom: "<'row'<'col-sm-12 col-md-5'f><'col-sm-12 col-md-7'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'d-flex justify-content-between'<p>>" +
                "<'d-flex justify-content-between'<i>>",
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
                {
                    text: '<i class="bi bi-arrow-clockwise"></i>',
                    titleAttr: 'Atualizar Filtros',
                    className: 'btn btn-warning',
                    action: function () {
                        tb_modal_grupo_usuario.clear().draw();
                        tb_modal_grupo_usuario.ajax.reload(null, false); // false mantém a página atual
                        tb_modal_grupo_usuario.columns.adjust().draw();
                    }
                },
                {
                    extend: 'pageLength',
                    titleAttr: 'Linhas',
                    text: '<i class="bi bi-list-ol"></i>',
                    className: 'btn btn-info',
                },
                {
                    extend: 'colvis',
                    titleAttr: 'Visibilidade de colunas',
                    text: '<i class="bi bi-eye"></i>',
                    className: 'btn btn-primary',

                }


            ],
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todos"]],
            select: true,          // seleção de linhas/colunas
            colReorder: true,      // arrastar e reordenar colunas
            responsive: true,      // responsivo
            processing: true,     // mostrar "processando" durante carregamento
            // Outras opções do DataTable, se necessário
        });
    }


}

// Função para Atribuir permissão ao grupo
function Atribuir_usuario_Grupo_Modal(id_usuario, btn) {
    // Lógica para atribuir a permissão ao grupo
    $.ajax({
        type: 'POST',
        url: '/api/rh/usuario/grupo/adicionar',
        data: {
            usuario_id: id_usuario
            , grupo_id: id_grupo_selecionado
        },
        success: function (resposta) {
            if (resposta?.status == 200) {
                window.alerta?.sucesso(resposta.mensagem);
                tb_modal_grupo_usuario.ajax.reload(null, false);
            } else {
                window.alerta.erro(resposta.mensagem);
                btn.prop('disabled', false).text('Adicionar');
            }
        },
        error: function (xhr, status, error) {
            if (xhr.status == 403) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            } else {
                window.alerta.erro('Erro ao atribuir permissão ao grupo: ' + xhr.responseJSON?.mensagem);
            }
            btn.prop('disabled', false).text('Adicionar');
        }
    });
}

// Função para Remover permissão do grupo
function Remover_usuario_Grupo_Modal(id_rel_usuario_grupo, btn) {
    // Lógica para remover a permissão do grupo
    $.ajax({
        type: 'delete',
        url: '/api/rh/usuario/grupo/remover/' + encodeURIComponent(id_rel_usuario_grupo),
        success: function (resposta) {
            if (resposta?.status == 200) {
                window.alerta?.sucesso(resposta.mensagem);
                tb_modal_grupo_usuario.ajax.reload(null, false);
            } else {
                window.alerta.erro(resposta.mensagem);
                btn.prop('disabled', false).text('Remover');
            }
        },
        error: function (xhr, status, error) {
            if (xhr.status == 403) {
                window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            } else {
                window.alerta.erro('Erro ao remover permissão do grupo: ' + xhr.responseJSON?.mensagem);
            }
            btn.prop('disabled', false).text('Remover');
        }
    });
}

// Interaçao dos botões da tabela da tabela grupo

$('#tb_grupo').on('click', '.btn-abrir-modal-tb-usuario', function () {
    console.log('Clicou no botão de permissões do grupo');
    const dados_grupo = tb_grupo.row($(this).closest('tr')).data();
    Carregar_Tb_Modal_Grupo_usuario(dados_grupo);

    // Abrir o modal de permissões
    const modalElement = document.getElementById('modal_grupo_usuario');
    const modalInstance = new bootstrap.Modal(modalElement);
    modalInstance.show();
});

$('#tb_modal_grupo_usuario').on('click', '.btn-modal-usuario-toggle', function () {
    const btn = $(this);
    const dados_usuario = tb_modal_grupo_usuario.row($(this).closest('tr')).data();

    btn.prop('disabled', true);

    // se id_rel_usuario_grupo não existir
    // é porque não exite vinculo, então atribuir
    if (!dados_usuario.id_rel_usuario_grupo) {
        Atribuir_usuario_Grupo_Modal(dados_usuario.id_usuario ?? dados_usuario.id_Usuario ?? dados_usuario.idUsuario, btn);
    } else {
        // se existir, remover o vinculo
        const idRelacao = dados_usuario.id_rel_usuario_grupo ?? dados_usuario.id_relUsuarioGrupo ?? dados_usuario.idRelUsuarioGrupo;
        Remover_usuario_Grupo_Modal(idRelacao, btn);
    }
});

// JS específico para a página de Usuários
// Este arquivo é importado pelo bundle principal (resources/js/app.js)
import $ from 'jquery';

$(function () {
    // se a tabela não existir nesta página, aborta
    if (!document.querySelector('#dataTable_Usuarios')) return;

    $('#btnNovo').on('click', function () {
        $('#modalUsuarioTitulo').text('Novo usuário');
        $('#id_Usuario_Modal').val('');
        $('#formUser')[0].reset();
        $('#btnGerarNovaSenha').addClass('d-none');
        $('#email_Modal').prop('disabled', false);
        $('#divSenhaModal').addClass('d-none');
    // ocultar botão de mostrar senha ao criar novo usuário
    $('#btnMostrarSenha').addClass('d-none');
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    });

    const table = $('#dataTable_Usuarios').DataTable({
        ajax: {
            method: 'POST',
            url: '/rh/api/usuarios/dados', // rota para buscar os dados (deve retornar JSON no formato DataTables)
            // dataSrc como função para validar e lidar com respostas inesperadas
            dataSrc: function (json) {
                try {
                    if (!json) {
                        // resposta vazia
                        window.alerta.erroPermissoes(mensagem = 'Acesso negado');
                        return [];
                    }
                    // se a propriedade data estiver presente e for um array, devolve-a
                    if (Array.isArray(json.data)) return json.data;

                    // se a própria resposta já for um array (endpoint simples), devolve-a
                    if (Array.isArray(json)) return json;

                } catch (e) {
                    window.alerta.erroPermissoes({ mensagem: String(e) });
                    return [];
                }
            },
            error: function (xhr, status, error) {
                window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
            }
        },
        columns: [
            { data: 'nome_Completo', title: 'Nome' },
            { data: 'email', title: 'Email' },
            {
                data: null,
                orderable: false,
                render: function (row) {
                    return `
                        <button class="btn btn-sm btn-primary btn-edit" data-id="${row.id_Usuario}">Editar</button>
                        <button class="btn btn-sm btn-secondary btn-grupo" data-id="${row.id_Usuario}">Atribuir grupo</button>
                        <button class="btn btn-sm btn-info btn-permissoes" data-id="${row.id_Usuario}">Permissões</button>
                    `;
                }
            }
        ]
    });

    $('#dataTable_Usuarios').on('click', '.btn-edit', function () {

        $('#modalUsuarioTitulo').text('Editar usuário');

        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        $('#id_Usuario_Modal').val(rowData.id_Usuario);
        $('#nome_Completo_Modal').val(rowData.nome_Completo);
        $('#email_Modal').val(rowData.email);
        $('#email_Modal').prop('disabled', true);
        $('#btnGerarNovaSenha').removeClass('d-none');

        if (rowData?.senha) {
            $('#senha_Modal').val(rowData.senha);
            $('#divSenhaModal').removeClass('d-none');

            // mostrar botão de visualizar senha
            $('#btnMostrarSenha').removeClass('d-none');

            // retirar typeo password por 5 segundos
                $('#modalUser').on('click', '#btnMostrarSenha', function () {
                    const $senhaInput = $('#senha_Modal');
                    $senhaInput.attr('type', 'text');
                    setTimeout(() => {
                        $senhaInput.attr('type', 'password');
                    }, 5000);
                });

            } else {
            $('#divSenhaModal').addClass('d-none');
            // ocultar botão quando não há senha
            $('#btnMostrarSenha').addClass('d-none');

        }

        new bootstrap.Modal(document.getElementById('modalUser')).show();

    });

    // handler simples e robusto para obter index e dados da linha
    $('#dataTable_Usuarios').on('click', '.btn-grupo', function () {
        // pegar o <tr> mais próximo
        const $tr = $(this).closest('tr');

        // obter índice e dados via DataTables
        const rowIndex = table.row($tr).index();      // índice interno do DataTable
        const rowData = table.row($tr).data();       // objeto com todos os campos da row

        console.log('rowIndex:', rowIndex, 'rowData:', rowData);

        // usar os dados para popular modal
        $('#gruposList').text('Carregando grupos do usuário ' + (rowData?.email || '??') + ' (index ' + rowIndex + ')');



        // mostrar modal
        new bootstrap.Modal(document.getElementById('modalGrupos')).show();
    });


    //
    //
    // abrir modal de permissões
    let dataTable_Permissoes_Modal = null;
    let usuario_Id_Atual = null;
    $('#dataTable_Usuarios').on('click', '.btn-permissoes', function () {
        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
         usuario_Id_Atual = rowData.id_Usuario;
        // abrir modal
        const modal = new bootstrap.Modal(document.getElementById('modalPermissoes'));
        modal.show();



        // inicializar ou recarregar DataTable de permissões
        // passar o id_Usuario pelo body da requisição POST
        if (!dataTable_Permissoes_Modal) {
            dataTable_Permissoes_Modal = $('#dataTable_Permissoes_Modal').DataTable({
                ajax: {
                    method: 'POST',
                    url: '/rh/api/permissoes/dados',
                    // enviar parametros dinamicamente a cada requisição
                    data: function (requestData) {
                        requestData.usuario_id = usuario_Id_Atual; // variável atualizada antes do reload
                        requestData.order_by = 'CASE WHEN rup.id_rel_usuario_permissao IS NOT NULL THEN 1 ELSE 0 END, p.cod_permissao';
                        return requestData;
                    },
                    dataSrc: function (json) {
                        try {
                            if (!json) {
                                window.alerta.erroPermissoes(mensagem = 'Acesso negado');
                                return [];
                            }
                            if (Array.isArray(json.data)) return json.data;
                            if (Array.isArray(json)) return json;
                        } catch (e) {
                            window.alerta.erroPermissoes({ mensagem: String(e) });
                            return [];
                        }
                    },
                    error: function (xhr, status, error) {
                        window.alerta.erroPermissoes(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoesNecessarias);
                    }
                },
                columns: [
                    { data: 'cod_permissao', title: 'Código' },
                    { data: 'descricao_permissao', title: 'Descrição' },
                    {
                        data: 'id_rel_usuario_permissao',
                        render: function (data, type, row) {

                            if (row.id_rel_usuario_permissao) {
                                return `<button class="btn btn-sm btn-danger btn-permissao-toggle" data-id="${row.id_rel_usuario_permissao}" data-action="remover">Remover</button>`;
                            }
                            return `<button class="btn btn-sm btn-success btn-permissao-toggle" data-id="${row.id_permissao}" data-action="adicionar">Adicionar</button>`;
                        }
                    }
                ]
            });
        } else {
            // atualizar variável com o id atual e recarregar (ajax.data() lerá usuario_Id_Atual)
            // (não é necessário mudar a URL)
            dataTable_Permissoes_Modal.ajax.reload(null, false); // false mantém a página atual
            dataTable_Permissoes_Modal.columns.adjust().draw();

        }
    });


    // handler para add/remover permissões dentro do modal
    $('#dataTable_Permissoes_Modal').on('click', '.btn-permissao-toggle', function () {

        const tr = $(this).closest('tr');
        const rowData = dataTable_Permissoes_Modal.row(tr).data();
        const usuario_id = usuario_Id_Atual;
        const id_rel_usuario_permissao = rowData.id_rel_usuario_permissao;
        const permissao_id = rowData.id_permissao;

        const $btn = $(this);
        $btn.prop('disabled', true).text('...');

        if (!id_rel_usuario_permissao) {
            $.ajax({
                url: '/rh/api/usuario/permissao/adicionar',
                method: 'POST',
                data: { usuario_id: usuario_id, permissao_id: permissao_id },
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status) {
                        window.alerta?.sucesso?.(resp.mensagem || 'Permissão adicionada.');
                        dataTable_Permissoes_Modal.ajax.reload(null, false);
                    } else {
                        window.alerta?.erro?.(resp.mensagem || 'Erro ao adicionar permissão.');
                        $btn.prop('disabled', false).text('Adicionar');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 403) {
                        window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
                        return;
                    } else {
                        window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                    }
                }
            });
        } else {
            // remover usa id_rel_usuario_permissao
            $.ajax({
                url: '/rh/api/usuario/permissao/remover/' + encodeURIComponent(id_rel_usuario_permissao),
                method: 'delete',
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status) {
                        window.alerta?.sucesso?.(resp.mensagem || 'Permissão removida.');
                        dataTable_Permissoes_Modal.ajax.reload(null, false);
                    } else {
                        window.alerta?.erro?.(resp.mensagem || 'Erro ao remover permissão.');
                        $btn.prop('disabled', false).text('Remover');
                    }
                },
                error: function (xhr) {
                   if (xhr.status === 403) {
                        window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
                        return;
                    } else {
                        window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                    }
                }
            });
        }
    });


    // onlclik para gera nova senha - chama API e preenche o campo senha_Modal com a senha retornada
    $('#btnGerarNovaSenha').on('click', function () {
        const id = $('#id_Usuario_Modal').val();

        const $btn = $(this);
        $btn.prop('disabled', true).text('Gerando...');

        $.ajax({
            url: '/rh/usuario/' + encodeURIComponent(id) + '/gerar-senha',
            method: 'POST',
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.status && resp.data && resp.data.senha) {
                    // preencher e mostrar campo de senha
                    $('#senha_Modal').val(resp.data.senha);
                    $('#divSenhaModal').removeClass('d-none');

                    // mostrar botão de visualizar senha
                    $('#btnMostrarSenha').removeClass('d-none');

                    // mostrar senha em texto por 8s (comportamento automático existente)
                    const $senhaInput = $('#senha_Modal');
                    $senhaInput.attr('type', 'text');
                    setTimeout(() => {
                        $senhaInput.attr('type', 'password');
                    }, 8000);

                    // feedback curto ao usuário
                    window.alerta?.sucesso?.('Senha temporária gerada com sucesso. Senha temporária: 10 minutos');


                    // refresca a tabela sem fechar o modal
                    table.ajax.reload(null, false); // false para não resetar a paginação
                } else {
                    window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada ao gerar senha.');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.mensagem || xhr.responseJSON?.mensagem || 'Erro ao gerar senha.';
                if (xhr.status === 403) {
                    window.alerta.erroPermissoes?.(msg, xhr.responseJSON?.cod_permissoesNecessarias);
                } else {
                    window.alerta?.erro?.(msg);
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Gerar Nova Senha');
            }
        });
    });


    $('#formUser').on('submit', function (e) {
        e.preventDefault();
        const id = $('#id_Usuario_Modal').val();


        if (!id) {

            const payload = {
                nome_Completo: $('#nome_Completo_Modal').val(),
                email: $('#email_Modal').val(),
            };

            // criar
            $.ajax({
                url: '/rh/api/usuario/cadastrar',
                method: 'POST',
                data: payload,
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status) {
                        // se a API retornar senha (resp.data.senha) preenche e mostra como no fluxo gerar-senha
                        if (resp.data && resp.data.senha) {
                            $('#senha_Modal').val(resp.data.senha);
                            $('#divSenhaModal').removeClass('d-none');

                            // mostrar botão de visualizar senha
                            $('#btnMostrarSenha').removeClass('d-none');

                            // mostrar senha em texto por 8s
                            const $senhaInput = $('#senha_Modal');
                            $senhaInput.attr('type', 'text');
                            setTimeout(() => {
                                $senhaInput.attr('type', 'password');
                            }, 8000);

                            window.alerta?.sucesso?.('Usuário criado. Senha temporária: 10 minutos');
                        } else {
                            window.alerta?.sucesso?.('Usuário criado com sucesso.');
                        }
                        // retirar botão de gerar senha para evitar múltiplos cliques rápidos

                        // se a API retornar lastId, preencher o id no modal para permitir gerar nova senha / edição
                        if (resp.data && resp.data.lastId) {
                            $('#id_Usuario_Modal').val(resp.data.lastId);
                            $('#email_Modal').prop('disabled', true);
                            $('#btnGerarNovaSenha').removeClass('d-none');
                        }

                        table.ajax.reload();
                    } else {
                        window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada do servidor.');
                    }
                },
                error: function (xhr, status, err) {

                    if (xhr.status === 403) {
                        window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoesNecessarias);
                        return;
                    } else {
                        window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                    }
                }
            });
        } else {
            const payload = {
                nome_Completo: $('#nome_Completo_Modal').val(),
            };
            // atualizar (usa mesmo molde de retorno/erros que o POST de cadastro)
            $.ajax({
                url: '/rh/api/usuario/atualizar/' + encodeURIComponent(id),
                method: 'PUT',
                data: payload,
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.status) {
                        // se a API retornar senha (resp.data.senha) preenche e mostra como no fluxo gerar-senha
                        if (resp.data && resp.data.senha) {
                            $('#senha_Modal').val(resp.data.senha);
                            $('#divSenhaModal').removeClass('d-none');

                            // mostrar botão de visualizar senha
                            $('#btnMostrarSenha').removeClass('d-none');

                            // mostrar senha em texto por 8s
                            const $senhaInput = $('#senha_Modal');
                            $senhaInput.attr('type', 'text');
                            setTimeout(() => {
                                $senhaInput.attr('type', 'password');
                            }, 8000);

                            window.alerta?.sucesso?.('Usuário atualizado. Senha temporária: 10 minutos');
                        } else {
                            window.alerta?.sucesso?.('Usuário atualizado com sucesso.');
                        }



                        // fecha modal e atualiza tabela
                        $('#modalUser').modal('hide');
                        table.ajax.reload();
                    } else {
                        window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada do servidor.');
                    }
                },
                error: function (xhr, status, err) {
                    if (xhr.status === 403) {
                        window.alerta.erroPermissoes?.(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoesNecessarias);
                        return;
                    } else {
                        window.alerta?.erro?.('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                    }
                }
            });
        }
    });

    $('btnMostrarSenha').on('click', function () {
        const $senhaInput = $('#senha_Modal');
        $senhaInput.attr('type', 'text');
        setTimeout(() => {
            $senhaInput.attr('type', 'password');
        }, 15000);
    });
});

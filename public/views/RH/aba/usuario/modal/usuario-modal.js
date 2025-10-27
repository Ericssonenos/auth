

// abrir formulario de novo usuário
Abrir_Modal_Novo_Usuario = function () {

    // resetar variável global
    id_usuario_selecionado = null;

    // atualizar título do modal
    $('#titulo_modal_usuario').text('Novo usuário');

    // resetar formulário
    $('#fm_modal_usuario')[0].reset();

    // esconder campo de senha e botão de gerar senha
    $('#btn_modal_usuario_gerar_senha').addClass('d-none');
    $('#div_modal_usuario_senha').addClass('d-none');

    // habilitar campo de email
    $('#email_modal_usuario').prop('disabled', false);

    // ocultar botão de mostrar senha ao criar novo usuário
    $('#btn_modal_usuario_mostrar_senha').addClass('d-none');

    // abrir modal
    new bootstrap.Modal(document.getElementById('modal_usuario')).show();
}

// abrir formulario de edição
$('#tb_usuario')
    .on('click', '.btn-abrir-modal-editar-usuario', function () {

        // obter dados da linha selecionada
        const $tr = $(this).closest('tr');
        const rowData = tb_usuario.row($tr).data();

        // atualizar título do modal
        $('#titulo_modal_usuario').text('Editar usuário');

        // atualizar variável global
        id_usuario_selecionado = rowData.id_Usuario;

        // preencher formulário com os dados do usuário
        $('#nome_completo_modal_usuario').val(rowData.nome_Completo);
        $('#email_modal_usuario').val(rowData.email);

        // desabilitar campo de email
        $('#email_modal_usuario').prop('disabled', true);

        // mostrar botão de gerar senha
        $('#btn_modal_usuario_gerar_senha').removeClass('d-none');

        // preencher campo de senha se disponível
        if (rowData?.senha) {
            $('#senha_modal_usuario').val(rowData.senha);
            $('#div_modal_usuario_senha').removeClass('d-none');

            // mostrar botão de visualizar senha
            $('#btn_modal_usuario_mostrar_senha').removeClass('d-none');

            // retirar typeo password por 5 segundos
            $('#modal_usuario').off('click', '#btn_modal_usuario_mostrar_senha').on('click', '#btn_modal_usuario_mostrar_senha', function () {
                const $senhaInput = $('#senha_modal_usuario');
                $senhaInput.attr('type', 'text');
                setTimeout(() => {
                    $senhaInput.attr('type', 'password');
                }, 5000);
            });

        } else {
            // esconder campo de senha
            $('#div_modal_usuario_senha').addClass('d-none');
            // ocultar botão quando não há senha
            $('#btn_modal_usuario_mostrar_senha').addClass('d-none');

        }

        // abrir modal
        new bootstrap.Modal(document.getElementById('modal_usuario')).show();

    });

// imprimir modal
$('#btn_modal_usuario_imprimir').on('click', function () {
        const modal_usuario = document.getElementById('modal_usuario');
        // função de impressão de modais (se disponível)
        window.impressao?.imprimirConteudoModal(modal_usuario);

    });

// Delete
$('#btn_modal_usuario_excluir').on('click', function () {
    // verificar se há usuário selecionado
    if (!id_usuario_selecionado) {
        window.alerta?.erroPermissoes?.({ mensagem: 'Nenhum usuário selecionado para exclusão.' });
        return;
    }

    // confirmar exclusão
    if (!confirm('Deseja realmente excluir este usuário? Esta ação é irreversível (soft-delete).')) return;

    // desabilitar botão de excluir e mostrar spinner
    const $btn = $(this);
    $btn
        .prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Excluindo...');

    // chamar API de delete
    $.ajax({
        url: '/rh/api/usuario/deletar/' + encodeURIComponent(id_usuario_selecionado),
        method: 'DELETE',
        dataType: 'json',
        success: function (resp) {
            if (resp?.status == 200) {
                // fechar modal
                const elemento_modal = document.getElementById('modal_usuario');
                const modal = bootstrap.Modal.getInstance(elemento_modal);
                if (modal) modal.hide();

                // atualizar tabela de usuários limpando filtros e reconstruindo searchPanes
                tb_usuario.ajax.reload(() => {
                    tb_usuario.searchPanes.clearSelections();
                    tb_usuario.searchPanes.rebuildPane();
                });

                // mostrar mensagem de sucesso
                window.alerta.sucesso?.(resp.mensagem);
            } else {
                // mostrar mensagem de erro
                window.alerta.erroPermissoes?.(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoes_necessarias);
            }
        },
        error: function (xhr, status, error) {
            // mostrar mensagem de erro
            window.alerta.erroPermissoes?.(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoes_necessarias);
        },
        complete: function () {
            // reabilitar botão de excluir
            $btn.prop('disabled', false).text('Excluir');
        }
    });
});


//  gera nova senha
$('#btn_modal_usuario_gerar_senha').on('click', function () {

    // desabilitar o botão para evitar múltiplos cliques rápidos
    const $btn = $(this);
    $btn
        .prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

    $.ajax({
        url: '/rh/usuario/' + encodeURIComponent(id_usuario_selecionado) + '/gerar-senha',
        method: 'POST',
        dataType: 'json',
        success: function (resp) {
            if (resp.status == 200 && resp.data?.senha) {
                // preencher e mostrar campo de senha
                $('#senha_modal_usuario').val(resp.data.senha);

                // mostrar botão de visualizar senha
                $('#btn_modal_usuario_mostrar_senha').removeClass('d-none');
                $('#div_modal_usuario_senha').removeClass('d-none');

                // mostrar senha em texto por 8s (comportamento automático existente)
                const $senhaInput = $('#senha_modal_usuario');
                $senhaInput.attr('type', 'text');
                setTimeout(() => {
                    $senhaInput.attr('type', 'password');
                }, 8000);

                // feedback curto ao usuário
                window.alerta?.sucesso?.('Senha temporária gerada com sucesso. Senha temporária: 10 minutos');


                // atualizar tabela de usuários
                tb_usuario.ajax.reload(() => {
                    tb_usuario.searchPanes.clearSelections();
                    tb_usuario.searchPanes.rebuildPane();
                }, false);

            } else {
                // resposta inesperada
                window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada ao gerar senha.');
            }
        },
        error: function (xhr) {
            // mostrar mensagem de erro
            if (xhr.status === 403) {
                window.alerta.erroPermissoes?.(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
            } else {
                window.alerta?.erro?.(xhr.responseJSON?.mensagem);
            }
        },
        complete: function () {
            // reabilitar o botão após a conclusão da requisição
            $btn.text('Gerar Senha').prop('disabled', false);
        }
    });
});


// Salvar (criar ou atualizar)
$('#btn_modal_usuario_salvar').on('click', function () {


    if (!id_usuario_selecionado) {

        const payload = {
            nome_Completo: $('#nome_completo_modal_usuario').val(),
            email: $('#email_modal_usuario').val(),
        };

        // criar
        $.ajax({
            url: '/rh/api/usuario/cadastrar',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function (resp) {
                if (resp.status == 200) {
                    // se a API retornar senha (resp.data.senha) preenche e mostra como no fluxo gerar-senha
                    if (resp.data && resp.data.senha) {
                        $('#senha_modal_usuario').val(resp.data.senha);
                        $('#div_modal_usuario_senha').removeClass('d-none');

                        // mostrar botão de visualizar senha
                        $('#btn_modal_usuario_mostrar_senha').removeClass('d-none');

                        // mostrar senha em texto por 8s
                        const $senhaInput = $('#senha_modal_usuario');
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
                        id_usuario_selecionado = resp.data.lastId;
                        $('#email_modal_usuario').prop('disabled', true);
                        $('#btn_modal_usuario_gerar_senha').removeClass('d-none');
                    }

                    tb_usuario.ajax.reload(() => {
                        tb_usuario.searchPanes.clearSelections();
                        tb_usuario.searchPanes.rebuildPane();
                    });
                } else {
                    window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada do servidor.');
                }
            },
            error: function (xhr, status, err) {

                if (xhr.status === 403) {
                    window.alerta.erroPermissoes(xhr.responseJSON.mensagem, xhr.responseJSON.cod_permissoes_necessarias);
                    return;
                } else {
                    window.alerta.erro('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                }
                tb_usuario.ajax.reload();
            }
        });
    } else {
        const payload = {
            nome_Completo: $('#nome_completo_modal_usuario').val(),
        };
        // atualizar (usa mesmo molde de retorno/erros que o POST de cadastro)
        $.ajax({
            url: '/rh/api/usuario/atualizar/' + encodeURIComponent(id_usuario_selecionado),
            method: 'PUT',
            data: payload,
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.status) {
                    // se a API retornar senha (resp.data.senha) preenche e mostra como no fluxo gerar-senha
                    if (resp.data && resp.data.senha) {
                        $('#senha_modal_usuario').val(resp.data.senha);
                        $('#div_modal_usuario_senha').removeClass('d-none');

                        // mostrar botão de visualizar senha
                        $('#btn_modal_usuario_mostrar_senha').removeClass('d-none');

                        // mostrar senha em texto por 8s
                        const $senhaInput = $('#senha_modal_usuario');
                        $senhaInput.attr('type', 'text');
                        setTimeout(() => {
                            $senhaInput.attr('type', 'password');
                        }, 8000);

                        window.alerta?.sucesso?.('Usuário atualizado. Senha temporária: 10 minutos');
                    } else {
                        window.alerta?.sucesso?.('Usuário atualizado com sucesso.');
                    }
                    //  atualiza tabela
                    tb_usuario.ajax.reload(() => {
                        tb_usuario.searchPanes.clearSelections();
                        tb_usuario.searchPanes.rebuildPane();
                    });
                } else {
                    window.alerta?.erro?.(resp.mensagem || 'Resposta inesperada do servidor.');
                }
            },
            error: function (xhr, status, err) {
                if (xhr.status === 403) {
                    window.alerta.erroPermissoes?.(xhr.responseJSON?.mensagem, xhr.responseJSON?.cod_permissoes_necessarias);
                    return;
                } else {
                    window.alerta?.erro?.('Erro: ' + (xhr.responseJSON?.mensagem || err), 'Erro', 7000);
                }
            }
        });
    }

});

// mostrar senha por 15 segundos ao clicar no botão
$('#btn_modal_usuario_mostrar_senha').on('click', function () {
    const $senhaInput = $('#senha_modal_usuario');
    $senhaInput.attr('type', 'text');
    setTimeout(() => {
        $senhaInput.attr('type', 'password');
    }, 15000);
});

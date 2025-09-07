// JS específico para a página de Usuários
// Este arquivo é importado pelo bundle principal (resources/js/app.js)
import $ from 'jquery';

$(function () {
    // se a tabela não existir nesta página, aborta
    if (!document.querySelector('#dataTable_Usuarios')) return;


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
                    `;
                }
            }
        ]
    });

    $('#btnNovo').on('click', function () {
        $('#modalUsuarioTitulo').text('Novo usuário');
        $('#id_Usuario_Modal').val('');
        $('#formUser')[0].reset();
        $('#btnGerarNovaSenha').addClass('d-none');
        $('#email_Modal').prop('disabled', false);
        $('#divSenhaModal').addClass('d-none');
        new bootstrap.Modal(document.getElementById('modalUser')).show();
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

            // retirar typeo password por 5 segundos
            const $senhaInput = $('#senha_Modal');
            $senhaInput.attr('type', 'text');
            setTimeout(() => {
                $senhaInput.attr('type', 'password');
            }, 5000);

        } else {
            $('#divSenhaModal').addClass('d-none');

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

                    // mostrar senha em texto por 8s
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
            // atualizar (ainda não implementado no controller)
            $.ajax({
                url: '/rh/usuarios/' + id,
                method: 'PUT',
                data: payload,
                success: function (resp) {
                    if (resp.status) {
                        $('#modalUser').modal('hide');
                        table.ajax.reload();
                    } else {
                        alert('Erro: ' + (resp.mensagem || 'não foi possível atualizar'));
                    }
                },
                error: function (xhr) {
                    mostrarErroAjax(xhr);
                }
            });
        }
    });
});

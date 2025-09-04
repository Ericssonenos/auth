// JS específico para a página de Usuários
// Este arquivo é importado pelo bundle principal (resources/js/app.js)
import $ from 'jquery';

$(function () {
    // se a tabela não existir nesta página, aborta
    if (!document.querySelector('#dataTable_Usuarios')) return;


    const table = $('#dataTable_Usuarios').DataTable({
        ajax: {
            method: 'POST',
            url: '/rh/api/usuarios', // rota para buscar os dados (deve retornar JSON no formato DataTables)
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
        $('#modalUserTitle').text('Novo usuário');
        $('#userId').val('');
        $('#formUser')[0].reset();
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    });

    $('#dataTable_Usuarios').on('click', '.btn-edit', function () {

        $('#modalUserTitle').text('Editar usuário');

        const $tr = $(this).closest('tr');
        const rowData = table.row($tr).data();
        $('#userId').val(rowData.id_Usuario);
        $('#nome_Completo').val(rowData.nome_Completo);
        $('#email').val(rowData.email);
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

    $('#formUser').on('submit', function (e) {
        e.preventDefault();
        const id = $('#userId').val();
        const payload = {
            nome_Completo: $('#nome_Completo').val(),
            email: $('#email').val(),
            senha: $('#senha').val()
        };

        if (!id) {
            // criar
            $.post(window.Routes?.rhUsuariosStore || '/rh/usuarios', payload, function (resp) {
                if (resp.status) {
                    $('#modalUser').modal('hide');
                    table.ajax.reload();
                } else {
                    if (window.alerta && typeof window.alerta.erro === 'function') {
                        window.alerta.erro(resp.message || 'Não foi possível criar', 'Erro', 7000);
                    } else {
                        alert('Erro: ' + (resp.message || 'não foi possível criar'));
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
                        alert('Erro: ' + (resp.message || 'não foi possível atualizar'));
                    }
                },
                error: function (xhr) {
                    mostrarErroAjax(xhr);
                }
            });
        }
    });
});

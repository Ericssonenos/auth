// JS específico para a página de Usuários
// Este arquivo é importado pelo bundle principal (resources/js/app.js)
import $ from 'jquery';

$(function () {
    // se a tabela não existir nesta página, aborta
    if (!document.querySelector('#dataTable_Usuarios')) return;

    function mostrarErroAjax(xhr, status, error) {
        // xhr pode ser um XHR real ou um objeto simples com dados do servidor
        let mensagem = null;
        let statusCode = null;
        try {
            if (Array.isArray(xhr.responseJSON.permissoes_necessarias_para_acesso) && xhr.responseJSON.permissoes_necessarias_para_acesso.length) {
				mensagem = '<br><small><strong>Permissões necessárias:</strong> ' + xhr.responseJSON.permissoes_necessarias_para_acesso.join(', ') + '</small>';
                statusCode = xhr.status || null;
			} else if (xhr && xhr.responseJSON) {
                mensagem = xhr.responseJSON.message || xhr.responseJSON.mensagem || JSON.stringify(xhr.responseJSON);
                statusCode = xhr.status || null;
            } else if (xhr && xhr.responseText) {
                mensagem = xhr.responseText;
                statusCode = xhr.status || null;
            }
        } catch (e) {
            mensagem = null;
        }

        if (!mensagem) {
            mensagem = (xhr && xhr.status) ? ('Erro ' + xhr.status + ': ' + (xhr.statusText || error || 'Erro na requisição')) : ('Erro: ' + (error || status));
        }

        const titulo = (statusCode === 403 || (xhr && xhr.status_autenticacao === 'nao_autenticado')) ? 'Acesso negado' : 'Erro';

        if (window.alerta && typeof window.alerta.erro === 'function') {
            window.alerta.erro(mensagem, titulo, 10000);
        } else {
            alert(mensagem);
        }
    }

    const table = $('#dataTable_Usuarios').DataTable({
        ajax: {
            method: 'POST',
            url: '/rh/api/usuarios', // rota para buscar os dados (deve retornar JSON no formato DataTables)
            // dataSrc como função para validar e lidar com respostas inesperadas
            dataSrc: function (json) {
                try {
                    if (!json) {
                        // resposta vazia
                        mostrarErroAjax({ responseText: 'Resposta vazia do servidor' });
                        return [];
                    }

                    // caso o servidor retorne erro no corpo (ex: { status:false, message: '...' })
                    if (json.status === false || json.error) {
                        // resposta com formato de erro (campo message/mensagem)
                        mostrarErroAjax({ responseJSON: json });
                        return [];
                    }

                    // se a propriedade data estiver presente e for um array, devolve-a
                    if (Array.isArray(json.data)) return json.data;

                    // se a própria resposta já for um array (endpoint simples), devolve-a
                    if (Array.isArray(json)) return json;



                    // fallback: evitar TypeError retornando array vazio
                    return [];
                } catch (e) {
                    mostrarErroAjax({ responseText: String(e) });
                    return [];
                }
            },
            error: function (xhr, status, error) {
                // DataTables delega para aqui em caso de falha
                mostrarErroAjax(xhr, status, error);
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

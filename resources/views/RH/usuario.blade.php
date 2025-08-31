@extends('layout.main')

@section('title', 'Usuários')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Usuários</h1>
            <div>
                <button id="btnNovo" class="btn btn-success">Novo usuário</button>
            </div>
        </div>

        <table id="dataTable_Usuarios" class="table table-striped table-bordered" style="width:100%">
        </table>
    </div>

    <!-- Modal Novo/Editar (reaproveitável) -->
    <div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formUser">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUserTitle">Novo usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="userId" />
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input id="nome_Completo" name="nome_Completo" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input id="email" name="email" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input id="senha" name="senha" class="form-control" type="password" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Atribuir Grupo (placeholder) -->
    <div class="modal fade" id="modalGrupos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atribuir Grupos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="gruposList">Carregando...</div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });
    </script>
    <script>
        $(function() {
            const table = $('#dataTable_Usuarios').DataTable({
                ajax: {
                    method: 'POST',
                    url: '{{ route('usuariosAPI') }}',
                    dataSrc: 'data',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                columns: [

                    {
                        data: 'nome_Completo',
                        title: 'Nome'
                    },
                    {
                        data: 'email',
                        title: 'Email'
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(row) {
                            return `
                                <button class="btn btn-sm btn-primary btn-edit" data-id="${row.id_Usuario}">Editar</button>
                                <button class="btn btn-sm btn-secondary btn-grupo" data-id="${row.id_Usuario}">Atribuir grupo</button>
                            `;
                        }
                    }
                ]
            });

            $('#btnNovo').on('click', function() {
                $('#modalUserTitle').text('Novo usuário');
                $('#userId').val('');
                $('#formUser')[0].reset();
                new bootstrap.Modal(document.getElementById('modalUser')).show();
            });

            $('#dataTable_Usuarios').on('click', '.btn-edit', function() {
                const id_Usuario = $(this).data('id');
                $('#modalUserTitle').text('Editar usuário');
                // carregar dados do usuário (simples) usando a API de listagem atual (ou criar endpoint show)
                $.post(
                    '{{ route('usuariosAPI') }}', {
                        id_Usuario: id_Usuario
                    },
                    function(resp) {
                        const user = resp.data[0];
                        console.log(user);
                        if (user) {
                            $('#userId').val(user.id_Usuario);
                            $('#nome_Completo').val(user.nome_Completo);
                            $('#email').val(user.email);
                            new bootstrap.Modal(document.getElementById('modalUser')).show();
                        }
                    });
            });

            $('#dataTable_Usuarios').on('click', '.btn-grupo', function() {
                const id = $(this).data('id');
                // abrir modal de grupos (placeholder)
                $('#gruposList').text('Carregando grupos do usuário ' + id + '...');
                new bootstrap.Modal(document.getElementById('modalGrupos')).show();
            });

            $('#formUser').on('submit', function(e) {
                e.preventDefault();
                const id = $('#userId').val();
                const payload = {
                    nome_Completo: $('#nome_Completo').val(),
                    email: $('#email').val(),
                    senha: $('#senha').val()
                };

                if (!id) {
                    // criar
                    $.post('{{ route('rh.usuarios.store') }}', payload, function(resp) {
                        if (resp.status) {
                            $('#modalUser').modal('hide');
                            table.ajax.reload();
                        } else {
                            alert('Erro: ' + (resp.message || 'não foi possível criar'));
                        }
                    });
                } else {
                    // atualizar (ainda não implementado no controller)
                    $.ajax({
                        url: '/rh/usuarios/' + id,
                        method: 'PUT',
                        data: payload,
                        success: function(resp) {
                            if (resp.status) {
                                $('#modalUser').modal('hide');
                                table.ajax.reload();
                            } else {
                                alert('Erro: ' + (resp.message ||
                                    'não foi possível atualizar'));
                            }
                        },
                        error: function(xhr) {
                            alert('Erro: ' + xhr.responseText || xhr.statusText);
                        }
                    });
                }
            });
        });
    </script>

@extends('layout.main')

@section('title', 'Usuários')



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


@endsection

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
                        <h5 class="modal-title" id="modalUsuarioTitulo">Novo usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id_Usuario_Modal" />

                        <div class="mb-3">

                            <label class="form-label" for="nome_Completo_Modal">Nome</label>
                            <!-- ter no minimo 4 caracteres -->
                            <input id="nome_Completo_Modal" minlength="4" name="nome_Completo" class="form-control" maxlength="255" required aria-describedby="nome_Completo_Modal_feedback nome_Completo_Modal_help" />
                            <div id="nome_Completo_Modal_help" class="form-text">Mínimo de 4 caracteres.</div>
                            <div id="nome_Completo_Modal_feedback" class="invalid-feedback">&nbsp;</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email_Modal">Email</label>
                            <input id="email_Modal" name="email" class="form-control" type="email" maxlength="255" required aria-describedby="email_Modal_feedback" />
                            <div id="email_Modal_feedback" class="invalid-feedback">&nbsp;</div>
                            <div class="form-text text-muted">O email será usado como login e deve ser único.</div>
                        </div>

                        <div class="mb-3 d-none" id="divSenhaModal">
                            <label class="form-label" for="senha_Modal">Senha Temporária</label>
                            <input id="senha_Modal" name="senha" class="form-control" type="password" disabled aria-describedby="senha_Modal_feedback" />
                            <div id="senha_Modal_feedback" class="invalid-feedback">&nbsp;</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" id="btnGerarNovaSenha" class="btn btn-warning">Gerar Nova Senha</button>
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

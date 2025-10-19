
@section('title', 'Grupos')


    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Grupos</h1>
            <div>
                <button id="btnNovoGrupo" class="btn btn-success">Novo grupo</button>
            </div>
        </div>

        <table id="dataTable_Grupos" class="table table-striped table-bordered" style="width:100%">
        </table>
    </div>

    <!-- Modal Novo/Editar Grupo -->
    <div class="modal fade" id="modalGrupo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formGrupo">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalGrupoTitulo">Novo grupo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="nome_Grupo_Modal">Nome do Grupo</label>
                            <input id="nome_Grupo_Modal" minlength="3" name="nome_Grupo" class="form-control"
                                maxlength="200" required
                                aria-describedby="nome_Grupo_Modal_feedback nome_Grupo_Modal_help" />
                            <div id="nome_Grupo_Modal_help" class="form-text">Mínimo de 3 caracteres.</div>
                            <div id="nome_Grupo_Modal_feedback" class="invalid-feedback">&nbsp;</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="descricao_Grupo_Modal">Descrição</label>
                            <textarea id="descricao_Grupo_Modal" name="descricao_Grupo" class="form-control"
                                maxlength="1000" rows="3"
                                aria-describedby="descricao_Grupo_Modal_feedback"></textarea>
                            <div id="descricao_Grupo_Modal_feedback" class="invalid-feedback">&nbsp;</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="categoria_id_Modal">Categoria</label>
                            <select id="categoria_id_Modal" name="categoria_id" class="form-control"
                                aria-describedby="categoria_id_Modal_feedback">
                                <option value="">Selecione uma categoria</option>
                                <!-- Opções serão carregadas dinamicamente -->
                            </select>
                            <div id="categoria_id_Modal_feedback" class="invalid-feedback">&nbsp;</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="btnExcluirGrupo" class="btn btn-danger">Excluir</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Permissões do Grupo -->
    <div class="modal fade" id="modalPermissoes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalPermissoesTitulo" class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <table id="dataTable_Permissoes_Modal" class="table table-sm table-striped" style="width:100%"></table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>



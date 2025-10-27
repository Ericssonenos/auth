@push('scripts')
    <script src="{{ asset('/views/RH/aba/grupo/modal/grupo-modal.js') }}"></script>
@endpush

<!-- Modal Novo/Editar Grupo -->
<div class="modal fade" id="modal_grupo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="fm_modal_grupo">

                <div class="modal-header">
                    <h5 class="modal-title" id="titulo_modal_usuario">Novo grupo</h5>
                    <!-- Título será definido dinamicamente -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">

                        <label class="form-label" for="nome_modal_grupo">Nome do Grupo</label>
                        <input id="nome_modal_grupo" minlength="3" name="nome_Grupo" class="form-control"
                            maxlength="200" required />
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="descricao_modal_grupo">Descrição</label>
                        <textarea id="descricao_modal_grupo" name="descricao_Grupo" class="form-control" maxlength="1000" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="categoria_id_modal_grupo">Categoria</label>
                        <select id="categoria_id_modal_grupo" name="categoria_id" class="form-control">
                            <option value="">Selecione uma categoria</option>
                            <!-- Opções serão carregadas dinamicamente -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btn_modal_grupo_excluir"
                        class="btn btn-outline-danger d-print-none">Excluir</button>
                    <button type="button" data-bs-dismiss="modal"
                        class="btn btn-outline-dark d-print-none">Cancelar</button>
                    <button type="button" id="btn_modal_grupo_imprimir"
                        class="btn btn-outline-warning d-print-none">Imprimir</button>
                    <button type="button" id="btn_modal_grupo_salvar"
                        class="btn btn-outline-success d-print-none">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

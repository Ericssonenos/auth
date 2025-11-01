@push('scripts')
    <script src="{{ asset('/views/rh/aba/permissao/modal/permissao-modal.js') }}"></script>
@endpush

<!-- Modal Novo/Editar permissao -->
<div class="modal fade" id="modal_permissao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="fm_modal_permissao">

                <div class="modal-header">
                    <h5 class="modal-title" id="titulo_modal_permissao"></h5><!-- Título será definido dinamicamente -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">

                        <label class="form-label" for="nome_modal_permissao">Nome do permissao</label>
                        <input id="nome_modal_permissao" minlength="3" name="nome_permissao" class="form-control"
                            maxlength="200" required />
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="descricao_modal_permissao">Descrição</label>
                        <textarea id="descricao_modal_permissao" name="descricao_permissao" class="form-control" maxlength="1000" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" id="btn_modal_permissao_excluir"
                        class="btn btn-outline-danger d-print-none">Excluir</button>
                    <button type="button" data-bs-dismiss="modal"
                        class="btn btn-outline-dark d-print-none">Cancelar</button>
                    <button type="button" id="btn_modal_permissao_imprimir"
                        class="btn btn-outline-warning d-print-none">Imprimir</button>
                    <button type="button" id="btn_modal_permissao_salvar"
                        class="btn btn-outline-success d-print-none">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

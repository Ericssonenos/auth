@push('scripts')
    <script src="{{ asset('/views/RH/aba/usuario/modal/usuario-modal.js') }}"></script>
@endpush

<!-- Modal Novo/Editar (reaproveitável) -->

<div class="modal fade" id="modal_usuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="fm_modal_usuario">

                <div class="modal-header">
                    <h5 class="modal-title" id="titulo_modal_usuario"></h5> <!-- Título será definido dinamicamente -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label" for="nome_completo_modal_usuario">Nome</label>
                        <!-- ter no minimo 4 caracteres -->
                        <input id="nome_completo_modal_usuario" minlength="4" name="nome_completo" class="form-control"
                            maxlength="255" required />

                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email_modal_usuario">Email</label>
                        <input id="email_modal_usuario" name="email" class="form-control" type="email"
                            maxlength="255" />

                    </div>

                    <div class="mb-3 d-none" id="div_modal_usuario_senha">
                        <label class="form-label" for="senha_modal_usuario">Senha Temporária</label>
                        <div class="d-flex align-items-center">
                            <input id="senha_modal_usuario" name="senha" class="form-control me-2" type="password"
                                disabled aria-describedby="senha_modal_usuario_feedback" />
                            <button type="button" id="btn_modal_usuario_mostrar_senha" class="btn btn-sm btn-outline-secondary d-none"
                                title="Mostrar senha por 10s">Mostrar</button>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btn_modal_usuario_excluir"
                        class="btn btn-outline-danger d-print-none">Excluir</button>
                    <button type="button" data-bs-dismiss="modal"
                        class="btn btn-outline-dark d-print-none">Cancelar</button>
                    <button type="button" id="btn_modal_usuario_imprimir"
                        class="btn btn-outline-warning d-print-none">Imprimir</button>
                    <button type="button" id="btn_modal_usuario_gerar_senha"
                        class="btn btn-outline-primary d-print-none">Gerar Senha</button>
                    <button type="button" id="btn_modal_usuario_salvar"
                        class="btn btn-outline-success d-print-none">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

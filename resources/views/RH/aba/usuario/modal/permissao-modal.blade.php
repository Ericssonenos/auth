@push('scripts')
    <script src="{{ asset('/views/RH/aba/usuario/modal/permissao-modal.js') }}"></script>
@endpush

<!-- Modal Permissões (simples) -->
<div class="modal fade" id="modal_usuario_permissao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titulo_modal_usuario_permissao" class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <table id="tb_modal_usuario_permissao" class="table table-sm table-striped" style="width:100%"></table>
            </div>
            {{-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div> --}}
        </div>
    </div>
</div>

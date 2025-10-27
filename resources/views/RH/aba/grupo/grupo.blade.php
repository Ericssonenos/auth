@section('title', 'Grupo')
@push('scripts')
    <script src="{{ asset('/views/RH/aba/grupo/grupo.js') }}"></script>
@endpush

<div class="container py-4">
    <table id="tb_modal_usuario_grupo" class="table table-striped table-bordered" style="width:100%">
    </table>
</div>

@include('RH.aba.grupo.modal.grupo-modal')

<!-- Modal Permissões do Grupo -->
<div class="modal fade" id="modal_permissao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modal_permissao_titulo" class="modal-title"></h5>
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

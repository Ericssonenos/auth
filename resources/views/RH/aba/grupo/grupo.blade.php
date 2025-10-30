@section('title', 'Grupo')
@push('scripts')
    <script src="{{ asset('/views/rh/aba/grupo/grupo.js') }}"></script>
@endpush

<div class="container py-4">
    <table id="tb_grupo" class="table table-striped table-bordered" style="width:100%">
    </table>
</div>

@include('rh.aba.grupo.modal.grupo-modal')

@include('rh.aba.grupo.modal.permissao-modal')

@include('rh.aba.grupo.modal.usuario-modal')

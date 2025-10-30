@section('title','Usuários')
@push('scripts')
    <script src="{{ asset('/views/rh/aba/usuario/usuario.js') }}"></script>
@endpush

<div class="container py-4">
    <table id="tb_usuario" class="table table-striped table-bordered" style="width:100%">
    </table>
</div>


@include('rh.aba.usuario.modal.usuario-modal')

@include('rh.aba.usuario.modal.permissao-modal')

@include('rh.aba.usuario.modal.grupo-modal')

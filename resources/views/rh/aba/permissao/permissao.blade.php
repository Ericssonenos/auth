@section('title', 'Permissão')
@push('scripts')
    <script src="{{ asset('/views/rh/aba/permissao/permissao.js') }}"></script>
@endpush

<div class="container py-4">
    <table id="tb_permissao" class="table table-striped table-bordered" style="width:100%">
    </table>
</div>

@include('rh.aba.permissao.modal.permissao-modal')

@include('rh.aba.permissao.modal.grupo-modal')


@include('rh.aba.permissao.modal.usuario-modal')

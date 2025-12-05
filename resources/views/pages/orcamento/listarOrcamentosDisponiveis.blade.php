@extends('layout.main')

@section('title', 'Orçamentos')

@push('scripts')
    <script src="{{ asset('views/orcamento/listarOrcamentosDisponiveis.js') }}"></script>
@endpush

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-file-text"></i>
                Gerenciamento de Orçamentos
            </h1>
            <button type="button" id="btn_novo_orcamento" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Novo Orçamento
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="tb_orcamentos" class="table table-striped table-bordered" style="width:100%">
                </table>
            </div>
        </div>
    </div>

    <!-- Modais -->
    @include('pages.orcamento.modal.orcamento-modal')

    @include('pages.orcamento.modal.detalhes-modal')
@endsection

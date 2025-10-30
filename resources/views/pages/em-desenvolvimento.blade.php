@extends('layout.main')

@section('title', 'Em desenvolvimento')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">
                    <i class="bi bi-tools display-4 text-warning mb-3" aria-hidden="true"></i>
                    <h1 class="h3 mb-3">Esta funcionalidade esta em desenvolvimento</h1>
                    <p class="text-muted mb-4">
                        Estamos trabalhando para disponibilizar esta area em breve. Enquanto isso, fique a vontade para explorar as demais secoes do sistema.
                    </p>
                    <a href="{{ route('home.view') }}" class="btn btn-primary">
                        Voltar para a pagina inicial
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

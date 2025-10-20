@extends('layout.main')

@section('title', 'Exemplo - Tabs Dinâmicas')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>🎯 Sistema de Gerenciamento RH</h1>
                <p class="text-muted">Exemplo de interface com tabs dinâmicas e carregamento AJAX</p>
            </div>
        </div>

        <div class=" shadow-sm">

            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-pills mb-0">
                    <li class="nav-item">
                        <a id="aba_usuario" class="nav-link active" href="#tab-usuarios" data-bs-toggle="pill" data-tab="usuarios">
                            👥 Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-grupo" data-bs-toggle="pill" data-tab="grupo">
                            🔐 Grupo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-estatisticas" data-bs-toggle="pill" data-tab="estatisticas">
                            📊 Estatísticas
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" style="min-height: 400px;">
                    <div class="tab-pane fade show active" id="tab-usuarios" role="tabpanel">
                        @include('rh.abas.usuario')
                    </div>

                    <div class="tab-pane fade" id="tab-grupo" role="tabpanel">
                        {{-- @include('rh.abas.grupo') --}}
                    </div>

                    <div class="tab-pane fade" id="tab-estatisticas" role="tabpanel">
                        {{-- @include('rh.login') --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


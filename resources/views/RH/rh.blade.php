@extends('layout.main')


@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1> Sistema de Gerenciamento de Permissões</h1>
        </div>

        <div class=" shadow-sm">

            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-pills mb-0">
                    <li class="nav-item ">
                        <a  class="nav-link  active" href="#tab-usuarios" data-bs-toggle="pill" data-tab="usuarios" style="color: blue">
                            <i class="bi bi-people-fill success"></i> Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-grupo" data-bs-toggle="pill" data-tab="grupo" style="color: green">
                           <i class="bi bi-people"></i> Grupos
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
                        @include('rh.aba.usuario.usuario')
                    </div>

                    <div class="tab-pane fade" id="tab-grupo" role="tabpanel">
                         @include('rh.aba.grupo.grupo')
                    </div>

                    <div class="tab-pane fade" id="tab-estatisticas" role="tabpanel">
                        {{-- @include('rh.aba.estatisticas.estatisticas') --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layout.main')

@section('title', 'Alterar Senha')

@section('header')
    <header class="login-top">
        <div class="d-flex align-items-center justify-content-center gap-3 mx-auto">
            <img src="{{ asset('icon-512.png') }}" alt="Logo" class="logo-img" onerror="this.style.display='none'" />
        </div>
    </header>
    <style>
        main {
           margin-top: 0px !important;
        }
    </style>
@endsection

@push('styles')
    @vite(['resources/css/login.css'])
@endpush

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5 bg-login">
        <div class="shadow-sm w-100 login-page card" style="max-width:420px;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div>

                        <h3 id="login-title" class=" text-white text-center text-warning card-title mb-0">
                            <i class="bi bi-shield-lock p-2 text-gradient-gear"></i>
                            <span class="text-gradient-arrow">Alterar Senha</span>
                        </h3>
                    </div>
                </div>

                @if (session('info'))
                    <div class="alert alert-info" role="status">{{ session('info') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('alterar.senha') }}" class="mt-3">
                    @csrf

                    <div class="mb-3">
                        <label for="senha_atual" class="text-white form-label">Senha atual</label>
                        <input id="senha_atual" name="senha_atual" type="password" required class="form-control" placeholder="Senha atual" />
                    </div>

                    <div class="mb-3">
                        <label for="nova_senha" class="text-white form-label">Nova senha</label>
                        <input id="nova_senha" name="nova_senha" type="password" required class="form-control" placeholder="Nova senha" />
                    </div>

                    <div class="mb-3">
                        <label for="nova_senha_confirmation" class="text-white form-label">Confirme a nova senha</label>
                        <input id="nova_senha_confirmation" name="nova_senha_confirmation" type="password" required class="form-control" placeholder="Confirme a nova senha" />
                    </div>

                    <button type="submit" class="btn btn-primary w-100" aria-label="Alterar senha">Alterar senha</button>
                </form>

                <div class="text-center mt-3 small">
                    <a href="{{ route('login') }}">Retornar ao login</a>
                </div>
            </div>
        </div>
    </div>
@endsection

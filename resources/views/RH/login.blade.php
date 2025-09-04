@extends('layout.main')

@section('title', 'Login')


@section('header')
    <header class="login-top">
        <div class="d-flex align-items-center justify-content-center gap-3 mx-auto">
            <img src="{{ asset('favicon.png') }}" alt="Logo" class="logo-img" onerror="this.style.display='none'" />
        </div>
    </header>
@endsection

@push('styles')
    @vite(['resources/css/login.css'])
@endpush

<!-- estilos movidos para resources/css/login.css e importados em app.css -->

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5 bg-login">
        <div class="card shadow-sm w-100" style="max-width:420px;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ asset('images/apresentacao/logo.png') }}" alt="Logo" class="logo-img me-3"
                        onerror="this.style.display='none'" />
                    <div>
                        <h5 id="login-title" class="text-center card-title mb-0">Suplay Teck</h5>
                    </div>
                </div>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
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


                <form method="POST" action="{{ route('login') }}" class="mt-3">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="form-control" placeholder="seu.email@empresa.com" />
                    </div>

                    <div class="mb-3">
                        <input id="senha" name="senha" type="password" required class="form-control"
                            placeholder="Sua senha" />
                    </div>


                    <button type="submit" class="btn btn-primary w-100" aria-label="Entrar no sistema">Entrar no
                        Sistema</button>
                </form>

                <div class="text-center mt-3 small">Precisa de ajuda? <a
                        href="mailto:suporte@empresa.com">suporte@empresa.com</a></div>
            </div>
        </div>
    </div>

@endsection

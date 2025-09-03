@extends('layout.main')

@section('title', 'Login')

@push('styles')
    <style>
        /* regras específicas desta página (mantêm Bootstrap para componentes) */
        :root {
            --bg1: #0f172a;
            --accent: #0ea5e9;
            --card: #ffffff;
            --muted: #64748b
        }

        body {
            background: linear-gradient(135deg, var(--bg1) 0%, #0b1220 35%, var(--accent) 100%);
            min-height: 100vh
        }

        /* levemente personaliza o card para destacar sobre o fundo */
        .card {
            background: var(--card);
            border-radius: 12px
        }

        .card.shadow-sm {
            box-shadow: 0 10px 30px rgba(2, 6, 23, 0.45)
        }

        .logo-img {
            height: 44px;
            object-fit: contain
        }

        .text-muted {
            color: var(--muted) !important
        }
    </style>
@endpush

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center  py-5">
        <div class="card shadow-sm w-100" style="max-width:420px;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ asset('images/apresentacao/logo.png') }}" alt="Logo" class="logo-img me-3"
                        onerror="this.style.display='none'" />
                    <div>
                        <h5 id="login-title" class="card-title mb-0">Suplay Teck</h5>
                        <small class="text-muted">Acesse o sistema com suas credenciais</small>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="senha" class="form-label mb-0">Senha</label>
                            <a href="/recuperar-senha" class="small">Esqueceu a senha?</a>
                        </div>
                        <input id="senha" name="senha" type="password" required class="form-control"
                            placeholder="Sua senha" />
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <a href="/cadastro" class="small">Solicitar cadastro</a>
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

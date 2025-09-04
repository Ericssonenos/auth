<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistema RH')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.AppErro = @json(session('erro', (object) []));
    </script>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/css/main.css', 'resources/js/app.js'])

</head>

<body>

    @hasSection('header')
        @yield('header')
    @else
        <header class="site-header">
            <nav class="navbar">
                <div class="brand d-flex align-items-center">
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="logo-img" width="40" height="40" />
                    <a href="{{ route('home.view') }}">Suplay Teck</a>
                </div>

                <div class="nav-actions d-flex align-items-center gap-3">
                    @usuarioLogado
                        <span class="user-name">{{ $dadosUsuario->nome_Completo }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="logout-btn">Sair</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="login-btn">Entrar</a>
                    @endusuarioLogado
                </div>
            </nav>
        </header>
    @endif


    <main>
        @yield('content')
    </main>

    <footer>
        @yield('footer')
    </footer>

    <!-- estilos movidos para resources/css/main.css e carregados via Vite -->

    <!-- script removido, comportamento antigo do dropdown não é mais necessário -->

</body>

</html>

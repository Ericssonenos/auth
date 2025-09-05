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
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="logo-img" width="40"
                        height="40" />
                    <a href="{{ route('home.view') }}">Suplay Teck</a>
                </div>

                <div class="nav-actions d-flex align-items-center gap-3">
                    {{-- Menu do módulo RH (o menu flutuante será renderizado após o header, para ficar separado dos dados do usuário) --}}

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

        {{-- Secondary navigation acoplada ao header: menus principais com dropdowns --}}
        <nav class="subnav" aria-label="Secondary navigation">
            <ul class="subnav-list">
                {{-- Módulo RH --}}
                    <li class="subnav-item has-dropdown">
                        <button class="subnav-link" aria-expanded="false">RH <span class="chev">▾</span></button>
                        <ul class="subnav-dropdown" role="menu">
                            @possuiQualquerUmaDasPermissoes('R_GET_RH_USUARIOS')
                                <li role="none"><a role="menuitem" href="{{ route('usuario.view') }}" class="subnav-dropdown-item-2">Gestão de Usuários</a></li>
                            @endpossuiQualquerUmaDasPermissoes
                        </ul>
                    </li>
                {{-- outros módulos aqui --}}
            </ul>
        </nav>
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

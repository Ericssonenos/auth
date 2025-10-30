<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistema rh')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.AppErro = @json(session('erro', (object) []));
    </script>
    @stack('styles')



    <script src="{{ asset('js/layout/setup-csrf.js') }}"></script>
    <script src="{{ asset('js/bibliotecas/datatables/datatables.js') }}"></script>
    <script src="{{ asset('js/bibliotecas/datatables/default.js') }}"></script>
    <link href="{{ asset('js/bibliotecas/datatables/datatables.css') }}" rel="stylesheet">


    @vite(['resources/css/app.css', 'resources/css/main.css', 'resources/js/app.js'])

</head>

<body>

    @hasSection('header')
        @yield('header')
    @else
        <header class="header">
            <div class="brand">
                <img src="{{ asset('favicon.png') }}" alt="Logo" class="brand-logo" />
                <span class="brand-name">SUPPLYTEK</span>
            </div>

            <div class="nav-actions d-flex align-items-center gap-2">
                @usuarioLogado
                    <span class="user-name">{{ $dadosUsuario->nome_completo ?? 'Usuário' }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-ghost">Sair</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Entrar</a>
                @endusuarioLogado
            </div>
        </header>

        <nav class="navbar navbar-expand-lg navbar-light bg-light" aria-label="Navegação principal">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.view') ? 'active' : '' }}"
                            href="{{ route('home.view') }}">
                            <i class="bi bi-house-door-fill me-1" aria-hidden="true"></i>
                            Início
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('em-desenvolvimento') }}" id="navbarDropdownMaquinas" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear-wide-connected me-1" aria-hidden="true"></i>
                            Máquinas de Solda
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMaquinas" role="menu">
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}"><i class="bi bi-lightning-charge me-2"
                                        aria-hidden="true"></i>MIG/MAG</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}"><i class="bi bi-droplet me-2"
                                        aria-hidden="true"></i>TIG</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}"><i class="bi bi-hammer me-2"
                                        aria-hidden="true"></i>MMA (Eletrodo)</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}"><i class="bi bi-layers me-2"
                                        aria-hidden="true"></i>Multiprocesso</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}"><i class="bi bi-scissors me-2"
                                        aria-hidden="true"></i>Plasma</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('em-desenvolvimento') }}" id="navbarDropdownCons" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-seam me-1" aria-hidden="true"></i>
                            Consumíveis
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownCons" role="menu">
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}">Arames</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}">Eletrodos</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}">Gases</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}">Bicos e Ponteiras</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('em-desenvolvimento') }}" id="navbarDropdownSol" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid-3x3-gap me-1" aria-hidden="true"></i>
                            Soluções
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownSol" role="menu">
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}">Automotiva</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}">Metalmecânica</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}">Oil & Gas</a></li>
                            <li><a class="dropdown-item" href="{{ route('em-desenvolvimento') }}">Construção</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ route('em-desenvolvimento') }}" id="navbarDropdownProc" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-diagram-3 me-1" aria-hidden="true"></i>
                            Processos Internos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownProc" role="menu">
                            <li>@href_permissa('usuario.view', 'Cadastros', 'bi bi-people', 'dropdown-item')</li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#catalogo"><i class="bi bi-journal-text me-1"
                                aria-hidden="true"></i>Catálogo</a>
                    </li>
            <li class="nav-item">
            <a class="nav-link" href="{{ route('em-desenvolvimento') }}"><i class="bi bi-tools me-1"
                aria-hidden="true"></i>Assistência Técnica</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="{{ route('em-desenvolvimento') }}"><i class="bi bi-tag me-1"
                aria-hidden="true"></i>Ofertas</a>
            </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="btn btn-warning" href="#orcamento" aria-label="Solicitar orçamento">
                            <i class="bi bi-clipboard-check me-1" aria-hidden="true"></i>
                            Solicitar Orçamento
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    @endif


    <main>
        @yield('content')
    </main>

    <footer>
        @yield('footer')
    </footer>

    {{-- Pequeno script para painel de filtros (toggle) --}}
    <script>
        (function() {
            const btn = document.getElementById('filtersToggle');
            if (!btn) return;
            btn.addEventListener('click', () => {
                document.documentElement.classList.toggle('filters-open');
                btn.classList.toggle('has-active');
            });
            // fecha com Esc
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') document.documentElement.classList.remove('filters-open');
            });
        })();
    </script>

    <!-- Scripts das pǭginas -->
    @stack('scripts')
</body>

</html>

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
            <span class="brand-name">MENU</span>
          </div>

          <div class="nav-actions">


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

        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom" aria-label="Navegação principal">
          <a class="navbar-brand d-lg-none" href="{{ route('home.view') }}">SUPPLYTEK</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('home.view') ? 'active' : '' }}" href="{{ route('home.view') }}">
                  <i class="bi bi-house-door-fill me-1" aria-hidden="true"></i>
                  Início
                </a>
              </li>

              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownRelatorios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-bar-chart me-1" aria-hidden="true"></i>
                  Relatórios
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownRelatorios" role="menu">
                  <li>@href_permissa('relatorios.vendas', 'Vendas', 'bi bi-currency-dollar', 'dropdown-item')</li>
                  <li>@href_permissa('relatorios.logistica', 'Logística', 'bi bi-truck', 'dropdown-item')</li>
                  <li>@href_permissa('relatorios.financeiro', 'Financeiro', 'bi bi-wallet2', 'dropdown-item')</li>
                  <li>@href_permissa('usuario.view', 'Cadastros', 'bi bi-people', 'dropdown-item')</li>
                </ul>
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
      (function(){
        const btn = document.getElementById('filtersToggle');
        if(!btn) return;
        btn.addEventListener('click', () => {
          document.documentElement.classList.toggle('filters-open');
          btn.classList.toggle('has-active');
        });
        // fecha com Esc
        document.addEventListener('keydown', (e) => {
          if(e.key === 'Escape') document.documentElement.classList.remove('filters-open');
        });
      })();
    </script>

    <!-- Scripts das páginas -->
    @stack('scripts')
</body>

</html>

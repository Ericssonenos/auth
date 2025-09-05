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
        <header class="header">
          <div class="brand">
            <img src="{{ asset('favicon.png') }}" alt="Logo" class="brand-logo" />
            <span class="brand-name">SUPPLYTEK</span>
          </div>

          <div class="nav-actions">


            @usuarioLogado
              <span class="user-name">{{ $dadosUsuario->nome_Completo ?? 'Usuário' }}</span>
              <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-ghost">Sair</button>
              </form>
            @else
              <a href="{{ route('login') }}" class="btn btn-primary">Entrar</a>
            @endusuarioLogado
          </div>
        </header>

        <nav class="navbar st-metal" aria-label="Primary navigation">
          <div class="nav">
            <a class="active" href="{{ route('home.view') }}">Início</a>

            <div class="dropdown">
              <a href="#" aria-haspopup="true" aria-expanded="false">Relatórios ▾</a>
              <div class="dropdown-menu" role="menu" aria-label="Relatórios">
                <a href="#" role="menuitem">Vendas</a>
                <a href="#" role="menuitem">Logística</a>
                <a href="#" role="menuitem">Financeiro</a>
              </div>
            </div>

            <a href="#">Cadastros</a>
          </div>

          <!-- Filtros à direita da navbar -->
          <button class="filters-toggle" id="filtersToggle">🔎 Filtros</button>
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

</body>

</html>

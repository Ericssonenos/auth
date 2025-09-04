<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistema RH')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.AppErro = @json(session('erro', (object)[]));
    </script>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body>

    @hasSection('header')
        @yield('header')
    @else
        <header class="site-header">
            <nav class="navbar">
                <div class="brand">
                    <a href="{{ route('home.view') }}">Sistema RH</a>
                </div>

                <div class="nav-actions">
                    <div class="dropdown" id="userDropdown">
                        <button class="dropdown-toggle" aria-haspopup="true" aria-expanded="false" aria-controls="dropdown-menu">
                            <img src="{{ asset('favicon.png') }}" alt="Ícone" class="icon" />
                            <span class="username">Usuário</span>
                            <span class="caret">▾</span>
                        </button>

                        <ul class="dropdown-menu" id="dropdown-menu" role="menu" aria-hidden="true">
                            <li><a href="{{ route('login') }}">Entrar</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="link-button">Sair</button>
                                </form>
                            </li>
                        </ul>
                    </div>
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

    <!-- Estilos mínimos para o menu suspenso (colocar em seu CSS principal se desejar) -->
    <style>
        .site-header{background:#fff;border-bottom:1px solid #e6e6e6;padding:0.5rem 1rem}
        .navbar{display:flex;align-items:center;justify-content:space-between;max-width:1200px;margin:0 auto}
        .brand a{font-weight:600;color:#111;text-decoration:none}
        .nav-actions{display:flex;align-items:center}
        .dropdown{position:relative}
        .dropdown-toggle{background:transparent;border:0;display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.25rem}
        .dropdown-toggle .icon{width:28px;height:28px;border-radius:4px}
        .dropdown-menu{position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #ddd;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,0.08);min-width:160px;padding:0.25rem 0;list-style:none;display:none;margin:0;z-index:1000}
        .dropdown-menu[aria-hidden="false"]{display:block}
        .dropdown-menu li{padding:0}
        .dropdown-menu a,.link-button{display:block;padding:0.5rem 1rem;color:#1f2937;text-decoration:none}
        .dropdown-menu a:hover,.link-button:hover{background:#f3f4f6}
        .link-button{background:none;border:0;width:100%;text-align:left;cursor:pointer}
        @media (max-width:600px){.username{display:none}}
    </style>

    <!-- Script mínimo para abrir/fechar o dropdown -->
    <script>
        (function(){
            var toggle = document.querySelector('.dropdown-toggle');
            var menu = document.getElementById('dropdown-menu');
            if(!toggle || !menu) return;

            function closeMenu(){
                menu.setAttribute('aria-hidden','true');
                toggle.setAttribute('aria-expanded','false');
            }

            function openMenu(){
                menu.setAttribute('aria-hidden','false');
                toggle.setAttribute('aria-expanded','true');
            }

            toggle.addEventListener('click', function(e){
                e.stopPropagation();
                var hidden = menu.getAttribute('aria-hidden') === 'true';
                if(hidden) openMenu(); else closeMenu();
            });

            // Fecha ao clicar fora
            document.addEventListener('click', function(){ closeMenu(); });

            // fecha com ESC
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeMenu(); });
        })();
    </script>

</body>

</html>

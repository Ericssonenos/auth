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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body>

    @hasSection('header')
        @yield('header')
    @else
        <header class="site-header">
            <nav class="navbar">
                <div class="brand">
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="logo-img" width="40" hidden="40" />
                    <a href="{{ route('home.view') }}">Suplay Teck</a>
                </div>

                <div class="nav-actions">
                    <div class="dropdown" id="userDropdown">
                        <button class="dropdown-toggle" aria-haspopup="true" aria-expanded="false"
                            aria-controls="dropdown-menu">
                            <span class="username">Usuário</span>
                            <span class="caret"></span>
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
        .site-header {
            background: linear-gradient(135deg, #0f172a 0%, #0b1220 35%, #0ea5e9 100%);
            padding: 1rem 2rem;
            box-shadow: 0 0 25px rgba(14, 165, 233, 0.3), inset 0 0 10px rgba(255, 136, 64, 0.15);
            border-bottom: 1px solid rgba(255, 200, 150, 0.2);
            /* brilho metálico */
        }

        /* Marca com efeito "bronze/ferrugem brilhante" */
        .site-header .brand a {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(90deg, #d97706, #fbbf24, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(255, 190, 120, 0.5);
            transition: text-shadow 0.3s ease, transform 0.3s ease;
        }

        .site-header .brand a:hover {
            text-shadow: 0 0 15px rgba(255, 200, 150, 0.9);
            transform: scale(1.05);
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Ações do usuário */
        .nav-actions .dropdown-toggle {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 190, 120, 0.3);
            border-radius: 8px;
            padding: 0.4rem 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #e2e8f0;
        }

        .nav-actions .dropdown-toggle:hover {
            background: rgba(255, 190, 120, 0.1);
            box-shadow: 0 0 10px rgba(255, 190, 120, 0.3);
        }

        /* Ícone do usuário */
        .nav-actions .icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(255, 200, 150, 0.5);
        }

        /* Menu suspenso */
        .dropdown-menu {
            background: linear-gradient(180deg, #1e293b, #0f172a);
            border: 1px solid rgba(255, 190, 120, 0.25);
            border-radius: 10px;
            padding: 0.5rem;
            margin-top: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
        }

        .dropdown-menu li a,
        .dropdown-menu .link-button {
            display: block;
            padding: 0.5rem 1rem;
            color: #f8fafc;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .dropdown-menu li a:hover,
        .dropdown-menu .link-button:hover {
            background: rgba(255, 190, 120, 0.15);
            color: #fbbf24;
        }


    </style>

    <!-- Script mínimo para abrir/fechar o dropdown -->
    <script>
        (function() {
            var toggle = document.querySelector('.dropdown-toggle');
            var menu = document.getElementById('dropdown-menu');
            if (!toggle || !menu) return;

            function closeMenu() {
                menu.setAttribute('aria-hidden', 'true');
                toggle.setAttribute('aria-expanded', 'false');
            }

            function openMenu() {
                menu.setAttribute('aria-hidden', 'false');
                toggle.setAttribute('aria-expanded', 'true');
            }

            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                var hidden = menu.getAttribute('aria-hidden') === 'true';
                if (hidden) openMenu();
                else closeMenu();
            });

            // Fecha ao clicar fora
            document.addEventListener('click', function() {
                closeMenu();
            });

            // fecha com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeMenu();
            });
        })();
    </script>

</body>

</html>

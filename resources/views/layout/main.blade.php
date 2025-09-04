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
                <div class="brand d-flex align-items-center">
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="logo-img" width="60" height="0" />
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

        /* Logo ao lado da marca */
        .site-header .brand .logo-img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-right: 0.5rem;
            display: inline-block;
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

        /* Nav actions layout */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        /* Nome do usuário com estilo "bronze/ferrugem brilhante" */
        .nav-actions .user-name {
            font-weight: 600;
            font-size: 1rem;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

            background: linear-gradient(90deg, #f58402, #f3e4be, #ee7503);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;

            text-shadow: 0 0 8px rgba(255, 190, 120, 0.5);
            transition: text-shadow 0.3s ease, transform 0.3s ease;
        }

        .nav-actions .user-name:hover {
            text-shadow: 0 0 12px rgba(255, 200, 150, 0.9);
            transform: scale(1.05);
        }

        /* Botão sair */
        .logout-btn {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 190, 120, 0.4);
            border-radius: 8px;
            padding: 0.4rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: #f8fafc;
            cursor: pointer;

            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 190, 120, 0.15);
            color: #fbbf24;
            box-shadow: 0 0 10px rgba(255, 190, 120, 0.4);
        }

        /* Link entrar */
        .login-btn {
            background: linear-gradient(90deg, #0ea5e9, #38bdf8);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #0f172a;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: linear-gradient(90deg, #38bdf8, #0ea5e9);
            color: #fff;
            box-shadow: 0 0 12px rgba(14, 165, 233, 0.6);
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

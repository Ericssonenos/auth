<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistema RH')</title>
    @stack('styles')
     @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header>
        @yield('header')
        @if(isset($dadosUsuario) && !empty($dadosUsuario))
            <nav>
                <p>Bem-vindo: {{ $dadosUsuario["Nome_Completo"] ?? 'Usuário' }}</p>
            </nav>
        @endif
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        @yield('footer')
    </footer>

    <!-- Dados globais para JavaScript -->
    <script>
        window.AppData = {
            dadosUsuario: @json($dadosUsuario ?? []),
            permissoes: @json($permissoes ?? [])
        };
    </script>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>

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

    @yield('header')


    <main>
        @yield('content')
    </main>

    <footer>
        @yield('footer')
    </footer>

    <!-- Dados globais para JavaScript -->
    <script>
        window.AppData = {
            dados_Usuario: @json($dados_Usuario ?? []),
            permissoes: @json($permissoes ?? [])
        };
    </script>


</body>

</html>

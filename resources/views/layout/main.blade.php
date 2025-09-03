<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistema RH')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <script>
        window.AppUsuario = @json($dadosUsuario ?? (object) []);
    </script>
</body>

</html>

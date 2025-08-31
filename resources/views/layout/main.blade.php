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
        window.AppData = (function () {
            // dados vindos do servidor
            const dadosUsuario = @json($usuarioServices->usuario() ?? (object)[]);
            const permissoes = @json($usuarioServices->permissoes() ?? []);

            // objeto que espelha a classe usuarioServices com métodos utilitários em JS
            const usuarioServices = {
                // dados brutos
                dadosUsuario: dadosUsuario,
                permissoes: permissoes,

                // retorna lista de permissões
                listarPermissoes() {
                    return Array.isArray(this.permissoes) ? this.permissoes : [];
                },

                // retorna dados do usuário
                usuario() {
                    return this.dadosUsuario || {};
                },

                // verifica existência de permissão
                temPermissao(codigoPermissao) {
                    if (!codigoPermissao) return false;
                    return this.listarPermissoes().indexOf(codigoPermissao) !== -1;
                },

                // representação serializável
                toArray() {
                    return {
                        permissoes: this.listarPermissoes(),
                        usuario: this.usuario()
                    };
                }
            };

            return {
                dados_Usuario: dadosUsuario,
                permissoes: permissoes,
                usuarioServices: usuarioServices
            };
        })();
    </script>


</body>

</html>

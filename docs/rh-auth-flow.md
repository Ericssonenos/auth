// passo a passo da autenticação
1. O usuário faz login na aplicação.
    app\Http\Controllers\RH\LoginController.php
    -- Session::put('dadosUsuarioSession', $dadosUsuario);
    $dadosUsuario ={
        id_Usuario,
        nome_Completo,
        email,
        permissoesUsuario
    }
2. Adciononar a Session no Singleton usuarioServices
    app\Providers\AppServiceProvider.php

    -- $this->app->singleton(usuarioServices::class, function ($app) {
            return new usuarioServices(
                session("dadosUsuarioSession", []),
            );
        });

3. Receber e preparar classe usuarioService
           
            dadosUsuario{
                id_Usuario,
                nome_Completo,
                email,
                permissoesUsuario
            }


4.  Volto para AppServiceProvider e Add  Singleton usuarioServices em todas as Views
     app\Providers\AppServiceProvider.php
     $dadosUsuario = $this->app->make(usuarioServices::class);
     $view->with('dadosUsuario', $dadosUsuario);

     crio Blade: temPermissao e possuiQualquerUmaDasPermissoes


5. O middleware captura o ID do usuário e suas permissões.
    app\Http\Middleware\RH\VerificarPermissoes.php

    retorna dadosUsuario{
        mensagem,
        dados_do_usuario_logado,
        permissoes_do_usuario_logado
    }


3. As permissões são armazenadas na sessão.
4. Em cada requisição, o sistema verifica as permissões do usuário.
5. O usuário é autorizado ou negado com base em suas permissões.

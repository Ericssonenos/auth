// passo a passo da autenticação
1. O usuário faz login na aplicação.
    app\Http\Controllers\RH\LoginController.php
    -- Session::put('dadosUsuarioSession', $dadosUsuario);
    $dadosUsuario =class usuarioServices

2. Adciononar a Session no Singleton usuarioServices
    app\Providers\AppServiceProvider.php

    -- $this->app->singleton(usuarioServices::class, function ($app) {
            return new usuarioServices(
                session("dadosUsuarioSession", []),
            );
        });

3. Receber e preparar classe usuarioService
           
         $dadosUsuario =class usuarioServices


4.  Volto para AppServiceProvider e Add  Singleton usuarioServices em todas as Views
     app\Providers\AppServiceProvider.php
     $dadosUsuario = $this->app->make(usuarioServices::class);
     $view->with('dadosUsuario', $dadosUsuario);

     crio Blade: temPermissao e possuiQualquerUmaDasPermissoes


5. O middleware captura o ID do usuário e suas permissões.
    app\Http\Middleware\RH\VerificarPermissoes.php

   $dadosUsuario =class usuarioServices


6. chamar a cada carregemando de pagina o appjs
    resources\js\app.js
    chama  window.alerta.erro

7. mensagem de erro é exibida quando o usuário não tem permissão para acessar um recurso.
    resources\js\components\mensagens_alerta.js


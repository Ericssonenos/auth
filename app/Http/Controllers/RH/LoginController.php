<?php

namespace App\Http\Controllers\RH;

use Illuminate\Http\Request;
use App\Models\RH\usuarioModel;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Exibir o formulário de login.
     * Este método simplesmente retorna a view de login.
     */
    public function exibirFormularioLogin()
    {
        return view('RH.login');
    }

    /**
     * Processar o envio do formulário de login.
     * Valida os dados e tenta autenticar o usuário baseado em email e senha.
     */
    public function processarLogin(Request $request)
    {

        $modeloUsuario = new usuarioModel();

        $resultadoStatus_Usuario = $modeloUsuario->ObterDadosUsuarios(
            [
                'email' => $request['email'],
                'senha' => $request['senha'],//[ ] usar hash apos os teste
                'locatario_id' => 1  // Eliezer
            ]
        );

        // Se o status do usuário for inválido ou os dados estiverem vazios
        if (    $resultadoStatus_Usuario['status'] == false
            ||  empty($resultadoStatus_Usuario['data'])
            ) {
            // Apagar dados da sessão
            Session::forget('dadosUsuarioSession');

            // Redirecionar com mensagem de erro
            return redirect()->back()->withErrors(
                [
                    'email' => 'Credenciais inválidas.',
                ]
            )->withInput();


        }

        // Verificar se a conta do usuário está bloqueada
        if ($resultadoStatus_Usuario['data'][0]['senha_bloqueada'] == 1) {
            // Apagar dados da sessão
            Session::forget('dadosUsuarioSession');

            // Redirecionar com mensagem de erro
            return redirect()->back()->withErrors(
                [
                    'email' => 'Usuário bloqueado. Contate o administrador.',
                ]
            )->withInput();
        }

        // Verificar se o usuário retorna senha, se sim forçar alteração
        if ($resultadoStatus_Usuario['data'][0]['senha'] === null) {
            // Apagar dados da sessão
            Session::forget('dadosUsuarioSession');
            // Redirecionar para a página de alteração de senha
            return redirect()->route('alterar.senha.view')->with('info', 'Você precisa alterar sua senha antes de continuar.');
        }

        // Se o login for bem-sucedido
        $dadosUsuario = $resultadoStatus_Usuario['data'][0];

        // Obter permissões do usuário
        $permissoesUsuario = $modeloUsuario->ObterPermissoesUsuario(['Usuario_id' => $dadosUsuario['id_Usuario']]);

        // Verificar se a resposta contém permissões
        if (isset($permissoesUsuario['status']) && $permissoesUsuario['status'] === true) {
           $dadosUsuario['permissoesUsuario'] = $permissoesUsuario['data'];
        } else {
            $dadosUsuario['permissoesUsuario'] = [];
        }

        // Armazenar os dados do usuário na sessão
        Session::put('dadosUsuarioSession', $dadosUsuario);

        // Redirecionar para a URL que o usuário tentou acessar antes do login, ou para a home se não houver
        $urlIntentada = session('url_intentada', route('home.view'));
        session()->forget('url_intentada'); // Limpar a URL intentada da sessão
        return redirect()->to($urlIntentada);
    }

    /**
     * Encerrar a sessão do usuário atualmente autenticado.
     */
    public function logout(Request $request)
    {
        Session::forget('dadosUsuarioSession');
        return redirect()->route('login');
    }
}

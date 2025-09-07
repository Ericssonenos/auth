<?php

namespace App\Http\Controllers\RH;

use Illuminate\Http\Request;
use App\Models\RH\usuarioModel;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\RH\permissao;
use Illuminate\Support\Facades\Cache;

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
        $request->validate(
            [
                'email' => 'required|email',
                'senha' => 'required|string',
            ],
            [
                'email.required' => 'O campo email é obrigatório.',
                'email.email' => 'O campo email deve ser um endereço de email válido.',
                'senha.required' => 'O campo senha é obrigatório.',
                'senha.string' => 'O campo senha deve ser uma string.',
            ]
        );

        $modeloUsuario = new usuarioModel();

        $resultadoStatus_Usuario = $modeloUsuario->ObterDadosUsuarios(
            [
                'email' => $request['email'],
                'senha' => $request['senha'], //[ ] usar hash apos os teste
                'locatario_id' => 1  // Eliezer
            ]
        );

        // Se o status do usuário for inválido ou os dados estiverem vazios
        if (
            $resultadoStatus_Usuario['status'] == false
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
        if ($resultadoStatus_Usuario['data'][0]['senha'] != null) {
            // Apagar dados da sessão
            Session::forget('dadosUsuarioSession');
            // Redirecionar para a página de alteração de senha
            return redirect()->route('alterar.senha.view')->with('info', 'Você precisa alterar sua senha antes de continuar.');
        }

        // Se o login for bem-sucedido
        $dadosUsuario = $resultadoStatus_Usuario['data'][0];


        $modelPermissao = new permissao();
        // id_usuario traz as permissões ativas
        // usuario_id traz todas as permissões com flag (possui ou não)
        $permissoesUsuario = $modelPermissao->ObterDadosPermissoes(['id_usuario' => $dadosUsuario['id_Usuario']]);

        // Verificar se a resposta contém permissões
        if (isset($permissoesUsuario['status']) && $permissoesUsuario['status'] === true ) {
            $dadosUsuario['permissoesUsuario'] = $permissoesUsuario['data'];
        } else {
            $dadosUsuario['permissoesUsuario'] = [];
        }

        // Armazenar os dados do usuário na sessão
        // Sincroniza versão de permissões: se existir uma versão global, usa-a; caso contrário cria
        $cacheKey = "perms_version_user_{$dadosUsuario['id_Usuario']}";
        $globalVersion = Cache::get($cacheKey, null);
        if ($globalVersion === null) {
            $globalVersion = time();
            Cache::put($cacheKey, $globalVersion);
        }
        $dadosUsuario['perms_version'] = $globalVersion;

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

    /**
     * Exibir formulário de alteração de senha (quando for obrigatório alterar a senha ao logar)
     */
    public function exibirAlterarSenha()
    {
        return view('RH.alterar_senha');
    }

    /**
     * Processar alteração de senha mínima: valida e atualiza via model
     */
    public function processarAlterarSenha(Request $request)
    {
        $request->validate(
            [
                'senha_atual' => 'required|string',
                'nova_senha' => 'required|string|min:6|confirmed',
            ],
            [
                'senha_atual.required' => 'A senha atual é obrigatória.',
                'senha_atual.string' => 'A senha atual deve ser uma string.',
                'nova_senha.required' => 'A nova senha é obrigatória.',
                'nova_senha.string' => 'A nova senha deve ser uma string.',
                'nova_senha.min' => 'A nova senha deve ter pelo menos :min caracteres.',
                'nova_senha.confirmed' => 'A confirmação da nova senha não corresponde.',
            ]
        );

        $modeloUsuario = new usuarioModel();
        $resultadoStatus_Usuario = $modeloUsuario->ObterDadosUsuarios(
            [
                'email' => $request['email'],
                'senha' => $request['senha_atual'], //[ ] usar hash apos os teste
                'locatario_id' => 1  // Eliezer
            ]
        );

         // Se o status do usuário for inválido ou os dados estiverem vazios
        if (
            $resultadoStatus_Usuario['status'] == false
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

        // Se o login for bem-sucedido
        $dadosUsuario = $resultadoStatus_Usuario['data'][0];


        $usuarioId = $dadosUsuario['id_Usuario'];

        $modeloUsuario = new usuarioModel();
        $resultado = $modeloUsuario->AtualizarSenha([
            'usuario_id' => $usuarioId,
            'senha_atual' => $request->input('senha_atual'),
            'nova_senha' => $request->input('nova_senha'),
            'criado_Usuario_id' => $usuarioId,
        ]);

        if (!empty($resultado['status']) && $resultado['status'] === true) {
            // atualizar sessão com novo flag
            Session::forget('dadosUsuarioSession');
            return redirect()->route('login')->with('status', 'Senha alterada com sucesso. Faça login novamente.');
        }

        return redirect()->back()->withErrors(['senha_atual' => $resultado['mensagem'] ?? 'Não foi possível alterar a senha.'])->withInput();
    }
}

<?php

namespace App\Http\Controllers\RH;

use Illuminate\Http\Request;
use App\Models\RH\usuario;
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

        $modeloUsuario = new usuario();

        $resultadoStatus_Usuario = $modeloUsuario->ObterDadosUsuarios(
            [
                'email' => $request['email'],
                'senha' => $request['senha'],//[ ] usar hash apos os teste
                'locatario_id' => 1  // Eliezer
            ]
        );

        if (!$resultadoStatus_Usuario['status'] || empty($resultadoStatus_Usuario['data'])) {
            Session::forget('dados_Usuario');
            Session::forget('list_Permissoes_session');
            return redirect()->back()->withErrors(
                [
                    'email' => 'Credenciais inválidas.',
                    'senha' => 'Credenciais inválidas.'
                ]
            )->withInput();
        }

        $registroUsuario = $resultadoStatus_Usuario[0]['data'];
        Session::put('dados_Usuario', $registroUsuario);

        $respostaPermissoes = $modeloUsuario->ObterPermissoesUsuario(['Usuario_id' => $registroUsuario['id_Usuario']]);
        if (isset($respostaPermissoes['status']) && $respostaPermissoes['status'] === true) {
            Session::put('list_Permissoes_session', $respostaPermissoes['data']);
        }else{
            Session::put('list_Permissoes_session', []);
        }

        return redirect()->route('painel');
    }

    /**
     * Encerrar a sessão do usuário atualmente autenticado.
     */
    public function logout(Request $request)
    {
        Session::forget('dados_Usuario');
        Session::forget('list_Permissoes_session');
        return redirect()->route('login');
    }
}

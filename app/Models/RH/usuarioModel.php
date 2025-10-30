<?php

namespace App\Models\RH;

use App\Services\Operacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\RH\usuarioServices;


class usuarioModel extends Model
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = DB::connection()->getPdo();
    }

    // primeiro contato exite o maximo de segurança
    public function ObterLoginUsuario($params)
    {

        $execParams[":email"] = $params['email'];
        $execParams[":senha"] = $params['senha'];
        $execParams[":locatario_id"] = $params['locatario_id'];

        //montar a consulta SQL
        $consultaSql = "SELECT
                        id_Usuario,
                        nome_Completo,
                        email,
                        senha =CASE
                            WHEN b_senha_Temporaria = 1
                            AND (dat_senha_Bloqueado_em IS NULL OR dat_senha_Bloqueado_em > GETDATE())
                        THEN senha ELSE null END,
                        senha_bloqueada = CASE
                            WHEN (dat_senha_Bloqueado_em < GETDATE())
                            THEN 1 ELSE 0 END,
                        dat_criado_em,
                        criado_Usuario_id
                    FROM RH.Tbl_Usuarios
                    WHERE dat_cancelamento_em IS NULL
                    and email = :email
                    and senha = :senha
                    and locatario_id = :locatario_id";


        try {
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute($execParams);

            $data = $comando->fetch(\PDO::FETCH_ASSOC);

            if (empty($data)) {
                return [
                    'data' => [],
                    'status' => 204,
                    'mensagem' => 'Nenhum usuário encontrado com os critérios fornecidos.'
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'data' => $data,
            'status' => 200,
            'mensagem' => 'Dados do usuário recuperados.'
        ];
    }


    public function ObterDadosUsuarios($params)
    {


        $fn = $params['fn'] ?? null;

        if ($fn === 'fn-grupo-status') {
            // este traz os usuários vinculados a um grupo específico
            $execParams[':grupo_id'] = $params['grupo_id'];
            $consultaSql = "SELECT
                        u.id_Usuario,
                        u.nome_Completo,
                        u.email,
                        u.dat_criado_em,
                        rug.id_rel_usuario_grupo
                    FROM RH.Tbl_Usuarios u
                    LEFT JOIN RH.Tbl_Rel_Usuarios_Grupos rug
                        ON u.id_Usuario = rug.usuario_id
                        AND rug.grupo_id = :grupo_id
                        AND rug.dat_cancelamento_em IS NULL
                    WHERE u.dat_cancelamento_em IS NULL
                    ORDER BY CASE WHEN rug.id_rel_usuario_grupo IS NOT NULL THEN 1 ELSE 0 END, u.nome_Completo";
        } else {

            $parametrizacao = Operacao::Parametrizar($params);
            // Verifica se houve erro na parametrização
            if ($parametrizacao['status'] !== 200) {
                return [
                    'status' => $parametrizacao['status'],
                    'mensagem' => $parametrizacao['mensagem'],
                    'data' => []
                ];
            }

            $whereParams = $parametrizacao['whereParams'];
            $optsParams  = $parametrizacao['optsParams'];
            $execParams  = $parametrizacao['execParams'];

            //montar a consulta SQL
            $consultaSql = "SELECT
                        id_Usuario,
                        nome_Completo,
                        email,
                        senha =CASE
                            WHEN b_senha_Temporaria = 1
                            AND (dat_senha_Bloqueado_em IS NULL OR dat_senha_Bloqueado_em > GETDATE())
                        THEN senha ELSE null END,
                        senha_bloqueada = CASE
                            WHEN (dat_senha_Bloqueado_em < GETDATE())
                            THEN 1 ELSE 0 END,
                        dat_criado_em,
                        criado_Usuario_id
                    FROM RH.Tbl_Usuarios
                    WHERE dat_cancelamento_em IS NULL"
                . implode(' ', $whereParams)
                . ($optsParams['order_by']   ?? '')
                . ($optsParams['limit']      ?? '')
                . ($optsParams['offset']     ?? '');
        }



        try {
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute($execParams);
            $data = $comando->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($data)) {
                return [
                    'status' => 204, // Sucesso porem sem dados
                    'mensagem' => 'Nenhum usuário encontrado com os critérios fornecidos.',
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Dados do usuário recuperados.',
            'data' => $data
        ];
    }

    public function AtribuirPermissoes($params)
    {
        try {
            $usuario_id = $params['usuario_id'];
            $permissao_id = $params['permissao_id'];
            $criado_Usuario_id = app(usuarioServices::class)->id_Usuario;


            $consultaSql = "INSERT INTO RH.Tbl_Rel_Usuarios_Permissoes (
                        usuario_id
                    ,   permissao_id
                    ,   criado_Usuario_id
                    ) VALUES (:usuario_id, :permissao_id, :criado_Usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':usuario_id' => $usuario_id,
                ':permissao_id' => $permissao_id,
                ':criado_Usuario_id' => $criado_Usuario_id
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma linha inserida.',
                    'data' => ['afetadas' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Permissão atribuída.',
            'data' => ['afetadas' => $rows]
        ];
    }

    public function AtribuirGrupo($params)
    {
        try {
            $usuario_id = $params['usuario_id'];
            $grupo_id = $params['grupo_id'];
            $criado_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "INSERT INTO RH.Tbl_Rel_Usuarios_Grupos (
                        usuario_id
                    ,   grupo_id
                    ,   criado_Usuario_id
                    ) VALUES (:usuario_id, :grupo_id, :criado_Usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':usuario_id' => $usuario_id,
                ':grupo_id' => $grupo_id,
                ':criado_Usuario_id' => $criado_Usuario_id
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma linha inserida.',
                    'data' => ['afetadas' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Grupo atribuído.',
            'data' => ['afetadas' => $rows]
        ];
    }

    public function RemoverPermissoes($params)
    {
        try {
            $id_rel_usuario_permissao = $params['id_rel_usuario_permissao'];
            $cancelamento_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "UPDATE RH.Tbl_Rel_Usuarios_Permissoes
                            SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                                dat_cancelamento_em = GETDATE()
                            WHERE id_rel_usuario_permissao = :id_rel_usuario_permissao
                            AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':id_rel_usuario_permissao' => $id_rel_usuario_permissao,
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma linha atualizada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Vínculo de permissão cancelado.',
            'data' => ['affected' => $rows]
        ];
    }

    public function RemoverGrupo($params)
    {
        try {
            $id_rel_usuario_grupo = $params['id_rel_usuario_grupo'];
            $cancelamento_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "UPDATE RH.Tbl_Rel_Usuarios_Grupos
                            SET cancelamento_Usuario_id = :cancelamento_Usuario_id,
                                dat_cancelamento_em = GETDATE()
                            WHERE id_rel_usuario_grupo = :id_rel_usuario_grupo
                            AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':id_rel_usuario_grupo' => $id_rel_usuario_grupo,
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma linha atualizada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }

        return [
            'status' => 200,
            'mensagem' => 'Vínculo de grupo cancelado.',
            'data' => ['affected' => $rows]
        ];
    }

    public function CadastrarUsuarios($params)
    {
        try {
            $nome_Completo = $params['nome_Completo'];
            $email = $params['email'];
            $senhaGerada = bin2hex(random_bytes(4));
            // ober o id do usuario que está criando o novo usuário
            $criado_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "INSERT INTO RH.Tbl_Usuarios
                            (nome_Completo,  email,   senha,  criado_Usuario_id,  b_senha_Temporaria,  dat_senha_Bloqueado_em,  locatario_id)
                            VALUES
                            (:nome_Completo, :email, :senha, :criado_Usuario_id, :b_senha_Temporaria, :dat_senha_Bloqueado_em, :locatario_id)";
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':nome_Completo' => $nome_Completo,
                ':email' => $email,
                ':senha' => $senhaGerada,
                ':criado_Usuario_id' => $criado_Usuario_id,
                ':b_senha_Temporaria' => 1,
                ':dat_senha_Bloqueado_em' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
                ':locatario_id' => 1 // Supplytek
            ]);

            $rows = $comando->rowCount();

            // Tentar obter id inserido quando possível
            $lastId = null;
            try {
                $lastId = $this->conexao->lastInsertId();
            } catch (\Exception $e) {
                $lastId = null;
            }

            if ($rows == 0) {
                return [
                    'status' => 404,
                    'mensagem' => 'Usuário não criado.',
                    'data' => ['affected' => $rows, 'senha' => null, 'id_Usuario' => null]
                ];
            }
        } catch (\PDOException $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Usuário criado.',
            'data' => ['affected' => $rows, 'senha' => $senhaGerada, 'id_Usuario' => $lastId]
        ];
    }

    /**
     * Atualizar senha do usuário: verifica senha atual (quando aplicável) e atualiza para a nova senha.
     */
    public function AtualizarSenha($params)
    {
        try {
            $usuario_id = $params['usuario_id'];
            $nova_senha = $params['nova_senha'];


            // Atualizar senha e marcar b_senha_Temporaria
            $consultaUpdate = "UPDATE
                RH.Tbl_Usuarios
                SET
                        senha = :nova_senha
                    ,   b_senha_Temporaria = 0
                    ,   dat_senha_Bloqueado_em = NULL
                WHERE id_Usuario = :id_Usuario";
            $cmd2 = $this->conexao->prepare($consultaUpdate);
            $cmd2->execute([':nova_senha' => $nova_senha, ':id_Usuario' => $usuario_id]);
            $rows = $cmd2->rowCount();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma alteração realizada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\PDOException $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Senha atualizada.',
            'data' => ['affected' => $rows]
        ];
    }

    /**
     * Gera uma senha temporária para o usuário, salva no banco e retorna a senha no resultado.
     */
    public function GerarSenhaTemporaria($params)
    {
        try {
            $usuario_id = $params['usuario_id'];

            // gera senha aleatória (8 hex chars)
            $senhaGerada = bin2hex(random_bytes(4));

            $consultaSql = "UPDATE RH.Tbl_Usuarios
                            SET senha = :senha,
                                b_senha_Temporaria = 1,
                                dat_senha_Bloqueado_em = DATEADD(minute, 10, GETDATE())
                            WHERE id_Usuario = :id_Usuario";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':senha' => $senhaGerada,
                ':id_Usuario' => $usuario_id
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma linha atualizada.',
                    'data' => ['affected' => $rows, 'senha' => null]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Senha temporária gerada.',
            'data' => ['affected' => $rows, 'senha' => $senhaGerada]
        ];
    }

    /**
     * Atualiza apenas o nome_Completo do usuário.
     */
    public function AtualizarUsuarios($params)
    {
        try {
            $usuario_id = $params['usuario_id'];
            $nome_Completo = $params['nome_Completo'] ?? null;

            if (is_null($nome_Completo)) {
                return [
                    'status' => 400,
                    'mensagem' => 'nome_Completo é obrigatório.',
                    'data' => []
                ];
            }

            $consultaSql = "UPDATE RH.Tbl_Usuarios SET nome_Completo = :nome_Completo WHERE id_Usuario = :id_Usuario";
            $cmd = $this->conexao->prepare($consultaSql);
            $cmd->execute([':nome_Completo' => $nome_Completo, ':id_Usuario' => $usuario_id]);
            $rows = $cmd->rowCount();
            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma alteração realizada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Nome atualizado.',
            'data' => ['affected' => $rows]
        ];
    }

    /**
     * Excluir (logicamente) um usuário. Atualiza cancelamento_Usuario_id e dat_cancelamento_em.
     */
    public function DeletarUsuarios($params)
    {
        try {
            $usuario_id = $params['usuario_id'];
            // id do usuário que executa a ação
            $cancelamento_Usuario_id = app(usuarioServices::class)->id_Usuario;

            $consultaSql = "UPDATE RH.Tbl_Usuarios SET cancelamento_Usuario_id = :cancelamento_Usuario_id, dat_cancelamento_em = GETDATE() WHERE id_Usuario = :id_Usuario AND dat_cancelamento_em IS NULL";
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cancelamento_Usuario_id' => $cancelamento_Usuario_id,
                ':id_Usuario' => $usuario_id
            ]);

            $rows = $comando->rowCount();
            $comando->closeCursor();

            if ($rows == 0) {
                return [
                    'status' => 204,
                    'mensagem' => 'Nenhuma alteração realizada.',
                    'data' => ['affected' => $rows]
                ];
            }
        } catch (\Exception $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Usuário excluído.',
            'data' => ['affected' => $rows]
        ];
    }


    public function __destruct()
    {
        $this->conexao = null;
    }
}

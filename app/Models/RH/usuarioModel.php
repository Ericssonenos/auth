<?php

namespace App\Models\RH;

use App\Services\Operacao;
use App\Services\RH\usuarioServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


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
        $execParams[':email'] = $params['email'] ?? null;
        $execParams[':senha'] = $params['senha'] ?? null;
        $execParams[':locatario_id'] = $params['locatario_id'] ?? null;

        $consultaSql = "SELECT
                        id_usuario,
                        nome_completo,
                        email,
                        CASE
                            WHEN b_senha_temporaria =  TRUE --- 1 PARA SQL SERVER ---
                             AND (dat_senha_bloqueado_em IS NULL OR dat_senha_bloqueado_em > CURRENT_TIMESTAMP)
                        THEN senha ELSE NULL END AS senha,
                        CASE
                            WHEN dat_senha_bloqueado_em IS NOT NULL AND dat_senha_bloqueado_em < CURRENT_TIMESTAMP
                        THEN 1 ELSE 0 END AS senha_bloqueada,
                        dat_criado_em,
                        criado_usuario_id
                    FROM rh.tb_usuarios
                    WHERE dat_cancelamento_em IS NULL
                      AND email = :email
                      AND senha = :senha
                      AND locatario_id = :locatario_id";


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
                        u.id_usuario,
                        u.nome_completo,
                        u.email,
                        u.dat_criado_em,
                        rug.id_rel_usuario_grupo
                    FROM rh.tb_usuarios u
                    LEFT JOIN rh.tr_usuarios_grupos rug
                        ON u.id_usuario = rug.usuario_id
                        AND rug.grupo_id = :grupo_id
                        AND rug.dat_cancelamento_em IS NULL
                    WHERE u.dat_cancelamento_em IS NULL
                    ORDER BY CASE WHEN rug.id_rel_usuario_grupo IS NOT NULL THEN 1 ELSE 0 END, u.nome_completo";
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
                        id_usuario,
                        nome_completo,
                        email,
                        CASE
                            WHEN b_senha_temporaria  =  TRUE --- 1 PARA SQL SERVER ---
                             AND (dat_senha_bloqueado_em IS NULL OR dat_senha_bloqueado_em > CURRENT_TIMESTAMP)
                        THEN senha ELSE NULL END AS senha,
                        CASE
                            WHEN dat_senha_bloqueado_em IS NOT NULL AND dat_senha_bloqueado_em < CURRENT_TIMESTAMP
                        THEN 1 ELSE 0 END AS senha_bloqueada,
                        dat_criado_em,
                        criado_usuario_id
                    FROM rh.tb_usuarios
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
            $usuarioId = $params['usuario_id'];
            $permissaoId = $params['permissao_id'];
            $criadoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "INSERT INTO rh.tr_usuarios_permissoes (
                        usuario_id,
                        permissao_id,
                        criado_usuario_id
                    ) VALUES (:usuario_id, :permissao_id, :criado_usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':usuario_id' => $usuarioId,
                ':permissao_id' => $permissaoId,
                ':criado_usuario_id' => $criadoUsuarioId
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
            $usuarioId = $params['usuario_id'];
            $grupoId = $params['grupo_id'];
            $criadoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "INSERT INTO rh.tr_usuarios_grupos (
                        usuario_id,
                        grupo_id,
                        criado_usuario_id
                    ) VALUES (:usuario_id, :grupo_id, :criado_usuario_id)";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':usuario_id' => $usuarioId,
                ':grupo_id' => $grupoId,
                ':criado_usuario_id' => $criadoUsuarioId
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
            $idRelUsuarioPermissao = $params['id_rel_usuario_permissao'];
            $cancelamentoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE rh.tr_usuarios_permissoes
                            SET cancelamento_usuario_id = :cancelamento_usuario_id,
                                dat_cancelamento_em = :dat_cancelamento_em
                            WHERE id_rel_usuario_permissao = :id_rel_usuario_permissao
                            AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':id_rel_usuario_permissao' => $idRelUsuarioPermissao,
                ':cancelamento_usuario_id' => $cancelamentoUsuarioId,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s')
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
            $idRelUsuarioGrupo = $params['id_rel_usuario_grupo'];
            $cancelamentoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE rh.tr_usuarios_grupos
                            SET cancelamento_usuario_id = :cancelamento_usuario_id,
                                dat_cancelamento_em = :dat_cancelamento_em
                            WHERE id_rel_usuario_grupo = :id_rel_usuario_grupo
                            AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);

            $comando->execute([
                ':id_rel_usuario_grupo' => $idRelUsuarioGrupo,
                ':cancelamento_usuario_id' => $cancelamentoUsuarioId,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s')
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
            $nomeCompleto = $params['nome_completo'] ?? $params['nome_Completo'] ?? null;
            $email = $params['email'] ?? null;
            $senhaGerada = bin2hex(random_bytes(4));
            $criadoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "INSERT INTO rh.tb_usuarios
                            (nome_completo,  email,   senha,  criado_usuario_id,  b_senha_temporaria,  dat_senha_bloqueado_em,  locatario_id)
                            VALUES
                            (:nome_completo, :email, :senha, :criado_usuario_id, :b_senha_temporaria, :dat_senha_bloqueado_em, :locatario_id)";
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':nome_completo' => $nomeCompleto,
                ':email' => $email,
                ':senha' => $senhaGerada,
                ':criado_usuario_id' => $criadoUsuarioId,
                ':b_senha_temporaria' => TRUE, // 1 PARA SQL SERVER
                ':dat_senha_bloqueado_em' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
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
                    'data' => ['affected' => $rows, 'senha' => null, 'id_usuario' => null]
                ];
            }
        } catch (\PDOException $e) {
            return Operacao::mapearExcecaoPDO($e, $params);
        }
        return [
            'status' => 200,
            'mensagem' => 'Usuário criado.',
            'data' => ['affected' => $rows, 'senha' => $senhaGerada, 'id_usuario' => $lastId]
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
                rh.tb_usuarios
                SET
                        senha = :nova_senha
                    ,   b_senha_temporaria = FALSE -- 0 PARA SQL SERVER
                    ,   dat_senha_bloqueado_em = NULL
                WHERE id_usuario = :id_usuario
                  AND dat_cancelamento_em IS NULL";
            $cmd2 = $this->conexao->prepare($consultaUpdate);
            $cmd2->execute([':nova_senha' => $nova_senha, ':id_usuario' => $usuario_id]);
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

            $consultaSql = "UPDATE rh.tb_usuarios
                            SET senha = :senha,
                                b_senha_temporaria = TRUE, -- 1 PARA SQL SERVER
                                dat_senha_bloqueado_em = :dat_senha_bloqueado_em
                            WHERE id_usuario = :id_usuario
                              AND dat_cancelamento_em IS NULL";

            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':senha' => $senhaGerada,
                ':id_usuario' => $usuario_id,
                ':dat_senha_bloqueado_em' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
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
            $nome_Completo = $params['nome_completo'] ?? $params['nome_Completo'] ?? null;

            if (is_null($nome_Completo)) {
                return [
                    'status' => 400,
                    'mensagem' => 'nome_completo é obrigatório.',
                    'data' => []
                ];
            }

            $consultaSql = "UPDATE rh.tb_usuarios SET nome_completo = :nome_completo WHERE id_usuario = :id_usuario AND dat_cancelamento_em IS NULL";
            $cmd = $this->conexao->prepare($consultaSql);
            $cmd->execute([':nome_completo' => $nome_Completo, ':id_usuario' => $usuario_id]);
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
            $usuarioId = $params['usuario_id'];
            $cancelamentoUsuarioId = $this->obterUsuarioAutenticadoId();

            $consultaSql = "UPDATE rh.tb_usuarios SET cancelamento_usuario_id = :cancelamento_usuario_id, dat_cancelamento_em = :dat_cancelamento_em WHERE id_usuario = :id_usuario AND dat_cancelamento_em IS NULL";
            $comando = $this->conexao->prepare($consultaSql);
            $comando->execute([
                ':cancelamento_usuario_id' => $cancelamentoUsuarioId,
                ':id_usuario' => $usuarioId,
                ':dat_cancelamento_em' => date('Y-m-d H:i:s')
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

    private function obterUsuarioAutenticadoId(): int
    {
        $usuarioService = app(usuarioServices::class);

        if (property_exists($usuarioService, 'id_usuario') && !empty($usuarioService->id_usuario)) {
            return (int) $usuarioService->id_usuario;
        }

        return (int) ($usuarioService->id_Usuario ?? 0);
    }


    public function __destruct()
    {
        $this->conexao = null;
    }
}

-- Seeders para testes iniciais de usuários e permissões

SET NOCOUNT ON;

DECLARE @i INT = 2;

WHILE @i < 10
BEGIN
    INSERT INTO RH.Tbl_Usuarios
        (
        nome_Completo,
        email,
        senha,
        criado_Usuario_id,
        dat_criado_em,
        locatario_id
        )
    VALUES
        (
            CONCAT(N'Usuário ', @i),                    -- nome_Completo
            CONCAT(N'usuario', @i, N'@exemplo.com'),    -- email
            'Senha',                                    -- senha (ajuste se usar hash)
                                                        --[ ] ativar apos testes: substituir 'Senha' pelo hash gerado com bcrypt/argon2
            0, -- criado_Usuario_id
            GETDATE(), -- dat_criado_em
            1                                         -- locatario_id
    );

    SET @i += 1;
END;

INSERT INTO RH.Tbl_Permissoes
    (cod_permissao, descricao_permissao, criado_Usuario_id)
VALUES
    ('PERM_ACESSAR_DASHBOARD', 'Permite acessar o painel principal do sistema.', '1'),
    ('PERM_CRIAR_USUARIO', 'Permite criar novos usuários no sistema.', '1'),
    ('PERM_ATUALIZAR_USUARIO', 'Permite editar informações de usuários existentes.', '1'),
    ('PERM_EXCLUIR_USUARIO', 'Permite marcar usuários como excluídos ou remover acesso (uso controlado).', '1'),
    ('PERM_ATRIBUIR_GRUPO', 'Permite vincular usuários a grupos/roles.', '1'),
    ('PERM_REMOVER_GRUPO', 'Permite revogar vínculo de usuário com um grupo (marca cancelamento).', '1'),
    ('PERM_VISUALIZAR_RELATORIOS', 'Permite visualizar relatórios e dashboards específicos.', '1'),
    ('PERM_EXPORTAR_DADOS', 'Permite exportar dados em CSV/XLS (uso sensível; auditar).', '1'),
    ('PERM_GERENCIAR_PERMISSOES', 'Permite criar/editar/permissões e atribuições globais.', '1'),
    ('PERM_GERENCIAR_GRUPOS', 'Permite criar/editar grupos e gerenciar hierarquia de grupos.', '1');


-- Atribuir várias permissões (evita duplicatas ativas) — insira os códigos abaixo
DECLARE @Usuario_id         int      = 1;
DECLARE @criado_Usuario_id  int      = 2;
DECLARE @Permissoes TABLE (cod_permissao NVARCHAR(200));

INSERT INTO @Permissoes
    (cod_permissao)
VALUES
    ('PERM_ACESSAR_DASHBOARD'),
    ('PERM_ATRIBUIR_GRUPO'),
    ('PERM_VISUALIZAR_RELATORIOS');



-- Atribuir permissões ao usuário
INSERT INTO RH.Tbl_Rel_Usuarios_Permissoes
    (Usuario_id, permissao_id, criado_Usuario_id)
SELECT
    @Usuario_id,
    p.id_permissao,
    @criado_Usuario_id
FROM RH.Tbl_Permissoes p
    INNER JOIN @Permissoes t ON t.cod_permissao = p.cod_permissao
WHERE p.dat_cancelamento_em IS NULL
    AND NOT EXISTS (
      SELECT 1
    FROM RH.Tbl_Rel_Usuarios_Permissoes rup
    WHERE rup.Usuario_id =@Usuario_id
        AND rup.permissao_id = p.id_permissao
        AND rup.dat_cancelamento_em IS NULL
  );

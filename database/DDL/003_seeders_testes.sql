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
            CONCAT(N'Usuário ', @i), -- nome_Completo
            CONCAT(N'usuario', @i, N'@exemplo.com'), -- email
            'Senha', -- senha (ajuste se usar hash)
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
    ('R_GET_RH_USUARIOS', 'Permite visualizar dados do usuário.', '1'),
    ('R_GET_HOME', 'Permite visualizar a página inicial.', '1'),
    ('R_GET_USUARIOS', 'Permite obter dados de usuários.', '1'),
    ('R_POST_RH_API_USUARIOS', 'Permite acessar o painel principal do sistema.', '1'),





-- Atribuir várias permissões (evita duplicatas ativas) — insira os códigos abaixo
DECLARE @usuario_id         int      = 1;
DECLARE @criado_Usuario_id  int      = 2;
DECLARE @Permissoes TABLE (cod_permissao NVARCHAR(200));

INSERT INTO @Permissoes
    (cod_permissao)
VALUES
    ('R_GET_RH_USUARIOS'),
    ('R_POST_RH_API_USUARIOS'),
    ('R_GET_HOME'),
    ('R_GET_USUARIOS'),




-- Atribuir permissões ao usuário
INSERT INTO RH.Tbl_Rel_Usuarios_Permissoes
    (usuario_id, permissao_id, criado_Usuario_id)
SELECT
    @usuario_id,
    p.id_permissao,
    @criado_Usuario_id
FROM RH.Tbl_Permissoes p
    INNER JOIN @Permissoes t ON t.cod_permissao = p.cod_permissao
WHERE p.dat_cancelamento_em IS NULL
    AND NOT EXISTS (
      SELECT 1
    FROM RH.Tbl_Rel_Usuarios_Permissoes rup
    WHERE rup.usuario_id =@usuario_id
        AND rup.permissao_id = p.id_permissao
        AND rup.dat_cancelamento_em IS NULL
  );

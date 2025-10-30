-- Seeders para testes iniciais de usuários e permissões

SET NOCOUNT ON;

DECLARE @AdminEmail NVARCHAR(200) = N'adm@exemplo.com';

IF NOT EXISTS (SELECT 1 FROM RH.Tbl_Usuarios WHERE email = @AdminEmail)
BEGIN
    INSERT INTO RH.Tbl_Usuarios (nome_Completo, email, senha, locatario_id, criado_Usuario_id)
    VALUES (N'Administrador', @AdminEmail, 'Senha', 1, 1);
END;

UPDATE RH.Tbl_Usuarios
SET criado_Usuario_id = 1
WHERE email = @AdminEmail;

DECLARE @i INT = 2;

WHILE @i < 10
BEGIN
    DECLARE @Email NVARCHAR(200) = CONCAT(N'usuario', @i, N'@exemplo.com');

    IF NOT EXISTS (SELECT 1 FROM RH.Tbl_Usuarios WHERE email = @Email)
    BEGIN
        INSERT INTO RH.Tbl_Usuarios (nome_Completo, email, senha, locatario_id, criado_Usuario_id)
        VALUES (CONCAT(N'Usuário ', @i), @Email, 'Senha', 1, 1);
    END;

    SET @i += 1;
END;

UPDATE RH.Tbl_Usuarios
SET criado_Usuario_id = 1
WHERE email LIKE N'usuario[0-9]@exemplo.com';

DECLARE @Permissoes TABLE (cod_permissao NVARCHAR(200), descricao_permissao NVARCHAR(1000));

DECLARE @CategoriaDefault NVARCHAR(200) = N'Sistema';

IF NOT EXISTS (
    SELECT 1
    FROM RH.Tbl_Categorias
    WHERE nome_Categoria = @CategoriaDefault
      AND dat_cancelamento_em IS NULL
)
BEGIN
    INSERT INTO RH.Tbl_Categorias (nome_Categoria, descricao_Categoria, criado_Usuario_id)
    VALUES (@CategoriaDefault, N'Categoria padrão para perfis administrativos.', 1);
END;

UPDATE RH.Tbl_Categorias
SET criado_Usuario_id = 1
WHERE nome_Categoria = @CategoriaDefault;

INSERT INTO @Permissoes (cod_permissao, descricao_permissao)
VALUES
    (N'R_POST_API_RH_USUARIO_DADOS', N'Autoriza o cadastro de usuários via API de RH.'),
    (N'N_USUARIO.VIEW', N'Permite visualizar a tela de consulta de usuários.'),
    (N'R_POST_API_RH_USUARIO_PERMISSAO_ADICIONAR', N'Permite conceder permissões manuais a um usuário.'),
    (N'R_POST_API_RH_GRUPO_DADOS', N'Autoriza cadastrar e editar dados de grupos via API de RH.'),
    (N'N_GRUPOS.VIEW', N'Permite visualizar a listagem de grupos no sistema.'),
    (N'R_POST_RH_API_USUARIOS', N'Autoriza operações de criação de usuários no módulo RH.'),
    (N'R_POST_API_RH_PERMISSAO_DADOS', N'Permite cadastrar ou alterar registros de permissões via API.'),
    (N'R_DELETE_API_RH_USUARIO_DELETAR_VALOR', N'Autoriza o cancelamento lógico de usuário via API de RH.'),
    (N'R_POST_API_RH_USUARIO_GRUPO_ADICIONAR', N'Permite vincular um usuário a um grupo.'),
    (N'R_DELETE_API_RH_USUARIO_GRUPO_REMOVER_VALOR', N'Autoriza remover o vínculo entre usuário e grupo.'),
    (N'R_PUT_API_RH_USUARIO_ATUALIZAR_VALOR', N'Permite atualizar dados cadastrais de usuário via API.'),
    (N'N_USUARIO.GERAR_SENHA', N'Habilita a funcionalidade de geração de senha na tela de usuários.'),
    (N'R_POST_RH_USUARIO_VALOR_GERAR_SENHA', N'Autoriza a geração de nova senha para um usuário via API.'),
    (N'R_POST_API_RH_CATEGORIA_DADOS', N'Permite cadastrar ou alterar categorias vinculadas a grupos.'),
    (N'R_DELETE_API_RH_GRUPO_DELETAR_VALOR', N'Autoriza o cancelamento lógico de um grupo via API.'),
    (N'R_PUT_API_RH_GRUPO_ATUALIZAR_VALOR', N'Permite atualizar dados de grupo via API.'),
    (N'R_DELETE_API_RH_USUARIO_PERMISSAO_REMOVER_VALOR', N'Autoriza revogar permissões atribuídas diretamente a um usuário.'),
    (N'R_POST_API_RH_GRUPO_PERMISSAO_ADICIONAR', N'Permite adicionar permissões a um grupo.'),
    (N'R_DELETE_API_RH_GRUPO_PERMISSAO_REMOVER_VALOR', N'Autoriza remover permissões associadas a um grupo.'),
    (N'R_POST_API_RH_GRUPO_CADASTRAR', N'Autoriza o cadastro de novos grupos pelo serviço de RH.'),
    (N'R_GET_RH_USUARIOS', N'Permite consultar usuários do RH via API.'),
    (N'R_GET_HOME', N'Permite carregar o painel inicial do sistema.'),
    (N'R_GET_USUARIOS', N'Permite listar usuários por meio da API pública.');

INSERT INTO RH.Tbl_Permissoes (cod_permissao, descricao_permissao, criado_Usuario_id)
SELECT p.cod_permissao, p.descricao_permissao, 1
FROM @Permissoes p
WHERE NOT EXISTS (
    SELECT 1
    FROM RH.Tbl_Permissoes tp
    WHERE tp.cod_permissao = p.cod_permissao
);

UPDATE RH.Tbl_Permissoes
SET criado_Usuario_id = 1
WHERE cod_permissao IN (SELECT cod_permissao FROM @Permissoes);

DECLARE @AdminId INT = (
    SELECT TOP (1) id_Usuario
    FROM RH.Tbl_Usuarios
    WHERE email = @AdminEmail
);

IF @AdminId IS NOT NULL
BEGIN
    INSERT INTO RH.Tbl_Rel_Usuarios_Permissoes (usuario_id, permissao_id, criado_Usuario_id)
    SELECT @AdminId, perm.id_permissao, 1
    FROM RH.Tbl_Permissoes perm
    WHERE NOT EXISTS (
        SELECT 1
        FROM RH.Tbl_Rel_Usuarios_Permissoes rup
        WHERE rup.usuario_id = @AdminId
          AND rup.permissao_id = perm.id_permissao
          AND rup.dat_cancelamento_em IS NULL
    );

        UPDATE rup
        SET criado_Usuario_id = 1
        FROM RH.Tbl_Rel_Usuarios_Permissoes rup
        WHERE rup.usuario_id = @AdminId
            AND EXISTS (
                    SELECT 1
                    FROM RH.Tbl_Permissoes perm
                    WHERE perm.id_permissao = rup.permissao_id
                        AND perm.cod_permissao IN (SELECT cod_permissao FROM @Permissoes)
            );
END;

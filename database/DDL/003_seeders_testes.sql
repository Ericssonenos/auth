-- Seeders para testes iniciais de usuários e permissões (SQL Server)

SET NOCOUNT ON;

DECLARE @admin_email NVARCHAR(200) = N'adm@exemplo.com';

IF NOT EXISTS (SELECT 1 FROM rh.tb_usuarios WHERE email = @admin_email)
BEGIN
    INSERT INTO rh.tb_usuarios (nome_completo, email, senha, locatario_id, criado_usuario_id)
    VALUES (N'Administrador', @admin_email, 'Senha', 1, 1);
END;

UPDATE rh.tb_usuarios
SET criado_usuario_id = 1
WHERE email = @admin_email;

DECLARE @i INT = 2;

WHILE @i < 10
BEGIN
    DECLARE @email NVARCHAR(200) = CONCAT(N'usuario', @i, N'@exemplo.com');

    IF NOT EXISTS (SELECT 1 FROM rh.tb_usuarios WHERE email = @email)
    BEGIN
        INSERT INTO rh.tb_usuarios (nome_completo, email, senha, locatario_id, criado_usuario_id)
        VALUES (CONCAT(N'Usuário ', @i), @email, 'Senha', 1, 1);
    END;

    SET @i += 1;
END;

UPDATE rh.tb_usuarios
SET criado_usuario_id = 1
WHERE email LIKE N'usuario%@exemplo.com';

DECLARE @categoria_default NVARCHAR(200) = N'Sistema';

IF NOT EXISTS (
    SELECT 1
    FROM rh.tb_categorias
    WHERE nome_categoria = @categoria_default
      AND dat_cancelamento_em IS NULL
)
BEGIN
    INSERT INTO rh.tb_categorias (nome_categoria, descricao_categoria, criado_usuario_id)
    VALUES (@categoria_default, N'Categoria padrão para perfis administrativos.', 1);
END;

UPDATE rh.tb_categorias
SET criado_usuario_id = 1
WHERE nome_categoria = @categoria_default;

DECLARE @permissoes TABLE (cod_permissao NVARCHAR(200), descricao_permissao NVARCHAR(1000));

INSERT INTO @permissoes (cod_permissao, descricao_permissao)
VALUES
    (N'R_POST_API_RH_USUARIO_DADOS', N'Autoriza o cadastro de usuários via API de rh.'),
    (N'N_USUARIO.VIEW', N'Permite visualizar a tela de consulta de usuários.'),
    (N'R_POST_API_RH_USUARIO_PERMISSAO_ADICIONAR', N'Permite conceder permissões manuais a um usuário.'),
    (N'R_POST_API_RH_GRUPO_DADOS', N'Autoriza cadastrar e editar dados de grupos via API de rh.'),
    (N'N_GRUPOS.VIEW', N'Permite visualizar a listagem de grupos no sistema.'),
    (N'R_POST_RH_API_USUARIOS', N'Autoriza operações de criação de usuários no módulo rh.'),
    (N'R_POST_API_RH_PERMISSAO_DADOS', N'Permite cadastrar ou alterar registros de permissões via API.'),
    (N'R_DELETE_API_RH_USUARIO_DELETAR_VALOR', N'Autoriza o cancelamento lógico de usuário via API de rh.'),
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
    (N'R_POST_API_RH_GRUPO_CADASTRAR', N'Autoriza o cadastro de novos grupos pelo serviço de rh.'),
    (N'R_GET_RH_USUARIOS', N'Permite consultar usuários do rh via API.'),
    (N'R_GET_HOME', N'Permite carregar o painel inicial do sistema.'),
    (N'R_GET_USUARIOS', N'Permite listar usuários por meio da API pública.');

INSERT INTO rh.tb_permissoes (cod_permissao, descricao_permissao, criado_usuario_id)
SELECT p.cod_permissao, p.descricao_permissao, 1
FROM @permissoes p
WHERE NOT EXISTS (
    SELECT 1 FROM rh.tb_permissoes tp WHERE tp.cod_permissao = p.cod_permissao
);

UPDATE rh.tb_permissoes
SET criado_usuario_id = 1
WHERE cod_permissao IN (SELECT cod_permissao FROM @permissoes);

DECLARE @admin_id INT = (
    SELECT TOP (1) id_usuario
    FROM rh.tb_usuarios
    WHERE email = @admin_email
);

IF @admin_id IS NOT NULL
BEGIN
    INSERT INTO rh.tr_usuarios_permissoes (usuario_id, permissao_id, criado_usuario_id)
    SELECT @admin_id, perm.id_permissao, 1
    FROM rh.tb_permissoes perm
    WHERE NOT EXISTS (
        SELECT 1
        FROM rh.tr_usuarios_permissoes rup
        WHERE rup.usuario_id = @admin_id
          AND rup.permissao_id = perm.id_permissao
          AND rup.dat_cancelamento_em IS NULL
    );

    UPDATE rup
    SET criado_usuario_id = 1
    FROM rh.tr_usuarios_permissoes rup
    WHERE rup.usuario_id = @admin_id
      AND EXISTS (
            SELECT 1
            FROM rh.tb_permissoes perm
            WHERE perm.id_permissao = rup.permissao_id
              AND perm.cod_permissao IN (SELECT cod_permissao FROM @permissoes)
        );
END;

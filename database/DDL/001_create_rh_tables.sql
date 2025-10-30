SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
SET NOCOUNT ON;

IF NOT EXISTS (SELECT 1 FROM sys.schemas WHERE name = 'rh')
BEGIN
    EXEC('CREATE SCHEMA [rh]');
END;

IF OBJECT_ID('rh.tb_usuarios', 'U') IS NULL
BEGIN
    CREATE TABLE rh.tb_usuarios
    (
        id_usuario INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        nome_completo NVARCHAR(200) NOT NULL,
        email NVARCHAR(200) NOT NULL,
        senha NVARCHAR(200) NOT NULL,
        b_senha_temporaria BIT NOT NULL DEFAULT 0,
        senha_tentativas INT NOT NULL DEFAULT 0,
        dat_senha_bloqueado_em DATETIME2(3) NULL,
        locatario_id INT NOT NULL,
        criado_usuario_id INT NOT NULL DEFAULT 1,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        atualizado_usuario_id INT NULL,
        dat_atualizado_em DATETIME2(3) NULL,
        cancelamento_usuario_id INT NULL,
        dat_cancelamento_em DATETIME2(3) NULL
    );

    CREATE UNIQUE INDEX uq_tb_usuarios_email_ativo
        ON rh.tb_usuarios(email)
        WHERE dat_cancelamento_em IS NULL;
END;

IF OBJECT_ID('rh.tb_categorias', 'U') IS NULL
BEGIN
    CREATE TABLE rh.tb_categorias
    (
        id_categoria INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        nome_categoria NVARCHAR(200) NOT NULL,
        descricao_categoria NVARCHAR(1000) NULL,
        criado_usuario_id INT NOT NULL DEFAULT 1,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        atualizado_usuario_id INT NULL,
        dat_atualizado_em DATETIME2(3) NULL,
        cancelamento_usuario_id INT NULL,
        dat_cancelamento_em DATETIME2(3) NULL
    );
END;

IF OBJECT_ID('rh.tb_grupos', 'U') IS NULL
BEGIN
    CREATE TABLE rh.tb_grupos
    (
        id_grupo INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        nome_grupo NVARCHAR(200) NOT NULL,
        descricao_grupo NVARCHAR(1000) NULL,
        categoria_id INT NULL,
        criado_usuario_id INT NOT NULL DEFAULT 1,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        atualizado_usuario_id INT NULL,
        dat_atualizado_em DATETIME2(3) NULL,
        cancelamento_usuario_id INT NULL,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT fk_tb_grupos_categorias FOREIGN KEY (categoria_id) REFERENCES rh.tb_categorias(id_categoria)
    );
END;

IF OBJECT_ID('rh.tb_permissoes', 'U') IS NULL
BEGIN
    CREATE TABLE rh.tb_permissoes
    (
        id_permissao INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        cod_permissao NVARCHAR(200) NOT NULL,
        descricao_permissao NVARCHAR(1000) NULL,
        criado_usuario_id INT NOT NULL DEFAULT 1,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        atualizado_usuario_id INT NULL,
        dat_atualizado_em DATETIME2(3) NULL,
        cancelamento_usuario_id INT NULL,
        dat_cancelamento_em DATETIME2(3) NULL
    );

    CREATE UNIQUE INDEX uq_tb_permissoes_cod_ativo
        ON rh.tb_permissoes(cod_permissao)
        WHERE dat_cancelamento_em IS NULL;
END;

IF OBJECT_ID('rh.tr_grupos_grupos', 'U') IS NULL
BEGIN
    CREATE TABLE rh.tr_grupos_grupos
    (
        id_rel_grupo_grupo INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        grupo_pai_id INT NOT NULL,
        grupo_filho_id INT NOT NULL,
        criado_usuario_id INT NOT NULL DEFAULT 1,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        cancelamento_usuario_id INT NULL,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT ck_grupos_pai_filho_diferentes CHECK (grupo_pai_id <> grupo_filho_id),
        CONSTRAINT fk_tr_grupos_grupos_pai FOREIGN KEY (grupo_pai_id) REFERENCES rh.tb_grupos(id_grupo),
        CONSTRAINT fk_tr_grupos_grupos_filho FOREIGN KEY (grupo_filho_id) REFERENCES rh.tb_grupos(id_grupo)
    );

    CREATE UNIQUE INDEX uq_tr_grupos_grupos_ativo
        ON rh.tr_grupos_grupos(grupo_pai_id, grupo_filho_id)
        WHERE dat_cancelamento_em IS NULL;
END;

IF OBJECT_ID('rh.tr_usuarios_grupos', 'U') IS NULL
BEGIN
    CREATE TABLE rh.tr_usuarios_grupos
    (
        id_rel_usuario_grupo INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        usuario_id INT NOT NULL,
        grupo_id INT NOT NULL,
        criado_usuario_id INT NOT NULL DEFAULT 1,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        cancelamento_usuario_id INT NULL,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT fk_tr_usuarios_grupos_usuario FOREIGN KEY (usuario_id) REFERENCES rh.tb_usuarios(id_usuario),
        CONSTRAINT fk_tr_usuarios_grupos_grupo FOREIGN KEY (grupo_id) REFERENCES rh.tb_grupos(id_grupo)
    );

    CREATE UNIQUE INDEX uq_tr_usuarios_grupos_ativo
        ON rh.tr_usuarios_grupos(usuario_id, grupo_id)
        WHERE dat_cancelamento_em IS NULL;
END;

IF OBJECT_ID('rh.tr_usuarios_permissoes', 'U') IS NULL
BEGIN
    CREATE TABLE rh.tr_usuarios_permissoes
    (
        id_rel_usuario_permissao INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        usuario_id INT NOT NULL,
        permissao_id INT NOT NULL,
        criado_usuario_id INT NOT NULL DEFAULT 1,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        cancelamento_usuario_id INT NULL,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT fk_tr_usuarios_permissoes_usuario FOREIGN KEY (usuario_id) REFERENCES rh.tb_usuarios(id_usuario),
        CONSTRAINT fk_tr_usuarios_permissoes_permissao FOREIGN KEY (permissao_id) REFERENCES rh.tb_permissoes(id_permissao)
    );

    CREATE UNIQUE INDEX uq_tr_usuarios_permissoes_ativo
        ON rh.tr_usuarios_permissoes(usuario_id, permissao_id)
        WHERE dat_cancelamento_em IS NULL;
END;

IF OBJECT_ID('rh.tr_grupos_permissoes', 'U') IS NULL
BEGIN
    CREATE TABLE rh.tr_grupos_permissoes
    (
        id_rel_grupo_permissao INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        grupo_id INT NOT NULL,
        permissao_id INT NOT NULL,
        criado_usuario_id INT NOT NULL DEFAULT 1,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        cancelamento_usuario_id INT NULL,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT fk_tr_grupos_permissoes_grupo FOREIGN KEY (grupo_id) REFERENCES rh.tb_grupos(id_grupo),
        CONSTRAINT fk_tr_grupos_permissoes_permissao FOREIGN KEY (permissao_id) REFERENCES rh.tb_permissoes(id_permissao)
    );

    CREATE UNIQUE INDEX uq_tr_grupos_permissoes_ativo
        ON rh.tr_grupos_permissoes(grupo_id, permissao_id)
        WHERE dat_cancelamento_em IS NULL;
END;

IF OBJECT_ID('rh.fn_getpermissoesgrupoxml', 'FN') IS NOT NULL
BEGIN
    DROP FUNCTION rh.fn_getpermissoesgrupoxml;
END;
GO

CREATE FUNCTION rh.fn_getpermissoesgrupoxml (@id_grupo INT)
RETURNS NVARCHAR(MAX)
AS
BEGIN
    DECLARE @permissoes NVARCHAR(MAX);

    SELECT @permissoes = STRING_AGG(p.cod_permissao, ', ')
    FROM rh.tb_permissoes p
    INNER JOIN rh.tr_grupos_permissoes rgp ON rgp.permissao_id = p.id_permissao
    WHERE rgp.grupo_id = @id_grupo
      AND rgp.dat_cancelamento_em IS NULL
      AND p.dat_cancelamento_em IS NULL;

    RETURN ISNULL(@permissoes, '');
END;
GO




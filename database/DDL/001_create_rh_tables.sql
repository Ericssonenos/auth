SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
SET NOCOUNT ON;

-- Criar schema RH se não existir
IF NOT EXISTS (SELECT 1
FROM sys.schemas
WHERE name = 'RH')
BEGIN
    EXEC('CREATE SCHEMA [RH]');
END


-- exemplo de base já existente
-- confirmar se id da tabela original é única
IF OBJECT_ID('RH.Tbl_Usuarios', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Usuarios
    (
        id_Usuario INT IDENTITY(1,1) PRIMARY KEY,
        nome_Completo NVARCHAR(200) NULL,
        email NVARCHAR(200) NULL,
        -- senha: recomendamos armazenar hash em produção.
        -- [ ] ativar apos testes: alterar para armazenar hash (bcrypt/argon2) em vez de texto claro
        senha NVARCHAR(200) NULL,
        b_senha_Temporaria BIT NOT NULL DEFAULT 0,
        senha_Tentativas INT NOT NULL DEFAULT 0,
        dat_senha_Bloqueado_em DATETIME2(3) NULL,
        locatario_id INT NOT NULL,
        criado_Usuario_id INT NOT NULL,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        atualizado_Usuario_id INT,
        dat_atualizado_em DATETIME2(3) NULL,
        cancelamento_Usuario_id INT,
        dat_cancelamento_em DATETIME2(3) NULL
    );


    -- email tem que ser unique para dat_cancelamento_em igual a null
    CREATE UNIQUE INDEX UQ_Tbl_Usuarios_Email_Ativo
    ON RH.Tbl_Usuarios(email)
    WHERE dat_cancelamento_em IS NULL;
END

-- Tabelas mestres
IF OBJECT_ID('RH.Tbl_Categorias', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Categorias
    (
        id_categoria INT IDENTITY(1,1) PRIMARY KEY,
        nome_Categoria NVARCHAR(200) NOT NULL,
        descricao_Categoria NVARCHAR(1000) NULL,
        criado_Usuario_id INT NOT NULL,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        atualizado_Usuario_id INT,
        dat_atualizado_em DATETIME2(3) NULL,
        cancelamento_Usuario_id INT,
        dat_cancelamento_em DATETIME2(3) NULL
    );
END

IF OBJECT_ID('RH.Tbl_Grupos', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Grupos
    (
        id_Grupo INT IDENTITY(1,1) PRIMARY KEY,
        nome_Grupo NVARCHAR(200) NOT NULL,
        descricao_Grupo NVARCHAR(1000) NULL,
        categoria_id INT NULL,
        criado_Usuario_id int NOT NULL,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        atualizado_Usuario_id INT,
        dat_atualizado_em DATETIME2(3) NULL,
        cancelamento_Usuario_id INT,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT FK_Tbl_Grupos_Categorias FOREIGN KEY (categoria_id) REFERENCES RH.Tbl_Categorias(id_categoria)
    );
END

IF OBJECT_ID('RH.Tbl_Permissoes', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Permissoes
    (
        id_permissao INT IDENTITY(1,1) PRIMARY KEY,
        cod_permissao NVARCHAR(200) NOT NULL,
        descricao_permissao NVARCHAR(1000) NULL,
        criado_Usuario_id int NOT NULL,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        atualizado_Usuario_id INT,
        dat_atualizado_em DATETIME2(3) NULL,
        cancelamento_Usuario_id INT,
        dat_cancelamento_em DATETIME2(3) NULL
    );
END

-- Tabelas relacionais (histórico) com surrogate PK
IF OBJECT_ID('RH.Tbl_Rel_Grupos_Grupos', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Rel_Grupos_Grupos
    (
        id_rel_grupo_grupo INT IDENTITY(1,1) PRIMARY KEY,
        grupo_pai_id INT NOT NULL,
        grupo_filho_id INT NOT NULL,
        criado_Usuario_id int NOT NULL,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        cancelamento_Usuario_id INT,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT FK_Rel_Grupos_Grupos_Pai FOREIGN KEY (grupo_pai_id) REFERENCES RH.Tbl_Grupos(id_Grupo),
        CONSTRAINT FK_Rel_Grupos_Grupos_Filho FOREIGN KEY (grupo_filho_id) REFERENCES RH.Tbl_Grupos(id_Grupo),
        CONSTRAINT CK_Rel_Grupos_Pai_Filho_DIF CHECK (grupo_pai_id <> grupo_filho_id)
    );
    IF NOT EXISTS (SELECT 1
    FROM sys.indexes
    WHERE name = 'UQ_Rel_Grupos_Grupos_Ativo' AND object_id = OBJECT_ID('RH.Tbl_Rel_Grupos_Grupos'))
    BEGIN
        CREATE UNIQUE INDEX UQ_Rel_Grupos_Grupos_Ativo
        ON RH.Tbl_Rel_Grupos_Grupos(grupo_pai_id, grupo_filho_id)
        WHERE dat_cancelamento_em IS NULL;
    END
END

IF OBJECT_ID('RH.Tbl_Rel_Usuarios_Grupos', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Rel_Usuarios_Grupos
    (
        id_rel_usuario_grupo INT IDENTITY(1,1) PRIMARY KEY,
        Usuario_id int NOT NULL,
        grupo_id INT NOT NULL,
        criado_Usuario_id int NOT NULL,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        cancelamento_Usuario_id INT,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT FK_Rel_Usuario_Grupos_Grupo FOREIGN KEY (grupo_id) REFERENCES RH.Tbl_Grupos(id_Grupo),
        CONSTRAINT FK_Rel_Usuario_Grupos_Usuario FOREIGN KEY (Usuario_id) REFERENCES RH.Tbl_Usuarios(id_Usuario)
    );
    IF NOT EXISTS (SELECT 1
    FROM sys.indexes
    WHERE name = 'UQ_Rel_Usuarios_Grupos_Usuario_Grupo_Active' AND object_id = OBJECT_ID('RH.Tbl_Rel_Usuarios_Grupos'))
    BEGIN
        CREATE UNIQUE INDEX UQ_Rel_Usuarios_Grupos_Usuario_Grupo_Active
        ON RH.Tbl_Rel_Usuarios_Grupos(Usuario_id, grupo_id)
        WHERE dat_cancelamento_em IS NULL;
    END
END

IF OBJECT_ID('RH.Tbl_Rel_Usuarios_Permissoes', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Rel_Usuarios_Permissoes
    (
        id_rel_usuario_permissao INT IDENTITY(1,1) PRIMARY KEY,
        Usuario_id int NOT NULL,
        permissao_id INT NOT NULL,
        criado_Usuario_id int NOT NULL,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        cancelamento_Usuario_id INT,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT FK_Rel_Usuario_Permissao_Permissao FOREIGN KEY (permissao_id) REFERENCES RH.Tbl_Permissoes(id_permissao),
        CONSTRAINT FK_Rel_Usuario_Permissao_Usuario FOREIGN KEY (Usuario_id) REFERENCES RH.Tbl_Usuarios(id_Usuario)
    );
    IF NOT EXISTS (SELECT 1
    FROM sys.indexes
    WHERE name = 'UQ_Rel_Usuarios_Permissoes_Usuario_Permissao_Active' AND object_id = OBJECT_ID('RH.Tbl_Rel_Usuarios_Permissoes'))
    BEGIN
        CREATE UNIQUE INDEX UQ_Rel_Usuarios_Permissoes_Usuario_Permissao_Active
        ON RH.Tbl_Rel_Usuarios_Permissoes(Usuario_id, permissao_id)
        WHERE dat_cancelamento_em IS NULL;
    END
END

IF OBJECT_ID('RH.Tbl_Rel_Grupos_Permissoes', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Rel_Grupos_Permissoes
    (
        id_rel_grupo_permissao INT IDENTITY(1,1) PRIMARY KEY,
        grupo_id INT NOT NULL,
        permissao_id INT NOT NULL,
        criado_Usuario_id int NOT NULL,
        dat_criado_em DATETIME2(3) NOT NULL DEFAULT GETDATE(),
        cancelamento_Usuario_id INT,
        dat_cancelamento_em DATETIME2(3) NULL,
        CONSTRAINT FK_Rel_Grupo_Permissao_Grupo FOREIGN KEY (grupo_id) REFERENCES RH.Tbl_Grupos(id_Grupo),
        CONSTRAINT FK_Rel_Grupo_Permissao_Permissao FOREIGN KEY (permissao_id) REFERENCES RH.Tbl_Permissoes(id_permissao)
    );
    IF NOT EXISTS (SELECT 1
    FROM sys.indexes
    WHERE name = 'UQ_Rel_Grupos_Permissoes_Grupo_Permissao_Active' AND object_id = OBJECT_ID('RH.Tbl_Rel_Grupos_Permissoes'))
    BEGIN
        CREATE UNIQUE INDEX UQ_Rel_Grupos_Permissoes_Grupo_Permissao_Active
        ON RH.Tbl_Rel_Grupos_Permissoes(grupo_id, permissao_id)
        WHERE dat_cancelamento_em IS NULL;
    END
END




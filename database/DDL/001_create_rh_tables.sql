
SET NOCOUNT ON;

-- Criar schema RH se não existir
IF NOT EXISTS (SELECT 1 FROM sys.schemas WHERE name = 'RH')
BEGIN
    EXEC('CREATE SCHEMA [RH]');
END


-- exemplo de base já existente
-- confirmar se id da tabela original é única
IF OBJECT_ID('RH.Users', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Users (
        Id INT IDENTITY(1,1) PRIMARY KEY,
        Matricula NVARCHAR(50) NOT NULL,
        Nome_Completo NVARCHAR(200) NULL
    );
END
ELSE


-- Tabelas mestres
IF OBJECT_ID('RH.Tbl_Categorias', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Categorias (
        id_categoria INT IDENTITY(1,1) PRIMARY KEY,
        txt_nome_categoria NVARCHAR(200) NOT NULL,
        txt_descricao_categoria NVARCHAR(1000) NULL,
        matricula_criado_em NVARCHAR(50) NOT NULL,
        dat_criado_em DATETIME2 NOT NULL,
        matricula_atualizado_em NVARCHAR(50) NULL,
        dat_atualizado_em DATETIME2 NULL,
        matricula_cancelamento_em NVARCHAR(50) NULL,
        dat_cancelamento_em DATETIME2 NULL
    );
END

IF OBJECT_ID('RH.Tbl_Grupos', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Grupos (
        id_grupo INT IDENTITY(1,1) PRIMARY KEY,
        txt_nome_grupo NVARCHAR(200) NOT NULL,
        txt_descricao_grupo NVARCHAR(1000) NULL,
        categoria_id INT NULL,
        matricula_criado_em NVARCHAR(50) NOT NULL,
        dat_criado_em DATETIME2 NOT NULL,
        matricula_atualizado_em NVARCHAR(50) NULL,
        dat_atualizado_em DATETIME2 NULL,
        matricula_cancelamento_em NVARCHAR(50) NULL,
        dat_cancelamento_em DATETIME2 NULL,
        CONSTRAINT FK_Tbl_Grupos_Categorias FOREIGN KEY (categoria_id) REFERENCES RH.Tbl_Categorias(id_categoria)
    );
END

IF OBJECT_ID('RH.Tbl_Permissoes', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Permissoes (
        id_permissao INT IDENTITY(1,1) PRIMARY KEY,
        txt_nome_permissao NVARCHAR(200) NOT NULL,
        txt_descricao_permissao NVARCHAR(1000) NULL,
        matricula_criado_em NVARCHAR(50) NOT NULL,
        dat_criado_em DATETIME2 NOT NULL,
        matricula_atualizado_em NVARCHAR(50) NULL,
        dat_atualizado_em DATETIME2 NULL,
        matricula_cancelamento_em NVARCHAR(50) NULL,
        dat_cancelamento_em DATETIME2 NULL
    );
END

-- Tabelas relacionais (histórico) com surrogate PK
IF OBJECT_ID('RH.Tbl_Rel_Grupos_Grupos', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Rel_Grupos_Grupos (
        id_rel_grupo_grupo INT IDENTITY(1,1) PRIMARY KEY,
        grupo_pai_id INT NOT NULL,
        grupo_filho_id INT NOT NULL,
        matricula_criado_em NVARCHAR(50) NOT NULL,
        dat_criado_em DATETIME2 NOT NULL,
        matricula_cancelamento_em NVARCHAR(50) NULL,
        dat_cancelamento_em DATETIME2 NULL,
        CONSTRAINT FK_Rel_Grupos_Grupos_Pai FOREIGN KEY (grupo_pai_id) REFERENCES RH.Tbl_Grupos(id_grupo),
        CONSTRAINT FK_Rel_Grupos_Grupos_Filho FOREIGN KEY (grupo_filho_id) REFERENCES RH.Tbl_Grupos(id_grupo)
    );
END

IF OBJECT_ID('RH.Tbl_Rel_Usuarios_Grupos', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Rel_Usuarios_Grupos (
        id_rel_usuario_grupo INT IDENTITY(1,1) PRIMARY KEY,
        usuario_id INT NOT NULL,
        grupo_id INT NOT NULL,
        matricula_criado_em NVARCHAR(50) NOT NULL,
        dat_criado_em DATETIME2 NOT NULL,
        matricula_cancelamento_em NVARCHAR(50) NULL,
        dat_cancelamento_em DATETIME2 NULL,
        CONSTRAINT FK_Rel_Usuarios_Grupos_Grupo FOREIGN KEY (grupo_id) REFERENCES RH.Tbl_Grupos(id_grupo)
        -- FK para RH.Users será adicionada condicionalmente abaixo
    );
END

IF OBJECT_ID('RH.Tbl_Rel_Usuario_Permissao', 'U') IS NULL
BEGIN
    CREATE TABLE RH.Tbl_Rel_Usuario_Permissao (
        id_rel_usuario_permissao INT IDENTITY(1,1) PRIMARY KEY,
        usuario_id INT NOT NULL,
        permissao_id INT NOT NULL,
        matricula_criado_em NVARCHAR(50) NOT NULL,
        dat_criado_em DATETIME2 NOT NULL,
        matricula_cancelamento_em NVARCHAR(50) NULL,
        dat_cancelamento_em DATETIME2 NULL,
        CONSTRAINT FK_Rel_Usuario_Permissao_Permissao FOREIGN KEY (permissao_id) REFERENCES RH.Tbl_Permissoes(id_permissao)
        -- FK para RH.Users será adicionada condicionalmente abaixo
    );
END

-- Adicionar FKs para RH.Users: preferir RH.Users(Id) quando disponível; fallback para RH.Users(matricula)
IF EXISTS (
    SELECT 1
    FROM sys.schemas s
    JOIN sys.tables t ON t.schema_id = s.schema_id
    WHERE s.name = 'RH' AND t.name = 'Users'
)
BEGIN
    -- Se existir coluna Id (PK) em RH.Users, use-a para FK
    IF EXISTS (
        SELECT 1
        FROM sys.columns c
        JOIN sys.tables t ON c.object_id = t.object_id
        JOIN sys.schemas s ON t.schema_id = s.schema_id
        WHERE s.name = 'RH' AND t.name = 'Users' AND c.name = 'Id'
    )
    BEGIN
        IF OBJECT_ID('FK_Rel_Usuarios_Grupos_Users', 'F') IS NULL
        BEGIN
            ALTER TABLE RH.Tbl_Rel_Usuarios_Grupos
            ADD CONSTRAINT FK_Rel_Usuarios_Grupos_Users FOREIGN KEY (usuario_id) REFERENCES RH.Users(Id);
        END

        IF OBJECT_ID('FK_Rel_Usuario_Permissao_Users', 'F') IS NULL
        BEGIN
            ALTER TABLE RH.Tbl_Rel_Usuario_Permissao
            ADD CONSTRAINT FK_Rel_Usuario_Permissao_Users FOREIGN KEY (usuario_id) REFERENCES RH.Users(Id);
        END
    END
    ELSE
    BEGIN
        -- Fallback: se existir coluna matricula e for única, use-a
        IF EXISTS (
            SELECT 1
            FROM sys.indexes i
            JOIN sys.index_columns ic ON ic.object_id = i.object_id AND ic.index_id = i.index_id
            JOIN sys.columns c ON c.object_id = ic.object_id AND c.column_id = ic.column_id
            JOIN sys.tables t ON t.object_id = i.object_id
            JOIN sys.schemas s ON t.schema_id = s.schema_id
            WHERE s.name = 'RH' AND t.name = 'Users' AND c.name = 'matricula' AND i.is_unique = 1
        )
        BEGIN
            IF OBJECT_ID('FK_Rel_Usuarios_Grupos_Users', 'F') IS NULL
            BEGIN
                ALTER TABLE RH.Tbl_Rel_Usuarios_Grupos
                ADD CONSTRAINT FK_Rel_Usuarios_Grupos_Users FOREIGN KEY (usuario_id) REFERENCES RH.Users(matricula);
            END

            IF OBJECT_ID('FK_Rel_Usuario_Permissao_Users', 'F') IS NULL
            BEGIN
                ALTER TABLE RH.Tbl_Rel_Usuario_Permissao
                ADD CONSTRAINT FK_Rel_Usuario_Permissao_Users FOREIGN KEY (usuario_id) REFERENCES RH.Users(matricula);
            END
        END
        ELSE
        BEGIN
            PRINT 'AVISO: RH.Users encontrada mas não contém coluna Id nem matricula única; as FKs não foram criadas.';
            PRINT 'Para criar as FKs, adicione uma coluna Id (PK) ou um índice único em RH.Users(matricula).';
        END
    END
END
ELSE
BEGIN
    PRINT 'AVISO: RH.Users não encontrada; FKs para Usuario_Id não foram criadas.';
END

-- Índices recomendados para tabelas relacionais
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID('RH.Tbl_Rel_Usuarios_Grupos') AND name = 'IX_Rel_Usuarios_Grupos_Matricula_DatCancel')
BEGIN
    CREATE INDEX IX_Rel_Usuarios_Grupos_Matricula_DatCancel ON RH.Tbl_Rel_Usuarios_Grupos (Matricula_Cod, Dat_Cancelamento_Em);
END

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE object_id = OBJECT_ID('RH.Tbl_Rel_Usuario_Permissao') AND name = 'IX_Rel_Usuario_Permissao_Matricula_DatCancel')
BEGIN
    CREATE INDEX IX_Rel_Usuario_Permissao_Matricula_DatCancel ON RH.Tbl_Rel_Usuario_Permissao (Matricula_Cod, Dat_Cancelamento_Em);
END

PRINT 'Script finalizado.';

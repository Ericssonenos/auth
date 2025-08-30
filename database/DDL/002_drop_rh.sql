-- Script: 002_drop_rh.sql
-- Objetivo: remover objetos do schema RH (DROP) de forma segura.
-- Observação importante: este script NÃO remove a tabela RH.Tbl_Usuarios (pertence ao cliente).
-- Execute com cuidado em ambiente de produção.

SET NOCOUNT ON;

PRINT 'Iniciando limpeza do schema RH (exceto RH.Tbl_Usuarios)...';

DECLARE @sql NVARCHAR(MAX);
DECLARE @name SYSNAME;
DECLARE @schema SYSNAME;

-- 1) Dropar FKs em tabelas do schema RH
PRINT '1) Removendo constraints de FK em tabelas do schema RH...';
WHILE EXISTS (SELECT 1 FROM sys.foreign_keys fk JOIN sys.tables t ON fk.parent_object_id = t.object_id WHERE SCHEMA_NAME(t.schema_id) = 'RH')
BEGIN
    SELECT TOP(1) @name = fk.name, @schema = SCHEMA_NAME(t.schema_id)
    FROM sys.foreign_keys fk
    JOIN sys.tables t ON fk.parent_object_id = t.object_id
    WHERE SCHEMA_NAME(t.schema_id) = 'RH';

    SET @sql = N'ALTER TABLE [' + @schema + N'].[' + REPLACE(@name, '''', '''''') + N'] DROP CONSTRAINT [' + @name + N']';
    -- O SELECT acima pegou o nome da constraint, mas precisamos referenciar a tabela pai correta
    -- Melhor construir a instrução a partir do objeto fk e da tabela pai explicitamente
    SELECT TOP(1) @sql = N'ALTER TABLE [' + SCHEMA_NAME(tp.schema_id) + N'].[' + tp.name + N'] DROP CONSTRAINT [' + fk.name + N']'
    FROM sys.foreign_keys fk
    JOIN sys.tables tp ON fk.parent_object_id = tp.object_id
    WHERE SCHEMA_NAME(tp.schema_id) = 'RH' AND fk.name = @name;

    PRINT @sql;
    EXEC sp_executesql @sql;
END

-- 2) Dropar vistas, procedures e funções no schema RH
PRINT '2) Removendo views, procedures e funções em RH...';
-- Views
WHILE EXISTS (SELECT 1 FROM sys.views v JOIN sys.schemas s ON v.schema_id = s.schema_id WHERE s.name = 'RH')
BEGIN
    SELECT TOP(1) @sql = N'DROP VIEW [' + s.name + N'].[' + v.name + N']'
    FROM sys.views v
    JOIN sys.schemas s ON v.schema_id = s.schema_id
    WHERE s.name = 'RH';

    PRINT @sql;
    EXEC sp_executesql @sql;
END

-- Stored Procedures
WHILE EXISTS (SELECT 1 FROM sys.procedures p JOIN sys.schemas s ON p.schema_id = s.schema_id WHERE s.name = 'RH')
BEGIN
    SELECT TOP(1) @sql = N'DROP PROCEDURE [' + s.name + N'].[' + p.name + N']'
    FROM sys.procedures p
    JOIN sys.schemas s ON p.schema_id = s.schema_id
    WHERE s.name = 'RH';

    PRINT @sql;
    EXEC sp_executesql @sql;
END

-- Functions (scalar/table-valued)
WHILE EXISTS (SELECT 1 FROM sys.objects o JOIN sys.schemas s ON o.schema_id = s.schema_id WHERE s.name = 'RH' AND o.type IN ('FN','IF','TF','FS','FT'))
BEGIN
    SELECT TOP(1) @sql = N'DROP FUNCTION [' + s.name + N'].[' + o.name + N']'
    FROM sys.objects o
    JOIN sys.schemas s ON o.schema_id = s.schema_id
    WHERE s.name = 'RH' AND o.type IN ('FN','IF','TF','FS','FT');

    PRINT @sql;
    EXEC sp_executesql @sql;
END

-- 3) Dropar tabelas do schema RH exceto RH.Tbl_Usuarios
PRINT '3) Removendo tabelas do schema RH (exceto RH.Tbl_Usuarios)...';
WHILE EXISTS (SELECT 1 FROM sys.tables t JOIN sys.schemas s ON t.schema_id = s.schema_id WHERE s.name = 'RH' AND t.name <> 'Tbl_Usuarios')
BEGIN
    SELECT TOP(1) @sql = N'DROP TABLE [' + s.name + N'].[' + t.name + N']'
    FROM sys.tables t
    JOIN sys.schemas s ON t.schema_id = s.schema_id
    WHERE s.name = 'RH';

    PRINT @sql;
    EXEC sp_executesql @sql;
END

-- 4) Remover tipos e sequences (se existirem)
PRINT '4) Removendo types e sequences em RH (se existirem)...';
WHILE EXISTS (SELECT 1 FROM sys.types t JOIN sys.schemas s ON t.schema_id = s.schema_id WHERE s.name = 'RH' AND t.is_user_defined = 1)
BEGIN
    SELECT TOP(1) @sql = N'DROP TYPE [' + s.name + N'].[' + t.name + N']'
    FROM sys.types t
    JOIN sys.schemas s ON t.schema_id = s.schema_id
    WHERE s.name = 'RH' AND t.is_user_defined = 1;

    PRINT @sql;
    EXEC sp_executesql @sql;
END

WHILE EXISTS (SELECT 1 FROM sys.sequences seq JOIN sys.schemas s ON seq.schema_id = s.schema_id WHERE s.name = 'RH')
BEGIN
    SELECT TOP(1) @sql = N'DROP SEQUENCE [' + s.name + N'].[' + seq.name + N']'
    FROM sys.sequences seq
    JOIN sys.schemas s ON seq.schema_id = s.schema_id
    WHERE s.name = 'RH';

    PRINT @sql;
    EXEC sp_executesql @sql;
END



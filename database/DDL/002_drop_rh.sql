-- Script: 002_drop_rh.sql
-- Objetivo: remover objetos do schema rh sem apagar a tabela principal de usuários, se desejado.

SET NOCOUNT ON;

PRINT 'Iniciando limpeza do schema rh...';

DECLARE @sql NVARCHAR(MAX);

-- Remover função
IF OBJECT_ID('rh.fn_getpermissoesgrupoxml', 'FN') IS NOT NULL
BEGIN
    DROP FUNCTION rh.fn_getpermissoesgrupoxml;
END;

-- Remover tabelas relacionais
IF OBJECT_ID('rh.tr_usuarios_permissoes', 'U') IS NOT NULL
BEGIN
    DROP TABLE rh.tr_usuarios_permissoes;
END;

IF OBJECT_ID('rh.tr_usuarios_grupos', 'U') IS NOT NULL
BEGIN
    DROP TABLE rh.tr_usuarios_grupos;
END;

IF OBJECT_ID('rh.tr_grupos_permissoes', 'U') IS NOT NULL
BEGIN
    DROP TABLE rh.tr_grupos_permissoes;
END;

IF OBJECT_ID('rh.tr_grupos_grupos', 'U') IS NOT NULL
BEGIN
    DROP TABLE rh.tr_grupos_grupos;
END;

-- Remover tabelas mestre (exceto rh.tb_usuarios, caso já exista e deva ser preservada)
IF OBJECT_ID('rh.tb_permissoes', 'U') IS NOT NULL
BEGIN
    DROP TABLE rh.tb_permissoes;
END;

IF OBJECT_ID('rh.tb_grupos', 'U') IS NOT NULL
BEGIN
    DROP TABLE rh.tb_grupos;
END;

IF OBJECT_ID('rh.tb_categorias', 'U') IS NOT NULL
BEGIN
    DROP TABLE rh.tb_categorias;
END;

PRINT 'Limpeza do schema rh concluída. Tabela rh.tb_usuarios mantida.';



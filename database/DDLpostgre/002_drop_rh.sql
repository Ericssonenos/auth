-- Script: 002_drop_rh.sql (PostgreSQL)
-- Objetivo: remover objetos do schema rh preservando a tabela rh.tb_usuarios quando desejado.

DO $$
BEGIN
    RAISE NOTICE 'Limpando schema rh...';

    DROP FUNCTION IF EXISTS rh.fn_getpermissoesgrupoxml(INTEGER);

    DROP TABLE IF EXISTS rh.tr_usuarios_permissoes;
    DROP TABLE IF EXISTS rh.tr_usuarios_grupos;
    DROP TABLE IF EXISTS rh.tr_grupos_permissoes;
    DROP TABLE IF EXISTS rh.tr_grupos_grupos;
    DROP TABLE IF EXISTS rh.tb_permissoes;
    DROP TABLE IF EXISTS rh.tb_grupos;
    DROP TABLE IF EXISTS rh.tb_categorias;

    RAISE NOTICE 'Objetos auxiliares removidos. Tabela rh.tb_usuarios mantida.';
END;
$$;

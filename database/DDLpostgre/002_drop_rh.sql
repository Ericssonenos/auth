-- Script: 002_drop_rh.sql (PostgreSQL)
-- Objetivo: remover objetos do schema RH com segurança, preservando a tabela RH."Tbl_Usuarios".

DO $$
DECLARE
    rec RECORD;
BEGIN
    RAISE NOTICE 'Iniciando limpeza do schema RH (exceto RH.Tbl_Usuarios)...';

    -- Remover constraints de chave estrangeira
    FOR rec IN
        SELECT con.conname AS constraint_name,
               nsp.nspname AS schema_name,
               rel.relname AS table_name
        FROM pg_constraint con
        JOIN pg_class rel ON con.conrelid = rel.oid
        JOIN pg_namespace nsp ON rel.relnamespace = nsp.oid
        WHERE nsp.nspname = 'RH'
          AND con.contype = 'f'
    LOOP
        EXECUTE format('ALTER TABLE "%I"."%I" DROP CONSTRAINT "%I";',
                       rec.schema_name,
                       rec.table_name,
                       rec.constraint_name);
        RAISE NOTICE 'Constraint % dropped da tabela %.%',
                     rec.constraint_name, rec.schema_name, rec.table_name;
    END LOOP;
END $$;

-- Remover função
DROP FUNCTION IF EXISTS "RH"."Fn_GetPermissoesGrupoXML"(INTEGER);

-- Remover tabelas relacionais e auxiliares (exceto RH."Tbl_Usuarios")
DROP TABLE IF EXISTS "RH"."Tbl_Rel_Grupos_Permissoes";
DROP TABLE IF EXISTS "RH"."Tbl_Rel_Usuarios_Permissoes";
DROP TABLE IF EXISTS "RH"."Tbl_Rel_Usuarios_Grupos";
DROP TABLE IF EXISTS "RH"."Tbl_Rel_Grupos_Grupos";
DROP TABLE IF EXISTS "RH"."Tbl_Permissoes";
DROP TABLE IF EXISTS "RH"."Tbl_Grupos";
DROP TABLE IF EXISTS "RH"."Tbl_Categorias";

-- Observação: RH."Tbl_Usuarios" é mantida conforme requisito.

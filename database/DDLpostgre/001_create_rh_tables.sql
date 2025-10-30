-- Script: 001_create_rh_tables.sql (PostgreSQL)
-- Objetivo: criar os objetos principais do schema rh com identificadores minúsculos e prefixos tb_/tr_.

CREATE SCHEMA IF NOT EXISTS rh;

CREATE TABLE IF NOT EXISTS rh.tb_usuarios (
    id_usuario INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome_completo VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    senha VARCHAR(200) NOT NULL,
    b_senha_temporaria BOOLEAN NOT NULL DEFAULT FALSE,
    senha_tentativas INTEGER NOT NULL DEFAULT 0,
    dat_senha_bloqueado_em TIMESTAMP(3),
    locatario_id INTEGER NOT NULL,
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_usuarios_email_ativo
    ON rh.tb_usuarios (email)
    WHERE dat_cancelamento_em IS NULL;

CREATE TABLE IF NOT EXISTS rh.tb_categorias (
    id_categoria INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome_categoria VARCHAR(200) NOT NULL,
    descricao_categoria VARCHAR(1000),
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3)
);

CREATE TABLE IF NOT EXISTS rh.tb_grupos (
    id_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome_grupo VARCHAR(200) NOT NULL,
    descricao_grupo VARCHAR(1000),
    categoria_id INTEGER,
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3),
    CONSTRAINT fk_tb_grupos_categorias FOREIGN KEY (categoria_id) REFERENCES rh.tb_categorias (id_categoria)
);

CREATE TABLE IF NOT EXISTS rh.tb_permissoes (
    id_permissao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod_permissao VARCHAR(200) NOT NULL,
    descricao_permissao VARCHAR(1000),
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_permissoes_cod_ativo
    ON rh.tb_permissoes (cod_permissao)
    WHERE dat_cancelamento_em IS NULL;

CREATE TABLE IF NOT EXISTS rh.tr_grupos_grupos (
    id_rel_grupo_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    grupo_pai_id INTEGER NOT NULL,
    grupo_filho_id INTEGER NOT NULL,
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3),
    CONSTRAINT ck_grupos_pai_filho_diferentes CHECK (grupo_pai_id <> grupo_filho_id),
    CONSTRAINT fk_tr_grupos_grupos_pai FOREIGN KEY (grupo_pai_id) REFERENCES rh.tb_grupos (id_grupo),
    CONSTRAINT fk_tr_grupos_grupos_filho FOREIGN KEY (grupo_filho_id) REFERENCES rh.tb_grupos (id_grupo)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_tr_grupos_grupos_ativo
    ON rh.tr_grupos_grupos (grupo_pai_id, grupo_filho_id)
    WHERE dat_cancelamento_em IS NULL;

CREATE TABLE IF NOT EXISTS rh.tr_usuarios_grupos (
    id_rel_usuario_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id INTEGER NOT NULL,
    grupo_id INTEGER NOT NULL,
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3),
    CONSTRAINT fk_tr_usuarios_grupos_usuario FOREIGN KEY (usuario_id) REFERENCES rh.tb_usuarios (id_usuario),
    CONSTRAINT fk_tr_usuarios_grupos_grupo FOREIGN KEY (grupo_id) REFERENCES rh.tb_grupos (id_grupo)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_tr_usuarios_grupos_ativo
    ON rh.tr_usuarios_grupos (usuario_id, grupo_id)
    WHERE dat_cancelamento_em IS NULL;

CREATE TABLE IF NOT EXISTS rh.tr_usuarios_permissoes (
    id_rel_usuario_permissao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario_id INTEGER NOT NULL,
    permissao_id INTEGER NOT NULL,
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3),
    CONSTRAINT fk_tr_usuarios_permissoes_usuario FOREIGN KEY (usuario_id) REFERENCES rh.tb_usuarios (id_usuario),
    CONSTRAINT fk_tr_usuarios_permissoes_permissao FOREIGN KEY (permissao_id) REFERENCES rh.tb_permissoes (id_permissao)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_tr_usuarios_permissoes_ativo
    ON rh.tr_usuarios_permissoes (usuario_id, permissao_id)
    WHERE dat_cancelamento_em IS NULL;

CREATE TABLE IF NOT EXISTS rh.tr_grupos_permissoes (
    id_rel_grupo_permissao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    grupo_id INTEGER NOT NULL,
    permissao_id INTEGER NOT NULL,
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3),
    CONSTRAINT fk_tr_grupos_permissoes_grupo FOREIGN KEY (grupo_id) REFERENCES rh.tb_grupos (id_grupo),
    CONSTRAINT fk_tr_grupos_permissoes_permissao FOREIGN KEY (permissao_id) REFERENCES rh.tb_permissoes (id_permissao)
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_tr_grupos_permissoes_ativo
    ON rh.tr_grupos_permissoes (grupo_id, permissao_id)
    WHERE dat_cancelamento_em IS NULL;

DROP FUNCTION IF EXISTS rh.fn_getpermissoesgrupoxml(INTEGER);

CREATE OR REPLACE FUNCTION rh.fn_getpermissoesgrupoxml(id_grupo INTEGER)
RETURNS TEXT
LANGUAGE plpgsql
AS $$
DECLARE
    permissoes_text TEXT;
BEGIN
    SELECT STRING_AGG(p.cod_permissao, ', ' ORDER BY p.cod_permissao)
    INTO permissoes_text
    FROM rh.tb_permissoes p
    INNER JOIN rh.tr_grupos_permissoes rgp ON p.id_permissao = rgp.permissao_id
    WHERE rgp.grupo_id = id_grupo
      AND rgp.dat_cancelamento_em IS NULL
      AND p.dat_cancelamento_em IS NULL;

    RETURN COALESCE(permissoes_text, '');
END;
$$;

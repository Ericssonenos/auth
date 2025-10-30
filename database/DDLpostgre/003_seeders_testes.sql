-- Script: 003_seeders_testes.sql (PostgreSQL)
-- Objetivo: popular dados básicos com identificadores em minúsculo.

DO $$
DECLARE
    admin_email       TEXT := 'adm@exemplo.com';
    usuario_email     TEXT;
    i                 INTEGER := 2;
    categoria_default TEXT := 'Sistema';
    admin_id          INTEGER;
    perm              RECORD;
BEGIN
    IF NOT EXISTS (SELECT 1 FROM rh.tb_usuarios WHERE email = admin_email) THEN
        INSERT INTO rh.tb_usuarios (nome_completo, email, senha, locatario_id, criado_usuario_id)
        VALUES ('Administrador', admin_email, 'Senha', 1, 1);
    END IF;

    UPDATE rh.tb_usuarios
    SET criado_usuario_id = 1
    WHERE email = admin_email;

    WHILE i < 10 LOOP
        usuario_email := format('usuario%1$s@exemplo.com', i);

        IF NOT EXISTS (SELECT 1 FROM rh.tb_usuarios WHERE email = usuario_email) THEN
            INSERT INTO rh.tb_usuarios (nome_completo, email, senha, locatario_id, criado_usuario_id)
            VALUES (format('Usuário %s', i), usuario_email, 'Senha', 1, 1);
        END IF;

        i := i + 1;
    END LOOP;

    UPDATE rh.tb_usuarios
    SET criado_usuario_id = 1
    WHERE email ~ '^usuario[0-9]+@exemplo\.com$';

    IF NOT EXISTS (
        SELECT 1
        FROM rh.tb_categorias
        WHERE nome_categoria = categoria_default
          AND dat_cancelamento_em IS NULL
    ) THEN
        INSERT INTO rh.tb_categorias (nome_categoria, descricao_categoria, criado_usuario_id)
        VALUES (categoria_default, 'Categoria padrão para perfis administrativos.', 1);
    END IF;

    UPDATE rh.tb_categorias
    SET criado_usuario_id = 1
    WHERE nome_categoria = categoria_default;

    FOR perm IN
        SELECT *
        FROM (VALUES
            ('R_POST_API_RH_USUARIO_DADOS', 'Autoriza o cadastro de usuários via API de rh.'),
            ('N_USUARIO.VIEW', 'Permite visualizar a tela de consulta de usuários.'),
            ('R_POST_API_RH_USUARIO_PERMISSAO_ADICIONAR', 'Permite conceder permissões manuais a um usuário.'),
            ('R_POST_API_RH_GRUPO_DADOS', 'Autoriza cadastrar e editar dados de grupos via API de rh.'),
            ('N_GRUPOS.VIEW', 'Permite visualizar a listagem de grupos no sistema.'),
            ('R_POST_RH_API_USUARIOS', 'Autoriza operações de criação de usuários no módulo rh.'),
            ('R_POST_API_RH_PERMISSAO_DADOS', 'Permite cadastrar ou alterar registros de permissões via API.'),
            ('R_DELETE_API_RH_USUARIO_DELETAR_VALOR', 'Autoriza o cancelamento lógico de usuário via API de rh.'),
            ('R_POST_API_RH_USUARIO_GRUPO_ADICIONAR', 'Permite vincular um usuário a um grupo.'),
            ('R_DELETE_API_RH_USUARIO_GRUPO_REMOVER_VALOR', 'Autoriza remover o vínculo entre usuário e grupo.'),
            ('R_PUT_API_RH_USUARIO_ATUALIZAR_VALOR', 'Permite atualizar dados cadastrais de usuário via API.'),
            ('N_USUARIO.GERAR_SENHA', 'Habilita a funcionalidade de geração de senha na tela de usuários.'),
            ('R_POST_RH_USUARIO_VALOR_GERAR_SENHA', 'Autoriza a geração de nova senha para um usuário via API.'),
            ('R_POST_API_RH_CATEGORIA_DADOS', 'Permite cadastrar ou alterar categorias vinculadas a grupos.'),
            ('R_DELETE_API_RH_GRUPO_DELETAR_VALOR', 'Autoriza o cancelamento lógico de um grupo via API.'),
            ('R_PUT_API_RH_GRUPO_ATUALIZAR_VALOR', 'Permite atualizar dados de grupo via API.'),
            ('R_DELETE_API_RH_USUARIO_PERMISSAO_REMOVER_VALOR', 'Autoriza revogar permissões atribuídas diretamente a um usuário.'),
            ('R_POST_API_RH_GRUPO_PERMISSAO_ADICIONAR', 'Permite adicionar permissões a um grupo.'),
            ('R_DELETE_API_RH_GRUPO_PERMISSAO_REMOVER_VALOR', 'Autoriza remover permissões associadas a um grupo.'),
            ('R_POST_API_RH_GRUPO_CADASTRAR', 'Autoriza o cadastro de novos grupos pelo serviço de rh.'),
            ('R_GET_RH_USUARIOS', 'Permite consultar usuários do rh via API.'),
            ('R_GET_HOME', 'Permite carregar o painel inicial do sistema.'),
            ('R_GET_USUARIOS', 'Permite listar usuários por meio da API pública.')
        ) AS perms(cod_permissao, descricao_permissao)
    LOOP
        IF NOT EXISTS (SELECT 1 FROM rh.tb_permissoes WHERE cod_permissao = perm.cod_permissao) THEN
            INSERT INTO rh.tb_permissoes (cod_permissao, descricao_permissao, criado_usuario_id)
            VALUES (perm.cod_permissao, perm.descricao_permissao, 1);
        END IF;

        UPDATE rh.tb_permissoes
        SET criado_usuario_id = 1,
            descricao_permissao = perm.descricao_permissao
        WHERE cod_permissao = perm.cod_permissao;
    END LOOP;

    SELECT id_usuario
    INTO admin_id
    FROM rh.tb_usuarios
    WHERE email = admin_email
    ORDER BY id_usuario
    LIMIT 1;

    IF admin_id IS NOT NULL THEN
        FOR perm IN
            SELECT id_permissao AS permissao_id
            FROM rh.tb_permissoes
        LOOP
            IF NOT EXISTS (
                SELECT 1
                FROM rh.tr_usuarios_permissoes
                WHERE usuario_id = admin_id
                  AND permissao_id = perm.permissao_id
                  AND dat_cancelamento_em IS NULL
            ) THEN
                INSERT INTO rh.tr_usuarios_permissoes (usuario_id, permissao_id, criado_usuario_id)
                VALUES (admin_id, perm.permissao_id, 1);
            END IF;
        END LOOP;

        UPDATE rh.tr_usuarios_permissoes
        SET criado_usuario_id = 1
        WHERE usuario_id = admin_id
          AND permissao_id IN (
              SELECT id_permissao
              FROM rh.tb_permissoes
              WHERE cod_permissao IN (
                  'R_POST_API_RH_USUARIO_DADOS',
                  'N_USUARIO.VIEW',
                  'R_POST_API_RH_USUARIO_PERMISSAO_ADICIONAR',
                  'R_POST_API_RH_GRUPO_DADOS',
                  'N_GRUPOS.VIEW',
                  'R_POST_RH_API_USUARIOS',
                  'R_POST_API_RH_PERMISSAO_DADOS',
                  'R_DELETE_API_RH_USUARIO_DELETAR_VALOR',
                  'R_POST_API_RH_USUARIO_GRUPO_ADICIONAR',
                  'R_DELETE_API_RH_USUARIO_GRUPO_REMOVER_VALOR',
                  'R_PUT_API_RH_USUARIO_ATUALIZAR_VALOR',
                  'N_USUARIO.GERAR_SENHA',
                  'R_POST_RH_USUARIO_VALOR_GERAR_SENHA',
                  'R_POST_API_RH_CATEGORIA_DADOS',
                  'R_DELETE_API_RH_GRUPO_DELETAR_VALOR',
                  'R_PUT_API_RH_GRUPO_ATUALIZAR_VALOR',
                  'R_DELETE_API_RH_USUARIO_PERMISSAO_REMOVER_VALOR',
                  'R_POST_API_RH_GRUPO_PERMISSAO_ADICIONAR',
                  'R_DELETE_API_RH_GRUPO_PERMISSAO_REMOVER_VALOR',
                  'R_POST_API_RH_GRUPO_CADASTRAR',
                  'R_GET_RH_USUARIOS',
                  'R_GET_HOME',
                  'R_GET_USUARIOS'
              )
          );
    END IF;
END;
$$;

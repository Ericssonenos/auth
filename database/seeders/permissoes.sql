-- Insere 10 permissões de exemplo em RH.Tbl_Permissoes
SET NOCOUNT ON;

INSERT INTO RH.Tbl_Permissoes (cod_permissao, descricao_permissao, criado_Usuario_id)
VALUES
('PERM_ACESSAR_DASHBOARD',   'Permite acessar o painel principal do sistema.',                                       'C000000'),
('PERM_CRIAR_USUARIO',       'Permite criar novos usuários no sistema.',                                               'C000000'),
('PERM_ATUALIZAR_USUARIO',   'Permite editar informações de usuários existentes.',                                     'C000000'),
('PERM_EXCLUIR_USUARIO',     'Permite marcar usuários como excluídos ou remover acesso (uso controlado).',             'C000000'),
('PERM_ATRIBUIR_GRUPO',      'Permite vincular usuários a grupos/roles.',                                              'C000000'),
('PERM_REMOVER_GRUPO',       'Permite revogar vínculo de usuário com um grupo (marca cancelamento).',                 'C000000'),
('PERM_VISUALIZAR_RELATORIOS','Permite visualizar relatórios e dashboards específicos.',                               'C000000'),
('PERM_EXPORTAR_DADOS',      'Permite exportar dados em CSV/XLS (uso sensível; auditar).',                             'C000000'),
('PERM_GERENCIAR_PERMISSOES','Permite criar/editar/permissões e atribuições globais.',                                 'C000000'),
('PERM_GERENCIAR_GRUPOS',    'Permite criar/editar grupos e gerenciar hierarquia de grupos.',                          'C000000');

-- Verificação rápida
SELECT id_permissao, cod_permissao, descricao_permissao, criado_Usuario_id, dat_criado_em
FROM RH.Tbl_Permissoes
WHERE criado_Usuario_id = 'C000000'
  AND cod_permissao LIKE 'PERM_%'
ORDER BY id_permissao;

-- DELETE FROM RH.Tbl_Permissoes
-- WHERE criado_Usuario_id = 'C000000'
--   AND cod_permissao IN (
--     'PERM_ACESSAR_DASHBOARD','PERM_CRIAR_USUARIO','PERM_ATUALIZAR_USUARIO','PERM_EXCLUIR_USUARIO',
--     'PERM_ATRIBUIR_GRUPO','PERM_REMOVER_GRUPO','PERM_VISUALIZAR_RELATORIOS','PERM_EXPORTAR_DADOS',
--     'PERM_GERENCIAR_PERMISSOES','PERM_GERENCIAR_GRUPOS'
-- );

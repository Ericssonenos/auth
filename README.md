# Mind — Auth (RH) — Resumo do Projeto

O que é
- Módulo DDL/estrutura para gerenciamento de autorização (grupos, permissões e vínculos históricos) usado pela aplicação Mind.
- Fornece schema, tabelas mestres e relacionais (histórico) e convenções para auditoria e vigência de vínculos.


User stories / UX (alto nível)
- Como administrador, ao vincular um usuário a um grupo, eu crio um novo registro histórico — histórico preservado.
- Como administrador, ao revogar um vínculo, eu marco o registro com is_usuario + data de cancelamento; não deleto.
- Como sistema, para calcular permissões atuais de um usuário, eu:
  1) coleto vínculos ativos do usuário → grupos ativos;
  2) expando hierarquia de grupos (quando aplicável);
  3) junto permissões diretas + permissões herdadas via grupos.
- Relatórios e auditoria devem usar a lógica de vigência (criado <= data e (cancelamento IS NULL ou cancelamento > data)).

Onde estão os scripts
- database/DDL/001_create_rh_tables.sql — criação (rodar uma vez por ambiente).
- database/DDL/002_drop_rh.sql — drop (uso controlado).
- Diagrama em database/DDL/Diagrama_AUTH_.png



[ ] Add nivel do usuário 0 a 20 onde o de nivel maior podera ver dos niveis inferiores.

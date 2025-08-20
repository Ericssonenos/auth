Controle de Acesso

Descrição
- Objetivo: definir estruturas e convenções para tabelas de domínio e tabelas relacionais (histórico/versões) do módulo de autorização.

Principais decisões e convenções
- Nomenclatura: objetos criados por este projeto usam o schema/nome no formato: `RH.Tbl_<Nome>` (ex.: `RH.Tbl_Grupos`).
- `RH.Users` é uma tabela existente fora do escopo deste projeto; nela existe a coluna `matricula` que representa o `cod` do usuário. Essa tabela é considerada somente leitura e não será alterada por estes scripts.
- Matrícula: `matricula` é o código do usuário utilizado para relacionamentos (não alteraremos essa tabela). Assumimos que `RH.Users.matricula` é única — o script DDL tenta criar FKs (Foreign Keys - Chave estrangeira) apenas se a coluna for única; caso contrário, deixa um aviso.
- Auditoria (ordem e tipos sugeridos):
	- `matricula_criado_em` (VARCHAR)
	- `dat_criado_em` (DATETIME2)
	- `matricula_atualizado_em` (VARCHAR) — aplicar somente em tabelas mestre
	- `dat_atualizado_em` (DATETIME2) — aplicar somente em tabelas mestre
	- `matricula_cancelamento_em` (VARCHAR)
	- `dat_cancelamento_em` (DATETIME2)
- Regras para tabelas relacionais (Rel): não atualizar registros; para 'remover' marca-se `matricula_cancelamento_em` + `dat_cancelamento_em` e para criar um novo vínculo insere-se novo registro. Cada tabela Rel terá um surrogate PK (`IDENTITY`) para permitir histórico.

Tabelas relacionais (histórico) — comportamento e consultas
- Comportamento esperado:
	- Registros relacionais NÃO devem ser atualizados para alterar o vínculo; para remover um vínculo registre `matricula_cancelamento_em` e `dat_cancelamento_em` no registro existente e, se necessário, insira um novo registro para o novo vínculo.
	- Cada registro relacional possui um surrogate PK (`IDENTITY`) para permitir múltiplas versões/histórico entre as mesmas entidades.
	- Campos úteis para consulta de vigência: `dat_criado_em` e `dat_cancelamento_em`.

- Determinar se um vínculo estava ativo em um dado período:
	- Um vínculo está ativo se `dat_criado_em <= @data` e (`dat_cancelamento_em IS NULL OR dat_cancelamento_em > @data`).
	- Exemplo (SQL Server):

````sql
-- Verificar vínculos ativos do usuário X em uma data específica
DECLARE @data DATETIME2 = '2025-08-20';
DECLARE @usuario_id INT = 123;

SELECT *
FROM RH.Tbl_Rel_Usuarios_Grupos r
WHERE r.usuario_id = @usuario_id
	AND r.dat_criado_em <= @data
	AND (r.dat_cancelamento_em IS NULL OR r.dat_cancelamento_em > @data);
````

- Recomendações operacionais:
	- Use índices em colunas usadas com frequência nas consultas temporais (por exemplo `(usuario_id, dat_cancelamento_em)` ou `(grupo_id, dat_cancelamento_em)`).
	- Não crie PKs compostas que impeçam inserir múltiplas versões do mesmo vínculo.
	- Mantenha `matricula_criado_em`/`matricula_cancelamento_em` para auditoria (quem criou/cancelou) e `dat_` para o tempo.
	- Para relatórios de auditoria, considere criar views que retornem apenas os vínculos ativos ou que façam o last-version por par (usuario/grupo) quando necessário.

Tabelas (resumo)
- `RH.Tbl_Grupos` — catálogo de grupos (mestre)
- `RH.Tbl_Categorias` — categorias de grupos (mestre)
- `RH.Tbl_Permissoes` — catálogo de permissões (mestre)
- `RH.Tbl_Rel_Grupos_Grupos` — relação pai/filho entre grupos (relacional, histórico)
- `RH.Tbl_Rel_Usuarios_Grupos` — relação usuário ↔ grupo (relacional, histórico)
- `RH.Tbl_Rel_Usuario_Permissao` — relação usuário ↔ permissão (relacional, histórico)

Observações pendentes
- Confirmar se `RH.Users.id` é de fato única. Se não for, precisaremos discutir estratégia: (1) criar índice único na tabela existente (se permitido), (2) não criar FK e confiar em aplicação, ou (3) usar outra coluna identificadora.

Local dos scripts DDL: `database/DDL/` (primeiro script criado para SQL Server).

-- Fim da descrição --




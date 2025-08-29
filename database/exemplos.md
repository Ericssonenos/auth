# Fluxo usuário ↔ grupo ↔ permissão (histórico) — resumo prático

User story (comportamento esperado)
- Ao vincular um usuário a um grupo: insere-se um novo registro em RH.Tbl_Rel_Usuarios_Grupos (surrogate PK). Nunca atualizar o vínculo para "mudar" — sempre inserir novo e, quando necessário, cancelar o anterior.
- Ao revogar um vínculo: marca-se o registro com `cancelamento_Usuario_id` + `dat_cancelamento_em` (não remover).
- Mesma lógica para relações usuário↔permissão e grupo↔permissão.
- Registro ativo = `dat_cancelamento_em IS NULL`.

Como os dados são usados (principais operações)
1. Atribuir usuário → grupo
```sql
INSERT INTO RH.Tbl_Rel_Usuarios_Grupos (Usuario_id, grupo_id, criado_Usuario_id, dat_criado_em)
VALUES (@usuario, @grupo_id, @mat_logado, GETDATE());
```

2. Revogar vínculo (marca como cancelado)
```sql
UPDATE RH.Tbl_Rel_Usuarios_Grupos
SET cancelamento_Usuario_id = @mat_logado, dat_cancelamento_em = GETDATE()
WHERE id_rel_usuario_grupo = @id AND dat_cancelamento_em IS NULL;
```

3. Vínculos ativos de um usuário (hoje ou em data específica)
```sql
DECLARE @data DATETIME2 = GETDATE();

SELECT *
FROM RH.Tbl_Rel_Usuarios_Grupos r
WHERE r.Usuario_id = @usuario
  AND r.dat_criado_em <= @data
  AND (r.dat_cancelamento_em IS NULL OR r.dat_cancelamento_em > @data);
```

4. Permissões efetivas de um usuário (diretas + via grupos, considerando hierarquia de grupos)
- (a) obter grupos ativos do usuário
- (b) navegar hierarquia de grupos (CTE recursiva) para incluir grupos pai/filho conforme modelo
- (c) agregar permissões diretas + permissões dos grupos encontrados

Exemplo (SQL Server):
```sql
DECLARE @usuario CHAR(7) = '0000001';
DECLARE @data DATETIME2 = GETDATE();

-- grupos diretos ativos
WITH UserGroups AS (
    SELECT grupo_id
    FROM RH.Tbl_Rel_Usuarios_Grupos
    WHERE Usuario_id = @usuario
      AND dat_criado_em <= @data
      AND (dat_cancelamento_em IS NULL OR dat_cancelamento_em > @data)
),
-- hierarquia: expandir para incluir grupos relacionados (pai/filho)
GrpHierarchy AS (
    SELECT g.id_Grupo
    FROM UserGroups ug
    JOIN RH.Tbl_Grupos g ON g.id_Grupo = ug.grupo_id
    UNION ALL
    SELECT rg.grupo_pai_id
    FROM RH.Tbl_Rel_Grupos_Grupos rg
    JOIN GrpHierarchy h ON rg.grupo_filho_id = h.id_Grupo
    WHERE rg.dat_criado_em <= @data
      AND (rg.dat_cancelamento_em IS NULL OR rg.dat_cancelamento_em > @data)
)
-- permissões diretas do usuário
SELECT DISTINCT p.id_permissao, p.cod_permissao
FROM RH.Tbl_Permissoes p
JOIN RH.Tbl_Rel_Usuarios_Permissoes rup ON rup.permissao_id = p.id_permissao
WHERE rup.Usuario_id = @usuario
  AND rup.dat_criado_em <= @data
  AND (rup.dat_cancelamento_em IS NULL OR rup.dat_cancelamento_em > @data)

UNION

-- permissões vindas de grupos (diretos e via hierarquia)
SELECT DISTINCT p.id_permissao, p.cod_permissao
FROM RH.Tbl_Permissoes p
JOIN RH.Tbl_Rel_Grupos_Permissoes gp ON gp.permissao_id = p.id_permissao
JOIN (SELECT DISTINCT id_Grupo FROM GrpHierarchy) gh ON gh.id_Grupo = gp.grupo_id
WHERE gp.dat_criado_em <= @data
  AND (gp.dat_cancelamento_em IS NULL OR gp.dat_cancelamento_em > @data);
```

Boas práticas rápidas
- Definir índice único filtrado (ex.: `(Usuario_id, grupo_id)` WHERE dat_cancelamento_em IS NULL) para garantir só um vínculo ativo por par.
- Consultas temporais devem sempre usar a lógica: criado <= data AND (cancelamento IS NULL OR cancelamento > data).
- Não usar PKs compostas que impeçam histórico (usar surrogate ID).
- Em relatórios, criar views que exponham somente vínculos ativos para simplificar UX.

Resumo UX
- Admin adiciona → nova linha criada (instantâneo).
- Admin remove → marca cancelamento (visível como histórico).
- Usuário vê permissões atuais: resultado da união de permissões diretas + permissões herdadas via grupos (hierarquia).

-- B) Atribuir várias permissões (evita duplicatas ativas) — insira os códigos abaixo
DECLARE @matricula CHAR(7)      = 'C000000';
DECLARE @mat_criado_por CHAR(7) = 'C000002';

DECLARE @Perms TABLE (txt_cod NVARCHAR(200));
INSERT INTO @Perms (txt_cod) VALUES
('PERM_ACESSAR_DASHBOARD'),
('PERM_ATRIBUIR_GRUPO'),
('PERM_VISUALIZAR_RELATORIOS'); -- ajustar lista

INSERT INTO RH.Tbl_Rel_Usuarios_Permissoes (matricula_cod, permissao_id, matricula_criado_por)
SELECT
    @matricula,
    p.id_permissao,
    @mat_criado_por
FROM RH.Tbl_Permissoes p
INNER JOIN @Perms t ON t.txt_cod = p.txt_cod_permissao
WHERE p.dat_cancelamento_em IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM RH.Tbl_Rel_Usuarios_Permissoes rup
      WHERE rup.matricula_cod = @matricula
        AND rup.permissao_id = p.id_permissao
        AND rup.dat_cancelamento_em IS NULL
  );

-- Resultado / checagem
SELECT p.txt_cod_permissao, p.id_permissao, rup.dat_criado_em
FROM RH.Tbl_Permissoes p
LEFT JOIN RH.Tbl_Rel_Usuarios_Permissoes rup
  ON rup.permissao_id = p.id_permissao AND rup.matricula_cod = @matricula
WHERE p.txt_cod_permissao IN (SELECT txt_cod FROM @Perms)
ORDER BY p.txt_cod_permissao;

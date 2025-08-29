-- B) Atribuir várias permissões (evita duplicatas ativas) — insira os códigos abaixo
DECLARE @usuario int     = 1;
DECLARE @criado_Usuario_id int= 2;

DECLARE @Perms TABLE (txt_cod NVARCHAR(200));
INSERT INTO @Perms (txt_cod) VALUES
('PERM_ACESSAR_DASHBOARD'),
('PERM_ATRIBUIR_GRUPO'),
('PERM_VISUALIZAR_RELATORIOS'); -- ajustar lista

INSERT INTO RH.Tbl_Rel_Usuarios_Permissoes (Usuario_id, permissao_id, criado_Usuario_id)
SELECT
    @usuario,
    p.id_permissao,
    @criado_Usuario_id
FROM RH.Tbl_Permissoes p
INNER JOIN @Perms t ON t.txt_cod = p.cod_permissao
WHERE p.dat_cancelamento_em IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM RH.Tbl_Rel_Usuarios_Permissoes rup
      WHERE rup.Usuario_id = @usuario
        AND rup.permissao_id = p.id_permissao
        AND rup.dat_cancelamento_em IS NULL
  );

-- Resultado / checagem
SELECT p.cod_permissao, p.id_permissao, rup.dat_criado_em
FROM RH.Tbl_Permissoes p
LEFT JOIN RH.Tbl_Rel_Usuarios_Permissoes rup
  ON rup.permissao_id = p.id_permissao AND rup.Usuario_id = @usuario
WHERE p.cod_permissao IN (SELECT txt_cod FROM @Perms)
ORDER BY p.cod_permissao;

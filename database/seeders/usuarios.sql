-- Insere 10 usuários: C000000 .. C000009
SET NOCOUNT ON;

DECLARE @i INT = 0;
WHILE @i < 10
BEGIN
    INSERT INTO RH.Tbl_Usuarios (id_Usuario, Nome_Completo, email)
    VALUES (@i, 'Usuário ' + CONVERT(VARCHAR(1), @i), 'usuario' + CONVERT(VARCHAR(1), @i) + '@exemplo.com');
    SET @i = @i + 1;
END

--

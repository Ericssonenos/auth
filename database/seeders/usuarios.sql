-- Insere 10 usuários: C000000 .. C000009
SET NOCOUNT ON;

DECLARE @i INT = 0;
WHILE @i < 10
BEGIN
    DECLARE @mat CHAR(7) = 'C' + RIGHT('000000' + CONVERT(VARCHAR(6), @i), 6);
    INSERT INTO RH.Users (id_Usuario, Nome_Completo)
    VALUES (@mat, 'Usuário ' + @mat);
    SET @i = @i + 1;
END

--

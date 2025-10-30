USE [SUPPLYTEK]
GO

/****** Object:  Table [RH].[Tbl_Usuarios]    Script Date: 29/10/2025 22:23:57 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [RH].[Tbl_Usuarios](
	[id_Usuario] [int] IDENTITY(1,1) NOT NULL,
	[nome_Completo] [nvarchar](200) NULL,
	[email] [nvarchar](200) NULL,
	[senha] [nvarchar](200) NULL,
	[b_senha_Temporaria] [bit] NULL,
	[senha_Tentativas] [int] NULL,
	[dat_senha_Bloqueado_em] [datetime2](3) NULL,
	[locatario_id] [int] NOT NULL,
	[criado_Usuario_id] [int] NOT NULL,
	[dat_criado_em] [datetime2](3) NOT NULL,
	[atualizado_Usuario_id] [int] NULL,
	[dat_atualizado_em] [datetime2](3) NULL,
	[cancelamento_Usuario_id] [int] NULL,
	[dat_cancelamento_em] [datetime2](3) NULL,
 CONSTRAINT [PK__Tbl_Usua__8E901EAA146FE352] PRIMARY KEY CLUSTERED
(
	[id_Usuario] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [RH].[Tbl_Usuarios] ADD  CONSTRAINT [DF__Tbl_Usuar__dat_c__37C5420D]  DEFAULT (getdate()) FOR [dat_criado_em]
GO

USE [SUPPLYTEK]
GO

/****** Object:  Table [RH].[Tbl_Rel_Usuarios_Permissoes]    Script Date: 29/10/2025 22:23:52 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [RH].[Tbl_Rel_Usuarios_Permissoes](
	[id_rel_usuario_permissao] [int] IDENTITY(1,1) NOT NULL,
	[Usuario_id] [int] NOT NULL,
	[permissao_id] [int] NOT NULL,
	[criado_Usuario_id] [int] NOT NULL,
	[dat_criado_em] [datetime2](3) NOT NULL,
	[cancelamento_Usuario_id] [int] NULL,
	[dat_cancelamento_em] [datetime2](3) NULL,
PRIMARY KEY CLUSTERED
(
	[id_rel_usuario_permissao] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Permissoes] ADD  DEFAULT (getdate()) FOR [dat_criado_em]
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Permissoes]  WITH CHECK ADD  CONSTRAINT [FK_Rel_Usuario_Permissao_Permissao] FOREIGN KEY([permissao_id])
REFERENCES [RH].[Tbl_Permissoes] ([id_permissao])
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Permissoes] CHECK CONSTRAINT [FK_Rel_Usuario_Permissao_Permissao]
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Permissoes]  WITH CHECK ADD  CONSTRAINT [FK_Rel_Usuario_Permissao_Usuario] FOREIGN KEY([Usuario_id])
REFERENCES [RH].[Tbl_Usuarios] ([id_Usuario])
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Permissoes] CHECK CONSTRAINT [FK_Rel_Usuario_Permissao_Usuario]
GO

USE [SUPPLYTEK]
GO

/****** Object:  Table [RH].[Tbl_Rel_Usuarios_Grupos]    Script Date: 29/10/2025 22:23:48 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [RH].[Tbl_Rel_Usuarios_Grupos](
	[id_rel_usuario_grupo] [int] IDENTITY(1,1) NOT NULL,
	[Usuario_id] [int] NOT NULL,
	[grupo_id] [int] NOT NULL,
	[criado_Usuario_id] [int] NOT NULL,
	[dat_criado_em] [datetime2](3) NOT NULL,
	[cancelamento_Usuario_id] [int] NULL,
	[dat_cancelamento_em] [datetime2](3) NULL,
PRIMARY KEY CLUSTERED
(
	[id_rel_usuario_grupo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Grupos] ADD  DEFAULT (getdate()) FOR [dat_criado_em]
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Grupos]  WITH CHECK ADD  CONSTRAINT [FK_Rel_Usuario_Grupos_Grupo] FOREIGN KEY([grupo_id])
REFERENCES [RH].[Tbl_Grupos] ([id_Grupo])
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Grupos] CHECK CONSTRAINT [FK_Rel_Usuario_Grupos_Grupo]
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Grupos]  WITH CHECK ADD  CONSTRAINT [FK_Rel_Usuario_Grupos_Usuario] FOREIGN KEY([Usuario_id])
REFERENCES [RH].[Tbl_Usuarios] ([id_Usuario])
GO

ALTER TABLE [RH].[Tbl_Rel_Usuarios_Grupos] CHECK CONSTRAINT [FK_Rel_Usuario_Grupos_Usuario]
GO


USE [SUPPLYTEK]
GO

/****** Object:  Table [RH].[Tbl_Rel_Grupos_Permissoes]    Script Date: 29/10/2025 22:23:43 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [RH].[Tbl_Rel_Grupos_Permissoes](
	[id_rel_grupo_permissao] [int] IDENTITY(1,1) NOT NULL,
	[grupo_id] [int] NOT NULL,
	[permissao_id] [int] NOT NULL,
	[criado_Usuario_id] [int] NOT NULL,
	[dat_criado_em] [datetime2](3) NOT NULL,
	[cancelamento_Usuario_id] [int] NULL,
	[dat_cancelamento_em] [datetime2](3) NULL,
PRIMARY KEY CLUSTERED
(
	[id_rel_grupo_permissao] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Permissoes] ADD  DEFAULT (getdate()) FOR [dat_criado_em]
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Permissoes]  WITH CHECK ADD  CONSTRAINT [FK_Rel_Grupo_Permissao_Grupo] FOREIGN KEY([grupo_id])
REFERENCES [RH].[Tbl_Grupos] ([id_Grupo])
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Permissoes] CHECK CONSTRAINT [FK_Rel_Grupo_Permissao_Grupo]
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Permissoes]  WITH CHECK ADD  CONSTRAINT [FK_Rel_Grupo_Permissao_Permissao] FOREIGN KEY([permissao_id])
REFERENCES [RH].[Tbl_Permissoes] ([id_permissao])
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Permissoes] CHECK CONSTRAINT [FK_Rel_Grupo_Permissao_Permissao]
GO


USE [SUPPLYTEK]
GO

/****** Object:  Table [RH].[Tbl_Rel_Grupos_Grupos]    Script Date: 29/10/2025 22:23:39 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [RH].[Tbl_Rel_Grupos_Grupos](
	[id_rel_grupo_grupo] [int] IDENTITY(1,1) NOT NULL,
	[grupo_pai_id] [int] NOT NULL,
	[grupo_filho_id] [int] NOT NULL,
	[criado_Usuario_id] [int] NOT NULL,
	[dat_criado_em] [datetime2](3) NOT NULL,
	[cancelamento_Usuario_id] [int] NULL,
	[dat_cancelamento_em] [datetime2](3) NULL,
PRIMARY KEY CLUSTERED
(
	[id_rel_grupo_grupo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Grupos] ADD  DEFAULT (getdate()) FOR [dat_criado_em]
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Grupos]  WITH CHECK ADD  CONSTRAINT [FK_Rel_Grupos_Grupos_Filho] FOREIGN KEY([grupo_filho_id])
REFERENCES [RH].[Tbl_Grupos] ([id_Grupo])
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Grupos] CHECK CONSTRAINT [FK_Rel_Grupos_Grupos_Filho]
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Grupos]  WITH CHECK ADD  CONSTRAINT [FK_Rel_Grupos_Grupos_Pai] FOREIGN KEY([grupo_pai_id])
REFERENCES [RH].[Tbl_Grupos] ([id_Grupo])
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Grupos] CHECK CONSTRAINT [FK_Rel_Grupos_Grupos_Pai]
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Grupos]  WITH CHECK ADD  CONSTRAINT [CK_Rel_Grupos_Pai_Filho_DIF] CHECK  (([grupo_pai_id]<>[grupo_filho_id]))
GO

ALTER TABLE [RH].[Tbl_Rel_Grupos_Grupos] CHECK CONSTRAINT [CK_Rel_Grupos_Pai_Filho_DIF]
GO


USE [SUPPLYTEK]
GO

/****** Object:  Table [RH].[Tbl_Permissoes]    Script Date: 29/10/2025 22:23:34 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [RH].[Tbl_Permissoes](
	[id_permissao] [int] IDENTITY(1,1) NOT NULL,
	[cod_permissao] [nvarchar](200) NOT NULL,
	[descricao_permissao] [nvarchar](1000) NULL,
	[criado_Usuario_id] [int] NOT NULL,
	[dat_criado_em] [datetime2](3) NOT NULL,
	[atualizado_Usuario_id] [int] NULL,
	[dat_atualizado_em] [datetime2](3) NULL,
	[cancelamento_Usuario_id] [int] NULL,
	[dat_cancelamento_em] [datetime2](3) NULL,
PRIMARY KEY CLUSTERED
(
	[id_permissao] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [RH].[Tbl_Permissoes] ADD  DEFAULT (getdate()) FOR [dat_criado_em]
GO


USE [SUPPLYTEK]
GO

/****** Object:  Table [RH].[Tbl_Grupos]    Script Date: 29/10/2025 22:23:27 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [RH].[Tbl_Grupos](
	[id_Grupo] [int] IDENTITY(1,1) NOT NULL,
	[nome_Grupo] [nvarchar](200) NOT NULL,
	[descricao_Grupo] [nvarchar](1000) NULL,
	[categoria_id] [int] NULL,
	[criado_Usuario_id] [int] NOT NULL,
	[dat_criado_em] [datetime2](3) NOT NULL,
	[atualizado_Usuario_id] [int] NULL,
	[dat_atualizado_em] [datetime2](3) NULL,
	[cancelamento_Usuario_id] [int] NULL,
	[dat_cancelamento_em] [datetime2](3) NULL,
PRIMARY KEY CLUSTERED
(
	[id_Grupo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [RH].[Tbl_Grupos] ADD  DEFAULT (getdate()) FOR [dat_criado_em]
GO

ALTER TABLE [RH].[Tbl_Grupos]  WITH CHECK ADD  CONSTRAINT [FK_Tbl_Grupos_Categorias] FOREIGN KEY([categoria_id])
REFERENCES [RH].[Tbl_Categorias] ([id_Categoria])
GO

ALTER TABLE [RH].[Tbl_Grupos] CHECK CONSTRAINT [FK_Tbl_Grupos_Categorias]
GO


USE [SUPPLYTEK]
GO

/****** Object:  Table [RH].[Tbl_Categorias]    Script Date: 29/10/2025 22:22:29 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [RH].[Tbl_Categorias](
	[id_Categoria] [int] IDENTITY(1,1) NOT NULL,
	[nome_Categoria] [nvarchar](200) NOT NULL,
	[descricao_Categoria] [nvarchar](1000) NULL,
	[criado_Usuario_id] [int] NOT NULL,
	[dat_criado_em] [datetime2](3) NOT NULL,
	[atualizado_Usuario_id] [int] NULL,
	[dat_atualizado_em] [datetime2](3) NULL,
	[cancelamento_Usuario_id] [int] NULL,
	[dat_cancelamento_em] [datetime2](3) NULL,
PRIMARY KEY CLUSTERED
(
	[id_Categoria] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO

ALTER TABLE [RH].[Tbl_Categorias] ADD  DEFAULT (getdate()) FOR [dat_criado_em]
GO




-- Script: 005_create_sv_tables.sql (PostgreSQL)
-- Objetivo: criar schema sv e tabelas de Solicitação de Venda (cabeçalho + itens)
-- Cada item possui um id de negócio (id_item) e um id de workflow (id_instancia -> wf.tw_instancia).

CREATE SCHEMA IF NOT EXISTS sv;

-- tabela de cabeçalho da solicitação de venda
CREATE TABLE IF NOT EXISTS sv.tb_solicitacao_venda (
    id_solicitacao_venda INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod_solicitacao VARCHAR(100), -- código externo/negócio opcional
    cliente_id INTEGER, -- FK para tabela de clientes (manter descrições e dados do cliente no master de clientes)
    descricao VARCHAR(1000), -- observações gerais da solicitação
    -- NOTE: usamos o padrão de auditoria `dat_criado_em` como a data da solicitação;
    -- ou seja, `dat_criado_em` da própria tabela servirá como "data do pedido".

    -- auditoria / soft-delete (padrão usado no projeto)
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(), -- data de criação (usada como data da solicitação)
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3)
);



-- tabela de itens da solicitação
CREATE TABLE IF NOT EXISTS sv.tb_solicitacao_venda_item (
    id_item INTEGER GENERATED ALWAYS Aid_solicitacao_vendaEY,
    id_solicitacao_venda INTEGER NOT NULL
        REFERENCES sv.tb_solicitacao_venda (id_solicitacao_venda)
        ON UPDATE CASCADE ON DELETE CASCADE,

    -- dados do produto/item (apenas OID do produto)
    -- Observação: este fluxo representa um orçamento; NUNCA armazenar descrições
    -- ou preços unitários aqui. Esses dados serão calculados/determinados em etapas
    -- posteriores do fluxo (margem, impostos, comissão, etc.).
    produto_id INTEGER NOT NULL, -- OID do pade NUMERIC(14,4) NOT NULL DEFAULT 1,

    -- vínculo com workflow: cada item tem sua instância de WF
    id_instancia INTEGER NOT NULL
        REFERENCES wf.tw_instancia (id_instancia)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    observacao VARCHAR(2000), -- observação específica do item

    -- auditoria / soft-delete
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3)
);

-- índices úteis
CREATE INDEX IF NOT EXISTS ix_tb_solicitacao_venda_item_instancia
    ON sv.tb_solicitacao_venda_item (id_instancia)
    WHERE dat_cancelamento_em IS NULL;

CREATE INDEX IF NOT EXISTS ix_tb_solicitacao_venda_item_solicitacao
    ON sv.tb_solicitacao_venda_item (id_solicitacao_venda);


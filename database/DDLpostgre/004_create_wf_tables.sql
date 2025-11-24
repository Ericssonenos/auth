-- Script: 004_create_wf_tables.sql (PostgreSQL)
-- Objetivo: criar schema wf e a tabela canônica wf.tb_etapa (apenas esta migration + tb_acao)
CREATE SCHEMA IF NOT EXISTS wf;

CREATE TABLE IF NOT EXISTS wf.tb_etapa (
    id_etapa INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY, -- PK: identificador único da etapa (imutável)
    nome_etapa VARCHAR(200) NOT NULL, -- Nome legível da etapa. Único por ativo (case-insensitive) conforme índice uq_tb_etapa_nome_ativo
    cod_etapa VARCHAR(100), -- Código estável curto (ex: 'APROV_1'); usado em integrações; recomendado maiúsculas; único ativo em uq_tb_etapa_cod_ativo
    page_etapa VARCHAR(200), -- Identificador da página/rota/blade (ex: 'rh.admissao.form'); usado para autorização/roteamento; único ativo em uq_tb_etapa_page_ativo
    descricao_etapa VARCHAR(1000), -- Texto explicativo livre (por que existe, quando aplicar)
    tipo_etapa CHAR(1) NOT NULL DEFAULT 'n', -- Tipo: 'i'=inicial, 'f'=final, 'n'=normal (ver ck_tb_etapa_tipo)
    criado_usuario_id INTEGER NOT NULL DEFAULT 1, -- id do usuário que criou (não há FK aplicada aqui por decisão arquitetural)
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(), -- marca temporal de criação
    atualizado_usuario_id INTEGER, -- id do usuário que atualizou por último
    dat_atualizado_em TIMESTAMP(3), -- timestamp da última atualização
    cancelamento_usuario_id INTEGER, -- id do usuário que cancelou (soft-delete)
    dat_cancelamento_em TIMESTAMP(3), -- timestamp de cancelamento (soft-delete). NULL = ativo
    CONSTRAINT ck_tb_etapa_tipo CHECK (tipo_etapa IN ('i','f','n')) -- garante valores válidos para tipo_etapa
);

-- evitar duplicidade por nome (case-insensitive) enquanto ativo
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_etapa_nome_ativo
    ON wf.tb_etapa (LOWER(nome_etapa))
    WHERE dat_cancelamento_em IS NULL;

-- índice único para cod_etapa ativo (se usado)
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_etapa_cod_ativo
    ON wf.tb_etapa (cod_etapa)
    WHERE cod_etapa IS NOT NULL AND dat_cancelamento_em IS NULL;

-- índice único para page_etapa ativo (se usado) - case-insensitive
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_etapa_page_ativo
    ON wf.tb_etapa (LOWER(page_etapa))
    WHERE page_etapa IS NOT NULL AND dat_cancelamento_em IS NULL;

-- Cria tabela de ação (transição) — cada linha representa uma transição completa (origem -> destino)
CREATE TABLE IF NOT EXISTS wf.tb_acao (
    id_acao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY, -- PK: identificador único da ação/transição
    nome_acao VARCHAR(200) NOT NULL, -- Nome legível da ação (ex: 'Aprovar', 'Rejeitar')
    cod_acao VARCHAR(100), -- Código estável curto (ex: 'APROVAR'); usado em integrações; único ativo em uq_tb_acao_cod_ativo
    page_acao VARCHAR(200), -- Identificador da página/rota/blade (ex: 'rh.admissao.aprovar'); usado para autorização/roteamento; único ativo em uq_tb_acao_page_ativo
    descricao_acao VARCHAR(1000), -- Texto explicativo da ação
    id_etapa_origem INTEGER NOT NULL
        REFERENCES wf.tb_etapa (id_etapa) -- FK -> wf.tb_etapa.id_etapa (origem)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    id_etapa_destino INTEGER NOT NULL
        REFERENCES wf.tb_etapa (id_etapa) -- FK -> wf.tb_etapa.id_etapa (destino)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    obrigatoria BOOLEAN NOT NULL DEFAULT FALSE, -- se true, transição obrigatória para avançar (sem alternativa)
    criado_usuario_id INTEGER NOT NULL DEFAULT 1, -- id do usuário que criou esta ação
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(), -- timestamp de criação
    atualizado_usuario_id INTEGER, -- id do último usuário que atualizou
    dat_atualizado_em TIMESTAMP(3), -- timestamp da última atualização
    cancelamento_usuario_id INTEGER, -- id do usuário que cancelou (soft-delete)
    dat_cancelamento_em TIMESTAMP(3), -- timestamp de cancelamento. NULL = ativo
    CONSTRAINT ck_tb_acao_origem_destino_diff CHECK (id_etapa_origem <> id_etapa_destino) -- impede self-loop (origem != destino)
);

-- evita ações duplicadas ativas com mesmo nome e origem/destino (case-insensitive no nome)
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_acao_nome_origem_destino_ativo
    ON wf.tb_acao (LOWER(nome_acao), id_etapa_origem, id_etapa_destino) -- evita duplicidade de ações ativas com mesmo nome/origem/destino (nome case-insensitive)
    WHERE dat_cancelamento_em IS NULL;

-- índice único para cod_acao ativo (se usado)
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_acao_cod_ativo
    ON wf.tb_acao (cod_acao) -- índice único para cod_acao enquanto ativo
    WHERE cod_acao IS NOT NULL AND dat_cancelamento_em IS NULL;



-- CREATE / ALTER sugerido para wf.tb_processo
CREATE TABLE IF NOT EXISTS wf.tb_processo (
    id_processo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY, -- PK: identificador do processo
    nome_processo VARCHAR(200) NOT NULL, -- Nome legível do processo
    cod_processo VARCHAR(100), -- Código estável curto (ex: 'RH_ADMISSAO'); único ativo em uq_tb_processo_cod_ativo
    descricao_processo VARCHAR(1000), -- Descrição do processo
    criado_usuario_id INTEGER NOT NULL DEFAULT 1, -- id do usuário que criou
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(), -- timestamp de criação
    atualizado_usuario_id INTEGER, -- id do usuário que atualizou por último
    dat_atualizado_em TIMESTAMP(3), -- timestamp da última atualização
    cancelamento_usuario_id INTEGER, -- id do usuário que cancelou (soft-delete)
    dat_cancelamento_em TIMESTAMP(3) -- timestamp de cancelamento. NULL = ativo
);

-- evita duplicidade por nome (case-insensitive) enquanto ativo
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_processo_nome_ativo
    ON wf.tb_processo (LOWER(nome_processo)) -- evita nomes duplicados enquanto ativo (case-insensitive)
    WHERE dat_cancelamento_em IS NULL;

-- índice único para cod_processo ativo (se usado)
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_processo_cod_ativo
    ON wf.tb_processo (cod_processo) -- índice único para cod_processo enquanto ativo
    WHERE cod_processo IS NOT NULL AND dat_cancelamento_em IS NULL;

-- Cria tabela de versão de processo: cada versão agrupa as regras (etapas/ações) aplicáveis a um processo
CREATE TABLE IF NOT EXISTS wf.tb_versao (
    id_versao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY, -- PK: identificador da versão (global)
    cod_versao VARCHAR(100), -- código estável da versão (ex: 'v1.0', '2025-01-hr'); recomendado MAIÚSCULAS/underscore
    descricao_versao VARCHAR(1000), -- descrição humana da versão (o que mudou)
    dat_inicio TIMESTAMP(3) NOT NULL DEFAULT NOW(), -- início da vigência da versão
    dat_fim TIMESTAMP(3), -- fim da vigência; NULL = ainda vigente
    criado_usuario_id INTEGER NOT NULL DEFAULT 1, -- criador da versão
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(), -- timestamp de criação
    atualizado_usuario_id INTEGER, -- último atualizador
    dat_atualizado_em TIMESTAMP(3), -- timestamp da última atualização
    cancelamento_usuario_id INTEGER, -- usuário que cancelou (soft-delete)
    dat_cancelamento_em TIMESTAMP(3), -- timestamp de cancelamento. NULL = ativo
    CONSTRAINT ck_tb_versao_inicio_fim CHECK (dat_fim IS NULL OR dat_inicio < dat_fim) -- garante dat_inicio < dat_fim quando dat_fim presente
);

-- índice único para cod_versao ativo (se usado)
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_versao_cod_ativo
    ON wf.tb_versao (cod_versao) -- evita códigos duplicados enquanto ativo
    WHERE cod_versao IS NOT NULL AND dat_cancelamento_em IS NULL;

-- Observação: versões agora são globais; o vínculo com processos é feito pela tabela relacional tr_acao_processo_versao.


create table if not exists wf.tb_status (
    id_status integer generated always as identity primary key,
    cod_status varchar(100) not null,         -- ex: 'aguardando_cliente'
    texto_status varchar(200) not null,       -- ex: 'Aguardando decisão do Cliente'
    observacao varchar(1000),                 -- comentário/observação adicional
    criado_usuario_id integer not null default 1,
    dat_criado_em timestamp(3) not null default now(),
    atualizado_usuario_id integer,
    dat_atualizado_em timestamp(3),
    cancelamento_usuario_id integer,
    dat_cancelamento_em timestamp(3),
    unique (cod_status)
);

-- tabela de configuração chave-valor para perguntas e tipos de resposta no workflow
CREATE TABLE IF NOT EXISTS wf.tb_chave_valor (
    id_chave_valor INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cod_chave VARCHAR(100) NOT NULL, -- código único da pergunta/configuração
    pergunta VARCHAR(500) NOT NULL, -- texto da pergunta ou descrição da configuração
    tipo_resposta VARCHAR(50) NOT NULL, -- tipo esperado da resposta: 'texto', 'numero', 'boolean', 'data', 'lista', etc.
    valor_padrao VARCHAR(1000), -- valor padrão opcional
    obrigatoria BOOLEAN NOT NULL DEFAULT FALSE, -- indica se a pergunta é obrigatória
    observacao VARCHAR(2000), -- observações sobre a configuração

    -- auditoria / soft-delete (padrão usado no projeto)
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3),

    -- garante unicidade do código enquanto ativo
    CONSTRAINT ck_tb_chave_valor_tipo_resposta CHECK (tipo_resposta IN ('texto', 'numero', 'boolean', 'data', 'lista', 'decimal'))
);

-- índice único por código enquanto ativo
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_chave_valor_cod_ativo
    ON wf.tb_chave_valor (cod_chave)
    WHERE dat_cancelamento_em IS NULL;

-- tabela de opções para perguntas do tipo 'lista'
CREATE TABLE IF NOT EXISTS wf.tb_chave_valor_opcao (
    id_opcao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_chave_valor INTEGER NOT NULL
        REFERENCES wf.tb_chave_valor (id_chave_valor)
        ON UPDATE CASCADE ON DELETE CASCADE, -- quando a pergunta for removida, remove as opções
    cod_opcao VARCHAR(100) NOT NULL, -- código da opção (ex: 'SIM', 'NAO', 'TALVEZ')
    texto_opcao VARCHAR(500) NOT NULL, -- texto exibido para o usuário (ex: 'Sim', 'Não', 'Talvez')
    ordem_exibicao INTEGER DEFAULT 0, -- ordem de exibição das opções
    ativa BOOLEAN NOT NULL DEFAULT TRUE, -- se a opção está ativa para seleção

    -- auditoria / soft-delete (padrão usado no projeto)
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3)
);

-- índice único por código da opção dentro da mesma chave-valor (enquanto ativo)
CREATE UNIQUE INDEX IF NOT EXISTS uq_tb_chave_valor_opcao_cod_ativo
    ON wf.tb_chave_valor_opcao (id_chave_valor, cod_opcao)
    WHERE dat_cancelamento_em IS NULL;

-- índice para busca por chave-valor e ordem
CREATE INDEX IF NOT EXISTS ix_tb_chave_valor_opcao_ordem
    ON wf.tb_chave_valor_opcao (id_chave_valor, ordem_exibicao, id_opcao)
    WHERE dat_cancelamento_em IS NULL;

-- tabela relacional para armazenar respostas das chaves-valor em movimentos
CREATE TABLE IF NOT EXISTS wf.tr_movimento_resposta (
    id_movimento_resposta INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_movimento INTEGER NOT NULL
        REFERENCES wf.tw_movimento (id_movimento)
        ON UPDATE CASCADE ON DELETE CASCADE, -- quando movimento for removido, remove as respostas
    id_chave_valor INTEGER NOT NULL
        REFERENCES wf.tb_chave_valor (id_chave_valor)
        ON UPDATE CASCADE ON DELETE RESTRICT, -- impede remoção de chave-valor com respostas
    id_opcao INTEGER
        REFERENCES wf.tb_chave_valor_opcao (id_opcao)
        ON UPDATE CASCADE ON DELETE RESTRICT, -- usado quando tipo_resposta = 'lista'
    resposta_texto VARCHAR(2000), -- resposta em texto livre (para todos os tipos de resposta)

    -- auditoria / soft-delete (padrão usado no projeto)
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    atualizado_usuario_id INTEGER,
    dat_atualizado_em TIMESTAMP(3),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3),

    -- garante que não haja resposta duplicada para a mesma pergunta no mesmo movimento
    CONSTRAINT ck_movimento_resposta_opcao_ou_texto CHECK (
        (id_opcao IS NOT NULL AND resposta_texto IS NULL) OR  -- para tipo 'lista': usar id_opcao
        (id_opcao IS NULL AND resposta_texto IS NOT NULL) OR  -- para outros tipos: usar resposta_texto
        (id_opcao IS NOT NULL AND resposta_texto IS NOT NULL) -- permite ambos para flexibilidade
    )
);

-- índice único para evitar respostas duplicadas para a mesma chave-valor no mesmo movimento
CREATE UNIQUE INDEX IF NOT EXISTS uq_tr_movimento_resposta_ativo
    ON wf.tr_movimento_resposta (id_movimento, id_chave_valor)
    WHERE dat_cancelamento_em IS NULL;

-- índice para busca por movimento
CREATE INDEX IF NOT EXISTS ix_tr_movimento_resposta_movimento
    ON wf.tr_movimento_resposta (id_movimento)
    WHERE dat_cancelamento_em IS NULL;

-- índice para busca por chave-valor (relatórios/auditoria)
CREATE INDEX IF NOT EXISTS ix_tr_movimento_resposta_chave
    ON wf.tr_movimento_resposta (id_chave_valor, dat_criado_em DESC)
    WHERE dat_cancelamento_em IS NULL;





-- tabela de fluxo (regras de transição por processo e versão)
CREATE TABLE IF NOT EXISTS wf.tr_fluxo (
    id_fluxo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY, -- PK surrogate

    -- relacionamentos principais
    id_acao INTEGER NOT NULL
        REFERENCES wf.tb_acao (id_acao)
        ON UPDATE CASCADE ON DELETE RESTRICT,        -- ação vinculada ao fluxo
    id_processo INTEGER NOT NULL
        REFERENCES wf.tb_processo (id_processo)
        ON UPDATE CASCADE ON DELETE RESTRICT,        -- processo dono
    id_versao INTEGER NOT NULL
        REFERENCES wf.tb_versao (id_versao)
        ON UPDATE CASCADE ON DELETE RESTRICT,        -- versão obrigatória
    id_status_destino INTEGER
        REFERENCES wf.tb_status (id_status)
        ON UPDATE CASCADE ON DELETE RESTRICT,        -- status exibido ao chegar ao destino (opcional)

    -- auditoria básica
    criado_usuario_id INTEGER NOT NULL DEFAULT 1,
    dat_criado_em TIMESTAMP(3) NOT NULL DEFAULT NOW(),
    cancelamento_usuario_id INTEGER,
    dat_cancelamento_em TIMESTAMP(3)                -- soft-delete
);

-- índice principal para busca por ação (mais comum na execução)
CREATE INDEX IF NOT EXISTS ix_tr_fluxo_acao_proc_versao
    ON wf.tr_fluxo (id_acao, id_processo, id_versao)
    WHERE dat_cancelamento_em IS NULL;

-- garante que a mesma combinação ação + processo + versão não se repita
CREATE UNIQUE INDEX IF NOT EXISTS uq_tr_fluxo_ativo
    ON wf.tr_fluxo (id_acao, id_processo, id_versao)
    WHERE dat_cancelamento_em IS NULL;

-- Observação: tb_etapa e tb_acao são canônicas — descrevem o comportamento base; versionamento referenciará essas entidades.


-- execução real de um processo (apenas o "container" dos movimentos) -- sem id_versao aqui
CREATE TABLE IF NOT EXISTS wf.tw_instancia (
        id_instancia          INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY, -- PK: identificador da instância
        -- nota: id_processo removido intencionalmente; o vínculo entre processo e fluxo
        -- é feito em wf.tr_fluxo, e movimentos referenciam o fluxo via wf.tw_movimento.id_fluxo
        criado_usuario_id     INTEGER NOT NULL DEFAULT 1, -- id do usuário que criou a instância
        dat_criado_em         TIMESTAMP(3) NOT NULL DEFAULT NOW() -- timestamp de criação
);

-- índice para consultas por data de criação (sem vínculo direto a processo)
CREATE INDEX IF NOT EXISTS ix_tw_instancia_criadoem
    ON wf.tw_instancia (dat_criado_em DESC);

-- movimentos: registros imutáveis que definem mudanças de estado / versão
-- histórico de passos da instância (cada linha = 1 ação executada)
-- tabela de movimentações do workflow (cada linha = execução de um fluxo em uma instância)
create table if not exists wf.tw_movimento (
    id_movimento            integer generated always as identity primary key, -- PK: identificador do movimento

    id_instancia            integer not null
                              references wf.tw_instancia (id_instancia)
                              on update cascade on delete restrict, -- vínculo com a instância

    id_fluxo                integer not null
                              references wf.tr_fluxo (id_fluxo)
                              on update cascade on delete restrict, -- vínculo com a regra de fluxo

    criado_usuario_id       integer not null default 1, -- id do usuário que executou o movimento
    dat_criado_em           timestamp(3) not null default now(), -- timestamp da criação (momento do movimento)
    observacao              varchar(2000), -- texto opcional para comentários do movimento

    cancelamento_usuario_id integer, -- id do usuário que cancelou o movimento (soft-delete)
    dat_cancelamento_em     timestamp(3) -- timestamp do cancelamento (soft-delete)
);

-- índice principal para busca eficiente do último movimento de cada instância
create index if not exists ix_tw_movimento_instancia_data
    on wf.tw_movimento (id_instancia, dat_criado_em desc, id_movimento desc)
    where dat_cancelamento_em is null;


-- pegar rapidamente o último movimento de cada instância (timeline / estado atual)
-- INDICE COMENTADO (intencional): ix_tw_movimento_instancia_data
 CREATE INDEX IF NOT EXISTS ix_tw_movimento_instancia_data
     ON wf.tw_movimento (id_instancia, dat_criado_em DESC, id_movimento DESC);
--Motivo: índices com nomes fixos e definição de colunas tornam renomeações e refactors
-- mais custosos (obrigam ALTER INDEX/RECREATE). Mantido aqui como comentário para
-- referência. Descomentar/aplicar via migração caso o modelo esteja estabilizado.




# Overview do Módulo Workflow

Este documento resume o modelo de dados, decisões de projeto, contratos e próximos passos para o módulo "workflow" do sistema.

## Objetivo
Fornecer uma camada de orquestração de processos (process definitions) separada da execução (instances), com forte ênfase em auditabilidade e versionamento.

## Padrões e princípios adotados
- Banco: PostgreSQL (uso de features como identity, partial indexes, LATERAL).
- Nomes no singular: tabelas canonicas e runtime usam nomes no singular (`tb_etapa`, `tb_acao`, `tb_processo`, etc.).
- Soft-delete: uso consistente de `dat_cancelamento_em` para marcar exclusão lógica; consultas "ativas" usam `WHERE dat_cancelamento_em IS NULL`.
- Separação canonical vs runtime:
  - Canonical: estrutura definidora do processo (etapas, ações, processos, versões, associações).
  - Runtime: instâncias e movimentos (historico), armazenando snapshots para garantir auditabilidade.
- Versões: modelo "global" de versões (`tb_versao`) e associação via tabela de relação (`tr_acao_processo_versao`) que exige `id_versao NOT NULL`.
- Histórico de movimentos: `tw_movimento` guarda o estado real no momento do movimento (nome_acao, nome_etapa_origem, nome_etapa_destino, id_versao, etc.).

## Tabelas principais (resumo)
- `wf.tb_etapa`
  - Papéis: etapas referenciais do processo.
  - Campos chave: `id_etapa`, `nome_etapa`, `cod_etapa`, `page_etapa`, timestamps e `dat_cancelamento_em`.
  - Constraint: `tipo_etapa` com valores permitidos (ex.: `'i','f','n'`).
  - Índices: unicidade por nome/cod/page para registros ativos (case-insensitive via LOWER).

- `wf.tb_acao`
  - Papéis: ações/transições entre etapas.
  - Campos chave: `id_acao`, `nome_acao`, `cod_acao`, `page_acao`, `id_etapa_origem`, `id_etapa_destino`, `obrigatoria`.
  - Constraint: proibição de self-loop (`id_etapa_origem <> id_etapa_destino`).
  - Nota: `page_acao` não tem unicidade global (pode ser reaproveitada).

- `wf.tb_processo`
  - Papéis: definição de processo (nome, cod_processo, descrição).

- `wf.tb_versao`
  - Papéis: versões aplicáveis (modelo global — não vinculada diretamente a `id_processo`).
  - Campos: `cod_versao`, `dat_inicio`, `dat_fim`, `dat_cancelamento_em`.
  - Constraint: quando `dat_fim` não for NULL, deve haver `dat_inicio < dat_fim`.

- `wf.tr_acao_processo_versao`
  - Papéis: tabela de relacionamento que vincula `acao` + `processo` + `versao`.
  - Regras: `id_versao` é NOT NULL; existe uma unicidade parcial para evitar duplicidade em registros ativos.

- `wf.tw_instancia`
  - Papéis: container da execução do processo (uma instância por corrida de processo).
  - Campos: `id_instancia`, `id_processo`, `criado_usuario_id`, `dat_criado_em`.
  - Observação: não armazena `id_versao` — versão aplicada é determinada por cada movimento.

- `wf.tw_movimento`
  - Papéis: histórico de movimentos/estados da instância.
  - Abordagem: cada movimento armazena snapshots: `nome_acao`, `id_versao` (NOT NULL), `id_etapa_origem`, `nome_etapa_origem`, `id_etapa_destino`, `nome_etapa_destino`, `dat_movimento`, `observacao`, `dat_cancelamento_em`.
  - Regras: manter tudo imutável (soft-delete quando necessário), `id_versao` obrigatório para preservar contexto histórico.

- Views e utilitários
  - `wf.vw_tw_instancia_estado`: view que retorna o último movimento por instância (usa LATERAL/ROW_NUMBER ou subselect para pegar o movimento mais recente por `id_instancia`).

## Contratos essenciais
- Operação principal: registrar um movimento (insert em `tw_movimento`) fornece o snapshot do estado após a ação.
- Integridade: não deletar fisicamente movimentos; usar `dat_cancelamento_em` para marcação.
- Unicidades: aplicar índices parciais para regras de negócio apenas em registros ativos (WHERE dat_cancelamento_em IS NULL).

## Casos de borda importantes
- Inserir movimento com `id_versao` que não pertence ao `id_processo` — solução: validar via trigger ou via aplicação usando `tr_acao_processo_versao`.
- Concorrência: múltiplos movimentos simultâneos para uma instância — tratar com lock de aplicação ou constraint/trigger que valida último movimento.
- Reuso de páginas/ações: `page_acao` pode ser reutilizada entre processos; por isso não aplicamos unique global em `page_acao`.
- Histórico incompleto: se snapshots não forem gravados, alterações posteriores em `tb_acao`/`tb_etapa` podem invalidar o histórico — por isso snapshots são mandatórios.

## Regras e validações sugeridas (próximos passos técnicos)
- Trigger BEFORE INSERT ON `wf.tw_movimento` que valida:
  - `id_versao` está dentro do período de vigência (`dat_inicio` <= now() < `dat_fim` ou `dat_fim` NULL) (opcional: permitir movimentos somente para versões vigentes).
  - `id_versao` tem associação com `id_processo` (consultando `tr_acao_processo_versao`).
  - `id_etapa_origem` corresponde ao estado atual da instância (opcional, evitar saltos inválidos).
- Funções auxiliares: `wf.fn_get_ultima_etapa(id_instancia)` e `wf.fn_validate_movimento(...)`.
- Testes: unit e integração para migrates + inserts simulando mudança de versões e cancelamentos.

## Plano de migração e deploy
- Separar DDL em migrações pequenas e revisáveis (uma por grupo lógico: canonical, versões+rel, runtime, views, triggers).
- Validar ordem de criação pelo fato das FKs (criar tabelas alvo antes das que referenciam).
- Criar scripts de seed para processos/etapas/ações de exemplo.

## Exemplos rápidos (SQL) — inserção de movimento (exemplo simplificado)
```sql
-- Supondo que id_instancia=1, id_acao=10, id_versao=2
INSERT INTO wf.tw_movimento (
  id_instancia, id_acao, nome_acao, id_versao,
  id_etapa_origem, nome_etapa_origem, id_etapa_destino, nome_etapa_destino,
  executado_usuario_id, dat_movimento)
VALUES (
  1, 10, 'Aprovar', 2,
  5, 'Revisão', 6, 'Aprovado',
  42, now()
);
```

## Checklist de aceitação (para cada tarefa/migração)
- [ ] DDL cria tabelas com constraints e índices necessários.
- [ ] Não há deletes físicos em `tw_movimento` (apenas `dat_cancelamento_em`).
- [ ] Triggers que exigimos estão implementados e cobertos por testes.
- [ ] Views retornam estado atual corretamente em diferentes cenários (sem movimentos, com movimentos cancelados, com múltiplos movimentos).

## Próximos passos sugeridos (você escolhe ordem)
1. Implementar triggers de validação (`wf.fn_validate_movimento`) e testes automatizados.  
2. Criar scripts de seed e exemplos de uso para a equipe de front/back.  
3. Gerar diagrama ER (drawio/PNG) e incluir em `docs/diagrams/workflow-er.drawio`.  
4. Revisão em sessão com equipe para aprovar `tb_versao` global vs. por-processo (se ainda houver dúvida).

---
Se quer, eu já aplico este arquivo no repositório (`docs/workflow_overview.md`) e gero também o diagrama ER básico. Quer que eu gere o diagrama ER e os scripts de seed agora, ou prefere revisar/editar este overview primeiro?

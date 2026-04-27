# Plano de Tarefas e Implementação

Objetivo: Implementar os contratos e infra documental necessários para desenvolver sem alterar o layout/infra existente.

## Tarefas (ordem sugerida)

- [x] create-initial-artifacts — Criar artefatos iniciais
  - Descrição: Consolidar proposal.md, schemas, OpenAPI, dicionário e regras.
  - Saída: arquivos em /docs e /schemas.
  - Critério de aceite: Artefatos revisados pelo solicitante.

- [x] review-repo-infra — Revisar repositório e infra
  - Descrição: Inspeção de docker-compose, Dockerfile, config.php, autenticar.php, salvar/editar endpoints e db_data.
  - Saída: Relatório de inconsistências e riscos.

- [x] generate-json-schemas — Gerar/validar JSON Schemas
  - Descrição: Ajustar schemas (naming, formatos) e incluir exemplos.
  - Saída: /schemas/*.json

- [x] implement-api-endpoints — Implementar endpoints principais
  - Descrição: CRUD para equipamentos, movimentações, usuários, locais, manutenções conforme OpenAPI.
  - Saída: Código de endpoints, testes e PRs.

- [x] db-migrations — Criar migrações de banco
  - Descrição: Scripts de migração/rollback, índices de unicidade.

- [x] integrate-auth — Integrar autenticação e autorização
  - Descrição: Conectar com sistema de auth existente, proteger endpoints por perfil.

- [x] tests-and-smoke — Escrever testes e smoke tests
  - Descrição: Unitaria/integracao e smoke para pós-deploy.

- [x] documentation-openapi — Publicar OpenAPI e exemplos
  - Descrição: Adicionar openapi.yaml, exemplos e instruções de uso.

- code-review-and-merge — Revisão de código e merge
  - Descrição: Abrir PRs, checklist (segurança, migração, testes).

- deploy-staging — Deploy em staging e validação
  - Descrição: Deploy controlado, rodar smoke, validação manual e preparar rollback.

## Critérios Transversais
- Manter integridade do layout/infra; qualquer mudança exige aprovação.
- Utilizar prepared statements e validação de inputs.
- Históricos de movimentação são append-only.
- Backups antes de migrações e scripts de rollback obrigatórios.
- Revisão por analista e responsável de infra em alterações de infra/DB.

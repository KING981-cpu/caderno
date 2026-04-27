# Regras e Restrições

## Visão Geral
Este documento lista restrições absolutas e diretrizes para alterações. Em nenhuma hipótese o layout atual, recursos provisionados (containers, volumes, redes), e contratos públicos (APIs/rotas existentes, nomes de tabelas) devem ser alterados sem aprovação formal.

## Restrições Absolutas
- Não alterar o layout/HTML/CSS atual sem aprovação do dono do produto; mudanças visuais requerem revisão de design.
- Não modificar imagens Docker, nomes de serviços ou configurações em docker-compose.yml sem coordenação com infra.
- Não renomear tabelas/colunas existentes ou remover colunas sem plano de migração e script de rollback.
- Não eliminar endpoints públicos existentes; somente versionar ou criar novos endpoints compatíveis.
- Não sobrescrever histórico de movimentações (append-only).
- Não embutir credenciais em código; use variáveis de ambiente e arquivos de configuração já existentes (config.php, .env se presente).
- Preservar compatibilidade com integrações externas (ex.: sistema de autenticação).

## Boas práticas exigidas
- Qualquer alteração no banco deve ter script de migração e rollback testado.
- Testes de regressão e smoke tests automáticos antes de deploy em produção.
- Documentar mudanças em CHANGELOG e no artefato de design antes de PR.
- Revisão por pelo menos um analista e um responsável de infra para alterações críticas.

## Checklist de Verificação (inspeção inicial do repositório)
Verificar os seguintes arquivos/áreas por inconsistências antes de implementar:
- docker-compose.yml e Dockerfile — confirmar serviços, volumes e variáveis de ambiente.
- config.php — revisar parâmetros de conexão, uso de constantes, e validação de inputs.
- autenticar.php / login.php — validar fluxo de autenticação, proteção contra sessions fixation e SQL injection.
- salvar.php / cadastrar_rapido.php / editar.php / deletar.php — confirmar validação de entrada e tratamento de erros.
- index.php / header.php — verificar dependências de layout e inclusão de arquivos.
- arquivos SQL (se houver) e db_data/ — conferir esquema atual e dados de exemplo.
- integrações externas (se usadas) — endpoints, tokens e timeouts.

Itens a checar em cada arquivo:
- Uso de prepared statements para queries SQL.
- Tratamento adequado de sessões e autorização por perfil.
- Validação/normalização de formatos de datas e fuso horário.
- Logs de erro sem exposição de credenciais.
- Compatibilidade de encoding (UTF-8) e sanitização de saída.

## Processo para mudanças que tocam infra/layout
- Proposta: criar change proposal (design.md + tasks.md) e agendar revisão.
- Migração: incluir scripts e backup antes de aplicar.
- Testes: executar suite de testes + smoke + validação manual em staging.
- Rollback: documentar passos claros de rollback e responsável.

## Riscos e mitigação
- Mudanças no DB sem migração -> perda de dados: mitigar com backups e scripts transacionais.
- Alterações visuais sem revisão -> quebra de fluxo de usuário: exigir aprovação de UX/Produto.
- Mudanças em autenticação -> vazamento de acesso: revisão de segurança e testes de penetração simples.

## Aprovação
Qualquer alteração que viole uma das restrições absolutas requer aprovação explícita do responsável pelo produto e do time de infra.

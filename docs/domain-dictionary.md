# Dicionário de Domínio

- **Equipamento:** Ativo físico de TI gerenciado no sistema. Campos chave: id (UUID), codigo, tipo, fabricante, modelo, numeroSerie, patrimonio, estado, localId, responsavelId.
- **Movimentação:** Registro append-only de ação sobre um equipamento (checkout, checkin, transfer, maintenance). Campos: id, equipamentoId, tipo, origemLocalId, destinoLocalId, usuarioResponsavelId, dataHora, condicaoAtual, observacao.
- **Check-out:** Movimentação do tipo checkout; equipamento sai do local para responsável/usuário.
- **Check-in:** Movimentação do tipo checkin; equipamento retorna ao local de origem ou outro destino.
- **Transferência:** Movimentação do tipo transfer; mudança entre locais sem alterar responsabilidade.
- **Manutenção:** Evento de manutenção técnica; possui início, término, descrição, técnico e status.
- **Usuário:** Identidade de uma pessoa que interage com o sistema. Campos: id, nome, matricula, email, perfil (analista, operador, auditor, admin), ativo.
- **Responsável:** Usuário designado como custódio temporário/permanente do equipamento (pode ser null).
- **Local:** Lugar físico (almoxarifado, setor, sala, externo). Campos: id, nome, endereco, tipo, responsavelId.
- **Número de Série (numeroSerie):** Identificador do fabricante; deve ser tratado como preferencialmente único.
- **Patrimônio (patrimonio/codigo patrimonial):** Código institucional de patrimônio; único quando presente.
- **Histórico:** Conjunto de Movimentações associadas a um Equipamento; imutável por regra de negócio.
- **Estado do Equipamento (estado):** Enum — disponivel, em_uso, em_manutencao, inativo, perdido.
- **Metadata:** Objeto livre para extensões não padronizadas; preserva compatibilidade com infra existente.
- **Auditor:** Papel com permissão de visualização e exportação de relatórios; não altera registros.
- **Operador:** Papel responsável por registrar entradas/saídas físicas.
- **Analista:** Papel com permissão de cadastro e alterações administrativas no catálogo.

**Observações:**
- Termos em maiúscula/nomes de campos seguem os schemas gerados (use camelCase conforme o schema ou adapte ao padrão do sistema existente).
- Todos os IDs usam UUID por padrão; adaptar se necessário ao sistema atual.
- Regras de unicidade (numeroSerie, patrimonio) são responsabilidade da camada de persistência.

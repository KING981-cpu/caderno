-- Inicialização do schema para o sistema de equipamentos

CREATE TABLE IF NOT EXISTS equipamento (
  id CHAR(32) PRIMARY KEY,
  codigo VARCHAR(100),
  tipo VARCHAR(50) NOT NULL,
  fabricante VARCHAR(100) NOT NULL,
  modelo VARCHAR(100),
  numeroSerie VARCHAR(100) NOT NULL,
  patrimonio VARCHAR(100),
  estado VARCHAR(30) NOT NULL DEFAULT 'disponivel',
  localId CHAR(32),
  responsavelId CHAR(32),
  dataCadastro DATETIME,
  observacoes TEXT,
  metadata JSON,
  UNIQUE KEY ux_numeroSerie (numeroSerie),
  UNIQUE KEY ux_patrimonio (patrimonio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS manutencao (
  id CHAR(32) PRIMARY KEY,
  equipamentoId CHAR(32) NOT NULL,
  inicio DATETIME NOT NULL,
  termino DATETIME,
  descricao TEXT,
  tecnico VARCHAR(200),
  custo DECIMAL(10,2) DEFAULT 0,
  status VARCHAR(30) NOT NULL,
  FOREIGN KEY (equipamentoId) REFERENCES equipamento(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Observação: tabelas movimentacao e movimentacao_itens podem já existir conforme implementação legada.

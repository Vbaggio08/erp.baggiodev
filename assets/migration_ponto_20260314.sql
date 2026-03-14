-- Migration: Sistema de Ponto Eletrônico Completo
-- Data: 2026-03-14
-- Descrição: Cria tabelas e altera estrutura para suportar sistema de ponto eletrônico
--            com suporte a múltiplas batidas, geolocalização, fotos, audit trail e sincronização offline

SET FOREIGN_KEY_CHECKS=0;

-- ============================================================================
-- 1. EXPANDIR TABELA USUARIOS COM CAMPOS DO PONTO
-- ============================================================================

ALTER TABLE usuarios ADD COLUMN departamento VARCHAR(100) DEFAULT 'Geral';
ALTER TABLE usuarios ADD COLUMN cargo VARCHAR(100) DEFAULT 'Operacional';
ALTER TABLE usuarios ADD COLUMN carga_horaria_diaria DECIMAL(4,2) DEFAULT 8.0;
ALTER TABLE usuarios ADD COLUMN data_admissao DATE DEFAULT CURDATE();
ALTER TABLE usuarios ADD COLUMN tipo_contrato VARCHAR(50) DEFAULT 'CLT';

-- ============================================================================
-- 2. TABELA APONTAMENTOS_PONTO (PRINCIPAL - UMA LINHA POR DIA)
-- ============================================================================

CREATE TABLE IF NOT EXISTS apontamentos_ponto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  data DATE NOT NULL,
  
  -- Batida 1
  hora_entrada_1 TIME NULL,
  hora_saida_1 TIME NULL,
  foto_entrada_1 VARCHAR(255) NULL COMMENT 'Path: 2026-03-14/user_5_entrada_1.jpg',
  foto_saida_1 VARCHAR(255) NULL,
  geo_entrada_1 VARCHAR(50) NULL COMMENT 'Formato: -23.5505,-46.6333',
  geo_saida_1 VARCHAR(50) NULL,
  geo_precisao_entrada_1 INT NULL COMMENT 'Precisão em metros',
  geo_precisao_saida_1 INT NULL,
  ip_origem_entrada_1 VARCHAR(50) NULL,
  ip_origem_saida_1 VARCHAR(50) NULL,
  device_id_entrada_1 VARCHAR(255) NULL COMMENT 'Canvas fingerprint',
  device_id_saida_1 VARCHAR(255) NULL,
  user_agent_entrada_1 TEXT NULL,
  user_agent_saida_1 TEXT NULL,
  
  -- Batida 2
  hora_entrada_2 TIME NULL,
  hora_saida_2 TIME NULL,
  foto_entrada_2 VARCHAR(255) NULL,
  foto_saida_2 VARCHAR(255) NULL,
  geo_entrada_2 VARCHAR(50) NULL,
  geo_saida_2 VARCHAR(50) NULL,
  geo_precisao_entrada_2 INT NULL,
  geo_precisao_saida_2 INT NULL,
  ip_origem_entrada_2 VARCHAR(50) NULL,
  ip_origem_saida_2 VARCHAR(50) NULL,
  device_id_entrada_2 VARCHAR(255) NULL,
  device_id_saida_2 VARCHAR(255) NULL,
  user_agent_entrada_2 TEXT NULL,
  user_agent_saida_2 TEXT NULL,
  
  -- Batida 3
  hora_entrada_3 TIME NULL,
  hora_saida_3 TIME NULL,
  foto_entrada_3 VARCHAR(255) NULL,
  foto_saida_3 VARCHAR(255) NULL,
  geo_entrada_3 VARCHAR(50) NULL,
  geo_saida_3 VARCHAR(50) NULL,
  geo_precisao_entrada_3 INT NULL,
  geo_precisao_saida_3 INT NULL,
  ip_origem_entrada_3 VARCHAR(50) NULL,
  ip_origem_saida_3 VARCHAR(50) NULL,
  device_id_entrada_3 VARCHAR(255) NULL,
  device_id_saida_3 VARCHAR(255) NULL,
  user_agent_entrada_3 TEXT NULL,
  user_agent_saida_3 TEXT NULL,
  
  -- Status e controle
  status VARCHAR(50) DEFAULT 'presente' COMMENT 'presente, ausente, falta, atestado',
  observacao TEXT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Índices
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY unique_usuario_data (usuario_id, data),
  KEY idx_usuario_id (usuario_id),
  KEY idx_data (data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TABELA HISTORICO_ALTERACOES_PONTO (AUDITORIA - IMUTÁVEL)
-- ============================================================================

CREATE TABLE IF NOT EXISTS historico_alteracoes_ponto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  apontamento_id INT NOT NULL,
  usuario_alterador_id INT NOT NULL COMMENT 'Quem fez a alteração',
  tipo_alteracao VARCHAR(100) NOT NULL COMMENT 'entrada_criada, saida_criada, entrada_editada, saida_editada, validacao_proximidade_confirmada_saida, etc',
  valor_anterior JSON NULL COMMENT 'Valores originais em JSON',
  valor_novo JSON NULL COMMENT 'Novos valores em JSON',
  motivo_alteracao TEXT NOT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  hash_sha256 VARCHAR(64) NULL COMMENT 'Hash de integridade (sha256)',
  
  -- Índices
  FOREIGN KEY (apontamento_id) REFERENCES apontamentos_ponto(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_alterador_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
  KEY idx_apontamento_id (apontamento_id),
  KEY idx_usuario_alterador (usuario_alterador_id),
  KEY idx_criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. TABELA ATESTADOS (LICENÇAS MÉDICAS, JUSTIFICATIVAS, ETC)
-- ============================================================================

CREATE TABLE IF NOT EXISTS atestados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  data_inicio DATE NOT NULL,
  data_fim DATE NOT NULL,
  tipo VARCHAR(50) NOT NULL COMMENT 'medico, falta_justificada, licenca_remunerada, licenca_nao_remunerada',
  comprovante_url VARCHAR(255) NULL COMMENT 'Path do arquivo: atestados/2026-03-14/user_5_atestado.pdf',
  status VARCHAR(50) DEFAULT 'pendente' COMMENT 'pendente, aprovado, rejeitado',
  motivo_rejeicao TEXT NULL,
  aprovador_id INT NULL,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  aprovado_em DATETIME NULL,
  
  -- Índices
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (aprovador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  KEY idx_usuario_id (usuario_id),
  KEY idx_status (status),
  KEY idx_data_inicio (data_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. TABELA DISPOSITIVOS_AUTORIZADOS (MÁQUINAS DO USUÁRIO)
-- ============================================================================

CREATE TABLE IF NOT EXISTS dispositivos_autorizados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  device_id VARCHAR(255) NOT NULL COMMENT 'Fingerprint único da máquina (canvas hash)',
  device_nome VARCHAR(255) NULL COMMENT 'Notebook João, Desktop Oficina, etc',
  ip_address VARCHAR(100) NULL,
  user_agent TEXT NULL,
  tipo_dispositivo VARCHAR(50) NULL COMMENT 'desktop, mobile, tablet',
  primeiro_uso DATETIME DEFAULT CURRENT_TIMESTAMP,
  ultimo_uso DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  ativo TINYINT DEFAULT 1,
  autorizado_por_admin DATETIME NULL COMMENT 'NULL = auto-registro, DATETIME = autorizado por admin',
  
  -- Índices
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY unique_usuario_device (usuario_id, device_id),
  KEY idx_usuario_id (usuario_id),
  KEY idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. TABELA GEOLOCATION_EMPRESA (COORDENADAS DAS FILIAIS)
-- ============================================================================

CREATE TABLE IF NOT EXISTS geolocation_empresa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT DEFAULT 1 COMMENT 'Para multi-empresa, FK para tabela empresa (se houver)',
  latitude DECIMAL(10, 8) NOT NULL COMMENT 'Ex: -23.55048',
  longitude DECIMAL(10, 8) NOT NULL COMMENT 'Ex: -46.63331',
  endereco TEXT NULL,
  raio_metros INT DEFAULT 500 COMMENT 'Raio permitido a partir do ponto de coordenada',
  ativo TINYINT DEFAULT 1,
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  KEY idx_empresa_id (empresa_id),
  KEY idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. TABELA CONFIGURACAO_PONTO (SETTINGS GLOBAIS - ID=1 APENAS)
-- ============================================================================

CREATE TABLE IF NOT EXISTS configuracao_ponto (
  id INT PRIMARY KEY DEFAULT 1,
  
  -- Tolerância e horários
  tolerancia_atraso_minutos INT DEFAULT 5,
  horario_inicio_expediente TIME DEFAULT '08:00:00',
  horario_fim_expediente TIME DEFAULT '17:00:00',
  
  -- Feriados e DSR
  considerar_feriados TINYINT DEFAULT 1,
  lista_feriados JSON NULL COMMENT 'Array de datas: ["2026-03-14", "2026-04-21", ...]',
  usar_dsr TINYINT DEFAULT 1 COMMENT 'Descanso Semanal Remunerado',
  
  -- Quantidade de batidas
  quantidade_batidas INT DEFAULT 2 COMMENT 'Valores: 2 (entrada/saída), 4 (entrada/saída/entrada/saída), 6 (3x entrada/saída)',
  
  -- Geolocalização
  usar_geolocalizacao TINYINT DEFAULT 0,
  raio_permitido_metros INT DEFAULT 500,
  
  -- Fotos
  exigir_foto_mobile TINYINT DEFAULT 1,
  exigir_foto_desktop TINYINT DEFAULT 0,
  
  -- Máquinas autorizadas
  modo_multiplas_maquinas TINYINT DEFAULT 0 COMMENT 'Se 0: apenas 1 máquina/usuário. Se 1: múltiplas permitidas',
  
  -- Offline
  limiar_proximidade_minutos INT DEFAULT 5 COMMENT 'Aviso de batida próxima',
  
  atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insere config padrão se não existir
INSERT IGNORE INTO configuracao_ponto (id) VALUES (1);

-- ============================================================================
-- 8. TABELA SINCRONIZACOES_OFFLINE (LOG DE SINCRONIZAÇÃO)
-- ============================================================================

CREATE TABLE IF NOT EXISTS sincronizacoes_offline (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  data_offline DATE NOT NULL COMMENT 'Data do primeiro ponto offline',
  data_online DATE NOT NULL COMMENT 'Data quando voltou online',
  timestamp_volta DATETIME NOT NULL COMMENT 'Timestamp exato de quando voltou online',
  pontos_synced INT DEFAULT 0 COMMENT 'Quantidade de pontos sincronizados',
  conflitos INT DEFAULT 0 COMMENT 'Quantidade de conflitos resolvidos',
  sincronizado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  -- Índices
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  KEY idx_usuario_id (usuario_id),
  KEY idx_sincronizado_em (sincronizado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CRIAÇÃO DE PASTAS NECESSÁRIAS (Script manual - executar depois)
-- ============================================================================

-- Criar as seguintes pastas manualmente em assets/uploads/:
-- - fotos_ponto/ (para armazenar fotos de entrada/saída)
-- - atestados/ (para armazenar comprovantes de atestados)

SET FOREIGN_KEY_CHECKS=1;

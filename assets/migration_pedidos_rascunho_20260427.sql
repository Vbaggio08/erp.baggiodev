CREATE TABLE IF NOT EXISTS sequencias_pedidos (
  tipo VARCHAR(50) NOT NULL PRIMARY KEY,
  proximo_numero INT NOT NULL DEFAULT 1,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO sequencias_pedidos (tipo, proximo_numero)
SELECT 'gabarito', COALESCE(MAX(CAST(numero_pedido AS UNSIGNED)), 0) + 1
FROM gabaritos
ON DUPLICATE KEY UPDATE proximo_numero = GREATEST(proximo_numero, VALUES(proximo_numero));

INSERT INTO sequencias_pedidos (tipo, proximo_numero)
SELECT 'dtf', COALESCE(MAX(CAST(numero_pedido AS UNSIGNED)), 0) + 1
FROM pedidos_dtf
ON DUPLICATE KEY UPDATE proximo_numero = GREATEST(proximo_numero, VALUES(proximo_numero));

CREATE TABLE IF NOT EXISTS modelos_catalogo (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  tamanhos_json TEXT DEFAULT NULL,
  origem VARCHAR(20) NOT NULL DEFAULT 'manual',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_modelos_catalogo_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @has_col := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gabaritos' AND COLUMN_NAME = 'status_pedido'
);
SET @sql := IF(@has_col = 0,
  "ALTER TABLE gabaritos ADD COLUMN status_pedido VARCHAR(20) NOT NULL DEFAULT 'finalizado' AFTER status",
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gabaritos' AND COLUMN_NAME = 'reservado_em'
);
SET @sql := IF(@has_col = 0,
  'ALTER TABLE gabaritos ADD COLUMN reservado_em DATETIME NULL AFTER status_pedido',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gabaritos' AND COLUMN_NAME = 'finalizado_em'
);
SET @sql := IF(@has_col = 0,
  'ALTER TABLE gabaritos ADD COLUMN finalizado_em DATETIME NULL AFTER reservado_em',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_dtf' AND COLUMN_NAME = 'status'
);
SET @sql := IF(@has_col = 0,
  "ALTER TABLE pedidos_dtf ADD COLUMN status VARCHAR(50) DEFAULT 'Mockup' AFTER data_criacao",
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_dtf' AND COLUMN_NAME = 'status_pedido'
);
SET @sql := IF(@has_col = 0,
  'ALTER TABLE pedidos_dtf ADD COLUMN status_pedido VARCHAR(20) NOT NULL DEFAULT ''finalizado'' AFTER status',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_dtf' AND COLUMN_NAME = 'reservado_em'
);
SET @sql := IF(@has_col = 0,
  'ALTER TABLE pedidos_dtf ADD COLUMN reservado_em DATETIME NULL AFTER status_pedido',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_dtf' AND COLUMN_NAME = 'finalizado_em'
);
SET @sql := IF(@has_col = 0,
  'ALTER TABLE pedidos_dtf ADD COLUMN finalizado_em DATETIME NULL AFTER reservado_em',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gabaritos' AND INDEX_NAME = 'idx_gabaritos_numero_pedido'
);
SET @sql := IF(@has_idx = 0,
  'CREATE INDEX idx_gabaritos_numero_pedido ON gabaritos (numero_pedido)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gabaritos' AND INDEX_NAME = 'idx_gabaritos_status_pedido'
);
SET @sql := IF(@has_idx = 0,
  'CREATE INDEX idx_gabaritos_status_pedido ON gabaritos (status_pedido)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_dtf' AND INDEX_NAME = 'idx_pedidos_dtf_numero_pedido'
);
SET @sql := IF(@has_idx = 0,
  'CREATE INDEX idx_pedidos_dtf_numero_pedido ON pedidos_dtf (numero_pedido)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos_dtf' AND INDEX_NAME = 'idx_pedidos_dtf_status_pedido'
);
SET @sql := IF(@has_idx = 0,
  'CREATE INDEX idx_pedidos_dtf_status_pedido ON pedidos_dtf (status_pedido)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
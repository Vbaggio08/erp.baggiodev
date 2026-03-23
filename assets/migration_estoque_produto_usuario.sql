-- Migration: Estoque com produto_id e usuario_id
-- Objetivo:
-- 1) Adicionar colunas produto_id e usuario_id em estoque_movimentacoes
-- 2) Popular as colunas com base nos dados legados (produto/cor/tamanho e usuario)
-- 3) Criar indices e foreign keys

START TRANSACTION;

-- 1) Adicionar colunas se ainda nao existirem
SET @has_produto_id := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'estoque_movimentacoes'
      AND COLUMN_NAME = 'produto_id'
);
SET @sql := IF(@has_produto_id = 0,
    'ALTER TABLE estoque_movimentacoes ADD COLUMN produto_id INT NULL AFTER cor',
    'SELECT "produto_id ja existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_usuario_id := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'estoque_movimentacoes'
      AND COLUMN_NAME = 'usuario_id'
);
SET @sql := IF(@has_usuario_id = 0,
    'ALTER TABLE estoque_movimentacoes ADD COLUMN usuario_id INT NULL AFTER usuario',
    'SELECT "usuario_id ja existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Backfill de produto_id usando os campos legados
UPDATE estoque_movimentacoes m
JOIN produtos p
  ON p.nome = m.produto
 AND p.tamanho = m.tamanho
 AND p.cor = m.cor
SET m.produto_id = p.id
WHERE m.produto_id IS NULL;

-- 3) Backfill de usuario_id a partir do nome do usuario (quando bater exatamente)
UPDATE estoque_movimentacoes m
JOIN usuarios u
  ON u.nome = m.usuario
SET m.usuario_id = u.id
WHERE m.usuario_id IS NULL;

-- 4) Criar indices se nao existirem
SET @has_idx_produto_id := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'estoque_movimentacoes'
      AND INDEX_NAME = 'idx_estoque_mov_produto_id'
);
SET @sql := IF(@has_idx_produto_id = 0,
    'ALTER TABLE estoque_movimentacoes ADD INDEX idx_estoque_mov_produto_id (produto_id)',
    'SELECT "idx_estoque_mov_produto_id ja existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_usuario_id := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'estoque_movimentacoes'
      AND INDEX_NAME = 'idx_estoque_mov_usuario_id'
);
SET @sql := IF(@has_idx_usuario_id = 0,
    'ALTER TABLE estoque_movimentacoes ADD INDEX idx_estoque_mov_usuario_id (usuario_id)',
    'SELECT "idx_estoque_mov_usuario_id ja existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_data := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'estoque_movimentacoes'
      AND INDEX_NAME = 'idx_estoque_mov_data'
);
SET @sql := IF(@has_idx_data = 0,
    'ALTER TABLE estoque_movimentacoes ADD INDEX idx_estoque_mov_data (data_movimento)',
    'SELECT "idx_estoque_mov_data ja existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5) Criar foreign key para produtos se nao existir
SET @has_fk_produto := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'estoque_movimentacoes'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
      AND CONSTRAINT_NAME = 'fk_estoque_mov_produto'
);
SET @sql := IF(@has_fk_produto = 0,
    'ALTER TABLE estoque_movimentacoes ADD CONSTRAINT fk_estoque_mov_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "fk_estoque_mov_produto ja existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6) Criar foreign key para usuarios se nao existir
SET @has_fk_usuario := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'estoque_movimentacoes'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
      AND CONSTRAINT_NAME = 'fk_estoque_mov_usuario'
);
SET @sql := IF(@has_fk_usuario = 0,
    'ALTER TABLE estoque_movimentacoes ADD CONSTRAINT fk_estoque_mov_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "fk_estoque_mov_usuario ja existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;

-- Verificacao rapida
SELECT
    COUNT(*) AS total_movimentacoes,
    SUM(CASE WHEN produto_id IS NOT NULL THEN 1 ELSE 0 END) AS com_produto_id,
    SUM(CASE WHEN usuario_id IS NOT NULL THEN 1 ELSE 0 END) AS com_usuario_id
FROM estoque_movimentacoes;

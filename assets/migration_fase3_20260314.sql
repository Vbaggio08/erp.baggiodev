-- FASE 3: Cálculos Avançados (DSR, Feriados, Horas Extras)
-- Data: 14/03/2026
-- Versão: migration_fase3_20260314.sql

USE ripfire;

-- ==========================================
-- TABELA: feriados (Feriados Fixos e Móveis)
-- ==========================================
CREATE TABLE IF NOT EXISTS feriados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data DATE NOT NULL UNIQUE,
    descricao VARCHAR(100) NOT NULL,
    tipo ENUM('nacional', 'estadual', 'municipal', 'ponte', 'personalizado') DEFAULT 'nacional',
    empresa_id INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_data (data),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABELA: horas_extras (Registro de Horas Extras)
-- ==========================================
CREATE TABLE IF NOT EXISTS horas_extras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    apontamento_id INT,
    data_referencia DATE NOT NULL,
    horas_extras DECIMAL(5,2) NOT NULL,
    tipo ENUM('50', '100') DEFAULT '50',
    motivo VARCHAR(255),
    aprovado_por INT,
    status ENUM('pendente', 'aprovado', 'rejeitado', 'pago', 'compensado') DEFAULT 'pendente',
    data_aprovacao TIMESTAMP NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_usuario_data (usuario_id, data_referencia),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABELA: dsr_descansos (Descanso Semanal Remunerado)
-- ==========================================
CREATE TABLE IF NOT EXISTS dsr_descansos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    data_dsr DATE NOT NULL,
    semana_referencia DATE NOT NULL,
    dias_trabalhados INT DEFAULT 6,
    indice_dsr DECIMAL(5,2) DEFAULT 1.0,
    valor_hora DECIMAL(10,2),
    valor_dsr DECIMAL(10,2),
    status ENUM('calculado', 'compensado', 'pago') DEFAULT 'calculado',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_usuario_semana (usuario_id, semana_referencia),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABELA: configuracao_pontos_avancado (Configurações de Cálculos)
-- ==========================================
CREATE TABLE IF NOT EXISTS configuracao_pontos_avancado (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa_id INT,
    
    permite_horas_extras BOOLEAN DEFAULT TRUE,
    limite_horas_extras_diarias DECIMAL(5,2) DEFAULT 2.0,
    limite_horas_extras_mensais DECIMAL(6,2) DEFAULT 20.0,
    percentual_hora_extra_50 DECIMAL(5,2) DEFAULT 50.0,
    percentual_hora_extra_100 DECIMAL(5,2) DEFAULT 100.0,
    
    calcula_dsr BOOLEAN DEFAULT TRUE,
    dsr_dias_compensacao INT DEFAULT 1,
    
    desconta_feriado_nao_trabalhado BOOLEAN DEFAULT FALSE,
    aplicar_dsr_compensado_feriado BOOLEAN DEFAULT TRUE,
    
    tolerancia_entrada_minutos INT DEFAULT 5,
    tolerancia_saida_minutos INT DEFAULT 5,
    considerar_lunch_automatico BOOLEAN DEFAULT FALSE,
    duracao_lunch_minutos INT DEFAULT 60,
    
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_empresa (empresa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABELA: compensacao_horas (Compensação de Horas)
-- ==========================================
CREATE TABLE IF NOT EXISTS compensacao_horas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    horas_extras_id INT,
    dsr_id INT,
    data_compensacao DATE NOT NULL,
    horas_compensadas DECIMAL(5,2) NOT NULL,
    tipo ENUM('hora_extra', 'dsr', 'feriado') DEFAULT 'hora_extra',
    observacoes VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usuario_data (usuario_id, data_compensacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABELA: notificacoes_ponto (Sistema de Notificações)
-- ==========================================
CREATE TABLE IF NOT EXISTS notificacoes_ponto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    tipo ENUM('alerta', 'info', 'erro', 'sucesso') DEFAULT 'info',
    titulo VARCHAR(100) NOT NULL,
    mensagem TEXT,
    link VARCHAR(255),
    lida BOOLEAN DEFAULT FALSE,
    data_leitura TIMESTAMP NULL,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usuario_lida (usuario_id, lida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Foreign Keys (After table creation)
-- ==========================================
ALTER TABLE feriados 
ADD CONSTRAINT fk_feriado_empresa FOREIGN KEY (empresa_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE horas_extras 
ADD CONSTRAINT fk_he_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;

ALTER TABLE horas_extras 
ADD CONSTRAINT fk_he_apontamento FOREIGN KEY (apontamento_id) REFERENCES apontamentos_ponto(id) ON DELETE SET NULL;

ALTER TABLE horas_extras 
ADD CONSTRAINT fk_he_aprovador FOREIGN KEY (aprovado_por) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE dsr_descansos 
ADD CONSTRAINT fk_dsr_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;

ALTER TABLE compensacao_horas 
ADD CONSTRAINT fk_ch_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;

ALTER TABLE compensacao_horas 
ADD CONSTRAINT fk_ch_horas_extras FOREIGN KEY (horas_extras_id) REFERENCES horas_extras(id) ON DELETE SET NULL;

ALTER TABLE compensacao_horas 
ADD CONSTRAINT fk_ch_dsr FOREIGN KEY (dsr_id) REFERENCES dsr_descansos(id) ON DELETE SET NULL;

ALTER TABLE notificacoes_ponto 
ADD CONSTRAINT fk_not_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;

ALTER TABLE configuracao_pontos_avancado 
ADD CONSTRAINT fk_config_empresa FOREIGN KEY (empresa_id) REFERENCES usuarios(id) ON DELETE SET NULL;

-- ==========================================
-- ÍNDICES ADICIONAIS para Performance
-- ==========================================
ALTER TABLE apontamentos_ponto ADD INDEX idx_usuario_mes (usuario_id, data);
ALTER TABLE horas_extras ADD INDEX idx_data_referencia (data_referencia);
ALTER TABLE dsr_descansos ADD INDEX idx_data_dsr (data_dsr);

-- ==========================================
-- DADOS INICIAIS
-- ==========================================

-- Inserir feriados nacionais Brasil 2026
INSERT IGNORE INTO feriados (data, descricao, tipo) VALUES
('2026-01-01', 'Ano Novo', 'nacional'),
('2026-02-13', 'Sexta-feira de Carnaval (Pont)', 'ponte'),
('2026-02-16', 'Segunda-feira de Carnaval (Pont)', 'ponte'),
('2026-04-03', 'Sexta-feira Santa', 'nacional'),
('2026-04-21', 'Tiradentes', 'nacional'),
('2026-05-01', 'Dia do Trabalho', 'nacional'),
('2026-09-07', 'Independência do Brasil', 'nacional'),
('2026-10-12', 'Nossa Senhora Aparecida', 'nacional'),
('2026-11-02', 'Finados', 'nacional'),
('2026-11-20', 'Consciência Negra', 'nacional'),
('2026-12-25', 'Natal', 'nacional');

-- Inserir configuração padrão
INSERT IGNORE INTO configuracao_pontos_avancado (
    empresa_id,
    permite_horas_extras,
    limite_horas_extras_diarias,
    limite_horas_extras_mensais,
    percentual_hora_extra_50,
    percentual_hora_extra_100,
    calcula_dsr,
    tolerancia_entrada_minutos,
    tolerancia_saida_minutos
) VALUES (
    NULL,
    1,
    2.0,
    20.0,
    50.0,
    100.0,
    1,
    5,
    5
);

-- ==========================================
-- VISUALIZAÇÕES (Views) para Relatórios
-- ==========================================

-- View: Saldo de Horas Mensais
CREATE OR REPLACE VIEW vw_saldo_horas_mensais AS
SELECT 
    u.id usuario_id,
    u.nome,
    u.departamento,
    YEAR(ap.data) ano,
    MONTH(ap.data) mes,
    COUNT(DISTINCT ap.data) dias_trabalhados,
    ROUND(COALESCE(SUM(
        CASE 
            WHEN ap.hora_saida_1 IS NOT NULL AND ap.hora_entrada_1 IS NOT NULL
            THEN (TIME_TO_SEC(ap.hora_saida_1) - TIME_TO_SEC(ap.hora_entrada_1)) / 3600.0
            ELSE 0
        END +
        CASE 
            WHEN ap.hora_saida_2 IS NOT NULL AND ap.hora_entrada_2 IS NOT NULL
            THEN (TIME_TO_SEC(ap.hora_saida_2) - TIME_TO_SEC(ap.hora_entrada_2)) / 3600.0
            ELSE 0
        END
    ), 0), 2) total_horas,
    ROUND(CAST(u.carga_horaria_diaria AS DECIMAL(5,2)) * 20, 2) horas_esperadas,
    ROUND(COALESCE(SUM(
        CASE 
            WHEN ap.hora_saida_1 IS NOT NULL AND ap.hora_entrada_1 IS NOT NULL
            THEN (TIME_TO_SEC(ap.hora_saida_1) - TIME_TO_SEC(ap.hora_entrada_1)) / 3600.0
            ELSE 0
        END +
        CASE 
            WHEN ap.hora_saida_2 IS NOT NULL AND ap.hora_entrada_2 IS NOT NULL
            THEN (TIME_TO_SEC(ap.hora_saida_2) - TIME_TO_SEC(ap.hora_entrada_2)) / 3600.0
            ELSE 0
        END
    ), 0) - (CAST(u.carga_horaria_diaria AS DECIMAL(5,2)) * 20), 2) saldo_horas
FROM usuarios u
LEFT JOIN apontamentos_ponto ap ON u.id = ap.usuario_id AND YEAR(ap.data) = YEAR(NOW()) AND MONTH(ap.data) = MONTH(NOW())
GROUP BY u.id, YEAR(ap.data), MONTH(ap.data);

-- View: Horas Extras por Usuário
CREATE OR REPLACE VIEW vw_horas_extras_resumo AS
SELECT 
    he.usuario_id,
    u.nome,
    YEAR(he.data_referencia) ano,
    MONTH(he.data_referencia) mes,
    COUNT(*) quantidade,
    ROUND(SUM(he.horas_extras), 2) total_horas,
    ROUND(SUM(CASE WHEN he.status = 'aprovado' THEN he.horas_extras ELSE 0 END), 2) horas_aprovadas,
    ROUND(SUM(CASE WHEN he.status = 'pago' THEN he.horas_extras ELSE 0 END), 2) horas_pagas
FROM horas_extras he
JOIN usuarios u ON he.usuario_id = u.id
GROUP BY he.usuario_id, YEAR(he.data_referencia), MONTH(he.data_referencia);

COMMIT;

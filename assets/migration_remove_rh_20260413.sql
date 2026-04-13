-- Retirada do modulo RH/Ponto
-- Data: 2026-04-13
-- ATENCAO: Esta migration remove definitivamente dados e estruturas de RH.

SET FOREIGN_KEY_CHECKS = 0;

-- Views RH
DROP VIEW IF EXISTS vw_horas_extras_resumo;
DROP VIEW IF EXISTS vw_saldo_horas_mensais;

-- Tabelas RH (ordem do menor acoplamento para maior)
DROP TABLE IF EXISTS notificacoes_ponto;
DROP TABLE IF EXISTS sincronizacoes_offline;
DROP TABLE IF EXISTS solicitacoes_alteracao_ponto;
DROP TABLE IF EXISTS historico_alteracoes_ponto;
DROP TABLE IF EXISTS compensacao_horas;
DROP TABLE IF EXISTS dsr_descansos;
DROP TABLE IF EXISTS horas_extras;
DROP TABLE IF EXISTS atestados;
DROP TABLE IF EXISTS saldos_mensais;
DROP TABLE IF EXISTS dispositivos_autorizados;
DROP TABLE IF EXISTS geolocation_empresa;
DROP TABLE IF EXISTS configuracao_ponto_usuario;
DROP TABLE IF EXISTS configuracoes_ponto;
DROP TABLE IF EXISTS configuracao_pontos_avancado;
DROP TABLE IF EXISTS configuracao_ponto;
DROP TABLE IF EXISTS feriados;
DROP TABLE IF EXISTS apontamentos_ponto;

SET FOREIGN_KEY_CHECKS = 1;

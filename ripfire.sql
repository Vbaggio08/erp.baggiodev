-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15/03/2026 às 02:28
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ripfire`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `apontamentos_ponto`
--

CREATE TABLE `apontamentos_ponto` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora_entrada_1` time DEFAULT NULL,
  `hora_saida_1` time DEFAULT NULL,
  `foto_entrada_1` varchar(255) DEFAULT NULL COMMENT 'Path: 2026-03-14/user_5_entrada_1.jpg',
  `foto_saida_1` varchar(255) DEFAULT NULL,
  `geo_entrada_1` varchar(50) DEFAULT NULL COMMENT 'Formato: -23.5505,-46.6333',
  `geo_saida_1` varchar(50) DEFAULT NULL,
  `geo_precisao_entrada_1` int(11) DEFAULT NULL COMMENT 'Precisão em metros',
  `geo_precisao_saida_1` int(11) DEFAULT NULL,
  `ip_origem_entrada_1` varchar(50) DEFAULT NULL,
  `ip_origem_saida_1` varchar(50) DEFAULT NULL,
  `device_id_entrada_1` varchar(255) DEFAULT NULL COMMENT 'Canvas fingerprint',
  `device_id_saida_1` varchar(255) DEFAULT NULL,
  `user_agent_entrada_1` text DEFAULT NULL,
  `user_agent_saida_1` text DEFAULT NULL,
  `hora_entrada_2` time DEFAULT NULL,
  `hora_saida_2` time DEFAULT NULL,
  `foto_entrada_2` varchar(255) DEFAULT NULL,
  `foto_saida_2` varchar(255) DEFAULT NULL,
  `geo_entrada_2` varchar(50) DEFAULT NULL,
  `geo_saida_2` varchar(50) DEFAULT NULL,
  `geo_precisao_entrada_2` int(11) DEFAULT NULL,
  `geo_precisao_saida_2` int(11) DEFAULT NULL,
  `ip_origem_entrada_2` varchar(50) DEFAULT NULL,
  `ip_origem_saida_2` varchar(50) DEFAULT NULL,
  `device_id_entrada_2` varchar(255) DEFAULT NULL,
  `device_id_saida_2` varchar(255) DEFAULT NULL,
  `user_agent_entrada_2` text DEFAULT NULL,
  `user_agent_saida_2` text DEFAULT NULL,
  `hora_entrada_3` time DEFAULT NULL,
  `hora_saida_3` time DEFAULT NULL,
  `foto_entrada_3` varchar(255) DEFAULT NULL,
  `foto_saida_3` varchar(255) DEFAULT NULL,
  `geo_entrada_3` varchar(50) DEFAULT NULL,
  `geo_saida_3` varchar(50) DEFAULT NULL,
  `geo_precisao_entrada_3` int(11) DEFAULT NULL,
  `geo_precisao_saida_3` int(11) DEFAULT NULL,
  `ip_origem_entrada_3` varchar(50) DEFAULT NULL,
  `ip_origem_saida_3` varchar(50) DEFAULT NULL,
  `device_id_entrada_3` varchar(255) DEFAULT NULL,
  `device_id_saida_3` varchar(255) DEFAULT NULL,
  `user_agent_entrada_3` text DEFAULT NULL,
  `user_agent_saida_3` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'presente' COMMENT 'presente, ausente, falta, atestado',
  `observacao` text DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `atestados`
--

CREATE TABLE `atestados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `tipo` varchar(50) NOT NULL COMMENT 'medico, falta_justificada, licenca_remunerada, licenca_nao_remunerada',
  `comprovante_url` varchar(255) DEFAULT NULL COMMENT 'Path do arquivo: atestados/2026-03-14/user_5_atestado.pdf',
  `status` varchar(50) DEFAULT 'pendente' COMMENT 'pendente, aprovado, rejeitado',
  `motivo_rejeicao` text DEFAULT NULL,
  `aprovador_id` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `aprovado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `compensacao_horas`
--

CREATE TABLE `compensacao_horas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `horas_extras_id` int(11) DEFAULT NULL,
  `dsr_id` int(11) DEFAULT NULL,
  `data_compensacao` date NOT NULL,
  `horas_compensadas` decimal(5,2) NOT NULL,
  `tipo` enum('hora_extra','dsr','feriado') DEFAULT 'hora_extra',
  `observacoes` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `fornecedor` varchar(100) DEFAULT NULL,
  `produto` varchar(255) DEFAULT NULL,
  `itens_json` text NOT NULL,
  `observacoes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `data_compra` date DEFAULT NULL,
  `data_pedido` datetime DEFAULT current_timestamp(),
  `data_chegada` datetime DEFAULT NULL,
  `empresa` varchar(100) DEFAULT 'Ripfire',
  `valor_total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracao_ponto`
--

CREATE TABLE `configuracao_ponto` (
  `id` int(11) NOT NULL DEFAULT 1,
  `tolerancia_atraso_minutos` int(11) DEFAULT 5,
  `horario_inicio_expediente` time DEFAULT '08:00:00',
  `horario_fim_expediente` time DEFAULT '17:00:00',
  `considerar_feriados` tinyint(4) DEFAULT 1,
  `lista_feriados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array de datas: ["2026-03-14", "2026-04-21", ...]' CHECK (json_valid(`lista_feriados`)),
  `usar_dsr` tinyint(4) DEFAULT 1 COMMENT 'Descanso Semanal Remunerado',
  `quantidade_batidas` int(11) DEFAULT 2 COMMENT 'Valores: 2 (entrada/saída), 4 (entrada/saída/entrada/saída), 6 (3x entrada/saída)',
  `usar_geolocalizacao` tinyint(4) DEFAULT 0,
  `raio_permitido_metros` int(11) DEFAULT 500,
  `exigir_foto_mobile` tinyint(4) DEFAULT 1,
  `exigir_foto_desktop` tinyint(4) DEFAULT 0,
  `modo_multiplas_maquinas` tinyint(4) DEFAULT 0 COMMENT 'Se 0: apenas 1 máquina/usuário. Se 1: múltiplas permitidas',
  `limiar_proximidade_minutos` int(11) DEFAULT 5 COMMENT 'Aviso de batida próxima',
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracao_ponto`
--

INSERT INTO `configuracao_ponto` (`id`, `tolerancia_atraso_minutos`, `horario_inicio_expediente`, `horario_fim_expediente`, `considerar_feriados`, `lista_feriados`, `usar_dsr`, `quantidade_batidas`, `usar_geolocalizacao`, `raio_permitido_metros`, `exigir_foto_mobile`, `exigir_foto_desktop`, `modo_multiplas_maquinas`, `limiar_proximidade_minutos`, `atualizado_em`) VALUES
(1, 5, '08:00:00', '17:00:00', 1, NULL, 1, 2, 0, 500, 1, 0, 0, 5, '2026-03-14 14:10:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracao_pontos_avancado`
--

CREATE TABLE `configuracao_pontos_avancado` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `permite_horas_extras` tinyint(1) DEFAULT 1,
  `limite_horas_extras_diarias` decimal(5,2) DEFAULT 2.00,
  `limite_horas_extras_mensais` decimal(6,2) DEFAULT 20.00,
  `percentual_hora_extra_50` decimal(5,2) DEFAULT 50.00,
  `percentual_hora_extra_100` decimal(5,2) DEFAULT 100.00,
  `calcula_dsr` tinyint(1) DEFAULT 1,
  `dsr_dias_compensacao` int(11) DEFAULT 1,
  `desconta_feriado_nao_trabalhado` tinyint(1) DEFAULT 0,
  `aplicar_dsr_compensado_feriado` tinyint(1) DEFAULT 1,
  `tolerancia_entrada_minutos` int(11) DEFAULT 5,
  `tolerancia_saida_minutos` int(11) DEFAULT 5,
  `considerar_lunch_automatico` tinyint(1) DEFAULT 0,
  `duracao_lunch_minutos` int(11) DEFAULT 60,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracao_pontos_avancado`
--

INSERT INTO `configuracao_pontos_avancado` (`id`, `empresa_id`, `permite_horas_extras`, `limite_horas_extras_diarias`, `limite_horas_extras_mensais`, `percentual_hora_extra_50`, `percentual_hora_extra_100`, `calcula_dsr`, `dsr_dias_compensacao`, `desconta_feriado_nao_trabalhado`, `aplicar_dsr_compensado_feriado`, `tolerancia_entrada_minutos`, `tolerancia_saida_minutos`, `considerar_lunch_automatico`, `duracao_lunch_minutos`, `criado_em`, `updated_at`) VALUES
(1, NULL, 1, 2.00, 20.00, 50.00, 100.00, 1, 1, 0, 1, 5, 5, 0, 60, '2026-03-14 20:23:06', '2026-03-14 20:23:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `dispositivos_autorizados`
--

CREATE TABLE `dispositivos_autorizados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `device_id` varchar(255) NOT NULL COMMENT 'Fingerprint único da máquina (canvas hash)',
  `device_nome` varchar(255) DEFAULT NULL COMMENT 'Notebook João, Desktop Oficina, etc',
  `ip_address` varchar(100) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `tipo_dispositivo` varchar(50) DEFAULT NULL COMMENT 'desktop, mobile, tablet',
  `primeiro_uso` datetime DEFAULT current_timestamp(),
  `ultimo_uso` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ativo` tinyint(4) DEFAULT 1,
  `autorizado_por_admin` datetime DEFAULT NULL COMMENT 'NULL = auto-registro, DATETIME = autorizado por admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `dsr_descansos`
--

CREATE TABLE `dsr_descansos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_dsr` date NOT NULL,
  `semana_referencia` date NOT NULL,
  `dias_trabalhados` int(11) DEFAULT 6,
  `indice_dsr` decimal(5,2) DEFAULT 1.00,
  `valor_hora` decimal(10,2) DEFAULT NULL,
  `valor_dsr` decimal(10,2) DEFAULT NULL,
  `status` enum('calculado','compensado','pago') DEFAULT 'calculado',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `ativo` tinyint(4) DEFAULT 1,
  `responsavel` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_movimentacao`
--

CREATE TABLE `estoque_movimentacao` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `tipo` enum('Entrada','Saida') NOT NULL,
  `origem` varchar(50) DEFAULT NULL,
  `data_movimento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_movimentacoes`
--

CREATE TABLE `estoque_movimentacoes` (
  `id` int(11) NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `produto` varchar(100) NOT NULL,
  `tamanho` varchar(10) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `quantidade` int(11) NOT NULL,
  `observacao` text DEFAULT NULL,
  `data_movimento` datetime DEFAULT current_timestamp(),
  `usuario` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_saldo`
--

CREATE TABLE `estoque_saldo` (
  `id` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT 0,
  `ultima_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `feriados`
--

CREATE TABLE `feriados` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `tipo` enum('nacional','estadual','municipal','ponte','personalizado') DEFAULT 'nacional',
  `empresa_id` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `feriados`
--

INSERT INTO `feriados` (`id`, `data`, `descricao`, `tipo`, `empresa_id`, `criado_em`, `updated_at`) VALUES
(1, '2026-01-01', 'Ano Novo', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(2, '2026-02-13', 'Sexta-feira de Carnaval (Pont)', 'ponte', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(3, '2026-02-16', 'Segunda-feira de Carnaval (Pont)', 'ponte', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(4, '2026-04-03', 'Sexta-feira Santa', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(5, '2026-04-21', 'Tiradentes', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(6, '2026-05-01', 'Dia do Trabalho', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(7, '2026-09-07', 'Independência do Brasil', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(8, '2026-10-12', 'Nossa Senhora Aparecida', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(9, '2026-11-02', 'Finados', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(10, '2026-11-20', 'Consciência Negra', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06'),
(11, '2026-12-25', 'Natal', 'nacional', NULL, '2026-03-14 20:23:06', '2026-03-14 20:23:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `gabaritos`
--

CREATE TABLE `gabaritos` (
  `id` int(11) NOT NULL,
  `cliente` varchar(100) DEFAULT NULL,
  `numero_pedido` varchar(50) DEFAULT NULL,
  `plataforma` varchar(50) DEFAULT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `tamanho` varchar(100) DEFAULT NULL,
  `quantidade` int(11) DEFAULT 1,
  `valor_unit` decimal(10,2) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `data_pagamento` date DEFAULT NULL,
  `data_pedido` date DEFAULT NULL,
  `data_entrega` date DEFAULT NULL,
  `imagem_mockup` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `meio_pagamento` varchar(50) DEFAULT NULL,
  `caminho_comprovante` varchar(255) DEFAULT NULL,
  `vendedor_id` int(11) DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Mockup',
  `itens_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `gabaritos`
--

INSERT INTO `gabaritos` (`id`, `cliente`, `numero_pedido`, `plataforma`, `contato`, `modelo`, `cor`, `tamanho`, `quantidade`, `valor_unit`, `valor_total`, `forma_pagamento`, `data_pagamento`, `data_pedido`, `data_entrega`, `imagem_mockup`, `observacoes`, `meio_pagamento`, `caminho_comprovante`, `vendedor_id`, `data_criacao`, `status`, `itens_json`) VALUES
(1, 'FABENE CRUZ', '01', 'WhatsApp', '+55 47 8444-2116', 'Camiseta Oversized', 'Preta', 'G1:1', 1, 0.00, 0.00, NULL, NULL, '2026-02-26', '2026-03-03', '69a57c914889e.png', '', 'Pago em Loja', '', 6, '2026-03-02 08:18:15', 'Estampado', '{\"G1\":\"1\"}'),
(2, 'FABENE CRUZ', '01', 'WhatsApp', '+55 47 8444-2116', 'Camiseta Oversized', 'Branca', 'G1:1', 1, 0.00, 0.00, NULL, NULL, '2026-02-26', '2026-03-03', '69a573e44b028.png', '', NULL, NULL, NULL, '2026-03-02 08:19:03', 'Mockup', '{\"G1\":\"1\"}'),
(3, 'FABENE CRUZ', '02', 'WhatsApp', '+55 47 8444-2116', 'Camiseta Oversized', 'Preta', 'G1:1', 1, 0.00, 0.00, NULL, NULL, '2026-03-02', '2026-03-04', '69a5c656bc2dd.png', '', 'Pix', '69a6ca9cb1303_comp.jpeg', 6, '2026-03-02 14:16:53', 'Estampado', '{\"G1\":\"1\"}'),
(5, 'juboeing', '03', 'WhatsApp', '+55 47 9917-8871', 'Camiseta Oversized', 'Preta', 'P:1', 1, 0.00, 0.00, NULL, NULL, '2026-03-03', '2026-03-04', '69a74899a0a6d.jpeg', '', 'Pix', '69a74899a11a5_comp.jpeg', 6, '2026-03-03 17:46:17', 'Estampado', '{\"P\":\"1\"}'),
(6, 'Vitor Alves', '04', 'WhatsApp', '+55 13 98139-1242', 'Camiseta Oversized', 'Preta', 'G:2', 2, 0.00, 0.00, NULL, NULL, '2026-03-04', '2026-03-06', '69a82fdf8837f.png', '', 'Pix', '69a82fdf89130_comp.jpeg', 6, '2026-03-04 10:13:03', 'Impresso', '{\"G\":\"2\"}'),
(7, 'Vitor Alves', '04', 'WhatsApp', '+55 13 98139-1242', 'Camiseta Oversized', 'Branca', 'G:2', 2, 0.00, 0.00, NULL, NULL, '2026-03-04', '2026-03-06', '69a8300b41603.png', '', 'Pix', NULL, 0, '2026-03-04 10:13:47', 'Mockup', '{\"G\":\"2\"}'),
(8, 'cliente baby', '05', 'WhatsApp', '', 'Camiseta Oversized', 'Branca', 'PP:1', 1, 0.00, 0.00, NULL, NULL, '2026-03-06', '0000-00-00', '69aacb2fc9f2b.png', 'IMAGEM MAIOR', 'Pago em Loja', NULL, 6, '2026-03-06 09:40:15', 'Enviado', '{\"PP\":\"1\"}'),
(9, 'cliente baby', '05', 'WhatsApp', '', 'Camiseta Infantil Cotton', 'BRANCA', 'PP:1', 1, 0.00, 0.00, NULL, NULL, '2026-03-06', '0000-00-00', '69aacb5921d61.png', '', 'Dinheiro', NULL, 0, '2026-03-06 09:40:57', 'Mockup', '{\"PP\":\"1\"}'),
(13, 'Carla', '06', 'WhatsApp', '+55 47 9916-1931', 'Camiseta Infantil Cotton', 'Preta', '10:1', 1, 0.00, 0.00, NULL, NULL, '2026-03-12', '2026-03-12', '69b2c99f4dbb5.png', '', '', NULL, 6, '2026-03-12 11:11:43', 'Mockup', '{\"10\":\"1\"}'),
(14, 'Carla', '06', 'WhatsApp', '+55 47 9916-1931', 'Camiseta Infantil Cotton', 'Branca', '12:1', 1, 0.00, 0.00, NULL, NULL, '2026-03-12', '2026-03-12', '69b2c9bd7f085.png', '', '', NULL, 0, '2026-03-12 11:12:13', 'Mockup', '{\"12\":\"1\"}'),
(15, 'Andreia Nego', '07', 'WhatsApp', '+55 47 9601-2462', 'Camiseta Oversized', 'Preta', 'P:1 M:1 GG:1', 3, 0.00, 0.00, NULL, NULL, '2026-03-12', '2026-03-12', '69b2cc89e27cc.png', '', 'Pix', '69b2cc89e30b9_comp.jpeg', 0, '2026-03-12 11:24:09', 'Mockup', '{\"P\":\"1\",\"M\":\"1\",\"GG\":\"1\"}'),
(16, 'Pamela', '08', 'WhatsApp', '+55 47 9645-3325', 'Camiseta Regular Cotton', 'Preta', 'G:2', 2, 0.00, 0.00, NULL, NULL, '2026-03-13', '0000-00-00', '69b457691f1f3.png', 'Tamanho: G masculino \r\nFrase verso: Rafael da Sarah \r\n\r\nTamanho: G masculino\r\nFrase verso: Maicon da Pamela', 'Pago em Loja', '', 6, '2026-03-13 15:28:57', 'Estampado', '{\"G\":\"2\"}'),
(17, 'Pamela', '08', 'WhatsApp', '+55 47 9645-3325', 'Baby Look Feminina', 'Preta', 'P:1 G:1', 2, 0.00, 0.00, NULL, NULL, '2026-03-13', '0000-00-00', '69b45952a82f2.png', 'Tamanho: G(feminina)\r\nFrase verso: Sarah do Rafael \r\n\r\nTamanho: P (feminina)\r\nFrase verso: Pamela do Maicon ', 'Pago em Loja', NULL, 6, '2026-03-13 15:37:06', 'Mockup', '{\"P\":\"1\",\"G\":\"1\"}');

-- --------------------------------------------------------

--
-- Estrutura para tabela `geolocation_empresa`
--

CREATE TABLE `geolocation_empresa` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT 1 COMMENT 'Para multi-empresa, FK para tabela empresa (se houver)',
  `latitude` decimal(10,8) NOT NULL COMMENT 'Ex: -23.55048',
  `longitude` decimal(10,8) NOT NULL COMMENT 'Ex: -46.63331',
  `endereco` text DEFAULT NULL,
  `raio_metros` int(11) DEFAULT 500 COMMENT 'Raio permitido a partir do ponto de coordenada',
  `ativo` tinyint(4) DEFAULT 1,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_alteracoes_ponto`
--

CREATE TABLE `historico_alteracoes_ponto` (
  `id` int(11) NOT NULL,
  `apontamento_id` int(11) NOT NULL,
  `usuario_alterador_id` int(11) NOT NULL COMMENT 'Quem fez a alteração',
  `tipo_alteracao` varchar(100) NOT NULL COMMENT 'entrada_criada, saida_criada, entrada_editada, saida_editada, validacao_proximidade_confirmada_saida, etc',
  `valor_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Valores originais em JSON' CHECK (json_valid(`valor_anterior`)),
  `valor_novo` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Novos valores em JSON' CHECK (json_valid(`valor_novo`)),
  `motivo_alteracao` text NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `hash_sha256` varchar(64) DEFAULT NULL COMMENT 'Hash de integridade (sha256)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_movimentacao`
--

CREATE TABLE `historico_movimentacao` (
  `id` int(11) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `produto` varchar(100) DEFAULT NULL,
  `tamanho` varchar(10) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `tipo` enum('entrada','saida') DEFAULT NULL,
  `qtd` int(11) DEFAULT NULL,
  `data_movimento` datetime DEFAULT current_timestamp(),
  `observacao` text DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT 'Ripfire',
  `produto_tipo` varchar(50) DEFAULT 'Produto',
  `valor_unitario` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `horas_extras`
--

CREATE TABLE `horas_extras` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `apontamento_id` int(11) DEFAULT NULL,
  `data_referencia` date NOT NULL,
  `horas_extras` decimal(5,2) NOT NULL,
  `tipo` enum('50','100') DEFAULT '50',
  `motivo` varchar(255) DEFAULT NULL,
  `aprovado_por` int(11) DEFAULT NULL,
  `status` enum('pendente','aprovado','rejeitado','pago','compensado') DEFAULT 'pendente',
  `data_aprovacao` timestamp NULL DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes_ponto`
--

CREATE TABLE `notificacoes_ponto` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('alerta','info','erro','sucesso') DEFAULT 'info',
  `titulo` varchar(100) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `data_leitura` timestamp NULL DEFAULT NULL,
  `criada_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos_dtf`
--

CREATE TABLE `pedidos_dtf` (
  `id` int(11) NOT NULL,
  `cliente` varchar(255) NOT NULL,
  `contato` varchar(50) DEFAULT NULL,
  `plataforma` varchar(50) DEFAULT NULL,
  `numero_pedido` varchar(50) DEFAULT NULL,
  `data_pedido` date DEFAULT NULL,
  `data_entrega` date DEFAULT NULL,
  `metros` decimal(10,2) NOT NULL,
  `valor_metro` decimal(10,2) NOT NULL,
  `valor_final` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `arquivo_impressao` varchar(255) DEFAULT NULL,
  `caminho_comprovante` varchar(255) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos_dtf`
--

INSERT INTO `pedidos_dtf` (`id`, `cliente`, `contato`, `plataforma`, `numero_pedido`, `data_pedido`, `data_entrega`, `metros`, `valor_metro`, `valor_final`, `observacoes`, `arquivo_impressao`, `caminho_comprovante`, `data_criacao`) VALUES
(1, 'test', '+55 47 8444-2116', 'Balcão', '01', '2026-03-03', NULL, 24.00, 44.99, 1079.76, '', '69a7085016bd2.png', '69a7085017223_comp.jpeg', '2026-03-03 16:12:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos_producao`
--

CREATE TABLE `pedidos_producao` (
  `id` int(11) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `canal_venda` varchar(50) DEFAULT NULL,
  `pedido_id` varchar(50) DEFAULT NULL,
  `cliente` varchar(100) DEFAULT NULL,
  `produto` varchar(100) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `tamanho` varchar(10) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pendente',
  `data_entrada` datetime DEFAULT current_timestamp(),
  `data_conclusao` datetime DEFAULT NULL,
  `prioridade` int(11) DEFAULT 1,
  `custo_estimado` decimal(10,2) DEFAULT 0.00,
  `tempo_estimado` int(11) DEFAULT 0,
  `data_entrega` datetime DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tamanho` varchar(10) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `preco_venda` decimal(10,2) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `preco_custo` decimal(10,2) DEFAULT 0.00,
  `estoque_atual` int(11) DEFAULT 0,
  `ativo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `sku`, `nome`, `tamanho`, `cor`, `preco_venda`, `categoria`, `preco_custo`, `estoque_atual`, `ativo`) VALUES
(183, 'CO-PR-PP', 'Camiseta Oversized', 'PP', 'Preta', 0.00, NULL, 0.00, 0, 1),
(184, 'CO-PR-P', 'Camiseta Oversized', 'P', 'Preta', 0.00, NULL, 0.00, 0, 1),
(185, 'CO-PR-M', 'Camiseta Oversized', 'M', 'Preta', 0.00, NULL, 0.00, 0, 1),
(186, 'CO-PR-G', 'Camiseta Oversized', 'G', 'Preta', 0.00, NULL, 0.00, 0, 1),
(187, 'CO-PR-GG', 'Camiseta Oversized', 'GG', 'Preta', 0.00, NULL, 0.00, 0, 1),
(188, 'CO-PR-G1', 'Camiseta Oversized', 'G1', 'Preta', 0.00, NULL, 0.00, 0, 1),
(189, 'CO-PR-G2', 'Camiseta Oversized', 'G2', 'Preta', 0.00, NULL, 0.00, 0, 1),
(190, 'CO-PR-G3', 'Camiseta Oversized', 'G3', 'Preta', 0.00, NULL, 0.00, 0, 1),
(191, 'CO-BR-PP', 'Camiseta Oversized', 'PP', 'Branca', 0.00, NULL, 0.00, 0, 1),
(192, 'CO-BR-P', 'Camiseta Oversized', 'P', 'Branca', 0.00, NULL, 0.00, 0, 1),
(193, 'CO-BR-M', 'Camiseta Oversized', 'M', 'Branca', 0.00, NULL, 0.00, 0, 1),
(194, 'CO-BR-G', 'Camiseta Oversized', 'G', 'Branca', 0.00, NULL, 0.00, 0, 1),
(195, 'CO-BR-GG', 'Camiseta Oversized', 'GG', 'Branca', 0.00, NULL, 0.00, 0, 1),
(196, 'CO-BR-G1', 'Camiseta Oversized', 'G1', 'Branca', 0.00, NULL, 0.00, 0, 1),
(197, 'CO-BR-G2', 'Camiseta Oversized', 'G2', 'Branca', 0.00, NULL, 0.00, 0, 1),
(198, 'CO-BR-G3', 'Camiseta Oversized', 'G3', 'Branca', 0.00, NULL, 0.00, 0, 1),
(199, 'CO-OW-PP', 'Camiseta Oversized', 'PP', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(200, 'CO-OW-P', 'Camiseta Oversized', 'P', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(201, 'CO-OW-M', 'Camiseta Oversized', 'M', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(202, 'CO-OW-G', 'Camiseta Oversized', 'G', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(203, 'CO-OW-GG', 'Camiseta Oversized', 'GG', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(204, 'CO-OW-G1', 'Camiseta Oversized', 'G1', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(205, 'CO-OW-G2', 'Camiseta Oversized', 'G2', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(206, 'CO-OW-G3', 'Camiseta Oversized', 'G3', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(215, 'CO-MA-PP', 'Camiseta Oversized', 'PP', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(216, 'CO-MA-P', 'Camiseta Oversized', 'P', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(217, 'CO-MA-M', 'Camiseta Oversized', 'M', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(218, 'CO-MA-G', 'Camiseta Oversized', 'G', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(219, 'CO-MA-GG', 'Camiseta Oversized', 'GG', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(220, 'CO-MA-G1', 'Camiseta Oversized', 'G1', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(221, 'CO-MA-G2', 'Camiseta Oversized', 'G2', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(222, 'CO-MA-G3', 'Camiseta Oversized', 'G3', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(223, 'BTC-PR-PP', 'Baby Tee Feminina Cotton', 'PP', 'Preta', 0.00, NULL, 0.00, 0, 1),
(224, 'BTC-PR-P', 'Baby Tee Feminina Cotton', 'P', 'Preta', 0.00, NULL, 0.00, 0, 1),
(225, 'BTC-PR-M', 'Baby Tee Feminina Cotton', 'M', 'Preta', 0.00, NULL, 0.00, 0, 1),
(226, 'BTC-PR-G', 'Baby Tee Feminina Cotton', 'G', 'Preta', 0.00, NULL, 0.00, 0, 1),
(227, 'BTC-PR-GG', 'Baby Tee Feminina Cotton', 'GG', 'Preta', 0.00, NULL, 0.00, 0, 1),
(228, 'BTC-PR-G1', 'Baby Tee Feminina Cotton', 'G1', 'Preta', 0.00, NULL, 0.00, 0, 1),
(229, 'BTC-PR-G2', 'Baby Tee Feminina Cotton', 'G2', 'Preta', 0.00, NULL, 0.00, 0, 1),
(230, 'BTC-PR-G3', 'Baby Tee Feminina Cotton', 'G3', 'Preta', 0.00, NULL, 0.00, 0, 1),
(231, 'BTC-BR-PP', 'Baby Tee Feminina Cotton', 'PP', 'Branca', 0.00, NULL, 0.00, 0, 1),
(232, 'BTC-BR-P', 'Baby Tee Feminina Cotton', 'P', 'Branca', 0.00, NULL, 0.00, 0, 1),
(233, 'BTC-BR-M', 'Baby Tee Feminina Cotton', 'M', 'Branca', 0.00, NULL, 0.00, 0, 1),
(234, 'BTC-BR-G', 'Baby Tee Feminina Cotton', 'G', 'Branca', 0.00, NULL, 0.00, 0, 1),
(235, 'BTC-BR-GG', 'Baby Tee Feminina Cotton', 'GG', 'Branca', 0.00, NULL, 0.00, 0, 1),
(236, 'BTC-BR-G1', 'Baby Tee Feminina Cotton', 'G1', 'Branca', 0.00, NULL, 0.00, 0, 1),
(237, 'BTC-BR-G2', 'Baby Tee Feminina Cotton', 'G2', 'Branca', 0.00, NULL, 0.00, 0, 1),
(238, 'BTC-BR-G3', 'Baby Tee Feminina Cotton', 'G3', 'Branca', 0.00, NULL, 0.00, 0, 1),
(239, 'BTA-PR-PP', 'Baby Tee Feminina 100% ', 'PP', 'Preta', 0.00, NULL, 0.00, 0, 1),
(240, 'BTA-PR-P', 'Baby Tee Feminina 100% ', 'P', 'Preta', 0.00, NULL, 0.00, 0, 1),
(241, 'BTA-PR-M', 'Baby Tee Feminina 100% ', 'M', 'Preta', 0.00, NULL, 0.00, 0, 1),
(242, 'BTA-PR-G', 'Baby Tee Feminina 100% ', 'G', 'Preta', 0.00, NULL, 0.00, 0, 1),
(243, 'BTA-PR-GG', 'Baby Tee Feminina 100% ', 'GG', 'Preta', 0.00, NULL, 0.00, 0, 1),
(244, 'BTA-PR-G1', 'Baby Tee Feminina 100% ', 'G1', 'Preta', 0.00, NULL, 0.00, 0, 1),
(245, 'BTA-PR-G2', 'Baby Tee Feminina 100% ', 'G2', 'Preta', 0.00, NULL, 0.00, 0, 1),
(246, 'BTA-PR-G3', 'Baby Tee Feminina 100% ', 'G3', 'Preta', 0.00, NULL, 0.00, 0, 1),
(247, 'BTA-BR-PP', 'Baby Tee Feminina 100% ', 'PP', 'Branca', 0.00, NULL, 0.00, 0, 1),
(248, 'BTA-BR-P', 'Baby Tee Feminina 100% ', 'P', 'Branca', 0.00, NULL, 0.00, 0, 1),
(249, 'BTA-BR-M', 'Baby Tee Feminina 100% ', 'M', 'Branca', 0.00, NULL, 0.00, 0, 1),
(250, 'BTA-BR-G', 'Baby Tee Feminina 100% ', 'G', 'Branca', 0.00, NULL, 0.00, 0, 1),
(251, 'BTA-BR-GG', 'Baby Tee Feminina 100% ', 'GG', 'Branca', 0.00, NULL, 0.00, 0, 1),
(252, 'BTA-BR-G1', 'Baby Tee Feminina 100% ', 'G1', 'Branca', 0.00, NULL, 0.00, 0, 1),
(253, 'BTA-BR-G2', 'Baby Tee Feminina 100% ', 'G2', 'Branca', 0.00, NULL, 0.00, 0, 1),
(254, 'BTA-BR-G3', 'Baby Tee Feminina 100% ', 'G3', 'Branca', 0.00, NULL, 0.00, 0, 1),
(255, 'BLC-PR-PP', 'Baby Look Feminina  Cotton', 'PP', 'Preta', 0.00, NULL, 0.00, 0, 1),
(256, 'BLC-PR-P', 'Baby Look Feminina  Cotton', 'P', 'Preta', 0.00, NULL, 0.00, 0, 1),
(257, 'BLC-PR-M', 'Baby Look Feminina  Cotton', 'M', 'Preta', 0.00, NULL, 0.00, 0, 1),
(258, 'BLC-PR-G', 'Baby Look Feminina  Cotton', 'G', 'Preta', 0.00, NULL, 0.00, 0, 1),
(259, 'BLC-PR-GG', 'Baby Look Feminina  Cotton', 'GG', 'Preta', 0.00, NULL, 0.00, 0, 1),
(260, 'BLC-PR-G1', 'Baby Look Feminina  Cotton', 'G1', 'Preta', 0.00, NULL, 0.00, 0, 1),
(261, 'BLC-PR-G2', 'Baby Look Feminina  Cotton', 'G2', 'Preta', 0.00, NULL, 0.00, 0, 1),
(262, 'BLC-PR-G3', 'Baby Look Feminina  Cotton', 'G3', 'Preta', 0.00, NULL, 0.00, 0, 1),
(263, 'BLC-BR-PP', 'Baby Look Feminina  Cotton', 'PP', 'Branca', 0.00, NULL, 0.00, 0, 1),
(264, 'BLC-BR-P', 'Baby Look Feminina  Cotton', 'P', 'Branca', 0.00, NULL, 0.00, 0, 1),
(265, 'BLC-BR-M', 'Baby Look Feminina  Cotton', 'M', 'Branca', 0.00, NULL, 0.00, 0, 1),
(266, 'BLC-BR-G', 'Baby Look Feminina  Cotton', 'G', 'Branca', 0.00, NULL, 0.00, 0, 1),
(267, 'BLC-BR-GG', 'Baby Look Feminina  Cotton', 'GG', 'Branca', 0.00, NULL, 0.00, 0, 1),
(268, 'BLC-BR-G1', 'Baby Look Feminina  Cotton', 'G1', 'Branca', 0.00, NULL, 0.00, 0, 1),
(269, 'BLC-BR-G2', 'Baby Look Feminina  Cotton', 'G2', 'Branca', 0.00, NULL, 0.00, 0, 1),
(270, 'BLC-BR-G3', 'Baby Look Feminina  Cotton', 'G3', 'Branca', 0.00, NULL, 0.00, 0, 1),
(271, 'BLA-PR-PP', 'Baby Look Feminina 100% ', 'PP', 'Preta', 0.00, NULL, 0.00, 0, 1),
(272, 'BLA-PR-P', 'Baby Look Feminina 100% ', 'P', 'Preta', 0.00, NULL, 0.00, 0, 1),
(273, 'BLA-PR-M', 'Baby Look Feminina 100% ', 'M', 'Preta', 0.00, NULL, 0.00, 0, 1),
(274, 'BLA-PR-G', 'Baby Look Feminina 100% ', 'G', 'Preta', 0.00, NULL, 0.00, 0, 1),
(275, 'BLA-PR-GG', 'Baby Look Feminina 100% ', 'GG', 'Preta', 0.00, NULL, 0.00, 0, 1),
(276, 'BLA-PR-G1', 'Baby Look Feminina 100% ', 'G1', 'Preta', 0.00, NULL, 0.00, 0, 1),
(277, 'BLA-PR-G2', 'Baby Look Feminina 100% ', 'G2', 'Preta', 0.00, NULL, 0.00, 0, 1),
(278, 'BLA-PR-G3', 'Baby Look Feminina 100% ', 'G3', 'Preta', 0.00, NULL, 0.00, 0, 1),
(279, 'BLA-BR-PP', 'Baby Look Feminina 100% ', 'PP', 'Branca', 0.00, NULL, 0.00, 0, 1),
(280, 'BLA-BR-P', 'Baby Look Feminina 100% ', 'P', 'Branca', 0.00, NULL, 0.00, 0, 1),
(281, 'BLA-BR-M', 'Baby Look Feminina 100% ', 'M', 'Branca', 0.00, NULL, 0.00, 0, 1),
(282, 'BLA-BR-G', 'Baby Look Feminina 100% ', 'G', 'Branca', 0.00, NULL, 0.00, 0, 1),
(283, 'BLA-BR-GG', 'Baby Look Feminina 100% ', 'GG', 'Branca', 0.00, NULL, 0.00, 0, 1),
(284, 'BLA-BR-G1', 'Baby Look Feminina 100% ', 'G1', 'Branca', 0.00, NULL, 0.00, 0, 1),
(285, 'BLA-BR-G2', 'Baby Look Feminina 100% ', 'G2', 'Branca', 0.00, NULL, 0.00, 0, 1),
(286, 'BLA-BR-G3', 'Baby Look Feminina 100% ', 'G3', 'Branca', 0.00, NULL, 0.00, 0, 1),
(287, 'CRC-PR-PP', 'Camiseta Regular Cotton', 'PP', 'Preta', 0.00, NULL, 0.00, 0, 1),
(288, 'CRC-PR-P', 'Camiseta Regular Cotton', 'P', 'Preta', 0.00, NULL, 0.00, 0, 1),
(289, 'CRC-PR-M', 'Camiseta Regular Cotton', 'M', 'Preta', 0.00, NULL, 0.00, 0, 1),
(290, 'CRC-PR-G', 'Camiseta Regular Cotton', 'G', 'Preta', 0.00, NULL, 0.00, 0, 1),
(291, 'CRC-PR-GG', 'Camiseta Regular Cotton', 'GG', 'Preta', 0.00, NULL, 0.00, 0, 1),
(292, 'CRC-PR-G1', 'Camiseta Regular Cotton', 'G1', 'Preta', 0.00, NULL, 0.00, 0, 1),
(293, 'CRC-PR-G2', 'Camiseta Regular Cotton', 'G2', 'Preta', 0.00, NULL, 0.00, 0, 1),
(294, 'CRC-PR-G3', 'Camiseta Regular Cotton', 'G3', 'Preta', 0.00, NULL, 0.00, 0, 1),
(295, 'CRC-BR-PP', 'Camiseta Regular Cotton', 'PP', 'Branca', 0.00, NULL, 0.00, 0, 1),
(296, 'CRC-BR-P', 'Camiseta Regular Cotton', 'P', 'Branca', 0.00, NULL, 0.00, 0, 1),
(297, 'CRC-BR-M', 'Camiseta Regular Cotton', 'M', 'Branca', 0.00, NULL, 0.00, 0, 1),
(298, 'CRC-BR-G', 'Camiseta Regular Cotton', 'G', 'Branca', 0.00, NULL, 0.00, 0, 1),
(299, 'CRC-BR-GG', 'Camiseta Regular Cotton', 'GG', 'Branca', 0.00, NULL, 0.00, 0, 1),
(300, 'CRC-BR-G1', 'Camiseta Regular Cotton', 'G1', 'Branca', 0.00, NULL, 0.00, 0, 1),
(301, 'CRC-BR-G2', 'Camiseta Regular Cotton', 'G2', 'Branca', 0.00, NULL, 0.00, 0, 1),
(302, 'CRC-BR-G3', 'Camiseta Regular Cotton', 'G3', 'Branca', 0.00, NULL, 0.00, 0, 1),
(303, 'CRC-OW-PP', 'Camiseta Regular Cotton', 'PP', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(304, 'CRC-OW-P', 'Camiseta Regular Cotton', 'P', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(305, 'CRC-OW-M', 'Camiseta Regular Cotton', 'M', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(306, 'CRC-OW-G', 'Camiseta Regular Cotton', 'G', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(307, 'CRC-OW-GG', 'Camiseta Regular Cotton', 'GG', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(308, 'CRC-OW-G1', 'Camiseta Regular Cotton', 'G1', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(309, 'CRC-OW-G2', 'Camiseta Regular Cotton', 'G2', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(310, 'CRC-OW-G3', 'Camiseta Regular Cotton', 'G3', 'Off-White', 0.00, NULL, 0.00, 0, 1),
(319, 'CRC-MA-PP', 'Camiseta Regular Cotton', 'PP', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(320, 'CRC-MA-P', 'Camiseta Regular Cotton', 'P', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(321, 'CRC-MA-M', 'Camiseta Regular Cotton', 'M', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(322, 'CRC-MA-G', 'Camiseta Regular Cotton', 'G', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(323, 'CRC-MA-GG', 'Camiseta Regular Cotton', 'GG', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(324, 'CRC-MA-G1', 'Camiseta Regular Cotton', 'G1', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(325, 'CRC-MA-G2', 'Camiseta Regular Cotton', 'G2', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(326, 'CRC-MA-G3', 'Camiseta Regular Cotton', 'G3', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(328, 'CRC-VD-PP', 'Camiseta Regular Cotton ', 'PP', 'Verde', 0.00, NULL, 0.00, 0, 1),
(329, 'CRC-VD-P', 'Camiseta Regular Cotton ', 'P', 'Verde', 0.00, NULL, 0.00, 0, 1),
(330, 'CRC-VD-M', 'Camiseta Regular Cotton ', 'M', 'Verde', 0.00, NULL, 0.00, 0, 1),
(331, 'CRC-VD-G', 'Camiseta Regular Cotton ', 'G', 'Verde', 0.00, NULL, 0.00, 0, 1),
(332, 'CRC-VD-GG', 'Camiseta Regular Cotton ', 'GG', 'Verde', 0.00, NULL, 0.00, 0, 1),
(333, 'CRC-VD-G1', 'Camiseta Regular Cotton ', 'G1', 'Verde', 0.00, NULL, 0.00, 0, 1),
(334, 'CRC-VD-G2', 'Camiseta Regular Cotton ', 'G2', 'Verde', 0.00, NULL, 0.00, 0, 1),
(335, 'CRC-VD-G3', 'Camiseta Regular Cotton ', 'G3', 'Verde', 0.00, NULL, 0.00, 0, 1),
(336, 'CRC-VM-PP', 'Camiseta Regular Cotton ', 'PP', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(337, 'CRC-VM-P', 'Camiseta Regular Cotton ', 'P', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(338, 'CRC-VM-M', 'Camiseta Regular Cotton ', 'M', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(339, 'CRC-VM-G', 'Camiseta Regular Cotton ', 'G', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(340, 'CRC-VM-GG', 'Camiseta Regular Cotton ', 'GG', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(341, 'CRC-VM-G1', 'Camiseta Regular Cotton ', 'G1', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(342, 'CRC-VM-G2', 'Camiseta Regular Cotton ', 'G2', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(343, 'CRC-VM-G3', 'Camiseta Regular Cotton ', 'G3', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(344, 'CIC-PR-2', 'Camiseta Infantil Cotton', '2', 'Preta', 0.00, NULL, 0.00, 0, 1),
(345, 'CIC-PR-4', 'Camiseta Infantil Cotton', '4', 'Preta', 0.00, NULL, 0.00, 0, 1),
(346, 'CIC-PR-6', 'Camiseta Infantil Cotton', '6', 'Preta', 0.00, NULL, 0.00, 0, 1),
(347, 'CIC-PR-8', 'Camiseta Infantil Cotton', '8', 'Preta', 0.00, NULL, 0.00, 0, 1),
(348, 'CIC-PR-10', 'Camiseta Infantil Cotton', '10', 'Preta', 0.00, NULL, 0.00, 0, 1),
(349, 'CIC-PR-12', 'Camiseta Infantil Cotton', '12', 'Preta', 0.00, NULL, 0.00, 0, 1),
(350, 'CIC-PR-14', 'Camiseta Infantil Cotton', '14', 'Preta', 0.00, NULL, 0.00, 0, 1),
(351, 'CIC-PR-16', 'Camiseta Infantil Cotton', '16', 'Preta', 0.00, NULL, 0.00, 0, 1),
(352, 'CIC-BR-2', 'Camiseta Infantil Cotton', '2', 'Branca', 0.00, NULL, 0.00, 0, 1),
(353, 'CIC-BR-4', 'Camiseta Infantil Cotton', '4', 'Branca', 0.00, NULL, 0.00, 0, 1),
(354, 'CIC-BR-6', 'Camiseta Infantil Cotton', '6', 'Branca', 0.00, NULL, 0.00, 0, 1),
(355, 'CIC-BR-8', 'Camiseta Infantil Cotton', '8', 'Branca', 0.00, NULL, 0.00, 0, 1),
(356, 'CIC-BR-10', 'Camiseta Infantil Cotton', '10', 'Branca', 0.00, NULL, 0.00, 0, 1),
(357, 'CIC-BR-12', 'Camiseta Infantil Cotton', '12', 'Branca', 0.00, NULL, 0.00, 0, 1),
(358, 'CIC-BR-14', 'Camiseta Infantil Cotton', '14', 'Branca', 0.00, NULL, 0.00, 0, 1),
(359, 'CIC-BR-16', 'Camiseta Infantil Cotton', '16', 'Branca', 0.00, NULL, 0.00, 0, 1),
(360, 'CIC-MA-2', 'Camiseta Infantil Cotton', '2', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(361, 'CIC-MA-4', 'Camiseta Infantil Cotton', '4', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(362, 'CIC-MA-6', 'Camiseta Infantil Cotton', '6', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(363, 'CIC-MA-8', 'Camiseta Infantil Cotton', '8', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(364, 'CIC-MA-10', 'Camiseta Infantil Cotton', '10', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(365, 'CIC-MA-12', 'Camiseta Infantil Cotton', '12', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(366, 'CIC-MA-14', 'Camiseta Infantil Cotton', '14', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(367, 'CIC-MA-16', 'Camiseta Infantil Cotton', '16', 'Marrom', 0.00, NULL, 0.00, 0, 1),
(368, 'CIC-VM-2', 'Camiseta Infantil Cotton', '2', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(369, 'CIC-VM-4', 'Camiseta Infantil Cotton', '4', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(370, 'CIC-VM-6', 'Camiseta Infantil Cotton', '6', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(371, 'CIC-VM-8', 'Camiseta Infantil Cotton', '8', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(372, 'CIC-VM-10', 'Camiseta Infantil Cotton', '10', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(373, 'CIC-VM-12', 'Camiseta Infantil Cotton', '12', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(374, 'CIC-VM-14', 'Camiseta Infantil Cotton', '14', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(375, 'CIC-VM-16', 'Camiseta Infantil Cotton', '16', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(376, 'BLC-VM-PP', 'Baby Look Feminina', 'PP', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(377, 'BLC-VM-P', 'Baby Look Feminina', 'P', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(378, 'BLC-VM-M', 'Baby Look Feminina', 'M', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(379, 'BLC-VM-G', 'Baby Look Feminina', 'G', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(380, 'BLC-VM-GG', 'Baby Look Feminina', 'GG', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(381, 'BLC-VM-G1', 'Baby Look Feminina', 'G1', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(382, 'BLC-VM-G2', 'Baby Look Feminina', 'G2', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(383, 'BLC-VM-G3', 'Baby Look Feminina', 'G3', 'Vermelha', 0.00, NULL, 0.00, 0, 1),
(384, 'BLC-DL-PP', 'Baby Look Feminina', 'PP', 'Doce de Leite', 0.00, NULL, 0.00, 0, 1),
(385, 'BLC-DL-P', 'Baby Look Feminina', 'P', 'Doce de Leite', 0.00, NULL, 0.00, 0, 1),
(386, 'BLC-DL-M', 'Baby Look Feminina', 'M', 'Doce de Leite', 0.00, NULL, 0.00, 0, 1),
(387, 'BLC-DL-G', 'Baby Look Feminina', 'G', 'Doce de Leite', 0.00, NULL, 0.00, 0, 1),
(388, 'BLC-DL-GG', 'Baby Look Feminina', 'GG', 'Doce de Leite', 0.00, NULL, 0.00, 0, 1),
(389, 'BLC-DL-G1', 'Baby Look Feminina', 'G1', 'Doce de Leite', 0.00, NULL, 0.00, 0, 1),
(390, 'BLC-DL-G2', 'Baby Look Feminina', 'G2', 'Doce de Leite', 0.00, NULL, 0.00, 0, 1),
(391, 'BLC-DL-G3', 'Baby Look Feminina', 'G3', 'Doce de Leite', 0.00, NULL, 0.00, 0, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos`
--

CREATE TABLE `servicos` (
  `id` int(11) NOT NULL,
  `cliente` varchar(100) NOT NULL,
  `aparelho` varchar(100) DEFAULT NULL,
  `defeito` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pendente',
  `valor` decimal(10,2) DEFAULT NULL,
  `data_entrada` datetime DEFAULT current_timestamp(),
  `tecnico_responsavel` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `servicos_os`
--

CREATE TABLE `servicos_os` (
  `id` int(11) NOT NULL,
  `cliente` varchar(100) NOT NULL,
  `descricao` text NOT NULL,
  `valor` decimal(10,2) DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'pendente',
  `data_abertura` datetime DEFAULT current_timestamp(),
  `data_conclusao` datetime DEFAULT NULL,
  `empresa` varchar(100) DEFAULT 'Ripfire',
  `prestador` varchar(100) DEFAULT NULL,
  `itens_json` text DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sincronizacoes_offline`
--

CREATE TABLE `sincronizacoes_offline` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_offline` date NOT NULL COMMENT 'Data do primeiro ponto offline',
  `data_online` date NOT NULL COMMENT 'Data quando voltou online',
  `timestamp_volta` datetime NOT NULL COMMENT 'Timestamp exato de quando voltou online',
  `pontos_synced` int(11) DEFAULT 0 COMMENT 'Quantidade de pontos sincronizados',
  `conflitos` int(11) DEFAULT 0 COMMENT 'Quantidade de conflitos resolvidos',
  `sincronizado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `departamento` varchar(100) DEFAULT 'Geral',
  `cargo` varchar(100) DEFAULT 'Operacional',
  `senha` varchar(255) NOT NULL,
  `nivel` varchar(20) DEFAULT 'comum',
  `carga_horaria_diaria` decimal(4,2) DEFAULT 8.00,
  `data_admissao` date DEFAULT curdate(),
  `tipo_contrato` varchar(50) DEFAULT 'CLT'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `departamento`, `cargo`, `senha`, `nivel`, `carga_horaria_diaria`, `data_admissao`, `tipo_contrato`) VALUES
(5, 'Julia Gomes De Souza', 'ripfirejulia@gmail.com', 'Geral', 'Operacional', '$2y$10$PM2vt4UJA9P4H8dnSHjsQ.IisfC.2401iUFgEZzDAwsLqG1d3tD72', 'admin', 8.00, '2026-03-14', 'CLT'),
(6, 'Vinicius Baggio', 'Vbaggio08@gmail.com', 'Geral', 'Operacional', '$2y$10$yrMP27GEVz6sH1JiLrSvseKLGBbFXTgtHTm6Qkrcbe6VEuBxav66e', 'admin', 8.00, '2026-03-14', 'CLT'),
(7, 'Jeison De Souza', 'jeison.tst22@gmail.com', 'Geral', 'Operacional', '$2y$10$8FApHk1k7ERks5Zky8ixe.KCB16J1Ah8cW0dutpwv0RUjDHxv4OEu', 'admin', 8.00, '2026-03-14', 'CLT'),
(8, 'Caio Tribeck', 'rptribeck@gmail.com', 'Geral', 'Operacional', '$2y$10$ii17DpJpXLdC/QSNlreYa.eN80PK9n22yResljhuvdtOFX9nhOjPm', 'funcionario', 8.00, '2026-03-14', 'CLT'),
(10, 'mateus crescencio ', 'mtscrescencio@gmail.com', 'Geral', 'Operacional', '$2y$10$T1i8.5jklKfCZY3hBozFqe5jxkO8jZO0s4DGZLjjDwLr3YR4HU0Qu', 'funcionario', 8.00, '2026-03-14', 'CLT');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_horas_extras_resumo`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_horas_extras_resumo` (
`usuario_id` int(11)
,`nome` varchar(100)
,`ano` int(4)
,`mes` int(2)
,`quantidade` bigint(21)
,`total_horas` decimal(27,2)
,`horas_aprovadas` decimal(27,2)
,`horas_pagas` decimal(27,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_saldo_horas_mensais`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_saldo_horas_mensais` (
`usuario_id` int(11)
,`nome` varchar(100)
,`departamento` varchar(100)
,`ano` int(4)
,`mes` int(2)
,`dias_trabalhados` bigint(21)
,`total_horas` decimal(44,2)
,`horas_esperadas` decimal(7,2)
,`saldo_horas` decimal(45,2)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_horas_extras_resumo`
--
DROP TABLE IF EXISTS `vw_horas_extras_resumo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_horas_extras_resumo`  AS SELECT `he`.`usuario_id` AS `usuario_id`, `u`.`nome` AS `nome`, year(`he`.`data_referencia`) AS `ano`, month(`he`.`data_referencia`) AS `mes`, count(0) AS `quantidade`, round(sum(`he`.`horas_extras`),2) AS `total_horas`, round(sum(case when `he`.`status` = 'aprovado' then `he`.`horas_extras` else 0 end),2) AS `horas_aprovadas`, round(sum(case when `he`.`status` = 'pago' then `he`.`horas_extras` else 0 end),2) AS `horas_pagas` FROM (`horas_extras` `he` join `usuarios` `u` on(`he`.`usuario_id` = `u`.`id`)) GROUP BY `he`.`usuario_id`, year(`he`.`data_referencia`), month(`he`.`data_referencia`) ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_saldo_horas_mensais`
--
DROP TABLE IF EXISTS `vw_saldo_horas_mensais`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_saldo_horas_mensais`  AS SELECT `u`.`id` AS `usuario_id`, `u`.`nome` AS `nome`, `u`.`departamento` AS `departamento`, year(`ap`.`data`) AS `ano`, month(`ap`.`data`) AS `mes`, count(distinct `ap`.`data`) AS `dias_trabalhados`, round(coalesce(sum(case when `ap`.`hora_saida_1` is not null and `ap`.`hora_entrada_1` is not null then (time_to_sec(`ap`.`hora_saida_1`) - time_to_sec(`ap`.`hora_entrada_1`)) / 3600.0 else 0 end + case when `ap`.`hora_saida_2` is not null and `ap`.`hora_entrada_2` is not null then (time_to_sec(`ap`.`hora_saida_2`) - time_to_sec(`ap`.`hora_entrada_2`)) / 3600.0 else 0 end),0),2) AS `total_horas`, round(cast(`u`.`carga_horaria_diaria` as decimal(5,2)) * 20,2) AS `horas_esperadas`, round(coalesce(sum(case when `ap`.`hora_saida_1` is not null and `ap`.`hora_entrada_1` is not null then (time_to_sec(`ap`.`hora_saida_1`) - time_to_sec(`ap`.`hora_entrada_1`)) / 3600.0 else 0 end + case when `ap`.`hora_saida_2` is not null and `ap`.`hora_entrada_2` is not null then (time_to_sec(`ap`.`hora_saida_2`) - time_to_sec(`ap`.`hora_entrada_2`)) / 3600.0 else 0 end),0) - cast(`u`.`carga_horaria_diaria` as decimal(5,2)) * 20,2) AS `saldo_horas` FROM (`usuarios` `u` left join `apontamentos_ponto` `ap` on(`u`.`id` = `ap`.`usuario_id` and year(`ap`.`data`) = year(current_timestamp()) and month(`ap`.`data`) = month(current_timestamp()))) GROUP BY `u`.`id`, year(`ap`.`data`), month(`ap`.`data`) ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `apontamentos_ponto`
--
ALTER TABLE `apontamentos_ponto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_data` (`usuario_id`,`data`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_data` (`data`),
  ADD KEY `idx_usuario_mes` (`usuario_id`,`data`);

--
-- Índices de tabela `atestados`
--
ALTER TABLE `atestados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aprovador_id` (`aprovador_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_inicio` (`data_inicio`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `nome_2` (`nome`),
  ADD UNIQUE KEY `cpf_cnpj` (`cpf_cnpj`),
  ADD UNIQUE KEY `cpf_cnpj_2` (`cpf_cnpj`);

--
-- Índices de tabela `compensacao_horas`
--
ALTER TABLE `compensacao_horas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_data` (`usuario_id`,`data_compensacao`),
  ADD KEY `fk_ch_horas_extras` (`horas_extras_id`),
  ADD KEY `fk_ch_dsr` (`dsr_id`);

--
-- Índices de tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `configuracao_ponto`
--
ALTER TABLE `configuracao_ponto`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `configuracao_pontos_avancado`
--
ALTER TABLE `configuracao_pontos_avancado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_empresa` (`empresa_id`);

--
-- Índices de tabela `dispositivos_autorizados`
--
ALTER TABLE `dispositivos_autorizados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_device` (`usuario_id`,`device_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `dsr_descansos`
--
ALTER TABLE `dsr_descansos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_semana` (`usuario_id`,`semana_referencia`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_dsr` (`data_dsr`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `nome_2` (`nome`),
  ADD UNIQUE KEY `cnpj` (`cnpj`),
  ADD UNIQUE KEY `cnpj_2` (`cnpj`);

--
-- Índices de tabela `estoque_movimentacao`
--
ALTER TABLE `estoque_movimentacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `estoque_movimentacoes`
--
ALTER TABLE `estoque_movimentacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `estoque_saldo`
--
ALTER TABLE `estoque_saldo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `feriados`
--
ALTER TABLE `feriados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `data` (`data`),
  ADD KEY `idx_data` (`data`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `fk_feriado_empresa` (`empresa_id`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD UNIQUE KEY `nome_2` (`nome`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `gabaritos`
--
ALTER TABLE `gabaritos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `geolocation_empresa`
--
ALTER TABLE `geolocation_empresa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empresa_id` (`empresa_id`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `historico_alteracoes_ponto`
--
ALTER TABLE `historico_alteracoes_ponto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_apontamento_id` (`apontamento_id`),
  ADD KEY `idx_usuario_alterador` (`usuario_alterador_id`),
  ADD KEY `idx_criado_em` (`criado_em`);

--
-- Índices de tabela `historico_movimentacao`
--
ALTER TABLE `historico_movimentacao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `horas_extras`
--
ALTER TABLE `horas_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_data` (`usuario_id`,`data_referencia`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_he_apontamento` (`apontamento_id`),
  ADD KEY `fk_he_aprovador` (`aprovado_por`),
  ADD KEY `idx_data_referencia` (`data_referencia`);

--
-- Índices de tabela `notificacoes_ponto`
--
ALTER TABLE `notificacoes_ponto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_lida` (`usuario_id`,`lida`);

--
-- Índices de tabela `pedidos_dtf`
--
ALTER TABLE `pedidos_dtf`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pedidos_producao`
--
ALTER TABLE `pedidos_producao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Índices de tabela `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `servicos_os`
--
ALTER TABLE `servicos_os`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `sincronizacoes_offline`
--
ALTER TABLE `sincronizacoes_offline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_sincronizado_em` (`sincronizado_em`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `apontamentos_ponto`
--
ALTER TABLE `apontamentos_ponto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `atestados`
--
ALTER TABLE `atestados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `compensacao_horas`
--
ALTER TABLE `compensacao_horas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `configuracao_pontos_avancado`
--
ALTER TABLE `configuracao_pontos_avancado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `dispositivos_autorizados`
--
ALTER TABLE `dispositivos_autorizados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `dsr_descansos`
--
ALTER TABLE `dsr_descansos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_movimentacao`
--
ALTER TABLE `estoque_movimentacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_movimentacoes`
--
ALTER TABLE `estoque_movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_saldo`
--
ALTER TABLE `estoque_saldo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `feriados`
--
ALTER TABLE `feriados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `gabaritos`
--
ALTER TABLE `gabaritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `geolocation_empresa`
--
ALTER TABLE `geolocation_empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_alteracoes_ponto`
--
ALTER TABLE `historico_alteracoes_ponto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_movimentacao`
--
ALTER TABLE `historico_movimentacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `horas_extras`
--
ALTER TABLE `horas_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notificacoes_ponto`
--
ALTER TABLE `notificacoes_ponto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedidos_dtf`
--
ALTER TABLE `pedidos_dtf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pedidos_producao`
--
ALTER TABLE `pedidos_producao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=392;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos_os`
--
ALTER TABLE `servicos_os`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `sincronizacoes_offline`
--
ALTER TABLE `sincronizacoes_offline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `apontamentos_ponto`
--
ALTER TABLE `apontamentos_ponto`
  ADD CONSTRAINT `apontamentos_ponto_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `atestados`
--
ALTER TABLE `atestados`
  ADD CONSTRAINT `atestados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `atestados_ibfk_2` FOREIGN KEY (`aprovador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `compensacao_horas`
--
ALTER TABLE `compensacao_horas`
  ADD CONSTRAINT `fk_ch_dsr` FOREIGN KEY (`dsr_id`) REFERENCES `dsr_descansos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ch_horas_extras` FOREIGN KEY (`horas_extras_id`) REFERENCES `horas_extras` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ch_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `configuracao_pontos_avancado`
--
ALTER TABLE `configuracao_pontos_avancado`
  ADD CONSTRAINT `fk_config_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `dispositivos_autorizados`
--
ALTER TABLE `dispositivos_autorizados`
  ADD CONSTRAINT `dispositivos_autorizados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `dsr_descansos`
--
ALTER TABLE `dsr_descansos`
  ADD CONSTRAINT `fk_dsr_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `estoque_movimentacao`
--
ALTER TABLE `estoque_movimentacao`
  ADD CONSTRAINT `estoque_movimentacao_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `estoque_saldo`
--
ALTER TABLE `estoque_saldo`
  ADD CONSTRAINT `estoque_saldo_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `feriados`
--
ALTER TABLE `feriados`
  ADD CONSTRAINT `fk_feriado_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `historico_alteracoes_ponto`
--
ALTER TABLE `historico_alteracoes_ponto`
  ADD CONSTRAINT `historico_alteracoes_ponto_ibfk_1` FOREIGN KEY (`apontamento_id`) REFERENCES `apontamentos_ponto` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_alteracoes_ponto_ibfk_2` FOREIGN KEY (`usuario_alterador_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `horas_extras`
--
ALTER TABLE `horas_extras`
  ADD CONSTRAINT `fk_he_apontamento` FOREIGN KEY (`apontamento_id`) REFERENCES `apontamentos_ponto` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_he_aprovador` FOREIGN KEY (`aprovado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_he_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `notificacoes_ponto`
--
ALTER TABLE `notificacoes_ponto`
  ADD CONSTRAINT `fk_not_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `sincronizacoes_offline`
--
ALTER TABLE `sincronizacoes_offline`
  ADD CONSTRAINT `sincronizacoes_offline_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

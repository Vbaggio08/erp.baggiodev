-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/02/2026 às 21:33
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
-- Banco de dados: `ripfire_db`
--

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
-- Estrutura para tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `fornecedor` varchar(100) NOT NULL,
  `itens_json` text NOT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `data_pedido` datetime DEFAULT current_timestamp(),
  `data_chegada` datetime DEFAULT NULL,
  `empresa` varchar(100) DEFAULT 'Ripfire'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Despejando dados para a tabela `estoque_movimentacoes`
--

INSERT INTO `estoque_movimentacoes` (`id`, `tipo`, `produto`, `tamanho`, `cor`, `quantidade`, `observacao`, `data_movimento`, `usuario`) VALUES
(1, 'saida', 'Vinicius Baggio', 'GG', 'branca', -1, '', '2026-02-12 21:46:18', NULL);

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

--
-- Despejando dados para a tabela `estoque_saldo`
--

INSERT INTO `estoque_saldo` (`id`, `id_produto`, `quantidade`, `ultima_atualizacao`) VALUES
(1, 5, 0, '2026-02-13 00:06:42'),
(2, 4, 0, '2026-02-13 00:06:42'),
(3, 6, 0, '2026-02-13 00:06:42');

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
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `gabaritos`
--

INSERT INTO `gabaritos` (`id`, `cliente`, `numero_pedido`, `plataforma`, `contato`, `modelo`, `tamanho`, `quantidade`, `valor_unit`, `valor_total`, `forma_pagamento`, `data_pagamento`, `data_pedido`, `data_entrega`, `imagem_mockup`, `observacoes`, `data_criacao`) VALUES
(1, 'Vitor Alves', NULL, NULL, '+55 13 98139-1242', NULL, NULL, 1, 74.99, 74.99, 'Cartão', '2026-02-10', '2026-02-10', '2026-02-11', '698c6d3eb4d9d.png', '', '2026-02-11 08:51:26'),
(2, 'Vitor Alves', '', 'Loja Física', '+55 13 98139-1242', 'wdhe', 'gg', 1, 74.99, 74.99, 'Pix', '2026-02-10', '2026-02-11', '2026-02-11', '698e5299359c5.jpeg', '', '2026-02-11 09:04:50'),
(3, 'Vitor Alves', '6540', 'Loja Física', '+55 13 98139-1242', 'over', 'gg', 1, 74.99, 74.99, 'Pix', '2026-02-10', '2026-02-11', '2026-02-11', '698c706f05fd2.png', '', '2026-02-11 09:05:03'),
(4, 'Vitor Alves', NULL, NULL, '+55 13 98139-1242', 'Oversized', 'GG', 1, 74.99, 74.99, 'Cartão', '2026-02-10', '2026-02-10', '2026-02-11', '698c7c8450413.png', 'Logo com 10 cm', '2026-02-11 09:56:36'),
(5, 'Vitor Alves', '6540', 'Site', '211', 'Oversized', 'G', 1, 74.99, 74.99, 'Pix', '0000-00-00', '2026-02-11', '0000-00-00', '698c87f34e39a.png', '', '2026-02-11 10:45:23');

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
(4, 'CAM-OVR-M', 'Camiseta Oversized', 'M', 'Preta', 89.90, NULL, 40.00, 0, 1),
(5, 'CAM-OVR-G', 'Camiseta Oversized', 'G', 'Preta', 89.90, NULL, 40.00, 0, 1),
(6, 'CAM-STR-GG', 'Camiseta Street', 'GG', 'Branca', 79.90, NULL, 35.00, 0, 1),
(7, 'T-011', 'Vinicius Baggio', 'GG', 'branca', 90.00, NULL, 55.00, 0, 1);

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
  `empresa` varchar(100) DEFAULT 'Ripfire'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` varchar(20) DEFAULT 'comum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel`) VALUES
(5, 'Julia Gomes De Souza', 'ripfirejulia@gmail.com', '$2y$10$5icu1Ux.Azx3s94sCxytw.n5H2gzzqqU3hwx3yTZdHqFHGC1HfXaW', 'admin'),
(6, 'Vinicius Baggio', 'Vbaggio08@gmail.com', '$2y$10$yrMP27GEVz6sH1JiLrSvseKLGBbFXTgtHTm6Qkrcbe6VEuBxav66e', 'admin'),
(7, 'Jeison De Souza', 'jeison.tst22@gmail.com', '$2y$10$8FApHk1k7ERks5Zky8ixe.KCB16J1Ah8cW0dutpwv0RUjDHxv4OEu', 'admin'),
(8, 'Caio Tribeck', 'rptribeck@gmail.com', '$2y$10$ii17DpJpXLdC/QSNlreYa.eN80PK9n22yResljhuvdtOFX9nhOjPm', 'funcionario');

--
-- Índices para tabelas despejadas
--

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
-- Índices de tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`);

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
-- Índices de tabela `historico_movimentacao`
--
ALTER TABLE `historico_movimentacao`
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
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `estoque_movimentacoes`
--
ALTER TABLE `estoque_movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `estoque_saldo`
--
ALTER TABLE `estoque_saldo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `gabaritos`
--
ALTER TABLE `gabaritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `historico_movimentacao`
--
ALTER TABLE `historico_movimentacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedidos_producao`
--
ALTER TABLE `pedidos_producao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `servicos_os`
--
ALTER TABLE `servicos_os`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `estoque_saldo`
--
ALTER TABLE `estoque_saldo`
  ADD CONSTRAINT `estoque_saldo_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

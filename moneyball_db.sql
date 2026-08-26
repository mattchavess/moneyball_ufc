-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/08/2026 às 00:54
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
CREATE database moneyball_db;
use moneyball_db;
--
-- Banco de dados: `moneyball_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `estatisticas`
--

CREATE TABLE `estatisticas` (
  `id` int(11) NOT NULL,
  `lutador_id` int(11) NOT NULL,
  `temporada` varchar(20) NOT NULL,
  `vitorias` int(11) NOT NULL DEFAULT 0,
  `derrotas` int(11) NOT NULL DEFAULT 0,
  `empates` int(11) NOT NULL DEFAULT 0,
  `kos` int(11) NOT NULL DEFAULT 0,
  `finalizacoes` int(11) NOT NULL DEFAULT 0,
  `media_quedas_luta` decimal(5,2) DEFAULT 0.00,
  `tempo_medio_luta` decimal(5,2) DEFAULT 0.00,
  `golpes_significativos_min` decimal(5,2) DEFAULT 0.00,
  `precisao_striking` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estatisticas`
--

INSERT INTO `estatisticas` (`id`, `lutador_id`, `temporada`, `vitorias`, `derrotas`, `empates`, `kos`, `finalizacoes`, `media_quedas_luta`, `tempo_medio_luta`, `golpes_significativos_min`, `precisao_striking`) VALUES
(2, 2, '2026', 5, 0, 0, 0, 5, 2.00, 2.00, 42.00, 42.00),
(3, 3, '2026', 4, 1, 0, 0, 4, 3.00, 4.00, 2.00, 67.00),
(4, 4, '2026', 1, 4, 0, 0, 1, 2.00, 1.00, 2.00, 10.00),
(5, 5, '2024', 6, 1, 0, 3, 2, 1.50, 12.30, 0.00, 0.00),
(6, 5, '2025', 4, 0, 0, 2, 1, 1.20, 10.50, 0.00, 0.00),
(7, 6, '2024', 3, 2, 0, 1, 1, 0.80, 15.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `lutadores`
--

CREATE TABLE `lutadores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `apelido` varchar(50) DEFAULT NULL,
  `academia` varchar(100) DEFAULT NULL,
  `categoria_peso` varchar(50) NOT NULL,
  `estilo_luta` varchar(50) DEFAULT NULL,
  `pais` varchar(60) DEFAULT NULL,
  `bandeira_emoji` varchar(10) DEFAULT NULL,
  `idade` int(11) DEFAULT NULL,
  `altura_cm` int(11) DEFAULT NULL,
  `alcance_cm` int(11) DEFAULT NULL,
  `cadastrado_por` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `lutadores`
--

INSERT INTO `lutadores` (`id`, `nome`, `apelido`, `academia`, `categoria_peso`, `estilo_luta`, `pais`, `bandeira_emoji`, `idade`, `altura_cm`, `alcance_cm`, `cadastrado_por`, `criado_em`) VALUES
(2, 'Teste 42', '42', '42 gym', 'Peso Pena', 'Striker', 'Brasil', '🇧🇷', 32, 142, 142, 1, '2026-08-23 13:10:04'),
(3, 'Teste 67', '67', '67 gym', 'Meio-Médio', 'Striker', 'Brasil', '🇧🇷', 67, 167, 167, 1, '2026-08-23 13:10:43'),
(4, 'Teste 69', '69', '69 gym', 'Médio', 'Striker', 'Brasil', '🇧🇷', 69, 169, 169, 1, '2026-08-23 13:11:27'),
(5, 'Carlos Import', '', '', 'Peso Leve', '', 'Brasil', '', 27, 175, 180, 1, '2026-08-23 13:51:56'),
(6, 'Marina Import', '', '', 'Peso Pena', '', 'Argentina', '', 24, 165, 170, 1, '2026-08-23 13:51:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','comum') NOT NULL DEFAULT 'comum',
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `criado_em`) VALUES
(1, 'Admin Master', 'admin@moneyballufc.com', '$2y$10$KCF63HHw3vM1x92HQ70QQeDPBcYnJWBtWjx.aSNfFSqD85dpzylWi', 'admin', '2026-08-23 12:51:07');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `estatisticas`
--
ALTER TABLE `estatisticas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lutador_temporada` (`lutador_id`,`temporada`),
  ADD KEY `idx_estatistica_temporada` (`temporada`);

--
-- Índices de tabela `lutadores`
--
ALTER TABLE `lutadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lutador_usuario` (`cadastrado_por`),
  ADD KEY `idx_lutador_nome` (`nome`),
  ADD KEY `idx_lutador_categoria` (`categoria_peso`),
  ADD KEY `idx_lutador_estilo` (`estilo_luta`);

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
-- AUTO_INCREMENT de tabela `estatisticas`
--
ALTER TABLE `estatisticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `lutadores`
--
ALTER TABLE `lutadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `estatisticas`
--
ALTER TABLE `estatisticas`
  ADD CONSTRAINT `fk_estatistica_lutador` FOREIGN KEY (`lutador_id`) REFERENCES `lutadores` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `lutadores`
--
ALTER TABLE `lutadores`
  ADD CONSTRAINT `fk_lutador_usuario` FOREIGN KEY (`cadastrado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

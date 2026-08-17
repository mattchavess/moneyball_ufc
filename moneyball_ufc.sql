-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/08/2026 às 03:07
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
-- Banco de dados: `moneyball_ufc`
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
  `nocautes` int(11) NOT NULL DEFAULT 0,
  `finalizacoes` int(11) NOT NULL DEFAULT 0,
  `quedas_media` decimal(5,2) DEFAULT 0.00,
  `tempo_medio_luta_min` decimal(5,2) DEFAULT 0.00,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `lutadores`
--

CREATE TABLE `lutadores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `categoria_peso` varchar(50) NOT NULL,
  `idade` int(11) NOT NULL,
  `altura_cm` int(11) DEFAULT NULL,
  `alcance_cm` int(11) DEFAULT NULL,
  `pais` varchar(60) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `tipo` enum('administrador','comum') NOT NULL DEFAULT 'comum',
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `tipo`, `criado_em`) VALUES
(2, 'Mateus Chaves', 'mateus.f.chaves6@aluno.senai.br', '$2y$10$R22GWFChHrkyGbvNc8gOGe/MZj0CefBtmldSUIZi7cqcTD0htn4he', 'administrador', '2026-08-05 21:48:42');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `estatisticas`
--
ALTER TABLE `estatisticas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_estatisticas_lutador` (`lutador_id`);

--
-- Índices de tabela `lutadores`
--
ALTER TABLE `lutadores`
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
-- AUTO_INCREMENT de tabela `estatisticas`
--
ALTER TABLE `estatisticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `lutadores`
--
ALTER TABLE `lutadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_estatisticas_lutador` FOREIGN KEY (`lutador_id`) REFERENCES `lutadores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

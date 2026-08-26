-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 26/08/2026 às 21:35
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
-- Banco de dados: `moneyball_db`
--

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
(7, 'Charles Oliveira', '', '', 'Peso Leve', '', 'Brasil', '', 36, 178, 188, 1, '2026-08-26 15:38:48'),
(8, 'Alex Pereira', '', '', 'Meio-Pesado', '', 'Brasil', '', 39, 193, 201, 1, '2026-08-26 15:38:48'),
(9, 'Anderson Silva', '', '', 'Médio', '', 'Brasil', '', 51, 188, 197, 1, '2026-08-26 15:38:48'),
(10, 'Islam Makhachev', '', '', 'Meio-Médio', '', 'Rússia', '', 34, 178, 178, 1, '2026-08-26 15:38:48'),
(11, 'Jon Jones', '', '', 'Peso Pesado', '', 'Estados Unidos', '', 38, 193, 215, 1, '2026-08-26 15:38:48'),
(12, 'Israel Adesanya', '', '', 'Médio', '', 'Nigéria', '', 36, 193, 203, 1, '2026-08-26 15:38:48'),
(13, 'Ilia Topuria', '', '', 'Peso Leve', '', 'Espanha', '', 29, 170, 175, 1, '2026-08-26 15:38:48'),
(14, 'Alexander Volkanovski', '', '', 'Peso Pena', '', 'Austrália', '', 37, 168, 181, 1, '2026-08-26 15:38:48'),
(15, 'Justin Gaethje', '', '', 'Peso Leve', '', 'Estados Unidos', '', 37, 180, 178, 1, '2026-08-26 15:38:48'),
(16, 'Sean O\'Malley', '', '', 'Peso Galo', '', 'Estados Unidos', '', 31, 180, 183, 1, '2026-08-26 15:38:48'),
(17, 'Dricus Du Plessis', '', '', 'Médio', '', 'África do Sul', '', 32, 185, 193, 1, '2026-08-26 15:38:48'),
(18, 'Merab Dvalishvili', '', '', 'Peso Galo', '', 'Geórgia', '', 35, 168, 173, 1, '2026-08-26 15:38:48'),
(19, 'Max Holloway', '', '', 'Peso Leve', '', 'Estados Unidos', '', 34, 180, 175, 1, '2026-08-26 15:38:48'),
(20, 'Dustin Poirier', '', '', 'Peso Leve', '', 'Estados Unidos', '', 37, 175, 183, 1, '2026-08-26 15:38:48'),
(21, 'Conor McGregor', '', '', 'Peso Leve', '', 'Irlanda', '', 38, 175, 188, 1, '2026-08-26 15:38:48'),
(22, 'Kamaru Usman', '', '', 'Meio-Médio', '', 'Nigéria', '', 39, 183, 193, 1, '2026-08-26 15:38:48'),
(23, 'Jose Aldo', '', '', 'Peso Pena', '', 'Brasil', '', 39, 170, 178, 1, '2026-08-26 15:38:48'),
(24, 'Khabib Nurmagomedov', '', '', 'Peso Leve', '', 'Rússia', '', 37, 178, 178, 1, '2026-08-26 15:38:48'),
(25, 'Valentina Shevchenko', '', '', 'Peso Mosca', '', 'Quirguistão', '', 38, 165, 168, 1, '2026-08-26 15:38:48'),
(26, 'Leon Edwards', '', '', 'Meio-Médio', '', 'Inglaterra', '', 34, 183, 188, 1, '2026-08-26 15:38:48');

--
-- Índices para tabelas despejadas
--

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
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `lutadores`
--
ALTER TABLE `lutadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `lutadores`
--
ALTER TABLE `lutadores`
  ADD CONSTRAINT `fk_lutador_usuario` FOREIGN KEY (`cadastrado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

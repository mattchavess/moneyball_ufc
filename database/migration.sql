CREATE DATABASE IF NOT EXISTS moneyball_ufc
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE moneyball_ufc;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo ENUM('administrador', 'comum') NOT NULL DEFAULT 'comum',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lutadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria_peso VARCHAR(50) NOT NULL,
    idade INT NOT NULL,
    altura_cm INT DEFAULT NULL,
    alcance_cm INT DEFAULT NULL,
    pais VARCHAR(60) DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS estatisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lutador_id INT NOT NULL,
    temporada VARCHAR(20) NOT NULL,
    vitorias INT NOT NULL DEFAULT 0,
    derrotas INT NOT NULL DEFAULT 0,
    empates INT NOT NULL DEFAULT 0,
    nocautes INT NOT NULL DEFAULT 0,
    finalizacoes INT NOT NULL DEFAULT 0,
    quedas_media DECIMAL(5,2) DEFAULT 0,
    tempo_medio_luta_min DECIMAL(5,2) DEFAULT 0,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estatisticas_lutador
        FOREIGN KEY (lutador_id) REFERENCES lutadores(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO usuarios (nome, email, senha_hash, tipo)
VALUES (
    'Administrador',
    'admin@moneyballufc.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'administrador'
);

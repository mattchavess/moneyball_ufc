CREATE DATABASE IF NOT EXISTS moneyball_ufc;
USE moneyball_ufc;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('admin','comum') NOT NULL DEFAULT 'comum',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lutadores (
    id_lutador INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_cadastro INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    apelido VARCHAR(100),
    categoria_peso ENUM('Peso Palha','Peso Mosca','Peso Galo','Peso Pena','Peso Leve','Peso Meio-Medio','Peso Medio','Meio-Pesado','Peso Pesado') NOT NULL,
    nacionalidade VARCHAR(60),
    data_nascimento DATE,
    altura_cm INT,
    vitorias INT DEFAULT 0,
    derrotas INT DEFAULT 0,
    empates INT DEFAULT 0,
    status ENUM('ativo','aposentado') DEFAULT 'ativo',
    FOREIGN KEY (id_usuario_cadastro) REFERENCES usuarios(id_usuario)
);

CREATE TABLE eventos (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    nome_evento VARCHAR(150) NOT NULL,
    data_evento DATE NOT NULL,
    local_evento VARCHAR(150)
);

CREATE TABLE estatisticas (
    id_estatistica INT AUTO_INCREMENT PRIMARY KEY,
    id_lutador INT NOT NULL,
    id_evento INT NOT NULL,
    temporada VARCHAR(20) NOT NULL,
    golpes_acertados INT DEFAULT 0,
    golpes_tentados INT DEFAULT 0,
    quedas_completadas INT DEFAULT 0,
    quedas_tentadas INT DEFAULT 0,
    tempo_controle_seg INT DEFAULT 0,
    resultado ENUM('vitoria','derrota','empate','sem resultado') NOT NULL,
    metodo ENUM('nocaute','finalizacao','decisao','desqualificacao') NOT NULL,
    round_finalizacao TINYINT,
    FOREIGN KEY (id_lutador) REFERENCES lutadores(id_lutador),
    FOREIGN KEY (id_evento) REFERENCES eventos(id_evento)
);
-- Usuário administrador inicial
INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario)
VALUES ('Mateus', 'mateus.f.chaves6@aluno.senai.br', '$2y$10$MFqrrnRf1r30HXr5fU8uSOkUvv11K5c6SpuFujK.7R3FRwxX7MPSe', 'admin');
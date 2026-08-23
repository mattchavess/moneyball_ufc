<?php
// ============================================
// config/conexao.php
// Conexão com o MySQL usando mysqli PROCEDURAL
// ============================================

$DB_HOST = "localhost";
$DB_USER = "root";       // troque se seu MySQL usar outro usuário
$DB_SENHA = "";          // troque se seu MySQL tiver senha
$DB_NOME = "moneyball_db";

$conexao = mysqli_connect($DB_HOST, $DB_USER, $DB_SENHA, $DB_NOME);

if (!$conexao) {
    die("Erro ao conectar no banco de dados: " . mysqli_connect_error());
}

// Garante que acentos (ç, ã, etc.) sejam salvos/lidos corretamente
mysqli_set_charset($conexao, "utf8mb4");

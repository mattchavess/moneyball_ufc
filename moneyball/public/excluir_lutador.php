<?php
// ============================================
// public/excluir_lutador.php
// Processa a exclusão de um lutador (RF11)
// Só aceita POST — não pode ser acessado direto pela URL.
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: lutadores.php");
    exit;
}

$lutadorId = (int) $_POST["id"];

if ($lutadorId > 0) {
    excluirLutador($conexao, $lutadorId);
}

header("Location: lutadores.php");
exit;

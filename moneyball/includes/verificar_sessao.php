<?php
// ============================================
// includes/verificar_sessao.php
// Inclua este arquivo no topo de toda página
// que exige login (dashboard, lutadores, etc.)
// ============================================
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Função auxiliar para proteger páginas só de admin (RF06)
function exigirAdmin() {
    if ($_SESSION["usuario_tipo"] !== "admin") {
        die("Acesso negado: esta ação é restrita a administradores.");
    }
}

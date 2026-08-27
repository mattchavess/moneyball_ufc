<?php
// ============================================
// public/excluir_usuario.php
// Processa exclusão de usuário. Só admin, só POST.
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php";
exigirAdmin();

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_usuario.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cadastrar_usuario.php");
    exit;
}

$usuarioId = (int) $_POST["id"];

// Trava de segurança: impede que o admin se auto-exclua sem querer
// (se isso acontecesse, ele ficaria sem sessão válida e sem acesso
// pra desfazer, então é melhor bloquear aqui mesmo)
if ($usuarioId === (int) $_SESSION["usuario_id"]) {
    die("Você não pode excluir seu próprio usuário enquanto estiver logado com ele.");
}

if ($usuarioId > 0) {
    excluirUsuario($conexao, $usuarioId);
}

header("Location: cadastrar_usuario.php");
exit;

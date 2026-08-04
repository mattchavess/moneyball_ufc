<?php
/**
 * Middleware de verificação de sessão.
 * Deve ser incluído no topo de toda página interna (dashboard, lutadores, etc.)
 * Bloqueia o acesso de quem não estiver autenticado.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: /auth/login.php');
    exit;
}

/**
 * Restringe o acesso apenas a administradores.
 * Uso: chamar exigir_admin() no topo de páginas exclusivas do Admin,
 * como cadastro de usuários e alteração de permissões.
 * Requisitos: RF06, RF07
 */
function exigir_admin(): void
{
    if ($_SESSION['tipo_usuario'] !== 'admin') {
        http_response_code(403);
        die('Acesso restrito a administradores.');
    }
}
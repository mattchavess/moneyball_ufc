<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function exigirLogin(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /auth/login.php');
        exit;
    }
}

function exigirAdministrador(): void
{
    exigirLogin();

    if (($_SESSION['usuario_tipo'] ?? '') !== 'administrador') {
        http_response_code(403);
        die('Acesso negado: esta ação é restrita a administradores.');
    }
}

function usuarioEhAdministrador(): bool
{
    return ($_SESSION['usuario_tipo'] ?? '') === 'administrador';
}

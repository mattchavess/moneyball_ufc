<?php

session_start();
require_once __DIR__ . '/../config/conexao.php';

$erro = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: ../public/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $pdo = conectarBanco();

        $stmt = $pdo->prepare('SELECT id, nome, senha_hash, tipo FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            header('Location: ../public/dashboard.php');
            exit;
        }

        $erro = 'E-mail ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Moneyball UFC</title>
</head>
<body>
    <h1>Login</h1>

    <?php if ($erro): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>E-mail:
            <input type="email" name="email" required>
        </label><br>
        <label>Senha:
            <input type="password" name="senha" required>
        </label><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>

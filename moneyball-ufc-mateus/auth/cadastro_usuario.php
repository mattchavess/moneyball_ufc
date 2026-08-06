<?php

session_start();
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/verifica_login.php';

exigirAdministrador();

$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo  = $_POST['tipo'] ?? 'comum';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!in_array($tipo, ['administrador', 'comum'], true)) {
        $erro = 'Tipo de usuário inválido.';
    } else {
        $pdo = conectarBanco();

        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            $erro = 'Já existe um usuário cadastrado com esse e-mail.';
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha_hash, tipo)
                 VALUES (:nome, :email, :senha_hash, :tipo)'
            );
            $stmt->execute([
                'nome'       => $nome,
                'email'      => $email,
                'senha_hash' => $senhaHash,
                'tipo'       => $tipo,
            ]);

            $sucesso = 'Usuário cadastrado com sucesso.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário - Moneyball UFC</title>
</head>
<body>
    <h1>Cadastrar novo usuário</h1>

    <?php if ($erro): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <p style="color:green;"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <form method="POST" action="cadastro_usuario.php">
        <label>Nome:
            <input type="text" name="nome" required>
        </label><br>
        <label>E-mail:
            <input type="email" name="email" required>
        </label><br>
        <label>Senha:
            <input type="password" name="senha" required>
        </label><br>
        <label>Tipo:
            <select name="tipo">
                <option value="comum">Usuário Comum</option>
                <option value="administrador">Administrador</option>
            </select>
        </label><br>
        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>

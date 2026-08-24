<?php
// ============================================
// public/cadastrar_usuario.php
// Tela "Usuários" > "+ Novo Usuário" (RF06)
// Só admin pode acessar essa página.
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
exigirAdmin(); // exige tipo = admin

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_usuario.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $tipo = $_POST["tipo"]; // "admin" ou "comum"

    if ($nome === "" || $email === "" || $senha === "") {
        $erro = "Preencha todos os campos.";
    } elseif (buscarUsuarioPorEmail($conexao, $email)) {
        $erro = "Já existe um usuário com esse e-mail.";
    } else {
        $sucesso_cadastro = cadastrarUsuario($conexao, $nome, $email, $senha, $tipo);

        if ($sucesso_cadastro) {
            $sucesso = "Usuário cadastrado com sucesso!";
        } else {
            $erro = "Erro ao cadastrar usuário. Tente novamente.";
        }
    }
}

// Lista os usuários já cadastrados, pra mostrar na mesma tela (como no Figma)
$usuarios = listarUsuarios($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Usuários - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . "/../includes/menu.php"; ?>
    <main class="conteudo">

    <h1>Usuários</h1>
    <p><?php echo count($usuarios); ?> usuários cadastrados</p>

    <?php if ($erro): ?>
        <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <p class="sucesso"><?php echo htmlspecialchars($sucesso); ?></p>
    <?php endif; ?>

    <h2>Novo Usuário</h2>
    <form method="POST" action="cadastrar_usuario.php">
        <label>Nome</label>
        <input type="text" name="nome" required>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Senha</label>
        <input type="password" name="senha" required>

        <label>Tipo</label>
        <select name="tipo">
            <option value="comum">Comum</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Cadastrar</button>
    </form>

    <h2>Usuários cadastrados</h2>
    <table>
        <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Tipo</th>
            <th>Criado em</th>
        </tr>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u["nome"]); ?></td>
                <td><?php echo htmlspecialchars($u["email"]); ?></td>
                <td><?php echo htmlspecialchars($u["tipo"]); ?></td>
                <td><?php echo htmlspecialchars($u["criado_em"]); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    </main>
</div>
</body>
</html>

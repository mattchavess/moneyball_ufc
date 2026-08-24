<?php
// ============================================
// public/editar_usuario.php
// Edição de usuário (RF06 - só admin)
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php";
exigirAdmin();

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_usuario.php";

$usuarioId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($usuarioId <= 0) {
    die("ID de usuário inválido.");
}

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $tipo = $_POST["tipo"];
    $novaSenha = trim($_POST["nova_senha"]); // opcional

    if ($nome === "" || $email === "") {
        $erro = "Preencha nome e e-mail.";
    } else {
        $ok = atualizarUsuario($conexao, $usuarioId, $nome, $email, $tipo);

        // Se o admin digitou uma nova senha, atualiza também.
        // Deixar em branco = mantém a senha atual (não mexe nela).
        if ($ok && $novaSenha !== "") {
            atualizarSenhaUsuario($conexao, $usuarioId, $novaSenha);
        }

        if ($ok) {
            $sucesso = "Usuário atualizado com sucesso!";
        } else {
            $erro = "Erro ao atualizar usuário.";
        }
    }
}

$usuario = buscarUsuarioPorId($conexao, $usuarioId);

if (!$usuario) {
    die("Usuário não encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . "/../includes/menu.php"; ?>
    <main class="conteudo">

    <a href="cadastrar_usuario.php">&larr; Voltar para Usuários</a>

    <h1>Editar Usuário</h1>

    <?php if ($erro): ?>
        <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <p class="sucesso"><?php echo htmlspecialchars($sucesso); ?></p>
    <?php endif; ?>

    <form method="POST" action="editar_usuario.php?id=<?php echo $usuarioId; ?>">
        <label>Nome</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario["nome"]); ?>" required>

        <label>E-mail</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($usuario["email"]); ?>" required>

        <label>Tipo</label>
        <select name="tipo">
            <option value="comum" <?php echo ($usuario["tipo"] === "comum") ? "selected" : ""; ?>>Comum</option>
            <option value="admin" <?php echo ($usuario["tipo"] === "admin") ? "selected" : ""; ?>>Admin</option>
        </select>

        <label>Nova senha (deixe em branco para não alterar)</label>
        <input type="password" name="nova_senha" placeholder="••••••••">

        <button type="submit">Salvar alterações</button>
    </form>

    <!-- Exclusão em form separado com POST, mesma lógica do lutador -->
    <form method="POST" action="excluir_usuario.php" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
        <input type="hidden" name="id" value="<?php echo $usuarioId; ?>">
        <button type="submit" class="botao-perigo">Excluir Usuário</button>
    </form>

    </main>
</div>
</body>
</html>

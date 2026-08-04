<?php
/**
 * Cadastro, listagem e alteração de permissão de usuários.
 * Requisitos:
 * RF05 - Cadastro de usuários
 * RF06 - Apenas Administrador pode cadastrar
 * RF07 - Alteração de permissões
 * RF08 - Listagem de usuários
 */
require_once __DIR__ . '/../includes/verifica_login.php';
require_once __DIR__ . '/../config/conexao.php';

exigir_admin(); // RF06: só admin acessa esta página

$mensagem = '';

// RF05: cadastro de novo usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cadastrar') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo  = $_POST['tipo_usuario'] ?? 'comum';

    if ($nome === '' || $email === '' || $senha === '') {
        $mensagem = 'Preencha todos os campos.';
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT); // RF03

        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario) VALUES (:nome, :email, :senha_hash, :tipo)'
        );
        $stmt->execute([
            'nome'       => $nome,
            'email'      => $email,
            'senha_hash' => $senha_hash,
            'tipo'       => $tipo,
        ]);
        $mensagem = 'Usuário cadastrado com sucesso.';
    }
}

// RF07: alteração de permissão de um usuário existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'alterar_permissao') {
    $id_usuario = (int) ($_POST['id_usuario'] ?? 0);
    $novo_tipo  = $_POST['tipo_usuario'] ?? 'comum';

    $stmt = $pdo->prepare('UPDATE usuarios SET tipo_usuario = :tipo WHERE id_usuario = :id');
    $stmt->execute(['tipo' => $novo_tipo, 'id' => $id_usuario]);
    $mensagem = 'Permissão atualizada.';
}

// RF08: listagem de usuários cadastrados
$usuarios = $pdo->query('SELECT id_usuario, nome, email, tipo_usuario FROM usuarios ORDER BY nome')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar usuários - Moneyball UFC</title>
</head>
<body>
    <h1>Gerenciar usuários</h1>

    <?php if ($mensagem): ?>
        <p><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <h2>Cadastrar novo usuário</h2>
    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <select name="tipo_usuario">
            <option value="comum">Usuário comum</option>
            <option value="admin">Administrador</option>
        </select>
        <button type="submit">Cadastrar</button>
    </form>

    <h2>Usuários cadastrados</h2>
    <table border="1" cellpadding="6">
        <tr><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Alterar permissão</th></tr>
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['nome']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['tipo_usuario']) ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="acao" value="alterar_permissao">
                    <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                    <select name="tipo_usuario">
                        <option value="comum" <?= $u['tipo_usuario'] === 'comum' ? 'selected' : '' ?>>Comum</option>
                        <option value="admin" <?= $u['tipo_usuario'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button type="submit">Salvar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
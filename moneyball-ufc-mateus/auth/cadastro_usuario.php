<?php
/**
 * Cadastro de usuários / controle de permissões (RF05, RF06, RF07, RF08)
 * Tela: Pedro | Lógica de permissão (verifica_login/tipo admin): Mateus
 *
 * Acesso restrito a administradores (RF06).
 */
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/verifica_login.php';

$caminho_raiz = '../';

// RF06: só administrador pode acessar esta página
if ($usuario_tipo !== 'admin') {
    header('Location: ../dashboard.php?erro=acesso_negado');
    exit;
}

$erro = null;
$sucesso = null;

// ---------- RF07: alteração de permissão de um usuário existente ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_permissao') {
    $id_alvo = (int)($_POST['id'] ?? 0);
    $novo_tipo = $_POST['novo_tipo'] ?? '';

    if (!in_array($novo_tipo, ['admin', 'comum'], true) || $id_alvo <= 0) {
        $erro = 'Dados inválidos para alteração de permissão.';
    } elseif ($id_alvo === (int)$usuario_id) {
        $erro = 'Você não pode alterar a própria permissão.';
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET tipo = :tipo WHERE id = :id");
        $stmt->execute([':tipo' => $novo_tipo, ':id' => $id_alvo]);
        $sucesso = 'Permissão atualizada com sucesso.';
    }
}

// ---------- RF05: cadastro de novo usuário ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'novo_usuario') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo  = $_POST['tipo'] ?? 'comum';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha nome, e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif (!in_array($tipo, ['admin', 'comum'], true)) {
        $erro = 'Tipo de usuário inválido.';
    } else {
        // Verifica se já existe um usuário com este e-mail
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $erro = 'Já existe um usuário cadastrado com este e-mail.';
        } else {
            // RF03: senha nunca em texto puro
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)"
            );
            $stmt->execute([
                ':nome'  => $nome,
                ':email' => $email,
                ':senha' => $senha_hash,
                ':tipo'  => $tipo,
            ]);
            $sucesso = 'Usuário cadastrado com sucesso.';
        }
    }
}

// ---------- RF08: listagem de usuários cadastrados ----------
$usuarios = $pdo->query("SELECT id, nome, email, tipo, criado_em FROM usuarios ORDER BY nome ASC")
                 ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Moneyball UFC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <h1>Gerenciar Usuários</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Novo usuário</h2>
        <form method="POST" action="cadastro_usuario.php">
            <input type="hidden" name="acao" value="novo_usuario">

            <div class="campo">
                <label for="nome">Nome *</label>
                <input type="text" id="nome" name="nome" required>
            </div>

            <div class="campo">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="campo">
                <label for="senha">Senha *</label>
                <input type="password" id="senha" name="senha" minlength="6" required>
            </div>

            <div class="campo">
                <label for="tipo">Tipo de usuário *</label>
                <select id="tipo" name="tipo" required>
                    <option value="comum">Usuário Comum</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primario">Cadastrar usuário</button>
        </form>
    </div>

    <div class="card">
        <h2>Usuários cadastrados</h2>
        <div class="tabela-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Tipo</th>
                        <th>Cadastrado em</th>
                        <th>Alterar permissão</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nome']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['tipo'] === 'admin' ? 'Administrador' : 'Comum' ?></td>
                        <td><?= htmlspecialchars($u['criado_em']) ?></td>
                        <td>
                            <?php if ((int)$u['id'] === (int)$usuario_id): ?>
                                <em>(você)</em>
                            <?php else: ?>
                                <form method="POST" action="cadastro_usuario.php" style="display:flex; gap:6px;">
                                    <input type="hidden" name="acao" value="alterar_permissao">
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <select name="novo_tipo">
                                        <option value="comum" <?= $u['tipo'] === 'comum' ? 'selected' : '' ?>>Comum</option>
                                        <option value="admin" <?= $u['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-secundario">Salvar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

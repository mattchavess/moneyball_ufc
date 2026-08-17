<?php
/**
 * Exclusão de lutadores (RF11)
 * Lógica: Pedro | View: Alysson
 *
 * Fluxo:
 * 1) GET excluir.php?id=X       -> mostra tela de confirmação (não apaga nada ainda)
 * 2) POST excluir.php (confirmar=1) -> apaga de fato
 */
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/verifica_login.php';

$caminho_raiz = '../';
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $confirmar = $_POST['confirmar'] ?? '';

    if ($id <= 0) {
        header('Location: pesquisa.php');
        exit;
    }

    if ($confirmar === 'sim') {
        try {
            $pdo->beginTransaction();

            // Remove primeiro as estatísticas dependentes, depois o lutador
            $stmt = $pdo->prepare("DELETE FROM estatisticas WHERE lutador_id = :id");
            $stmt->execute([':id' => $id]);

            $stmt = $pdo->prepare("DELETE FROM lutadores WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $pdo->commit();

            header('Location: pesquisa.php?excluido=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $erro = 'Erro ao excluir lutador: ' . $e->getMessage();
        }
    } else {
        // Usuário cancelou
        header('Location: consulta.php?id=' . $id);
        exit;
    }
}

// GET -> carrega dados para exibir a tela de confirmação
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: pesquisa.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM lutadores WHERE id = :id");
$stmt->execute([':id' => $id]);
$lutador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lutador) {
    header('Location: pesquisa.php?erro=nao_encontrado');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Lutador - Moneyball UFC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <h1>Excluir Lutador</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div class="card">
        <p>Tem certeza que deseja excluir permanentemente o lutador abaixo? Todas as
           estatísticas associadas também serão apagadas. Essa ação não pode ser desfeita.</p>

        <p><strong>Nome:</strong> <?= htmlspecialchars($lutador['nome']) ?><br>
           <strong>Categoria:</strong> <?= htmlspecialchars($lutador['categoria_peso']) ?></p>

        <form method="POST" action="excluir.php">
            <input type="hidden" name="id" value="<?= (int)$lutador['id'] ?>">
            <div class="linha-botoes">
                <button type="submit" name="confirmar" value="sim" class="btn btn-perigo">
                    Sim, excluir
                </button>
                <button type="submit" name="confirmar" value="nao" class="btn btn-secundario">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

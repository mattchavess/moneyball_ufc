<?php
/**
 * Edição de lutadores (RF10)
 * Lógica: Pedro | View: Alysson
 */
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/verifica_login.php';

$caminho_raiz = '../';
$erro = null;

$categorias_peso = [
    'Peso-Palha', 'Peso-Mosca', 'Peso-Galo', 'Peso-Pena',
    'Peso-Leve', 'Meio-Médio', 'Médio', 'Meio-Pesado', 'Pesado'
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id <= 0) {
    header('Location: pesquisa.php');
    exit;
}

// Busca o lutador atual
$stmt = $pdo->prepare("SELECT * FROM lutadores WHERE id = :id");
$stmt->execute([':id' => $id]);
$lutador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lutador) {
    header('Location: pesquisa.php?erro=nao_encontrado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = trim($_POST['nome'] ?? '');
    $categoria_peso  = trim($_POST['categoria_peso'] ?? '');
    $idade           = $_POST['idade'] ?? '';
    $altura_cm       = $_POST['altura_cm'] ?? null;
    $alcance_cm      = $_POST['alcance_cm'] ?? null;
    $cidade          = trim($_POST['cidade'] ?? '');
    $pais            = trim($_POST['pais'] ?? '');

    if ($nome === '' || $categoria_peso === '' || $idade === '') {
        $erro = 'Preencha nome, categoria de peso e idade.';
    } else {
        try {
            $sql = "UPDATE lutadores SET
                        nome = :nome,
                        categoria_peso = :categoria_peso,
                        idade = :idade,
                        altura_cm = :altura_cm,
                        alcance_cm = :alcance_cm,
                        cidade = :cidade,
                        pais = :pais
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome'           => $nome,
                ':categoria_peso' => $categoria_peso,
                ':idade'          => (int)$idade,
                ':altura_cm'      => $altura_cm !== '' ? (int)$altura_cm : null,
                ':alcance_cm'     => $alcance_cm !== '' ? (int)$alcance_cm : null,
                ':cidade'         => $cidade !== '' ? $cidade : null,
                ':pais'           => $pais !== '' ? $pais : null,
                ':id'             => $id,
            ]);

            header('Location: consulta.php?id=' . $id . '&atualizado=1');
            exit;
        } catch (PDOException $e) {
            $erro = 'Erro ao atualizar lutador: ' . $e->getMessage();
        }
    }
    // Se deu erro, mantém os dados digitados na tela em vez dos originais do banco
    $lutador = array_merge($lutador, $_POST);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Lutador - Moneyball UFC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <h1>Editar Lutador</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="editar.php">
            <input type="hidden" name="id" value="<?= (int)$lutador['id'] ?>">

            <div class="campo">
                <label for="nome">Nome completo *</label>
                <input type="text" id="nome" name="nome" required
                       value="<?= htmlspecialchars($lutador['nome']) ?>">
            </div>

            <div class="campo">
                <label for="categoria_peso">Categoria de peso *</label>
                <select id="categoria_peso" name="categoria_peso" required>
                    <?php foreach ($categorias_peso as $cat): ?>
                        <option value="<?= $cat ?>"
                            <?= ($lutador['categoria_peso'] === $cat) ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="idade">Idade *</label>
                <input type="number" id="idade" name="idade" min="16" max="60" required
                       value="<?= htmlspecialchars($lutador['idade']) ?>">
            </div>

            <div class="campo">
                <label for="altura_cm">Altura (cm)</label>
                <input type="number" id="altura_cm" name="altura_cm"
                       value="<?= htmlspecialchars($lutador['altura_cm'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="alcance_cm">Alcance (cm)</label>
                <input type="number" id="alcance_cm" name="alcance_cm"
                       value="<?= htmlspecialchars($lutador['alcance_cm'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="cidade">Cidade natal</label>
                <input type="text" id="cidade" name="cidade"
                       value="<?= htmlspecialchars($lutador['cidade'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="pais">País</label>
                <input type="text" id="pais" name="pais"
                       value="<?= htmlspecialchars($lutador['pais'] ?? '') ?>">
            </div>

            <div class="linha-botoes">
                <button type="submit" class="btn btn-primario">Salvar alterações</button>
                <a href="consulta.php?id=<?= (int)$lutador['id'] ?>" class="btn btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

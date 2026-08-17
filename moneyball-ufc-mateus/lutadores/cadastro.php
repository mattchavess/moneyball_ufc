<?php
/**
 * Cadastro de lutadores (RF09)
 * Lógica: Pedro | View: Alysson
 */
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/verifica_login.php';

$caminho_raiz = '../';
$erro = null;
$sucesso = null;

// Categorias de peso do UFC (usadas também no filtro de pesquisa)
$categorias_peso = [
    'Peso-Palha', 'Peso-Mosca', 'Peso-Galo', 'Peso-Pena',
    'Peso-Leve', 'Meio-Médio', 'Médio', 'Meio-Pesado', 'Pesado'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = trim($_POST['nome'] ?? '');
    $categoria_peso  = trim($_POST['categoria_peso'] ?? '');
    $idade           = $_POST['idade'] ?? '';
    $altura_cm       = $_POST['altura_cm'] ?? null;
    $alcance_cm      = $_POST['alcance_cm'] ?? null;
    $cidade          = trim($_POST['cidade'] ?? '');
    $pais            = trim($_POST['pais'] ?? '');

    // Validação simples de campos obrigatórios
    if ($nome === '' || $categoria_peso === '' || $idade === '') {
        $erro = 'Preencha nome, categoria de peso e idade.';
    } elseif (!ctype_digit((string)$idade) || (int)$idade < 16 || (int)$idade > 60) {
        $erro = 'Idade inválida.';
    } else {
        try {
            $sql = "INSERT INTO lutadores (nome, categoria_peso, idade, altura_cm, alcance_cm, cidade, pais)
                    VALUES (:nome, :categoria_peso, :idade, :altura_cm, :alcance_cm, :cidade, :pais)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome'           => $nome,
                ':categoria_peso' => $categoria_peso,
                ':idade'          => (int)$idade,
                ':altura_cm'      => $altura_cm !== '' ? (int)$altura_cm : null,
                ':alcance_cm'     => $alcance_cm !== '' ? (int)$alcance_cm : null,
                ':cidade'         => $cidade !== '' ? $cidade : null,
                ':pais'           => $pais !== '' ? $pais : null,
            ]);

            $novo_id = $pdo->lastInsertId();

            // Cria automaticamente uma linha de estatísticas zerada para a temporada atual,
            // para o lutador já aparecer nas telas de estatística/ranking sem erro.
            $temporada_atual = date('Y');
            $stmtStats = $pdo->prepare(
                "INSERT INTO estatisticas (lutador_id, temporada) VALUES (:lutador_id, :temporada)"
            );
            $stmtStats->execute([':lutador_id' => $novo_id, ':temporada' => $temporada_atual]);

            header('Location: consulta.php?id=' . $novo_id . '&criado=1');
            exit;
        } catch (PDOException $e) {
            $erro = 'Erro ao cadastrar lutador: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Lutador - Moneyball UFC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <h1>Cadastrar Lutador</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="cadastro.php">
            <div class="campo">
                <label for="nome">Nome completo *</label>
                <input type="text" id="nome" name="nome" required
                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="categoria_peso">Categoria de peso *</label>
                <select id="categoria_peso" name="categoria_peso" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias_peso as $cat): ?>
                        <option value="<?= $cat ?>"
                            <?= (($_POST['categoria_peso'] ?? '') === $cat) ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="idade">Idade *</label>
                <input type="number" id="idade" name="idade" min="16" max="60" required
                       value="<?= htmlspecialchars($_POST['idade'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="altura_cm">Altura (cm)</label>
                <input type="number" id="altura_cm" name="altura_cm"
                       value="<?= htmlspecialchars($_POST['altura_cm'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="alcance_cm">Alcance (cm)</label>
                <input type="number" id="alcance_cm" name="alcance_cm"
                       value="<?= htmlspecialchars($_POST['alcance_cm'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="cidade">Cidade natal</label>
                <input type="text" id="cidade" name="cidade"
                       value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="pais">País</label>
                <input type="text" id="pais" name="pais"
                       value="<?= htmlspecialchars($_POST['pais'] ?? '') ?>">
            </div>

            <div class="linha-botoes">
                <button type="submit" class="btn btn-primario">Salvar lutador</button>
                <a href="pesquisa.php" class="btn btn-secundario">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

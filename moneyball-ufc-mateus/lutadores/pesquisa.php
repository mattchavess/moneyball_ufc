<?php
/**
 * Pesquisa por nome (RF14) + Filtro por categoria de peso (RF15)
 * Lógica: Pedro | View: Alysson
 */
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/verifica_login.php';

$caminho_raiz = '../';

$categorias_peso = [
    'Peso-Palha', 'Peso-Mosca', 'Peso-Galo', 'Peso-Pena',
    'Peso-Leve', 'Meio-Médio', 'Médio', 'Meio-Pesado', 'Pesado'
];

$termo_busca = trim($_GET['busca'] ?? '');
$categoria_filtro = trim($_GET['categoria'] ?? '');

// Monta a query dinamicamente conforme os filtros preenchidos
$sql = "SELECT * FROM lutadores WHERE 1 = 1";
$params = [];

if ($termo_busca !== '') {
    $sql .= " AND nome LIKE :busca";
    $params[':busca'] = '%' . $termo_busca . '%';
}

if ($categoria_filtro !== '') {
    $sql .= " AND categoria_peso = :categoria";
    $params[':categoria'] = $categoria_filtro;
}

$sql .= " ORDER BY nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lutadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lutadores - Moneyball UFC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <h1>Lutadores</h1>

    <?php if (isset($_GET['excluido'])): ?>
        <div class="alerta alerta-sucesso">Lutador excluído com sucesso.</div>
    <?php elseif (isset($_GET['erro']) && $_GET['erro'] === 'nao_encontrado'): ?>
        <div class="alerta alerta-erro">Lutador não encontrado.</div>
    <?php endif; ?>

    <form method="GET" action="pesquisa.php" class="barra-filtros">
        <div class="campo">
            <label for="busca">Pesquisar por nome</label>
            <input type="text" id="busca" name="busca" placeholder="Digite o nome..."
                   value="<?= htmlspecialchars($termo_busca) ?>">
        </div>

        <div class="campo">
            <label for="categoria">Categoria de peso</label>
            <select id="categoria" name="categoria">
                <option value="">Todas</option>
                <?php foreach ($categorias_peso as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($categoria_filtro === $cat) ? 'selected' : '' ?>>
                        <?= $cat ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primario">Filtrar</button>
        <a href="pesquisa.php" class="btn btn-secundario">Limpar</a>
        <a href="cadastro.php" class="btn btn-primario">+ Novo lutador</a>
    </form>

    <div class="card">
        <?php if (empty($lutadores)): ?>
            <p>Nenhum lutador encontrado com os filtros informados.</p>
        <?php else: ?>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Idade</th>
                            <th>Naturalidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lutadores as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars($l['nome']) ?></td>
                            <td><?= htmlspecialchars($l['categoria_peso']) ?></td>
                            <td><?= (int)$l['idade'] ?></td>
                            <td><?= htmlspecialchars($l['cidade'] ?? '-') ?></td>
                            <td>
                                <a href="consulta.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-secundario">Ver</a>
                                <a href="editar.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-secundario">Editar</a>
                                <a href="excluir.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-perigo">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

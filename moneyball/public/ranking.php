<?php
// ============================================
// public/ranking.php
// Tela "Ranking Geral" (RF16, RF17)
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_ranking.php";

$categoria = isset($_GET["categoria"]) ? $_GET["categoria"] : "Todos";
$categorias = ["Todos", "Peso Mosca", "Peso Galo", "Peso Pena", "Peso Leve", "Meio-Médio", "Médio", "Meio-Pesado", "Peso Pesado"];

$ranking = calcularRanking($conexao, $categoria);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ranking Geral - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <h1>Ranking Geral</h1>
    <p><?php echo count($ranking); ?> lutadores · ordenado por score de desempenho</p>

    <!-- Filtro de categoria -->
    <form method="GET" action="ranking.php">
        <?php foreach ($categorias as $c): ?>
            <a href="ranking.php?categoria=<?php echo urlencode($c); ?>"
               class="<?php echo ($categoria === $c) ? 'ativo' : ''; ?>">
                <?php echo htmlspecialchars($c); ?>
            </a>
        <?php endforeach; ?>
    </form>

    <table>
        <tr>
            <th>#</th>
            <th>Lutador</th>
            <th>Categoria</th>
            <th>Estilo</th>
            <th>V</th>
            <th>KOs</th>
            <th>Win Rate</th>
            <th>Finish Rate</th>
            <th>Score</th>
        </tr>
        <?php
        $posicao = 1;
        foreach ($ranking as $lutador):
        ?>
            <tr>
                <td>#<?php echo $posicao; ?></td>
                <td>
                    <?php echo htmlspecialchars($lutador["bandeira_emoji"]); ?>
                    <a href="lutador_detalhe.php?id=<?php echo $lutador["id"]; ?>">
                        <?php echo htmlspecialchars($lutador["nome"]); ?>
                        <?php if ($lutador["apelido"]): ?>
                            "<?php echo htmlspecialchars($lutador["apelido"]); ?>"
                        <?php endif; ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($lutador["categoria_peso"]); ?></td>
                <td><?php echo htmlspecialchars($lutador["estilo_luta"]); ?></td>
                <td><?php echo $lutador["total_vitorias"]; ?></td>
                <td><?php echo $lutador["total_kos"]; ?></td>
                <td><?php echo $lutador["win_rate"]; ?>%</td>
                <td><?php echo $lutador["finish_rate"]; ?>%</td>
                <td><strong><?php echo $lutador["score"]; ?></strong></td>
            </tr>
        <?php
            $posicao++;
        endforeach;
        ?>
    </table>

    <?php if (count($ranking) === 0): ?>
        <p>Nenhum lutador encontrado nessa categoria.</p>
    <?php endif; ?>

</body>
</html>

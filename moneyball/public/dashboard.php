<?php
// ============================================
// public/dashboard.php
// Tela inicial após login (RF16, RF18-22)
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_ranking.php";

$resumo = calcularResumoGeral($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <h1>DASHBOARD</h1>
    <p>Visão geral do plantel · Olá, <?php echo htmlspecialchars($_SESSION["usuario_nome"]); ?></p>
    <a href="logout.php">Sair do Sistema</a>

    <!-- Cards de resumo geral -->
    <div class="cards-resumo">
        <div class="card">
            <span>Lutadores cadastrados</span>
            <strong><?php echo $resumo["total_lutadores"]; ?></strong>
        </div>
        <div class="card">
            <span>Total de lutas</span>
            <strong><?php echo $resumo["total_lutas"]; ?></strong>
        </div>
        <div class="card">
            <span>Total de KOs</span>
            <strong><?php echo $resumo["total_kos"]; ?></strong>
        </div>
        <div class="card">
            <span>Total de finalizações</span>
            <strong><?php echo $resumo["total_finalizacoes"]; ?></strong>
        </div>
    </div>

    <!-- Melhor e pior desempenho -->
    <div class="cards-destaque">
        <?php if ($resumo["melhor_lutador"]): ?>
            <div class="card destaque-positivo">
                <span>Melhor desempenho</span>
                <strong><?php echo htmlspecialchars($resumo["melhor_lutador"]["nome"]); ?></strong>
                <p><?php echo $resumo["melhor_lutador"]["score"]; ?> pts de score</p>
            </div>
        <?php endif; ?>

        <?php if ($resumo["pior_lutador"]): ?>
            <div class="card destaque-negativo">
                <span>Pior desempenho</span>
                <strong><?php echo htmlspecialchars($resumo["pior_lutador"]["nome"]); ?></strong>
                <p><?php echo $resumo["pior_lutador"]["score"]; ?> pts de score</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Top 5 lutadores -->
    <h2>Top 5 Lutadores</h2>
    <a href="ranking.php">Ver ranking completo &rarr;</a>

    <table>
        <tr>
            <th>#</th>
            <th>Lutador</th>
            <th>Categoria</th>
            <th>Score</th>
        </tr>
        <?php $posicao = 1; foreach ($resumo["top5"] as $lutador): ?>
            <tr>
                <td>#<?php echo $posicao; ?></td>
                <td>
                    <?php echo htmlspecialchars($lutador["bandeira_emoji"]); ?>
                    <a href="lutador_detalhe.php?id=<?php echo $lutador["id"]; ?>">
                        <?php echo htmlspecialchars($lutador["nome"]); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($lutador["categoria_peso"]); ?></td>
                <td><strong><?php echo $lutador["score"]; ?></strong></td>
            </tr>
        <?php $posicao++; endforeach; ?>
    </table>

    <?php if (count($resumo["top5"]) === 0): ?>
        <p>Nenhum lutador cadastrado ainda. <a href="cadastrar_lutador.php">Cadastre o primeiro</a>.</p>
    <?php endif; ?>

    <!-- Menu simples de navegação (enquanto não temos o menu lateral com CSS) -->
    <h2>Navegação</h2>
    <ul>
        <li><a href="lutadores.php">Lutadores</a></li>
        <li><a href="ranking.php">Ranking</a></li>
        <li><a href="cadastrar_lutador.php">Cadastrar Lutador</a></li>
        <?php if ($_SESSION["usuario_tipo"] === "admin"): ?>
            <li><a href="cadastrar_usuario.php">Usuários</a></li>
        <?php endif; ?>
    </ul>

</body>
</html>

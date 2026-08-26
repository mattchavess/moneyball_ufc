<?php
// ============================================
// public/dashboard.php
// Tela inicial após login (RF16, RF18-22)
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";   // usado por funcoes_analytics.php
require_once __DIR__ . "/../includes/funcoes_ranking.php";
require_once __DIR__ . "/../includes/funcoes_analytics.php";

$resumo = calcularResumoGeral($conexao);

// Dados para os gráficos (Chart.js) e listas
$top5Consistencia = top5ScoreConsistencia($conexao);
$porCategoria = distribuicaoPorCategoria($conexao);
$porPais = distribuicaoPorPais($conexao);
$porEstilo = distribuicaoPorEstilo($conexao);

// Maior valor de cada lista, usado só pra calcular a largura (%) das
// barrinhas nas listas de país/estilo (barra proporcional ao maior)
$maiorPais = 0;
foreach ($porPais as $p) { $maiorPais = max($maiorPais, $p["total"]); }
$maiorEstilo = 0;
foreach ($porEstilo as $e) { $maiorEstilo = max($maiorEstilo, $e["total"]); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . "/../includes/menu.php"; ?>
    <main class="conteudo">

    <h1>DASHBOARD</h1>
    <p>Visão geral do plantel · Olá, <?php echo htmlspecialchars($_SESSION["usuario_nome"]); ?></p>

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

    <!-- ============================================
         GRÁFICOS (Chart.js)
         ============================================ -->
    <div class="chart-grid">
        <div class="chart-card">
            <h3>Top 5 — Score vs Consistência</h3>
            <div class="chart-canvas-wrap">
                <canvas id="graficoTop5"></canvas>
            </div>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <h3>Distribuição por Categoria</h3>
            <div class="chart-canvas-wrap">
                <canvas id="graficoCategoria"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3>Lutadores por País</h3>
            <ul class="lista-distribuicao">
                <?php foreach ($porPais as $p):
                    $largura = $maiorPais > 0 ? round(($p["total"] / $maiorPais) * 100) : 0;
                ?>
                    <li>
                        <div class="linha-distribuicao">
                            <span><?php echo htmlspecialchars($p["pais"]); ?></span>
                            <span><?php echo $p["total"]; ?></span>
                        </div>
                        <div class="barra-mini-fundo">
                            <div class="barra-mini" style="width: <?php echo $largura; ?>%"></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="chart-card">
            <h3>Estilo de Luta do Plantel</h3>
            <ul class="lista-distribuicao">
                <?php foreach ($porEstilo as $e):
                    $largura = $maiorEstilo > 0 ? round(($e["total"] / $maiorEstilo) * 100) : 0;
                ?>
                    <li>
                        <div class="linha-distribuicao">
                            <span><?php echo htmlspecialchars($e["estilo_luta"]); ?></span>
                            <span><?php echo $e["total"]; ?></span>
                        </div>
                        <div class="barra-mini-fundo">
                            <div class="barra-mini" style="width: <?php echo $largura; ?>%"></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
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

    </main>
</div>

<!-- Chart.js via CDN — biblioteca JS que desenha os gráficos -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
// ============================================
// Os dados abaixo vêm do PHP via json_encode().
// Isso "traduz" os arrays PHP calculados nas
// funções de includes/funcoes_analytics.php
// para o formato que o JavaScript entende.
// ============================================

const dadosTop5 = <?php echo json_encode($top5Consistencia); ?>;
const dadosCategoria = <?php echo json_encode($porCategoria); ?>;

// Configuração visual comum (tema escuro combinando com o site)
Chart.defaults.color = "#999999";
Chart.defaults.borderColor = "#262626";

// ---------- Gráfico 1: Top 5 Score vs Consistência (barras) ----------
new Chart(document.getElementById("graficoTop5"), {
    type: "bar",
    data: {
        labels: dadosTop5.map(item => item.nome),
        datasets: [
            {
                label: "Score",
                data: dadosTop5.map(item => item.score),
                backgroundColor: "#e10600",
            },
            {
                label: "Consistência",
                data: dadosTop5.map(item => item.consistencia),
                backgroundColor: "#3b82f6",
            }
        ]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        scales: {
            y: { min: 0, max: 100 }
        }
    }
});

// ---------- Gráfico 3: Distribuição por Categoria (rosca) ----------
new Chart(document.getElementById("graficoCategoria"), {
    type: "doughnut",
    data: {
        labels: dadosCategoria.map(item => item.categoria_peso),
        datasets: [{
            data: dadosCategoria.map(item => item.total),
            backgroundColor: [
                "#e10600", "#3b82f6", "#22c55e", "#eab308",
                "#a855f7", "#ec4899", "#14b8a6", "#f97316"
            ],
            borderColor: "#0d0d0d",
            borderWidth: 2,
        }]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: { position: "bottom", labels: { boxWidth: 12, font: { size: 11 } } }
        }
    }
});
</script>
</body>
</html>

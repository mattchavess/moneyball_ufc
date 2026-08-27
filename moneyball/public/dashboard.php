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
$vitoriasPorTemporada = vitoriasPorTemporada($conexao);

// Maior valor da lista de país
$maiorPais = 0;
foreach ($porPais as $p) {
    $maiorPais = max($maiorPais, $p["total"]);
}
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

    <p>
        Visão geral do plantel · Olá,
        <?php echo htmlspecialchars($_SESSION["usuario_nome"]); ?>
    </p>

    <!-- ============================================
         CARDS DE RESUMO GERAL
         ============================================ -->

    <div class="cards-resumo">

        <div class="card">
            <span>Lutadores cadastrados</span>
            <strong>
                <?php echo $resumo["total_lutadores"]; ?>
            </strong>
        </div>

        <div class="card">
            <span>Total de lutas</span>
            <strong>
                <?php echo $resumo["total_lutas"]; ?>
            </strong>
        </div>

        <div class="card">
            <span>Total de KOs</span>
            <strong>
                <?php echo $resumo["total_kos"]; ?>
            </strong>
        </div>

        <div class="card">
            <span>Total de finalizações</span>
            <strong>
                <?php echo $resumo["total_finalizacoes"]; ?>
            </strong>
        </div>

    </div>


    <!-- ============================================
         MELHOR E PIOR DESEMPENHO
         ============================================ -->

    <div class="cards-destaque">

        <?php if ($resumo["melhor_lutador"]): ?>

            <div class="card destaque-positivo">

                <span>Melhor desempenho</span>

                <strong>
                    <?php echo htmlspecialchars(
                        $resumo["melhor_lutador"]["nome"]
                    ); ?>
                </strong>

                <p>
                    <?php echo $resumo["melhor_lutador"]["score"]; ?>
                    pts de score
                </p>

            </div>

        <?php endif; ?>


        <?php if ($resumo["pior_lutador"]): ?>

            <div class="card destaque-negativo">

                <span>Pior desempenho</span>

                <strong>
                    <?php echo htmlspecialchars(
                        $resumo["pior_lutador"]["nome"]
                    ); ?>
                </strong>

                <p>
                    <?php echo $resumo["pior_lutador"]["score"]; ?>
                    pts de score
                </p>

            </div>

        <?php endif; ?>

    </div>


    <!-- ============================================
         GRÁFICOS
         ============================================ -->

    <!-- GRÁFICO TOP 5 -->

    <div class="chart-grid">

        <div class="chart-card">

            <h3>Top 5 — Score vs Consistência</h3>

            <div class="chart-canvas-wrap">

                <canvas id="graficoTop5"></canvas>

            </div>

        </div>

    </div>


    <!-- ============================================
         CATEGORIA + PAÍS + VITÓRIAS
         ============================================ -->

    <div class="chart-grid">


        <!-- DISTRIBUIÇÃO POR CATEGORIA -->

        <div class="chart-card">

            <h3>Distribuição por Categoria</h3>

            <div class="chart-canvas-wrap">

                <canvas id="graficoCategoria"></canvas>

            </div>

        </div>


        <!-- LUTADORES POR PAÍS -->

        <div class="chart-card">

            <h3>Lutadores por País</h3>

            <ul class="lista-distribuicao">

                <?php foreach ($porPais as $p):

                    $largura = $maiorPais > 0
                        ? round(($p["total"] / $maiorPais) * 100)
                        : 0;

                ?>

                    <li>

                        <div class="linha-distribuicao">

                            <span>
                                <?php echo htmlspecialchars($p["pais"]); ?>
                            </span>

                            <span>
                                <?php echo $p["total"]; ?>
                            </span>

                        </div>

                        <div class="barra-mini-fundo">

                            <div
                                class="barra-mini"
                                style="width: <?php echo $largura; ?>%">
                            </div>

                        </div>

                    </li>

                <?php endforeach; ?>

            </ul>

        </div>


        <!-- ============================================
             VITÓRIAS POR TEMPORADA
             ============================================ -->

        <div class="chart-card">

            <h3>Vitórias por Temporada</h3>

            <div class="chart-canvas-wrap">

                <canvas id="graficoVitoriasTemporada"></canvas>

            </div>

        </div>

    </div>


    <!-- ============================================
         TOP 5 LUTADORES
         ============================================ -->

    <h2>Top 5 Lutadores</h2>

    <a href="ranking.php">
        Ver ranking completo &rarr;
    </a>


    <table>

        <tr>

            <th>#</th>

            <th>Lutador</th>

            <th>Categoria</th>

            <th>Score</th>

        </tr>


        <?php
        $posicao = 1;

        foreach ($resumo["top5"] as $lutador):
        ?>

            <tr>

                <td>
                    #<?php echo $posicao; ?>
                </td>

                <td>

                    <?php echo htmlspecialchars(
                        $lutador["bandeira_emoji"]
                    ); ?>

                    <a href="lutador_detalhe.php?id=<?php echo $lutador["id"]; ?>">

                        <?php echo htmlspecialchars(
                            $lutador["nome"]
                        ); ?>

                    </a>

                </td>

                <td>

                    <?php echo htmlspecialchars(
                        $lutador["categoria_peso"]
                    ); ?>

                </td>

                <td>

                    <strong>
                        <?php echo $lutador["score"]; ?>
                    </strong>

                </td>

            </tr>


        <?php
            $posicao++;
        endforeach;
        ?>


    </table>


    <?php if (count($resumo["top5"]) === 0): ?>

        <p>
            Nenhum lutador cadastrado ainda.
            <a href="cadastrar_lutador.php">
                Cadastre o primeiro
            </a>.
        </p>

    <?php endif; ?>


    </main>

</div>


<!-- ============================================
     CHART.JS
     ============================================ -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

<script>

// ============================================
// DADOS VINDOS DO PHP
// ============================================

const dadosTop5 =
    <?php echo json_encode($top5Consistencia); ?>;

const dadosCategoria =
    <?php echo json_encode($porCategoria); ?>;

const dadosVitoriasTemporada =
    <?php echo json_encode($vitoriasPorTemporada); ?>;


// ============================================
// CONFIGURAÇÃO VISUAL
// ============================================

Chart.defaults.color = "#999999";
Chart.defaults.borderColor = "#262626";


// ============================================
// GRÁFICO 1
// TOP 5 — SCORE VS CONSISTÊNCIA
// ============================================

new Chart(
    document.getElementById("graficoTop5"),
    {
        type: "bar",

        data: {

            labels: dadosTop5.map(
                item => item.nome
            ),

            datasets: [

                {
                    label: "Score",

                    data: dadosTop5.map(
                        item => item.score
                    ),

                    backgroundColor: "#e10600",
                },

                {
                    label: "Consistência",

                    data: dadosTop5.map(
                        item => item.consistencia
                    ),

                    backgroundColor: "#3b82f6",
                }

            ]

        },

        options: {

            maintainAspectRatio: false,

            responsive: true,

            scales: {

                y: {
                    min: 0,
                    max: 100
                }

            }

        }

    }
);


// ============================================
// GRÁFICO 2
// DISTRIBUIÇÃO POR CATEGORIA
// ============================================

new Chart(
    document.getElementById("graficoCategoria"),
    {

        type: "doughnut",

        data: {

            labels: dadosCategoria.map(
                item => item.categoria_peso
            ),

            datasets: [

                {

                    data: dadosCategoria.map(
                        item => item.total
                    ),

                    backgroundColor: [

                        "#e10600",
                        "#3b82f6",
                        "#22c55e",
                        "#eab308",
                        "#a855f7",
                        "#ec4899",
                        "#14b8a6",
                        "#f97316"

                    ],

                    borderColor: "#0d0d0d",

                    borderWidth: 2

                }

            ]

        },

        options: {

            maintainAspectRatio: false,

            responsive: true,

            plugins: {

                legend: {

                    position: "bottom",

                    labels: {

                        boxWidth: 12,

                        font: {
                            size: 11
                        }

                    }

                }

            }

        }

    }
);


// ============================================
// GRÁFICO 3
// VITÓRIAS POR TEMPORADA
// ============================================

new Chart(
    document.getElementById("graficoVitoriasTemporada"),
    {

        type: "bar",

        data: {

            labels: dadosVitoriasTemporada.map(
                item => item.temporada
            ),

            datasets: [

                {

                    label: "Vitórias",

                    data: dadosVitoriasTemporada.map(
                        item => item.total_vitorias
                    ),

                    backgroundColor: "#e10600",

                    borderRadius: 4

                }

            ]

        },

        options: {

            maintainAspectRatio: false,

            responsive: true,

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {

                        precision: 0

                    }

                },

                x: {

                    ticks: {

                        color: "#999999"

                    }

                }

            },

            plugins: {

                legend: {

                    display: false

                }

            }

        }

    }
);

</script>

</body>
</html>
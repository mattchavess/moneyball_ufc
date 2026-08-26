<?php
// ============================================
// public/comparar.php
// Tela "Comparação de Lutadores" (RF23, RF24)
// Diferencial obrigatório - versão estilizada
// com layout VS e gráfico radar (Chart.js).
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";

$todosLutadores = listarLutadores($conexao);

$id1 = isset($_GET["lutador1"]) ? (int) $_GET["lutador1"] : 0;
$id2 = isset($_GET["lutador2"]) ? (int) $_GET["lutador2"] : 0;
$temporada1 = isset($_GET["temporada1"]) ? trim($_GET["temporada1"]) : "";
$temporada2 = isset($_GET["temporada2"]) ? trim($_GET["temporada2"]) : "";

$lutador1 = $id1 > 0 ? buscarLutadorPorTemporada($conexao, $id1, $temporada1) : null;
$lutador2 = $id2 > 0 ? buscarLutadorPorTemporada($conexao, $id2, $temporada2) : null;

$mostrarComparacao = $lutador1 && $lutador2
    && $lutador1["temporada_selecionada"]
    && $lutador2["temporada_selecionada"];

// Prepara os dados do gráfico radar só se os dois lutadores
// tiverem estatística válida na temporada escolhida
$radarLabels = [];
$radarDados1 = [];
$radarDados2 = [];

if ($mostrarComparacao) {
    $s1 = $lutador1["temporada_selecionada"];
    $s2 = $lutador2["temporada_selecionada"];

    $totalLutas1 = $s1["vitorias"] + $s1["derrotas"] + $s1["empates"];
    $totalLutas2 = $s2["vitorias"] + $s2["derrotas"] + $s2["empates"];
    $winRate1 = $totalLutas1 > 0 ? round(($s1["vitorias"] / $totalLutas1) * 100, 1) : 0;
    $winRate2 = $totalLutas2 > 0 ? round(($s2["vitorias"] / $totalLutas2) * 100, 1) : 0;

    // Função auxiliar: normaliza duas métricas pra uma escala 0-100
    // relativa (o maior dos dois vira 100). Isso é necessário porque
    // KOs, golpes/min e quedas/luta não são naturalmente 0-100 como
    // win rate e precisão já são — sem normalizar, o radar ficaria
    // ilegível (uma métrica em dezenas, outra em unidades).
    $normalizar = function ($a, $b) {
        $maior = max($a, $b);
        if ($maior <= 0) {
            return [0, 0];
        }
        return [round(($a / $maior) * 100, 1), round(($b / $maior) * 100, 1)];
    };

    [$koNorm1, $koNorm2] = $normalizar($s1["kos"], $s2["kos"]);
    [$finNorm1, $finNorm2] = $normalizar($s1["finalizacoes"], $s2["finalizacoes"]);
    [$golpesNorm1, $golpesNorm2] = $normalizar($s1["golpes_significativos_min"], $s2["golpes_significativos_min"]);
    [$quedasNorm1, $quedasNorm2] = $normalizar($s1["media_quedas_luta"], $s2["media_quedas_luta"]);

    $radarLabels = ["Win Rate", "Precisão Striking", "KOs", "Finalizações", "Golpes/min", "Quedas/luta"];
    $radarDados1 = [$winRate1, $s1["precisao_striking"], $koNorm1, $finNorm1, $golpesNorm1, $quedasNorm1];
    $radarDados2 = [$winRate2, $s2["precisao_striking"], $koNorm2, $finNorm2, $golpesNorm2, $quedasNorm2];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comparação de Lutadores - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . "/../includes/menu.php"; ?>
    <main class="conteudo">

    <h1>Comparação de Lutadores</h1>
    <p>Compare estatísticas por temporada, evolução e carreira</p>

    <form method="GET" action="comparar.php" class="form-comparacao">
        <div class="coluna-comparacao">
            <label>Lutador 1</label>
            <select name="lutador1">
                <option value="">Selecione</option>
                <?php foreach ($todosLutadores as $l): ?>
                    <option value="<?php echo $l["id"]; ?>" <?php echo ($id1 === (int) $l["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($l["nome"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Temporada (vazio = mais recente)</label>
            <input type="text" name="temporada1" value="<?php echo htmlspecialchars($temporada1); ?>" placeholder="ex: 2024">
        </div>

        <span class="versus-form">VS</span>

        <div class="coluna-comparacao">
            <label>Lutador 2</label>
            <select name="lutador2">
                <option value="">Selecione</option>
                <?php foreach ($todosLutadores as $l): ?>
                    <option value="<?php echo $l["id"]; ?>" <?php echo ($id2 === (int) $l["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($l["nome"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Temporada (vazio = mais recente)</label>
            <input type="text" name="temporada2" value="<?php echo htmlspecialchars($temporada2); ?>" placeholder="ex: 2024">
        </div>

        <button type="submit">Comparar</button>
    </form>

    <?php if ($lutador1 && !$lutador1["temporada_selecionada"]): ?>
        <p class="erro"><?php echo htmlspecialchars($lutador1["nome"]); ?> não tem estatística cadastrada<?php echo $temporada1 ? " para a temporada $temporada1" : ""; ?>.</p>
    <?php endif; ?>

    <?php if ($lutador2 && !$lutador2["temporada_selecionada"]): ?>
        <p class="erro"><?php echo htmlspecialchars($lutador2["nome"]); ?> não tem estatística cadastrada<?php echo $temporada2 ? " para a temporada $temporada2" : ""; ?>.</p>
    <?php endif; ?>

    <?php if ($mostrarComparacao): ?>

        <!-- Cards VS -->
        <div class="vs-container">
            <div class="card-versus vermelho">
                <span class="bandeira-versus"><?php echo htmlspecialchars($lutador1["bandeira_emoji"]); ?></span>
                <h2><?php echo htmlspecialchars($lutador1["nome"]); ?></h2>
                <p class="temporada-versus">Temporada <?php echo htmlspecialchars($s1["temporada"]); ?></p>
                <div class="score-versus"><?php echo $winRate1; ?>%</div>
                <span>Win Rate</span>
            </div>

            <span class="versus-texto">VS</span>

            <div class="card-versus azul">
                <span class="bandeira-versus"><?php echo htmlspecialchars($lutador2["bandeira_emoji"]); ?></span>
                <h2><?php echo htmlspecialchars($lutador2["nome"]); ?></h2>
                <p class="temporada-versus">Temporada <?php echo htmlspecialchars($s2["temporada"]); ?></p>
                <div class="score-versus"><?php echo $winRate2; ?>%</div>
                <span>Win Rate</span>
            </div>
        </div>

        <!-- Gráfico radar -->
        <div class="chart-card">
            <h3>Comparação Visual</h3>
            <div class="chart-canvas-wrap radar-wrap">
                <canvas id="graficoRadar"></canvas>
            </div>
        </div>

        <!-- Tabela detalhada -->
        <h2>Estatísticas Detalhadas</h2>
        <table>
            <tr>
                <th>Estatística</th>
                <th><?php echo htmlspecialchars($lutador1["nome"]); ?></th>
                <th><?php echo htmlspecialchars($lutador2["nome"]); ?></th>
            </tr>
            <tr><td>Altura</td><td><?php echo $lutador1["altura_cm"]; ?> cm</td><td><?php echo $lutador2["altura_cm"]; ?> cm</td></tr>
            <tr><td>Alcance</td><td><?php echo $lutador1["alcance_cm"]; ?> cm</td><td><?php echo $lutador2["alcance_cm"]; ?> cm</td></tr>
            <tr><td>Idade</td><td><?php echo $lutador1["idade"]; ?> anos</td><td><?php echo $lutador2["idade"]; ?> anos</td></tr>
            <tr><td>Vitórias</td><td><?php echo $s1["vitorias"]; ?></td><td><?php echo $s2["vitorias"]; ?></td></tr>
            <tr><td>Derrotas</td><td><?php echo $s1["derrotas"]; ?></td><td><?php echo $s2["derrotas"]; ?></td></tr>
            <tr><td>KOs</td><td><?php echo $s1["kos"]; ?></td><td><?php echo $s2["kos"]; ?></td></tr>
            <tr><td>Finalizações</td><td><?php echo $s1["finalizacoes"]; ?></td><td><?php echo $s2["finalizacoes"]; ?></td></tr>
            <tr><td>Win Rate</td><td><strong><?php echo $winRate1; ?>%</strong></td><td><strong><?php echo $winRate2; ?>%</strong></td></tr>
            <tr><td>Precisão Striking</td><td><?php echo $s1["precisao_striking"]; ?>%</td><td><?php echo $s2["precisao_striking"]; ?>%</td></tr>
            <tr><td>Golpes significativos/min</td><td><?php echo $s1["golpes_significativos_min"]; ?></td><td><?php echo $s2["golpes_significativos_min"]; ?></td></tr>
            <tr><td>Média quedas/luta</td><td><?php echo $s1["media_quedas_luta"]; ?></td><td><?php echo $s2["media_quedas_luta"]; ?></td></tr>
        </table>

    <?php elseif ($id1 > 0 || $id2 > 0): ?>
        <p>Selecione os DOIS lutadores para comparar.</p>
    <?php endif; ?>

    </main>
</div>

<?php if ($mostrarComparacao): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
Chart.defaults.color = "#999999";
Chart.defaults.borderColor = "#333333";

new Chart(document.getElementById("graficoRadar"), {
    type: "radar",
    data: {
        labels: <?php echo json_encode($radarLabels); ?>,
        datasets: [
            {
                label: <?php echo json_encode($lutador1["nome"]); ?>,
                data: <?php echo json_encode($radarDados1); ?>,
                borderColor: "#e10600",
                backgroundColor: "rgba(225, 6, 0, 0.2)",
                pointBackgroundColor: "#e10600",
            },
            {
                label: <?php echo json_encode($lutador2["nome"]); ?>,
                data: <?php echo json_encode($radarDados2); ?>,
                borderColor: "#3b82f6",
                backgroundColor: "rgba(59, 130, 246, 0.2)",
                pointBackgroundColor: "#3b82f6",
            }
        ]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        scales: {
            r: {
                min: 0,
                max: 100,
                ticks: { display: false },
                grid: { color: "#333333" },
                angleLines: { color: "#333333" },
                pointLabels: { color: "#cccccc", font: { size: 11 } }
            }
        }
    }
});
</script>
<?php endif; ?>
</body>
</html>

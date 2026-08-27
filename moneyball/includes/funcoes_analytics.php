<?php
// ============================================
// includes/funcoes_analytics.php
// Consultas agregadas usadas nos gráficos do
// Dashboard (RF18-22) - mysqli PROCEDURAL
// ============================================

// Conta quantos lutadores existem em cada categoria de peso
// (usado no gráfico de rosca "Distribuição por Categoria")
function distribuicaoPorCategoria($conexao) {
    $sql = "SELECT categoria_peso, COUNT(*) AS total
            FROM lutadores
            WHERE categoria_peso <> ''
            GROUP BY categoria_peso
            ORDER BY categoria_peso ASC";

    $resultado = mysqli_query($conexao, $sql);

    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}


// Conta lutadores por país
// (usado na lista "Lutadores por País")
function distribuicaoPorPais($conexao) {
    $sql = "SELECT pais, COUNT(*) AS total
            FROM lutadores
            WHERE pais <> ''
            GROUP BY pais
            ORDER BY total DESC";

    $resultado = mysqli_query($conexao, $sql);

    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}


// Soma o total de vitórias por temporada
// (usado no gráfico "Vitórias por Temporada")
function vitoriasPorTemporada($conexao) {
    $sql = "SELECT
                temporada,
                SUM(vitorias) AS total_vitorias
            FROM estatisticas
            GROUP BY temporada
            ORDER BY temporada ASC";

    $resultado = mysqli_query($conexao, $sql);

    if (!$resultado) {
        return [];
    }

    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}


// Calcula o win rate médio do PLANTEL INTEIRO, por temporada.
// Soma todas as vitórias/derrotas/empates de TODOS os lutadores
// numa mesma temporada, e tira o percentual geral.
// (usado no gráfico de linha "Evolução Win Rate Médio")
function evolucaoWinRatePorTemporada($conexao) {
    $sql = "SELECT
                temporada,
                SUM(vitorias) AS total_vitorias,
                SUM(derrotas) AS total_derrotas,
                SUM(empates) AS total_empates
            FROM estatisticas
            GROUP BY temporada
            ORDER BY temporada ASC";

    $resultado = mysqli_query($conexao, $sql);

    $linhas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

    $evolucao = [];

    foreach ($linhas as $linha) {

        $totalLutas =
            $linha["total_vitorias"] +
            $linha["total_derrotas"] +
            $linha["total_empates"];

        $winRate = $totalLutas > 0
            ? ($linha["total_vitorias"] / $totalLutas) * 100
            : 0;

        $evolucao[] = [
            "temporada" => $linha["temporada"],
            "win_rate"  => round($winRate, 1),
        ];
    }

    return $evolucao;
}


/**
 * CONSISTÊNCIA
 *
 * Um lutador é "consistente" quando o win rate dele não varia muito
 * de uma temporada pra outra. Calculamos isso com o DESVIO PADRÃO
 * do win rate entre as temporadas: quanto menor o desvio, mais
 * consistente o lutador é.
 *
 * consistencia = 100 - desvio_padrao(win_rate por temporada)
 *
 * Lutador com 1 temporada só (ou nenhuma) recebe consistência 100,
 * porque não há variação possível pra medir ainda.
 */
function calcularConsistencia($estatisticasDoLutador) {

    $winRatesPorTemporada = [];

    foreach ($estatisticasDoLutador as $temporada) {

        $totalLutas =
            $temporada["vitorias"] +
            $temporada["derrotas"] +
            $temporada["empates"];

        $winRatesPorTemporada[] = $totalLutas > 0
            ? ($temporada["vitorias"] / $totalLutas) * 100
            : 0;
    }

    $n = count($winRatesPorTemporada);

    if ($n <= 1) {
        return 100.0;
    }

    $media =
        array_sum($winRatesPorTemporada) / $n;

    $somaDosQuadrados = 0;

    foreach ($winRatesPorTemporada as $wr) {

        $somaDosQuadrados +=
            pow($wr - $media, 2);
    }

    $desvioPadrao =
        sqrt($somaDosQuadrados / $n);

    $consistencia =
        100 - $desvioPadrao;

    return round(
        max(0, $consistencia),
        1
    );
}


// Pega os top 5 do ranking (calcularRanking, já existente) e
// adiciona o campo "consistencia" de cada um.
// (usado no gráfico de barras "Top 5 — Score vs Consistência")
function top5ScoreConsistencia($conexao) {

    $ranking =
        calcularRanking($conexao, "Todos");

    $top5 =
        array_slice($ranking, 0, 5);

    foreach ($top5 as &$lutador) {

        $completo =
            buscarLutadorComEstatisticas(
                $conexao,
                $lutador["id"]
            );

        $lutador["consistencia"] =
            calcularConsistencia(
                $completo["estatisticas"]
            );
    }

    unset($lutador);

    return $top5;
}
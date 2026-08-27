<?php
// ============================================
// includes/funcoes_ranking.php
// Calcula o ranking de lutadores com base em
// um score de desempenho (RF16, RF17).
// mysqli PROCEDURAL
// ============================================

/**
 * FÓRMULA DO SCORE (documentar isso na apresentação!):
 *
 * score = (win_rate * 0.6) + (finish_rate * 0.4)
 *
 * - win_rate    = % de vitórias em relação ao total de lutas
 * - finish_rate = % das vitórias que terminaram em KO/TKO ou
 *                  finalização (em vez de decisão dos juízes)
 *
 * Por que esses pesos? Vitória é o critério mais importante
 * (peso 0.6), mas terminar a luta por finalização/KO é um sinal
 * extra de domínio técnico (peso 0.4) — não é só "ganhar", é
 * "ganhar com controle". Esse peso é uma escolha do grupo:
 * se quiserem outro critério, é só justificar na documentação.
 */
function calcularRanking($conexao, $categoria = "Todos") {
    $sql = "
        SELECT
            l.*,
            COALESCE(SUM(e.vitorias), 0)      AS total_vitorias,
            COALESCE(SUM(e.derrotas), 0)      AS total_derrotas,
            COALESCE(SUM(e.empates), 0)       AS total_empates,
            COALESCE(SUM(e.kos), 0)           AS total_kos,
            COALESCE(SUM(e.finalizacoes), 0)  AS total_finalizacoes
        FROM lutadores l
        LEFT JOIN estatisticas e ON e.lutador_id = l.id
    ";

    $tipos = "";
    $parametros = [];

    if ($categoria !== "Todos" && $categoria !== "") {
        $sql .= " WHERE l.categoria_peso = ?";
        $tipos .= "s";
        $parametros[] = $categoria;
    }

    $sql .= " GROUP BY l.id";

    $stmt = mysqli_prepare($conexao, $sql);
    if ($tipos !== "") {
        mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
    }
    mysqli_stmt_execute($stmt);
    $lutadores = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Calcula win_rate, finish_rate e score de cada lutador
    foreach ($lutadores as &$lutador) {
        $totalLutas = $lutador["total_vitorias"] + $lutador["total_derrotas"] + $lutador["total_empates"];

        $winRate = $totalLutas > 0
            ? ($lutador["total_vitorias"] / $totalLutas) * 100
            : 0;

        $finalizacoesEKos = $lutador["total_kos"] + $lutador["total_finalizacoes"];
        $finishRate = $lutador["total_vitorias"] > 0
            ? ($finalizacoesEKos / $lutador["total_vitorias"]) * 100
            : 0;

        $score = ($winRate * 0.6) + ($finishRate * 0.4);

        $lutador["total_lutas"] = $totalLutas;
        $lutador["win_rate"] = round($winRate, 1);
        $lutador["finish_rate"] = round($finishRate, 1);
        $lutador["score"] = round($score, 1);
    }
    unset($lutador); // boa prática após usar &$lutador em foreach

    // Ordena do maior score pro menor
    usort($lutadores, function ($a, $b) {
        return $b["score"] <=> $a["score"];
    });

    return $lutadores;
}

// Calcula os números gerais usados nos cards do Dashboard (RF16, RF18-22)
function calcularResumoGeral($conexao) {
    $ranking = calcularRanking($conexao, "Todos");

    $resumo = [
        "total_lutadores"    => count($ranking),
        "total_lutas"        => 0,
        "total_kos"          => 0,
        "total_finalizacoes" => 0,
        "melhor_lutador"     => null, // maior score
        "pior_lutador"       => null, // menor score
        "top5"               => array_slice($ranking, 0, 5),
    ];

    foreach ($ranking as $lutador) {
        $resumo["total_lutas"] += $lutador["total_lutas"];
        $resumo["total_kos"] += $lutador["total_kos"];
        $resumo["total_finalizacoes"] += $lutador["total_finalizacoes"];
    }

    if (count($ranking) > 0) {
        // $ranking já vem ordenado do maior score pro menor
        $resumo["melhor_lutador"] = $ranking[0];
        $resumo["pior_lutador"] = $ranking[count($ranking) - 1];
    }

    return $resumo;
}

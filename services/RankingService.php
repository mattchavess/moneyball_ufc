<?php
/**
 * Camada de Negócio - Ranking
 * Requisitos: RF16 (gerar ranking), RF17 (explicar a fórmula usada)
 *
 * FÓRMULA DO RANKING:
 * pontuacao = (vitorias * 10)
 *           - (derrotas * 4)
 *           + (taxa_acerto_golpes * 20)
 *           + (media_quedas_completadas * 3)
 *           + (bonus_finalizacao)
 *
 * Justificativa: pesa mais vitórias e finalizações, penaliza derrotas,
 * e usa taxa de acerto (não total bruto) para não favorecer quem tem mais lutas.
 */

function calcularRanking(PDO $pdo): array
{
    $sql = "
        SELECT
            l.id_lutador,
            l.nome,
            l.categoria_peso,
            l.vitorias,
            l.derrotas,
            COALESCE(SUM(e.golpes_acertados), 0) AS total_acertados,
            COALESCE(SUM(e.golpes_tentados), 0) AS total_tentados,
            COALESCE(AVG(e.quedas_completadas), 0) AS media_quedas,
            COALESCE(SUM(CASE WHEN e.resultado = 'vitoria' AND e.metodo IN ('nocaute','finalizacao') THEN 1 ELSE 0 END), 0) AS finalizacoes
        FROM lutadores l
        LEFT JOIN estatisticas e ON e.id_lutador = l.id_lutador
        GROUP BY l.id_lutador
    ";

    $stmt = $pdo->query($sql);
    $lutadores = $stmt->fetchAll();

    foreach ($lutadores as &$l) {
        $taxa_acerto = $l['total_tentados'] > 0
            ? $l['total_acertados'] / $l['total_tentados']
            : 0;

        $l['pontuacao'] = round(
            ($l['vitorias'] * 10)
            - ($l['derrotas'] * 4)
            + ($taxa_acerto * 20)
            + ($l['media_quedas'] * 3)
            + ($l['finalizacoes'] * 5),
            2
        );
    }
    unset($l);

    usort($lutadores, fn($a, $b) => $b['pontuacao'] <=> $a['pontuacao']);

    return $lutadores;
}

/**
 * RF21 - Top 5 lutadores do ranking.
 */
function top5Lutadores(PDO $pdo): array
{
    return array_slice(calcularRanking($pdo), 0, 5);
}
<?php

require_once __DIR__ . '/../config/conexao.php';

function calcularPontuacaoLutador(int $vitorias, int $derrotas, int $nocautes, int $finalizacoes): float
{
    return ($vitorias * 3)
         + ($nocautes * 1.5)
         + ($finalizacoes * 1.5)
         - ($derrotas * 1);
}

function buscarPontuacaoPorId(PDO $pdo, int $lutadorId): float
{
    $stmt = $pdo->prepare(
        'SELECT
            COALESCE(SUM(vitorias), 0)     AS total_vitorias,
            COALESCE(SUM(derrotas), 0)     AS total_derrotas,
            COALESCE(SUM(nocautes), 0)     AS total_nocautes,
            COALESCE(SUM(finalizacoes), 0) AS total_finalizacoes
         FROM estatisticas
         WHERE lutador_id = :id'
    );
    $stmt->execute(['id' => $lutadorId]);
    $s = $stmt->fetch();

    return calcularPontuacaoLutador(
        (int) $s['total_vitorias'],
        (int) $s['total_derrotas'],
        (int) $s['total_nocautes'],
        (int) $s['total_finalizacoes']
    );
}

function gerarRankingGeral(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT
            l.id,
            l.nome,
            l.categoria_peso,
            COALESCE(SUM(e.vitorias), 0)     AS total_vitorias,
            COALESCE(SUM(e.derrotas), 0)     AS total_derrotas,
            COALESCE(SUM(e.nocautes), 0)     AS total_nocautes,
            COALESCE(SUM(e.finalizacoes), 0) AS total_finalizacoes
         FROM lutadores l
         LEFT JOIN estatisticas e ON e.lutador_id = l.id
         GROUP BY l.id, l.nome, l.categoria_peso'
    );

    $lutadores = $stmt->fetchAll();

    foreach ($lutadores as &$l) {
        $l['pontuacao'] = calcularPontuacaoLutador(
            (int) $l['total_vitorias'],
            (int) $l['total_derrotas'],
            (int) $l['total_nocautes'],
            (int) $l['total_finalizacoes']
        );
    }
    unset($l);

    usort($lutadores, fn($a, $b) => $b['pontuacao'] <=> $a['pontuacao']);

    return $lutadores;
}

function top5Lutadores(PDO $pdo): array
{
    return array_slice(gerarRankingGeral($pdo), 0, 5);
}

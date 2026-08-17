<?php

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/ranking_service.php';

function melhorDesempenho(PDO $pdo): ?array
{
    $ranking = gerarRankingGeral($pdo);
    return $ranking[0] ?? null;
}

function piorDesempenho(PDO $pdo): ?array
{
    $ranking = gerarRankingGeral($pdo);
    return empty($ranking) ? null : end($ranking);
}

function calcularDesvioPadrao(array $valores): float
{
    $n = count($valores);
    if ($n === 0) {
        return 0.0;
    }

    $media = array_sum($valores) / $n;

    $somaQuadrados = 0;
    foreach ($valores as $v) {
        $somaQuadrados += ($v - $media) ** 2;
    }

    return sqrt($somaQuadrados / $n);
}

function maiorRegularidade(PDO $pdo): ?array
{
    $stmt = $pdo->query('SELECT lutador_id, vitorias, derrotas, empates FROM estatisticas');
    $linhas = $stmt->fetchAll();

    $porLutador = [];
    foreach ($linhas as $linha) {
        $totalLutas = $linha['vitorias'] + $linha['derrotas'] + $linha['empates'];
        if ($totalLutas === 0) {
            continue;
        }
        $taxaVitoria = $linha['vitorias'] / $totalLutas;
        $porLutador[$linha['lutador_id']][] = $taxaVitoria;
    }

    $melhorLutadorId = null;
    $menorDesvio = null;

    foreach ($porLutador as $lutadorId => $taxas) {
        if (count($taxas) < 2) {
            continue;
        }

        $desvio = calcularDesvioPadrao($taxas);

        if ($menorDesvio === null || $desvio < $menorDesvio) {
            $menorDesvio = $desvio;
            $melhorLutadorId = $lutadorId;
        }
    }

    if ($melhorLutadorId === null) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, nome, categoria_peso FROM lutadores WHERE id = :id');
    $stmt->execute(['id' => $melhorLutadorId]);
    $lutador = $stmt->fetch();

    $lutador['desvio_padrao_taxa_vitoria'] = round($menorDesvio, 4);

    return $lutador;
}

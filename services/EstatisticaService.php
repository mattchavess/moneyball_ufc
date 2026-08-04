<?php
require_once __DIR__ . '/RankingService.php';

/**
 * Camada de Negócio - Estatísticas gerais
 * Requisitos: RF19 (melhor desempenho), RF20 (pior desempenho), RF22 (maior regularidade)
 */

function melhorDesempenho(PDO $pdo): ?array
{
    $lista = calcularRanking($pdo);
    return $lista[0] ?? null;
}

function piorDesempenho(PDO $pdo): ?array
{
    $lista = calcularRanking($pdo);
    return end($lista) ?: null;
}

/**
 * RF22 - Lutador com maior regularidade.
 * FÓRMULA: desvio padrão da pontuação por luta.
 * Quanto MENOR o desvio, MAIS regular (desempenho consistente).
 * Considera só quem tem 2+ lutas registradas.
 */
function maiorRegularidade(PDO $pdo): ?array
{
    $sql = "
        SELECT
            l.id_lutador,
            l.nome,
            e.golpes_acertados,
            e.golpes_tentados,
            e.quedas_completadas
        FROM lutadores l
        JOIN estatisticas e ON e.id_lutador = l.id_lutador
    ";
    $linhas = $pdo->query($sql)->fetchAll();

    $porLutador = [];
    foreach ($linhas as $r) {
        $taxa = $r['golpes_tentados'] > 0
            ? $r['golpes_acertados'] / $r['golpes_tentados']
            : 0;
        $pontuacaoLuta = ($taxa * 20) + ($r['quedas_completadas'] * 3);
        $porLutador[$r['id_lutador']]['nome'] ??= $r['nome'];
        $porLutador[$r['id_lutador']]['pontuacoes'][] = $pontuacaoLuta;
    }

    $melhor = null;
    $menorDesvio = null;

    foreach ($porLutador as $id => $dados) {
        if (count($dados['pontuacoes']) < 2) {
            continue;
        }
        $desvio = calcularDesvioPadrao($dados['pontuacoes']);
        if ($menorDesvio === null || $desvio < $menorDesvio) {
            $menorDesvio = $desvio;
            $melhor = ['id_lutador' => $id, 'nome' => $dados['nome'], 'desvio_padrao' => round($desvio, 2)];
        }
    }

    return $melhor;
}

function calcularDesvioPadrao(array $valores): float
{
    $n = count($valores);
    $media = array_sum($valores) / $n;
    $somaQuadrados = array_reduce($valores, fn($carry, $v) => $carry + (($v - $media) ** 2), 0);
    return sqrt($somaQuadrados / $n);
}
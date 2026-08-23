<?php
// ============================================
// public/lutador_detalhe.php
// Mostra os dados completos de um lutador e
// suas estatísticas separadas por temporada.
// Acessado a partir do link "Ver detalhes" em lutadores.php
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";

// Pega o ID da URL (ex: lutador_detalhe.php?id=3)
$lutadorId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($lutadorId <= 0) {
    die("ID de lutador inválido.");
}

$lutador = buscarLutadorComEstatisticas($conexao, $lutadorId);

if (!$lutador) {
    die("Lutador não encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($lutador["nome"]); ?> - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <a href="lutadores.php">&larr; Voltar para Lutadores</a>
    |
    <a href="editar_lutador.php?id=<?php echo $lutadorId; ?>">Editar Lutador</a>

    <h1>
        <?php echo htmlspecialchars($lutador["bandeira_emoji"]); ?>
        <?php echo htmlspecialchars($lutador["nome"]); ?>
        <?php if ($lutador["apelido"]): ?>
            "<?php echo htmlspecialchars($lutador["apelido"]); ?>"
        <?php endif; ?>
    </h1>

    <p><?php echo htmlspecialchars($lutador["academia"]); ?> · <?php echo htmlspecialchars($lutador["pais"]); ?></p>

    <div class="info-grid">
        <div>
            <span>Categoria</span>
            <strong><?php echo htmlspecialchars($lutador["categoria_peso"]); ?></strong>
        </div>
        <div>
            <span>Estilo</span>
            <strong><?php echo htmlspecialchars($lutador["estilo_luta"]); ?></strong>
        </div>
        <div>
            <span>Idade</span>
            <strong><?php echo htmlspecialchars($lutador["idade"]); ?> anos</strong>
        </div>
        <div>
            <span>Altura</span>
            <strong><?php echo htmlspecialchars($lutador["altura_cm"]); ?> cm</strong>
        </div>
        <div>
            <span>Alcance</span>
            <strong><?php echo htmlspecialchars($lutador["alcance_cm"]); ?> cm</strong>
        </div>
    </div>

    <h2>Estatísticas por temporada</h2>

    <?php if (count($lutador["estatisticas"]) === 0): ?>
        <p>Nenhuma temporada cadastrada para este lutador ainda.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Temporada</th>
                <th>V</th>
                <th>D</th>
                <th>E</th>
                <th>KOs</th>
                <th>Finalizações</th>
                <th>Win Rate</th>
                <th>Precisão Striking</th>
                <th>Golpes/min</th>
                <th>Quedas/luta</th>
            </tr>
            <?php foreach ($lutador["estatisticas"] as $temporada):
                $totalLutasTemporada = $temporada["vitorias"] + $temporada["derrotas"] + $temporada["empates"];
                $winRateTemporada = $totalLutasTemporada > 0
                    ? round(($temporada["vitorias"] / $totalLutasTemporada) * 100, 1)
                    : 0;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($temporada["temporada"]); ?></td>
                    <td><?php echo $temporada["vitorias"]; ?></td>
                    <td><?php echo $temporada["derrotas"]; ?></td>
                    <td><?php echo $temporada["empates"]; ?></td>
                    <td><?php echo $temporada["kos"]; ?></td>
                    <td><?php echo $temporada["finalizacoes"]; ?></td>
                    <td><?php echo $winRateTemporada; ?>%</td>
                    <td><?php echo $temporada["precisao_striking"]; ?>%</td>
                    <td><?php echo $temporada["golpes_significativos_min"]; ?></td>
                    <td><?php echo $temporada["media_quedas_luta"]; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

</body>
</html>

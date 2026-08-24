<?php
// ============================================
// public/comparar.php
// Tela "Comparação de Lutadores" (RF23, RF24)
// Este é o DIFERENCIAL OBRIGATÓRIO do trabalho —
// comparar dois lutadores em temporadas específicas.
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";

// Lista simples de todos os lutadores, só pra preencher os <select>
$todosLutadores = listarLutadores($conexao);

$id1 = isset($_GET["lutador1"]) ? (int) $_GET["lutador1"] : 0;
$id2 = isset($_GET["lutador2"]) ? (int) $_GET["lutador2"] : 0;
$temporada1 = isset($_GET["temporada1"]) ? trim($_GET["temporada1"]) : "";
$temporada2 = isset($_GET["temporada2"]) ? trim($_GET["temporada2"]) : "";

$lutador1 = $id1 > 0 ? buscarLutadorPorTemporada($conexao, $id1, $temporada1) : null;
$lutador2 = $id2 > 0 ? buscarLutadorPorTemporada($conexao, $id2, $temporada2) : null;
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

    <!--
        Formulário simples: escolhe os 2 lutadores e,
        opcionalmente, a temporada de cada um (texto livre,
        ex: "2024"). Se deixar em branco, usa a temporada
        mais recente automaticamente.
    -->
    <form method="GET" action="comparar.php">
        <div>
            <label>Lutador 1</label>
            <select name="lutador1">
                <option value="">Selecione</option>
                <?php foreach ($todosLutadores as $l): ?>
                    <option value="<?php echo $l["id"]; ?>" <?php echo ($id1 === (int) $l["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($l["nome"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Temporada (deixe vazio p/ mais recente)</label>
            <input type="text" name="temporada1" value="<?php echo htmlspecialchars($temporada1); ?>" placeholder="ex: 2024">
        </div>

        <div>
            <label>Lutador 2</label>
            <select name="lutador2">
                <option value="">Selecione</option>
                <?php foreach ($todosLutadores as $l): ?>
                    <option value="<?php echo $l["id"]; ?>" <?php echo ($id2 === (int) $l["id"]) ? "selected" : ""; ?>>
                        <?php echo htmlspecialchars($l["nome"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Temporada (deixe vazio p/ mais recente)</label>
            <input type="text" name="temporada2" value="<?php echo htmlspecialchars($temporada2); ?>" placeholder="ex: 2024">
        </div>

        <button type="submit">Comparar</button>
    </form>

    <?php if ($lutador1 && $lutador2): ?>

        <?php if (!$lutador1["temporada_selecionada"]): ?>
            <p class="erro"><?php echo htmlspecialchars($lutador1["nome"]); ?> não tem estatística cadastrada
                <?php echo $temporada1 ? "para a temporada $temporada1" : ""; ?>.</p>
        <?php endif; ?>

        <?php if (!$lutador2["temporada_selecionada"]): ?>
            <p class="erro"><?php echo htmlspecialchars($lutador2["nome"]); ?> não tem estatística cadastrada
                <?php echo $temporada2 ? "para a temporada $temporada2" : ""; ?>.</p>
        <?php endif; ?>

        <?php if ($lutador1["temporada_selecionada"] && $lutador2["temporada_selecionada"]):
            $s1 = $lutador1["temporada_selecionada"];
            $s2 = $lutador2["temporada_selecionada"];

            $totalLutas1 = $s1["vitorias"] + $s1["derrotas"] + $s1["empates"];
            $totalLutas2 = $s2["vitorias"] + $s2["derrotas"] + $s2["empates"];
            $winRate1 = $totalLutas1 > 0 ? round(($s1["vitorias"] / $totalLutas1) * 100, 1) : 0;
            $winRate2 = $totalLutas2 > 0 ? round(($s2["vitorias"] / $totalLutas2) * 100, 1) : 0;
        ?>

        <h2>
            <?php echo htmlspecialchars($lutador1["nome"]); ?> (<?php echo htmlspecialchars($s1["temporada"]); ?>)
            vs
            <?php echo htmlspecialchars($lutador2["nome"]); ?> (<?php echo htmlspecialchars($s2["temporada"]); ?>)
        </h2>

        <table>
            <tr>
                <th>Estatística</th>
                <th><?php echo htmlspecialchars($lutador1["nome"]); ?></th>
                <th><?php echo htmlspecialchars($lutador2["nome"]); ?></th>
            </tr>
            <tr>
                <td>Altura</td>
                <td><?php echo $lutador1["altura_cm"]; ?> cm</td>
                <td><?php echo $lutador2["altura_cm"]; ?> cm</td>
            </tr>
            <tr>
                <td>Alcance</td>
                <td><?php echo $lutador1["alcance_cm"]; ?> cm</td>
                <td><?php echo $lutador2["alcance_cm"]; ?> cm</td>
            </tr>
            <tr>
                <td>Idade</td>
                <td><?php echo $lutador1["idade"]; ?> anos</td>
                <td><?php echo $lutador2["idade"]; ?> anos</td>
            </tr>
            <tr>
                <td>Vitórias</td>
                <td><?php echo $s1["vitorias"]; ?></td>
                <td><?php echo $s2["vitorias"]; ?></td>
            </tr>
            <tr>
                <td>Derrotas</td>
                <td><?php echo $s1["derrotas"]; ?></td>
                <td><?php echo $s2["derrotas"]; ?></td>
            </tr>
            <tr>
                <td>KOs</td>
                <td><?php echo $s1["kos"]; ?></td>
                <td><?php echo $s2["kos"]; ?></td>
            </tr>
            <tr>
                <td>Finalizações</td>
                <td><?php echo $s1["finalizacoes"]; ?></td>
                <td><?php echo $s2["finalizacoes"]; ?></td>
            </tr>
            <tr>
                <td>Win Rate</td>
                <td><strong><?php echo $winRate1; ?>%</strong></td>
                <td><strong><?php echo $winRate2; ?>%</strong></td>
            </tr>
            <tr>
                <td>Precisão Striking</td>
                <td><?php echo $s1["precisao_striking"]; ?>%</td>
                <td><?php echo $s2["precisao_striking"]; ?>%</td>
            </tr>
            <tr>
                <td>Golpes significativos/min</td>
                <td><?php echo $s1["golpes_significativos_min"]; ?></td>
                <td><?php echo $s2["golpes_significativos_min"]; ?></td>
            </tr>
            <tr>
                <td>Média quedas/luta</td>
                <td><?php echo $s1["media_quedas_luta"]; ?></td>
                <td><?php echo $s2["media_quedas_luta"]; ?></td>
            </tr>
        </table>

        <?php endif; ?>

    <?php elseif ($id1 > 0 || $id2 > 0): ?>
        <p>Selecione os DOIS lutadores para comparar.</p>
    <?php endif; ?>

    </main>
</div>
</body>
</html>

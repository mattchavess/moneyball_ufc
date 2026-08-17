<?php
/**
 * Consulta individual de lutador (RF13)
 * Lógica: Pedro | View: Alysson
 */
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/verifica_login.php';

$caminho_raiz = '../';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: pesquisa.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM lutadores WHERE id = :id");
$stmt->execute([':id' => $id]);
$lutador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lutador) {
    header('Location: pesquisa.php?erro=nao_encontrado');
    exit;
}

// Todas as estatísticas do lutador, por temporada (mais recente primeiro)
$stmt = $pdo->prepare("SELECT * FROM estatisticas WHERE lutador_id = :id ORDER BY temporada DESC");
$stmt->execute([':id' => $id]);
$estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lutador['nome']) ?> - Moneyball UFC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">

    <?php if (isset($_GET['criado'])): ?>
        <div class="alerta alerta-sucesso">Lutador cadastrado com sucesso!</div>
    <?php elseif (isset($_GET['atualizado'])): ?>
        <div class="alerta alerta-sucesso">Dados atualizados com sucesso!</div>
    <?php endif; ?>

    <div class="card">
        <h1><?= htmlspecialchars($lutador['nome']) ?></h1>
        <p>
            <strong>Categoria:</strong> <?= htmlspecialchars($lutador['categoria_peso']) ?> &nbsp;|&nbsp;
            <strong>Idade:</strong> <?= htmlspecialchars($lutador['idade']) ?> anos
            <?php if ($lutador['altura_cm']): ?> &nbsp;|&nbsp; <strong>Altura:</strong> <?= (int)$lutador['altura_cm'] ?> cm<?php endif; ?>
            <?php if ($lutador['alcance_cm']): ?> &nbsp;|&nbsp; <strong>Alcance:</strong> <?= (int)$lutador['alcance_cm'] ?> cm<?php endif; ?>
            <?php if ($lutador['cidade']): ?><br><strong>Naturalidade:</strong> <?= htmlspecialchars($lutador['cidade']) ?><?php if ($lutador['pais']): ?>, <?= htmlspecialchars($lutador['pais']) ?><?php endif; ?><?php endif; ?>
        </p>

        <div class="linha-botoes">
            <a href="editar.php?id=<?= $id ?>" class="btn btn-secundario">Editar</a>
            <a href="excluir.php?id=<?= $id ?>" class="btn btn-perigo">Excluir</a>
            <a href="pesquisa.php" class="btn btn-secundario">Voltar à lista</a>
        </div>
    </div>

    <div class="card">
        <h2>Estatísticas por temporada</h2>
        <?php if (empty($estatisticas)): ?>
            <p>Nenhuma estatística cadastrada para este lutador ainda.</p>
        <?php else: ?>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Temporada</th>
                            <th>Lutas</th>
                            <th>V</th>
                            <th>D</th>
                            <th>E</th>
                            <th>Nocautes</th>
                            <th>Finalizações</th>
                            <th>Decisões</th>
                            <th>Quedas (certas/tent.)</th>
                            <th>Golpes sig. (certos/tent.)</th>
                            <th>Tempo controle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estatisticas as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['temporada']) ?></td>
                            <td><?= (int)$e['lutas'] ?></td>
                            <td><?= (int)$e['vitorias'] ?></td>
                            <td><?= (int)$e['derrotas'] ?></td>
                            <td><?= (int)$e['empates'] ?></td>
                            <td><?= (int)$e['nocautes'] ?></td>
                            <td><?= (int)$e['finalizacoes'] ?></td>
                            <td><?= (int)$e['decisoes'] ?></td>
                            <td><?= (int)$e['quedas_certas'] ?>/<?= (int)$e['quedas_tentadas'] ?></td>
                            <td><?= (int)$e['golpes_significativos_certos'] ?>/<?= (int)$e['golpes_significativos_tentados'] ?></td>
                            <td><?= gmdate('i:s', (int)$e['tempo_controle_segundos']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

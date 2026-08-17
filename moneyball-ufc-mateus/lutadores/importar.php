<?php
/**
 * Importação de lutadores via Excel (RF12)
 * Lógica: Pedro | View: Alysson
 *
 * Usa a biblioteca PhpSpreadsheet (composer require phpoffice/phpspreadsheet).
 * Colunas esperadas na planilha (linha 1 = cabeçalho, ignorada):
 *   A: nome | B: categoria_peso | C: idade | D: altura_cm | E: alcance_cm
 *   F: cidade | G: pais | H: temporada | I: lutas | J: vitorias | K: derrotas
 *   L: empates | M: nocautes | N: finalizacoes | O: decisoes
 *   P: quedas_certas | Q: quedas_tentadas
 *   R: golpes_significativos_certos | S: golpes_significativos_tentados
 *   T: tempo_controle_segundos
 *
 * Apenas nome, categoria_peso e idade são obrigatórios; o resto pode vir vazio (assume 0/null).
 */
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/verifica_login.php';
require_once __DIR__ . '/../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

$caminho_raiz = '../';
$erro = null;
$resultado = null; // ['inseridos' => n, 'ignorados' => n, 'mensagens' => [...]]

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['planilha'])) {
    $arquivo = $_FILES['planilha'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erro = 'Falha no upload do arquivo.';
    } else {
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, ['xlsx', 'xls', 'csv'])) {
            $erro = 'Formato inválido. Envie um arquivo .xlsx, .xls ou .csv.';
        } else {
            try {
                $planilha = IOFactory::load($arquivo['tmp_name']);
                $aba = $planilha->getActiveSheet();
                $linhas = $aba->toArray(null, true, true, false);

                $inseridos = 0;
                $ignorados = 0;
                $mensagens = [];

                $sqlLutador = "INSERT INTO lutadores (nome, categoria_peso, idade, altura_cm, alcance_cm, cidade, pais)
                               VALUES (:nome, :categoria_peso, :idade, :altura_cm, :alcance_cm, :cidade, :pais)";
                $stmtLutador = $pdo->prepare($sqlLutador);

                $sqlEstat = "INSERT INTO estatisticas
                               (lutador_id, temporada, lutas, vitorias, derrotas, empates, nocautes,
                                finalizacoes, decisoes, quedas_certas, quedas_tentadas,
                                golpes_significativos_certos, golpes_significativos_tentados, tempo_controle_segundos)
                             VALUES
                               (:lutador_id, :temporada, :lutas, :vitorias, :derrotas, :empates, :nocautes,
                                :finalizacoes, :decisoes, :quedas_certas, :quedas_tentadas,
                                :golpes_sig_certos, :golpes_sig_tentados, :tempo_controle)";
                $stmtEstat = $pdo->prepare($sqlEstat);

                $pdo->beginTransaction();

                foreach ($linhas as $numeroLinha => $linha) {
                    if ($numeroLinha === 0) continue; // pula cabeçalho

                    $nome           = trim($linha[0] ?? '');
                    $categoria_peso = trim($linha[1] ?? '');
                    $idade          = trim((string)($linha[2] ?? ''));

                    // Linha em branco ou sem os campos obrigatórios -> ignora
                    if ($nome === '' || $categoria_peso === '' || $idade === '') {
                        if ($nome !== '' || $categoria_peso !== '' || $idade !== '') {
                            $ignorados++;
                            $mensagens[] = "Linha " . ($numeroLinha + 1) . ": campos obrigatórios ausentes, ignorada.";
                        }
                        continue;
                    }

                    $stmtLutador->execute([
                        ':nome'           => $nome,
                        ':categoria_peso' => $categoria_peso,
                        ':idade'          => (int)$idade,
                        ':altura_cm'      => $linha[3] !== '' ? (int)$linha[3] : null,
                        ':alcance_cm'     => $linha[4] !== '' ? (int)$linha[4] : null,
                        ':cidade'         => trim((string)($linha[5] ?? '')) ?: null,
                        ':pais'           => trim((string)($linha[6] ?? '')) ?: null,
                    ]);
                    $lutador_id = $pdo->lastInsertId();

                    $stmtEstat->execute([
                        ':lutador_id'          => $lutador_id,
                        ':temporada'           => trim((string)($linha[7] ?? '')) ?: date('Y'),
                        ':lutas'               => (int)($linha[8] ?? 0),
                        ':vitorias'            => (int)($linha[9] ?? 0),
                        ':derrotas'            => (int)($linha[10] ?? 0),
                        ':empates'             => (int)($linha[11] ?? 0),
                        ':nocautes'            => (int)($linha[12] ?? 0),
                        ':finalizacoes'        => (int)($linha[13] ?? 0),
                        ':decisoes'            => (int)($linha[14] ?? 0),
                        ':quedas_certas'       => (int)($linha[15] ?? 0),
                        ':quedas_tentadas'     => (int)($linha[16] ?? 0),
                        ':golpes_sig_certos'   => (int)($linha[17] ?? 0),
                        ':golpes_sig_tentados' => (int)($linha[18] ?? 0),
                        ':tempo_controle'      => (int)($linha[19] ?? 0),
                    ]);

                    $inseridos++;
                }

                $pdo->commit();

                $resultado = [
                    'inseridos' => $inseridos,
                    'ignorados' => $ignorados,
                    'mensagens' => $mensagens,
                ];
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $erro = 'Erro ao processar a planilha: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Lutadores - Moneyball UFC</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
    <h1>Importar Lutadores via Excel</h1>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if ($resultado): ?>
        <div class="alerta alerta-sucesso">
            Importação concluída: <?= $resultado['inseridos'] ?> lutador(es) inserido(s),
            <?= $resultado['ignorados'] ?> linha(s) ignorada(s).
        </div>
        <?php if (!empty($resultado['mensagens'])): ?>
            <div class="card">
                <strong>Detalhes:</strong>
                <ul>
                    <?php foreach ($resultado['mensagens'] as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <p>
            Envie uma planilha <strong>.xlsx</strong>, <strong>.xls</strong> ou <strong>.csv</strong> com a
            primeira linha de cabeçalho e as colunas na ordem:
            <em>nome, categoria_peso, idade, altura_cm, alcance_cm, cidade, pais, temporada, lutas,
            vitorias, derrotas, empates, nocautes, finalizacoes, decisoes, quedas_certas,
            quedas_tentadas, golpes_significativos_certos, golpes_significativos_tentados,
            tempo_controle_segundos</em>.
        </p>

        <form method="POST" action="importar.php" enctype="multipart/form-data">
            <div class="campo">
                <label for="planilha">Arquivo da planilha *</label>
                <input type="file" id="planilha" name="planilha" accept=".xlsx,.xls,.csv" required>
            </div>
            <div class="linha-botoes">
                <button type="submit" class="btn btn-primario">Importar</button>
                <a href="pesquisa.php" class="btn btn-secundario">Voltar</a>
            </div>
        </form>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>

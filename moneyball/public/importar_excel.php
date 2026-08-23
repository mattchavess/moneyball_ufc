<?php
// ============================================
// public/importar_excel.php
// Tela "Importar via Excel" (RF12)
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";     // usa cadastrarLutador() e adicionarEstatistica()
require_once __DIR__ . "/../includes/funcoes_importacao.php";

$resultadoImportacao = null;
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Confere se o upload deu certo antes de tentar ler o arquivo
    if (!isset($_FILES["planilha"]) || $_FILES["planilha"]["error"] !== UPLOAD_ERR_OK) {
        $erro = "Selecione um arquivo CSV válido.";
    } else {
        $extensao = strtolower(pathinfo($_FILES["planilha"]["name"], PATHINFO_EXTENSION));

        if ($extensao !== "csv") {
            $erro = "Por enquanto, só é aceito arquivo .csv. Se seu arquivo é .xlsx, abra no Excel e use 'Salvar como' → CSV.";
        } else {
            // $_FILES["planilha"]["tmp_name"] é o caminho temporário
            // onde o PHP guardou o arquivo enviado
            $resultadoImportacao = importarLutadoresCSV($conexao, $_FILES["planilha"]["tmp_name"], $_SESSION["usuario_id"]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Importar via Excel - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <h1>Importar via Excel</h1>
    <p>Importe múltiplos lutadores de uma vez a partir de uma planilha (CSV)</p>

    <div class="caixa-formato">
        <h3>Formato esperado da planilha</h3>
        <p>A primeira linha deve ter estes nomes de coluna (nessa ordem ou em qualquer ordem, desde que os nomes batam):</p>
        <code>
            nome, categoria_peso, idade, altura_cm, alcance_cm, pais,
            temporada, vitorias, derrotas, empates, kos, finalizacoes,
            media_quedas, tempo_medio_luta
        </code>
        <p>Um lutador com N temporadas deve ter N linhas com o mesmo nome. Os dados pessoais são repetidos em cada linha.</p>
    </div>

    <?php if ($erro): ?>
        <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <?php if ($resultadoImportacao !== null): ?>
        <p class="sucesso">
            <?php echo $resultadoImportacao["sucesso"]; ?> linha(s) importada(s) com sucesso.
        </p>

        <?php if (count($resultadoImportacao["erros"]) > 0): ?>
            <p class="erro"><?php echo count($resultadoImportacao["erros"]); ?> linha(s) com problema:</p>
            <ul>
                <?php foreach ($resultadoImportacao["erros"] as $msgErro): ?>
                    <li><?php echo htmlspecialchars($msgErro); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <a href="lutadores.php">Ver lutadores importados</a>
    <?php endif; ?>

    <!--
        enctype="multipart/form-data" é OBRIGATÓRIO em formulários
        que enviam arquivo — sem isso, o PHP não recebe o arquivo
        em $_FILES, só o nome dele como texto.
    -->
    <form method="POST" action="importar_excel.php" enctype="multipart/form-data">
        <input type="file" name="planilha" accept=".csv" required>
        <button type="submit">Importar</button>
    </form>

</body>
</html>

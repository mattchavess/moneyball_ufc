<?php
// ============================================
// public/cadastrar_lutador.php
// Tela "Cadastrar Novo Lutador" (RF09)
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Dados pessoais do lutador
    $dadosLutador = [
        "nome"           => trim($_POST["nome"]),
        "apelido"        => trim($_POST["apelido"]),
        "academia"       => trim($_POST["academia"]),
        "categoria_peso" => $_POST["categoria_peso"],
        "estilo_luta"    => $_POST["estilo_luta"],
        "pais"           => trim($_POST["pais"]),
        "bandeira_emoji" => trim($_POST["bandeira_emoji"]),
        "idade"          => (int) $_POST["idade"],
        "altura_cm"      => (int) $_POST["altura_cm"],
        "alcance_cm"     => (int) $_POST["alcance_cm"],
    ];

    // Validação simples dos campos obrigatórios (nome, categoria, país)
    if ($dadosLutador["nome"] === "" || $dadosLutador["categoria_peso"] === "" || $dadosLutador["pais"] === "") {
        $erro = "Preencha os campos obrigatórios: nome, categoria de peso e país.";
    } else {
        $lutadorId = cadastrarLutador($conexao, $dadosLutador, $_SESSION["usuario_id"]);

        if ($lutadorId) {
            // O formulário permite "+ Adicionar Temporada", então os campos
            // de temporada chegam como arrays: ano[], vitorias[], etc.
            $totalTemporadas = count($_POST["ano"]);

            for ($i = 0; $i < $totalTemporadas; $i++) {
                // Pula temporada em branco (usuário clicou em "+ Adicionar" mas não preencheu)
                if (trim($_POST["ano"][$i]) === "") {
                    continue;
                }

                $temporada = [
                    "ano"                        => trim($_POST["ano"][$i]),
                    "vitorias"                   => (int) $_POST["vitorias"][$i],
                    "derrotas"                   => (int) $_POST["derrotas"][$i],
                    "empates"                    => (int) $_POST["empates"][$i],
                    "kos"                        => (int) $_POST["kos"][$i],
                    "finalizacoes"               => (int) $_POST["finalizacoes"][$i],
                    "media_quedas_luta"          => (float) $_POST["media_quedas"][$i],
                    "tempo_medio_luta"           => (float) $_POST["tempo_medio"][$i],
                    "golpes_significativos_min"  => (float) $_POST["golpes_significativos"][$i],
                    "precisao_striking"          => (float) $_POST["precisao_striking"][$i],
                ];

                adicionarEstatistica($conexao, $lutadorId, $temporada);
            }

            $sucesso = "Lutador cadastrado com sucesso!";
        } else {
            $erro = "Erro ao cadastrar lutador. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Lutador - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <h1>Cadastrar Novo Lutador</h1>

    <?php if ($erro): ?>
        <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <p class="sucesso"><?php echo htmlspecialchars($sucesso); ?></p>
    <?php endif; ?>

    <form method="POST" action="cadastrar_lutador.php">

        <h2>Dados Pessoais</h2>

        <label>Nome completo *</label>
        <input type="text" name="nome" required>

        <label>Apelido / Nickname</label>
        <input type="text" name="apelido">

        <label>Academia / Gym</label>
        <input type="text" name="academia">

        <label>Categoria de peso *</label>
        <select name="categoria_peso" required>
            <option value="">Selecione</option>
            <option value="Peso Mosca">Peso Mosca</option>
            <option value="Peso Galo">Peso Galo</option>
            <option value="Peso Pena">Peso Pena</option>
            <option value="Peso Leve">Peso Leve</option>
            <option value="Meio-Médio">Meio-Médio</option>
            <option value="Médio">Médio</option>
            <option value="Meio-Pesado">Meio-Pesado</option>
            <option value="Peso Pesado">Peso Pesado</option>
        </select>

        <label>Estilo de luta</label>
        <select name="estilo_luta">
            <option value="Striker">Striker</option>
            <option value="Grappler">Grappler</option>
            <option value="Wrestler">Wrestler</option>
            <option value="Jiu-Jitsu">Jiu-Jitsu</option>
            <option value="Muay Thai">Muay Thai</option>
            <option value="Boxing">Boxing</option>
            <option value="MMA Completo">MMA Completo</option>
        </select>

        <label>País *</label>
        <input type="text" name="pais" required>

        <label>Emoji da bandeira</label>
        <input type="text" name="bandeira_emoji" placeholder="🇧🇷">

        <label>Idade</label>
        <input type="number" name="idade" min="0">

        <label>Altura (cm)</label>
        <input type="number" name="altura_cm" min="0">

        <label>Alcance (cm)</label>
        <input type="number" name="alcance_cm" min="0">

        <h2>Temporada #1</h2>
        <!--
            Os campos abaixo usam colchetes [] no "name" porque o
            formulário original do Figma tem "+ Adicionar Temporada",
            ou seja, pode ter VÁRIOS blocos de temporada.
            Com [], o PHP recebe tudo como array: $_POST["ano"][0], $_POST["ano"][1]...
            Se quiser adicionar mais blocos, duplique este <fieldset>
            trocando só os valores visuais — o "name" continua igual.
        -->
        <fieldset>
            <label>Ano</label>
            <input type="number" name="ano[]" value="<?php echo date('Y'); ?>">

            <label>Vitórias</label>
            <input type="number" name="vitorias[]" value="0">

            <label>Derrotas</label>
            <input type="number" name="derrotas[]" value="0">

            <label>Empates</label>
            <input type="number" name="empates[]" value="0">

            <label>KOs/TKOs</label>
            <input type="number" name="kos[]" value="0">

            <label>Finalizações</label>
            <input type="number" name="finalizacoes[]" value="0">

            <label>Média quedas/luta</label>
            <input type="number" step="0.01" name="media_quedas[]" value="0">

            <label>Tempo médio (min)</label>
            <input type="number" step="0.01" name="tempo_medio[]" value="0">

            <label>Golpes signif./min</label>
            <input type="number" step="0.01" name="golpes_significativos[]" value="0">

            <label>Precisão Striking (%)</label>
            <input type="number" step="0.01" name="precisao_striking[]" value="0">
        </fieldset>

        <button type="submit">Cadastrar Lutador</button>
        <a href="lutadores.php">Cancelar</a>
    </form>

</body>
</html>

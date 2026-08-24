<?php
// ============================================
// public/editar_lutador.php
// Tela de edição de lutador (RF10)
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";

$lutadorId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($lutadorId <= 0) {
    die("ID de lutador inválido.");
}

$erro = "";
$sucesso = "";

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    if ($dadosLutador["nome"] === "" || $dadosLutador["categoria_peso"] === "" || $dadosLutador["pais"] === "") {
        $erro = "Preencha os campos obrigatórios: nome, categoria de peso e país.";
    } else {
        $ok = atualizarLutador($conexao, $lutadorId, $dadosLutador);
        $sucesso = $ok ? "Lutador atualizado com sucesso!" : "";
        if (!$ok) {
            $erro = "Erro ao atualizar lutador. Tente novamente.";
        }
    }
}

// Busca os dados atuais pra preencher o formulário
$lutador = buscarLutadorComEstatisticas($conexao, $lutadorId);

if (!$lutador) {
    die("Lutador não encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar <?php echo htmlspecialchars($lutador["nome"]); ?> - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . "/../includes/menu.php"; ?>
    <main class="conteudo">

    <a href="lutador_detalhe.php?id=<?php echo $lutadorId; ?>">&larr; Voltar</a>

    <h1>Editar Lutador</h1>

    <?php if ($erro): ?>
        <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <p class="sucesso"><?php echo htmlspecialchars($sucesso); ?></p>
    <?php endif; ?>

    <form method="POST" action="editar_lutador.php?id=<?php echo $lutadorId; ?>">

        <label>Nome completo *</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($lutador["nome"]); ?>" required>

        <label>Apelido / Nickname</label>
        <input type="text" name="apelido" value="<?php echo htmlspecialchars($lutador["apelido"]); ?>">

        <label>Academia / Gym</label>
        <input type="text" name="academia" value="<?php echo htmlspecialchars($lutador["academia"]); ?>">

        <label>Categoria de peso *</label>
        <select name="categoria_peso" required>
            <?php
            $categorias = ["Peso Mosca", "Peso Galo", "Peso Pena", "Peso Leve", "Meio-Médio", "Médio", "Meio-Pesado", "Peso Pesado"];
            foreach ($categorias as $c):
            ?>
                <option value="<?php echo $c; ?>" <?php echo ($lutador["categoria_peso"] === $c) ? "selected" : ""; ?>>
                    <?php echo $c; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Estilo de luta</label>
        <select name="estilo_luta">
            <?php
            $estilos = ["Striker", "Grappler", "Wrestler", "Jiu-Jitsu", "Muay Thai", "Boxing", "MMA Completo"];
            foreach ($estilos as $e):
            ?>
                <option value="<?php echo $e; ?>" <?php echo ($lutador["estilo_luta"] === $e) ? "selected" : ""; ?>>
                    <?php echo $e; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>País *</label>
        <input type="text" name="pais" value="<?php echo htmlspecialchars($lutador["pais"]); ?>" required>

        <label>Emoji da bandeira</label>
        <input type="text" name="bandeira_emoji" value="<?php echo htmlspecialchars($lutador["bandeira_emoji"]); ?>">

        <label>Idade</label>
        <input type="number" name="idade" value="<?php echo (int) $lutador["idade"]; ?>" min="0">

        <label>Altura (cm)</label>
        <input type="number" name="altura_cm" value="<?php echo (int) $lutador["altura_cm"]; ?>" min="0">

        <label>Alcance (cm)</label>
        <input type="number" name="alcance_cm" value="<?php echo (int) $lutador["alcance_cm"]; ?>" min="0">

        <button type="submit">Salvar alterações</button>
    </form>

    <!--
        Exclusão fica num form separado, com method POST,
        porque excluir nunca deve ser feito por um link/GET
        (um GET pode ser disparado sem querer, ex: por um
        crawler ou pelo botão de "voltar" do navegador).
    -->
    <form method="POST" action="excluir_lutador.php" onsubmit="return confirm('Tem certeza que deseja excluir este lutador? Essa ação não pode ser desfeita.');">
        <input type="hidden" name="id" value="<?php echo $lutadorId; ?>">
        <button type="submit" class="botao-perigo">Excluir Lutador</button>
    </form>

    </main>
</div>
</body>
</html>

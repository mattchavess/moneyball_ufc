<?php
// ============================================
// public/lutadores.php
// Tela "Lutadores" (RF13, RF14, RF15)
// Listagem + busca por nome + filtro por categoria/estilo
// ============================================
require_once __DIR__ . "/../includes/verificar_sessao.php"; // exige login
require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes_lutador.php";

// Lê os filtros da URL (GET), com valor padrão vazio
$busca = isset($_GET["busca"]) ? trim($_GET["busca"]) : "";
$categoria = isset($_GET["categoria"]) ? $_GET["categoria"] : "Todos";
$estilo = isset($_GET["estilo"]) ? $_GET["estilo"] : "Todos";

$lutadores = listarLutadoresComFiltro($conexao, $busca, $categoria, $estilo);

$categorias = ["Todos", "Peso Mosca", "Peso Galo", "Peso Pena", "Peso Leve", "Meio-Médio", "Médio", "Meio-Pesado", "Peso Pesado"];
$estilos = ["Todos", "Striker", "Grappler", "Wrestler", "Jiu-Jitsu", "Muay Thai", "Boxing", "MMA Completo"];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lutadores - Moneyball</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . "/../includes/menu.php"; ?>
    <main class="conteudo">

    <h1>Lutadores</h1>
    <p><?php echo count($lutadores); ?> atletas encontrados</p>

    <!-- Formulário de busca e filtro. Usa GET para que o filtro
         apareça na URL (dá pra compartilhar o link já filtrado) -->
    <form method="GET" action="lutadores.php">
        <input
            type="text"
            name="busca"
            placeholder="Buscar por nome ou apelido..."
            value="<?php echo htmlspecialchars($busca); ?>"
        >

        <select name="categoria">
            <?php foreach ($categorias as $c): ?>
                <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($categoria === $c) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($c); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="estilo">
            <?php foreach ($estilos as $e): ?>
                <option value="<?php echo htmlspecialchars($e); ?>" <?php echo ($estilo === $e) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($e); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar</button>
        <a href="lutadores.php">Limpar filtros</a>
    </form>

    <!-- Cards dos lutadores -->
    <div class="grid-lutadores">
        <?php foreach ($lutadores as $lutador): ?>
            <div class="card-lutador">
                <p class="bandeira"><?php echo htmlspecialchars($lutador["bandeira_emoji"]); ?></p>
                <h3>
                    <?php echo htmlspecialchars($lutador["nome"]); ?>
                    <?php if ($lutador["apelido"]): ?>
                        "<?php echo htmlspecialchars($lutador["apelido"]); ?>"
                    <?php endif; ?>
                </h3>
                <p><?php echo htmlspecialchars($lutador["academia"]); ?></p>
                <span class="badge"><?php echo htmlspecialchars($lutador["categoria_peso"]); ?></span>

                <div class="stats-linha">
                    <span><?php echo $lutador["total_vitorias"]; ?> V</span>
                    <span><?php echo $lutador["total_derrotas"]; ?> D</span>
                    <span><?php echo $lutador["total_kos"]; ?> KO</span>
                </div>

                <p><?php echo $lutador["win_rate"]; ?>% WR · <?php echo $lutador["total_lutas"]; ?> lutas</p>

                <a href="lutador_detalhe.php?id=<?php echo $lutador["id"]; ?>">Ver detalhes</a>
            </div>
        <?php endforeach; ?>

        <?php if (count($lutadores) === 0): ?>
            <p>Nenhum lutador encontrado com esses filtros.</p>
        <?php endif; ?>
    </div>

    </main>
</div>
</body>
</html>

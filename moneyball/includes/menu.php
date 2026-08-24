<?php
// ============================================
// includes/menu.php
// Menu lateral (sidebar) compartilhado.
// Este arquivo espera que $_SESSION já exista
// (ou seja, deve ser incluído DEPOIS de
// verificar_sessao.php nas páginas).
//
// Como usar: dentro do <body> de cada página,
// logo no início:
//     <div class="layout">
//         <?php include __DIR__ . "/../includes/menu.php"; ?>
//         <main class="conteudo">
//         ... conteúdo da página ...
//         </main>
//     </div>
// ============================================

// Pega o nome do arquivo atual (ex: "dashboard.php") pra saber
// qual item do menu deve ficar destacado como "ativo"
$paginaAtual = basename($_SERVER["PHP_SELF"]);

function linkMenuAtivo($pagina, $paginaAtual) {
    return $pagina === $paginaAtual ? "link-ativo" : "";
}
?>
<aside class="sidebar">
    <div class="logo-sidebar">
        <h1>MONEYBALL</h1>
        <p>UFC Analytics Platform</p>
    </div>

    <nav>
        <p class="titulo-secao">Menu Principal</p>
        <a class="<?php echo linkMenuAtivo('dashboard.php', $paginaAtual); ?>" href="dashboard.php">Dashboard</a>
        <a class="<?php echo linkMenuAtivo('lutadores.php', $paginaAtual); ?>" href="lutadores.php">Lutadores</a>
        <a class="<?php echo linkMenuAtivo('ranking.php', $paginaAtual); ?>" href="ranking.php">Ranking</a>
        <a class="<?php echo linkMenuAtivo('comparar.php', $paginaAtual); ?>" href="comparar.php">Comparar</a>

        <p class="titulo-secao">Administração</p>
        <a class="<?php echo linkMenuAtivo('cadastrar_lutador.php', $paginaAtual); ?>" href="cadastrar_lutador.php">Cadastrar Lutador</a>

        <?php if ($_SESSION["usuario_tipo"] === "admin"): ?>
            <a class="<?php echo linkMenuAtivo('cadastrar_usuario.php', $paginaAtual); ?>" href="cadastrar_usuario.php">Usuários</a>
        <?php endif; ?>

        <a class="<?php echo linkMenuAtivo('importar_excel.php', $paginaAtual); ?>" href="importar_excel.php">Importar Excel</a>
    </nav>

    <div class="rodape-sidebar">
        <p><?php echo htmlspecialchars($_SESSION["usuario_nome"]); ?></p>
        <span><?php echo $_SESSION["usuario_tipo"] === "admin" ? "Administrador" : "Usuário Comum"; ?></span>
        <a href="logout.php">Sair do Sistema</a>
    </div>
</aside>

<?php
$paginaAtual = basename($_SERVER["PHP_SELF"]);

function linkMenuAtivo($pagina, $paginaAtual) {
    return $pagina === $paginaAtual ? "link-ativo" : "";
}
?>
<aside class="sidebar">
    <div class="logo-sidebar">
        <div class="logo-topo">
            <svg class="icone-logo" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 12.5V8a3 3 0 013-3h1a3 3 0 013 3v1.5" stroke="#e10600" stroke-width="1.6" stroke-linecap="round"/>
                <path d="M6 12.5h11a2.5 2.5 0 012.5 2.5v1a5 5 0 01-5 5H9a5 5 0 01-5-5v-1.5a2 2 0 012-2z" stroke="#e10600" stroke-width="1.6" stroke-linejoin="round"/>
                <path d="M10 12.5V10M13.5 12.5V10" stroke="#e10600" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <div>
                <h1>MONEYBALL</h1>
                <p>UFC Analytics Platform</p>
            </div>
        </div>
    </div>

    <nav>
        <p class="titulo-secao">Menu Principal</p>
        <a class="<?php echo linkMenuAtivo('dashboard.php', $paginaAtual); ?>" href="dashboard.php">
            <svg class="icone-menu" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="14" y="3" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="14" y="12" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="16" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="1.6"/></svg>
            Dashboard
        </a>
        <a class="<?php echo linkMenuAtivo('lutadores.php', $paginaAtual); ?>" href="lutadores.php">
            <svg class="icone-menu" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            Lutadores
        </a>
        <a class="<?php echo linkMenuAtivo('ranking.php', $paginaAtual); ?>" href="ranking.php">
            <svg class="icone-menu" viewBox="0 0 24 24" fill="none"><path d="M8 21V13M12 21V8M16 21v-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M6 8l6-5 6 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Ranking
        </a>
        <a class="<?php echo linkMenuAtivo('comparar.php', $paginaAtual); ?>" href="comparar.php">
            <svg class="icone-menu" viewBox="0 0 24 24" fill="none"><path d="M8 3v18M16 3v18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M3 8l5-5 5 5M21 16l-5 5-5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Comparar
        </a>

        <p class="titulo-secao">Administração</p>
        <a class="<?php echo linkMenuAtivo('cadastrar_lutador.php', $paginaAtual); ?>" href="cadastrar_lutador.php">
            <svg class="icone-menu" viewBox="0 0 24 24" fill="none"><circle cx="10" cy="8" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M3 20c0-3.3 3-5.8 7-5.8s7 2.5 7 5.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M18 9v5M15.5 11.5h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            Cadastrar Lutador
        </a>

        <?php if ($_SESSION["usuario_tipo"] === "admin"): ?>
            <a class="<?php echo linkMenuAtivo('cadastrar_usuario.php', $paginaAtual); ?>" href="cadastrar_usuario.php">
                <svg class="icone-menu" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="7" r="3.2" stroke="currentColor" stroke-width="1.6"/><circle cx="17" cy="9" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M3 20c0-3.3 2.7-5.8 6-5.8s6 2.5 6 5.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M15 15c2.5.3 4 2 4 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                Usuários
            </a>
        <?php endif; ?>

        <a class="<?php echo linkMenuAtivo('importar_excel.php', $paginaAtual); ?>" href="importar_excel.php">
            <svg class="icone-menu" viewBox="0 0 24 24" fill="none"><path d="M12 3v12m0 0l-4-4m4 4l4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            Importar Excel
        </a>
    </nav>

    <div class="rodape-sidebar">
        <p><?php echo htmlspecialchars($_SESSION["usuario_nome"]); ?></p>
        <span><?php echo $_SESSION["usuario_tipo"] === "admin" ? "Administrador" : "Usuário Comum"; ?></span>
        <a href="logout.php">Sair do Sistema</a>
    </div>
</aside>
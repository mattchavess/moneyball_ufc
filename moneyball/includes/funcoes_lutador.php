<?php
// ============================================
// includes/funcoes_lutador.php
// Funções relacionadas às tabelas "lutadores"
// e "estatisticas" (camada de Negócio)
// mysqli PROCEDURAL
// ============================================

// Cadastra um lutador e retorna o ID gerado (ou false se falhar)
function cadastrarLutador($conexao, $dados, $usuarioId) {
    $sql = "INSERT INTO lutadores
        (nome, apelido, academia, categoria_peso, estilo_luta, pais, bandeira_emoji, idade, altura_cm, alcance_cm, cadastrado_por)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssssiiii",
        $dados["nome"],
        $dados["apelido"],
        $dados["academia"],
        $dados["categoria_peso"],
        $dados["estilo_luta"],
        $dados["pais"],
        $dados["bandeira_emoji"],
        $dados["idade"],
        $dados["altura_cm"],
        $dados["alcance_cm"],
        $usuarioId
    );

    $sucesso = mysqli_stmt_execute($stmt);
    $novoId = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmt);

    return $sucesso ? $novoId : false;
}

// Adiciona uma estatística (temporada) para um lutador já cadastrado
function adicionarEstatistica($conexao, $lutadorId, $temporada) {
    $sql = "INSERT INTO estatisticas
        (lutador_id, temporada, vitorias, derrotas, empates, kos, finalizacoes, media_quedas_luta, tempo_medio_luta, golpes_significativos_min, precisao_striking)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "isiiiiidddd",
        $lutadorId,
        $temporada["ano"],
        $temporada["vitorias"],
        $temporada["derrotas"],
        $temporada["empates"],
        $temporada["kos"],
        $temporada["finalizacoes"],
        $temporada["media_quedas_luta"],
        $temporada["tempo_medio_luta"],
        $temporada["golpes_significativos_min"],
        $temporada["precisao_striking"]
    );

    $sucesso = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $sucesso;
}

// Lista todos os lutadores (usado na tela "Lutadores")
function listarLutadores($conexao) {
    $sql = "SELECT * FROM lutadores ORDER BY nome ASC";
    $resultado = mysqli_query($conexao, $sql);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

// Busca um lutador específico pelo ID, incluindo suas estatísticas
function buscarLutadorComEstatisticas($conexao, $lutadorId) {
    $sqlLutador = "SELECT * FROM lutadores WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sqlLutador);
    mysqli_stmt_bind_param($stmt, "i", $lutadorId);
    mysqli_stmt_execute($stmt);
    $lutador = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$lutador) {
        return null;
    }

    $sqlStats = "SELECT * FROM estatisticas WHERE lutador_id = ? ORDER BY temporada DESC";
    $stmt2 = mysqli_prepare($conexao, $sqlStats);
    mysqli_stmt_bind_param($stmt2, "i", $lutadorId);
    mysqli_stmt_execute($stmt2);
    $lutador["estatisticas"] = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt2);

    return $lutador;
}

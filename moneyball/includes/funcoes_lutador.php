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

// Lista lutadores com busca por nome/apelido, filtro por categoria
// e estilo, somando as estatísticas de todas as temporadas de cada um.
// (usado na tela "Lutadores" com os filtros do topo)
function listarLutadoresComFiltro($conexao, $busca = "", $categoria = "", $estilo = "") {
    $sql = "
        SELECT
            l.*,
            COALESCE(SUM(e.vitorias), 0)      AS total_vitorias,
            COALESCE(SUM(e.derrotas), 0)      AS total_derrotas,
            COALESCE(SUM(e.empates), 0)       AS total_empates,
            COALESCE(SUM(e.kos), 0)           AS total_kos,
            COALESCE(SUM(e.finalizacoes), 0)  AS total_finalizacoes
        FROM lutadores l
        LEFT JOIN estatisticas e ON e.lutador_id = l.id
        WHERE 1 = 1
    ";

    $tipos = "";
    $parametros = [];

    // Busca por nome OU apelido
    if ($busca !== "") {
        $sql .= " AND (l.nome LIKE ? OR l.apelido LIKE ?)";
        $termoBusca = "%" . $busca . "%";
        $tipos .= "ss";
        $parametros[] = $termoBusca;
        $parametros[] = $termoBusca;
    }

    // Filtro por categoria de peso
    if ($categoria !== "" && $categoria !== "Todos") {
        $sql .= " AND l.categoria_peso = ?";
        $tipos .= "s";
        $parametros[] = $categoria;
    }

    // Filtro por estilo de luta
    if ($estilo !== "" && $estilo !== "Todos") {
        $sql .= " AND l.estilo_luta = ?";
        $tipos .= "s";
        $parametros[] = $estilo;
    }

    $sql .= " GROUP BY l.id ORDER BY l.nome ASC";

    $stmt = mysqli_prepare($conexao, $sql);

    // bind_param só aceita parâmetros se houver pelo menos um;
    // por isso o if abaixo (evita erro quando nenhum filtro foi usado)
    if ($tipos !== "") {
        mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
    }

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $lutadores = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Calcula o win rate de cada lutador em PHP (não dá pra fazer
    // divisão segura direto no SQL sem risco de dividir por zero)
    foreach ($lutadores as &$lutador) {
        $totalLutas = $lutador["total_vitorias"] + $lutador["total_derrotas"] + $lutador["total_empates"];
        $lutador["win_rate"] = $totalLutas > 0
            ? round(($lutador["total_vitorias"] / $totalLutas) * 100, 1)
            : 0;
        $lutador["total_lutas"] = $totalLutas;
    }

    return $lutadores;
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

// Busca um lutador + a estatística de UMA temporada específica
// (usado na tela de Comparação - RF23/RF24, o diferencial obrigatório).
// Se $temporada vier vazio, usa a temporada mais recente cadastrada.
function buscarLutadorPorTemporada($conexao, $lutadorId, $temporada = "") {
    $lutador = buscarLutadorComEstatisticas($conexao, $lutadorId);

    if (!$lutador || count($lutador["estatisticas"]) === 0) {
        return $lutador; // sem estatística nenhuma cadastrada ainda
    }

    if ($temporada === "") {
        // estatisticas já vem ordenado DESC, então [0] é a mais recente
        $lutador["temporada_selecionada"] = $lutador["estatisticas"][0];
        return $lutador;
    }

    foreach ($lutador["estatisticas"] as $stat) {
        if ($stat["temporada"] === $temporada) {
            $lutador["temporada_selecionada"] = $stat;
            return $lutador;
        }
    }

    // Não achou a temporada pedida — devolve null nesse campo
    $lutador["temporada_selecionada"] = null;
    return $lutador;
}

// Atualiza os dados cadastrais de um lutador (RF10 - editar)
// Não mexe nas estatísticas — isso fica separado, feito por
// adicionarEstatistica() quando o usuário adiciona uma nova temporada.
function atualizarLutador($conexao, $lutadorId, $dados) {
    $sql = "UPDATE lutadores SET
        nome = ?, apelido = ?, academia = ?, categoria_peso = ?,
        estilo_luta = ?, pais = ?, bandeira_emoji = ?,
        idade = ?, altura_cm = ?, alcance_cm = ?
        WHERE id = ?";

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
        $lutadorId
    );

    $sucesso = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $sucesso;
}

// Exclui um lutador (RF11 - excluir).
// Como a FK estatisticas.lutador_id tem ON DELETE CASCADE,
// o próprio banco já apaga as estatísticas desse lutador junto —
// não precisa de um DELETE separado pra tabela estatisticas.
function excluirLutador($conexao, $lutadorId) {
    $sql = "DELETE FROM lutadores WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $lutadorId);
    $sucesso = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $sucesso;
}

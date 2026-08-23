<?php
// ============================================
// includes/funcoes_importacao.php
// Importação em massa de lutadores via CSV (RF12)
// mysqli PROCEDURAL
// ============================================

/**
 * Colunas esperadas no CSV (na primeira linha, como cabeçalho):
 * nome, categoria_peso, idade, altura_cm, alcance_cm, pais,
 * temporada, vitorias, derrotas, empates, kos, finalizacoes,
 * media_quedas, tempo_medio_luta
 *
 * Um lutador com N temporadas deve ter N linhas com o MESMO nome
 * (os dados pessoais são repetidos em cada linha, só a parte de
 * estatística muda). Isso é o que o enunciado da tela pede.
 */
function importarLutadoresCSV($conexao, $caminhoArquivo, $usuarioId) {
    $resultado = [
        "sucesso" => 0,
        "erros" => [],
    ];

    $arquivo = fopen($caminhoArquivo, "r");
    if (!$arquivo) {
        $resultado["erros"][] = "Não foi possível abrir o arquivo.";
        return $resultado;
    }

    // Lê a primeira linha (cabeçalho) pra saber a ordem das colunas
    $cabecalho = fgetcsv($arquivo, 0, ",");
    if (!$cabecalho) {
        $resultado["erros"][] = "Arquivo vazio ou inválido.";
        fclose($arquivo);
        return $resultado;
    }

    // Tira espaços em branco extras dos nomes das colunas
    $cabecalho = array_map("trim", $cabecalho);

    $numeroLinha = 1; // linha 1 já foi o cabeçalho

    while (($linha = fgetcsv($arquivo, 0, ",")) !== false) {
        $numeroLinha++;

        // Junta o cabeçalho com os valores da linha, tipo:
        // ["nome" => "João", "categoria_peso" => "Peso Leve", ...]
        // array_combine exige que as duas listas tenham o mesmo tamanho
        if (count($cabecalho) !== count($linha)) {
            $resultado["erros"][] = "Linha $numeroLinha: número de colunas não bate com o cabeçalho.";
            continue;
        }
        $dados = array_combine($cabecalho, $linha);

        // Validação mínima
        if (empty($dados["nome"]) || empty($dados["categoria_peso"])) {
            $resultado["erros"][] = "Linha $numeroLinha: faltando nome ou categoria_peso.";
            continue;
        }

        // Verifica se já existe um lutador com esse nome
        // (se já existe, reaproveita o ID em vez de duplicar o cadastro)
        $lutadorId = buscarLutadorIdPorNome($conexao, $dados["nome"]);

        if (!$lutadorId) {
            $novoLutador = [
                "nome"           => trim($dados["nome"]),
                "apelido"        => "",
                "academia"       => "",
                "categoria_peso" => trim($dados["categoria_peso"]),
                "estilo_luta"    => "",
                "pais"           => trim($dados["pais"] ?? ""),
                "bandeira_emoji" => "",
                "idade"          => (int) ($dados["idade"] ?? 0),
                "altura_cm"      => (int) ($dados["altura_cm"] ?? 0),
                "alcance_cm"     => (int) ($dados["alcance_cm"] ?? 0),
            ];
            $lutadorId = cadastrarLutador($conexao, $novoLutador, $usuarioId);
        }

        if (!$lutadorId) {
            $resultado["erros"][] = "Linha $numeroLinha: erro ao cadastrar o lutador '{$dados['nome']}'.";
            continue;
        }

        // Adiciona a temporada (estatística) dessa linha
        if (!empty($dados["temporada"])) {
            $temporada = [
                "ano"                       => trim($dados["temporada"]),
                "vitorias"                  => (int) ($dados["vitorias"] ?? 0),
                "derrotas"                  => (int) ($dados["derrotas"] ?? 0),
                "empates"                   => (int) ($dados["empates"] ?? 0),
                "kos"                       => (int) ($dados["kos"] ?? 0),
                "finalizacoes"              => (int) ($dados["finalizacoes"] ?? 0),
                "media_quedas_luta"         => (float) ($dados["media_quedas"] ?? 0),
                "tempo_medio_luta"          => (float) ($dados["tempo_medio_luta"] ?? 0),
                "golpes_significativos_min" => 0,
                "precisao_striking"         => 0,
            ];
            // Se já existir estatística pra esse lutador+temporada, o
            // UNIQUE KEY do banco (lutador_id, temporada) vai bloquear
            // duplicata — isso é proposital, evita importar 2x sem querer.
            adicionarEstatistica($conexao, $lutadorId, $temporada);
        }

        $resultado["sucesso"]++;
    }

    fclose($arquivo);
    return $resultado;
}

// Busca o ID de um lutador pelo nome exato (usado pra não duplicar
// cadastro quando o mesmo lutador aparece em várias linhas do CSV)
function buscarLutadorIdPorNome($conexao, $nome) {
    $sql = "SELECT id FROM lutadores WHERE nome = ? LIMIT 1";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "s", $nome);
    mysqli_stmt_execute($stmt);
    $linha = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    return $linha ? $linha["id"] : null;
}

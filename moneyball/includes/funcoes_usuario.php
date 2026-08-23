<?php
// ============================================
// includes/funcoes_usuario.php
// Funções relacionadas à tabela "usuarios"
// (camada de Negócio) - mysqli PROCEDURAL
// ============================================

// Busca um usuário pelo e-mail. Retorna um array associativo ou null.
function buscarUsuarioPorEmail($conexao, $email) {
    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
    return $usuario; // false/null se não encontrar
}

// Confere e-mail + senha. Retorna o array do usuário se OK, ou false se falhar.
function validarLogin($conexao, $email, $senhaDigitada) {
    $usuario = buscarUsuarioPorEmail($conexao, $email);

    if (!$usuario) {
        return false; // e-mail não existe
    }

    if (!password_verify($senhaDigitada, $usuario["senha"])) {
        return false; // senha incorreta
    }

    return $usuario;
}

// Cadastra um novo usuário. Só deve ser chamada se quem estiver
// logado for "admin" (RF06) — essa checagem é feita em cadastrar_usuario.php,
// esta função só cuida do INSERT.
function cadastrarUsuario($conexao, $nome, $email, $senhaTextoPuro, $tipo) {
    $hashSenha = password_hash($senhaTextoPuro, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $nome, $email, $hashSenha, $tipo);
    $sucesso = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $sucesso;
}

// Lista todos os usuários (para a tela "Usuários")
function listarUsuarios($conexao) {
    $sql = "SELECT id, nome, email, tipo, criado_em FROM usuarios ORDER BY criado_em DESC";
    $resultado = mysqli_query($conexao, $sql);
    return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
}

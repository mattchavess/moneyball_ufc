<?php
// ============================================
// logout.php
// Encerra a sessão do usuário (RF04)
// ============================================
session_start();
session_unset();     // apaga todas as variáveis de sessão
session_destroy();   // destrói a sessão no servidor

header("Location: login.php");
exit;

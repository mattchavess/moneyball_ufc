<?php
// ============================================
// gerar_hash_senha.php
// Rode este arquivo UMA VEZ no navegador
// (ex: http://localhost/moneyball/gerar_hash_senha.php)
// pra gerar o hash da senha do seu admin inicial.
// Depois copie o resultado no INSERT do usuário admin
// e APAGUE este arquivo (não deixe em produção).
// ============================================

$senha = "admin123"; // troque pela senha que você quer usar

echo password_hash($senha, PASSWORD_DEFAULT);

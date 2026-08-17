# Moneyball UFC — Parte do Mateus (Backend Core, Segurança e Lógica de Negócio)

Branch sugerida: `feature-mateus-backend-core`

## Arquivos

- `database/migration.sql`
- `config/conexao.php`
- `auth/login.php`
- `auth/cadastro_usuario.php`
- `auth/logout.php`
- `includes/verifica_login.php`
- `services/ranking_service.php`
- `services/estatistica_service.php`

## Como testar localmente

1. Importe `database/migration.sql` no phpMyAdmin.
2. Ajuste `DB_USER`/`DB_PASS` em `config/conexao.php` se necessário.
3. Acesse `auth/login.php` — login: `admin@moneyballufc.com` / senha `admin123`.

## Como usar os services

```php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/services/ranking_service.php';
require_once __DIR__ . '/services/estatistica_service.php';

$pdo = conectarBanco();

$top5 = top5Lutadores($pdo);
$rankingCompleto = gerarRankingGeral($pdo);
$melhor = melhorDesempenho($pdo);
$pior = piorDesempenho($pdo);
$maisRegular = maiorRegularidade($pdo);
```

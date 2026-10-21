# Ouvidoria – notas para agentes

## Verificação
- Testes: `php artisan test` (SQLite em memória, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`).
- Estilo: `vendor/bin/pint --dirty`.

## Convenções
- Banco local é MySQL: nomes de índices/FKs compostos devem ter no máximo 64 caracteres (defina nomes curtos explicitamente).
- Em `Rule::exists(...)->where()`, use `1`/`0` em colunas booleanas (não `true`/`false`).
- Encaminhamento interno (setores, encaminhamentos, mensagens) é exclusivo do painel `/admin`; nunca carregar essas relações nas telas públicas (`/acompanhar`).
- Pontos de integração futura: `App\Support\ResponsavelAtual` (SSO) e `ComunicacaoInternaInterface` (Microsoft Graph).

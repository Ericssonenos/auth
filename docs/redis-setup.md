# Redis - Configuração e Verificação para produção

Objetivo

Este documento descreve as variáveis de ambiente necessárias, passos de configuração e comandos de verificação para garantir que o Laravel use Redis como store de cache em produção (propagação entre instâncias).

Checklist rápido

- [ ] Ter um servidor Redis acessível a partir das instâncias de aplicação.
- [ ] Ter o cliente PHP para Redis instalado (phpredis) ou `predis/predis` via Composer.
- [ ] Definir as variáveis .env listadas abaixo em produção.
- [ ] Garantir `APP_ENV=production` e (opcional) `CACHE_STORE=redis` no .env.
- [ ] Executar `php artisan config:cache`/`php artisan route:cache` após mudanças de config.

Variáveis .env necessárias

- REDIS_CLIENT (phpredis ou predis) — ex: `REDIS_CLIENT=phpredis`
- REDIS_URL (opcional — URL completa)
- REDIS_HOST (ex: `127.0.0.1` ou `redis.internal`)
- REDIS_PORT (ex: `6379`)
- REDIS_PASSWORD (se aplicável)
- REDIS_DB (ex: `0`)
- REDIS_CACHE_DB (ex: `1`)

Exemplo de trecho para `.env` em produção

APP_ENV=production
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis.example.internal
REDIS_PORT=6379
REDIS_PASSWORD=supersecret    # remover ou deixar vazio se não houver senha
REDIS_DB=0
REDIS_CACHE_DB=1

Arquivos do Laravel que o projeto já usa

- `config/cache.php` — no repositório já foi ajustado para usar `redis` quando `APP_ENV=production`.
- `config/database.php` — contém as definições `redis.default` e `redis.cache` que leem as variáveis acima.

Instalar cliente PHP para Redis

- Recomendado: instalar a extensão `phpredis` no PHP (mais performática).
- Alternativa: usar `predis/predis` via Composer (não precisa de extensão PHP):

```bash
composer require predis/predis
```

Verificações rápidas (no servidor de aplicação)

- Verificar extensão PHP (phpredis):

Windows (PowerShell):

```powershell
php -m | Select-String redis
```

Linux/macOS:

```bash
php -m | grep redis
```

- Testar conexão ao servidor Redis com `redis-cli` (caso disponível):

```bash
redis-cli -h <REDIS_HOST> -p <REDIS_PORT> ping
# deve responder: PONG
```

- Teste via Artisan Tinker (verifica saneamento do cache driver):

```bash
php artisan tinker
>>> Cache::store('redis')->put('redis_test_key', 'ok', 60);
>>> Cache::store('redis')->get('redis_test_key'); // deve retornar 'ok'
```

- Teste com Artisan (forçar cache config e limpar):

```bash
php artisan config:clear
php artisan config:cache
```

Como o projeto usa a chave `perms_version_user_{id}`

- Em produção, ao garantir que o `cache` usa Redis, a chave `perms_version_user_{id}` será visível entre todas as instâncias da aplicação.
- Fluxo recomendado:
  1. Endpoint que altera permissões grava `Cache::put("perms_version_user_{$id}", time())`.
  2. Middleware lê `Cache::get("perms_version_user_{$id}")` e atualiza sessão se necessário.

Boas práticas e recomendações

- Use autenticação e rede privada entre app e Redis (VPN, VPC ou túnel) se Redis for remoto.
- Considere habilitar TLS para conexões Redis externas.
- Configure `CACHE_PREFIX`/`REDIS_PREFIX` para evitar colisões com outros aplicativos.
- Em ambientes com alta disponibilidade, use Redis Sentinel ou Cluster conforme necessidade.

Solução alternativa (se não for possível usar Redis)

- Em ambientes de uma única instância, `file` pode ser suficiente (default em dev).
- Para múltiplas instâncias sem Redis, considerar um banco de dados centralizado para controle de versão (tabela `permissions_versions`) e usar esse valor para sincronizar sessões.

Troubleshooting rápido

- Se `Cache::store('redis')->get()` retornar `null`:
  - Verifique `.env` (host/port/password)
  - Verifique se a extensão phpredis está disponível ou `predis` instalado
  - Teste `redis-cli` a partir da instância
  - Verifique `storage/logs/laravel.log` para erros de conexão

---

Se quiser, eu posso:

- Adicionar este arquivo ao README principal (ou referenciá-lo),
- Gerar um pequeno script de teste automático em `scripts/` que valida conexão e caching, ou
- Instruir como configurar TLS / Sentinel para Redis no ambiente de produção.

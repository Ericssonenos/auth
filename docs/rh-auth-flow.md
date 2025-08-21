# Fluxo de Autenticação / Autorização (RH)

Este documento descreve, de forma concisa, o fluxo de autenticação/autorização implementado na aplicação — do model até o `Gate` e o middleware RH. Está escrito em português e foca nas responsabilidades, contratos e pontos sensíveis (cache, invalidação, edge-cases).

## Objetivo
Centralizar a lógica de autorização para que as views e controllers apenas perguntem "o usuário tem a permissão X?" usando `@can('PERM_X')` (Blade) ou `Gate::allows('PERM_X')` (PHP), enquanto o middleware popula e mantém em cache as permissões por matrícula na `session`.

## Componentes principais
- Models: `usuario`, `grupo`, `permissao` — métodos para CRUD e para buscar permissões ativas de uma matrícula.
- Middleware: `App\Http\Middleware\RhPermissionMiddleware` — carrega permissões para a matrícula atual e grava na session.
- Gate: `Gate::before(...)` registrado em `App\Providers\AppServiceProvider` — delega a decisão de autorização lendo a session.
- Views: usam `@can('PERM_COD')` para autorizar a renderização de blocos.
- Controllers: ao alterar estruturas de permissão chamam `Session::forget('rh_permissions.{matricula}')` para invalidar cache.

## Sequência de uma requisição (resumida)
1. Cliente faz requisição HTTP com header `X-Matricula: <MATRICULA>` (ou outro mecanismo que popule `session('rh_matricula')`).
2. Middleware `RhPermissionMiddleware` é executado (aplicado nas rotas com alias `rh.auth`):
   - Obtém a matrícula (header ou outra fonte);
   - Chama model `usuario->ObterPermissoesMatricula(['matricula_cod' => $mat])` que retorna lista de códigos (`txt_cod_permissao`);
   - Grava em session: `session(['rh_permissions.'.$mat => $arrayDePermissoes])` e `session(['rh_matricula' => $mat])`;
   - Segue o request pipeline.
3. Quando o framework avalia uma autorização (por exemplo `@can('PERM_X')`):
   - O Laravel chama o Gate; o `Gate::before` registrado recebe ($user, $ability).
   - Nosso `Gate::before` lê `session('rh_matricula')` e `session('rh_permissions.{mat}')` e retorna `true` se a permissão existir, `null` caso contrário (permite que outras políticas decidam).
4. Se autorizado, a view/renderização segue; se negado, o bloco `@can` não é exibido e `abort(403)` pode ser usado em controllers.

## Formato / contrato dos dados
- session key: `rh_permissions.{matricula}` → array de strings, ex: `['PERM_GERENCIAR_PERMISSOES', 'PERM_LER_RELATORIOS']`.
- session key: `rh_matricula` → string com a matrícula atual.
- Model `usuario->ObterPermissoesMatricula` → retorno: `array` de linhas/strings com códigos de permissão (somente ativas: `dat_cancelamento_em IS NULL`).

## Invalidação de cache
- Controladores que criam/atualizam/removem permissões/grupos devem limpar a session relevante:
  - Se alteração afeta matrícula específica: `Session::forget("rh_permissions.{$mat}")`.
  - Se alteração é global (ex.: criar nova permissão): limpar `rh_permissions.{mat}` para a matrícula atual ou todas (conforme política).
- Essa invalidação garante que na próxima requisição o middleware recarregue permissões do BD.

## Boas práticas / recomendações
- Use `@can('PERM_X')` nas views e `Gate::allows('PERM_X')` ou `authorize()` nos controllers — evita lógica duplicada nas views.
- Middleware deve ser a fonte de verdade para popular a session; o Gate só consulta a session (ou delega para um serviço central se preferir).
- Cuidado ao acessar `request()` ou `session()` em `AppServiceProvider::boot()` — nem sempre essas estruturas estão disponíveis (console, queue). Prefira view composers ou o middleware para injetar dados por request.
- Mantenha correspondência exata entre os códigos usados em `@can('...')` e os valores em `txt_cod_permissao` no banco.
- Para multi-tenant/concorrência: prefira invalidar por matrícula em vez de varrer todas as sessions.

## Edge cases
- Sem matrícula no header/session → o Gate retorna `null` (não decide) e a autorização segue o fluxo padrão (negada por falta de contexto).
- Sessão corrompida / limpeza — garanta fallback vazio `session('rh_permissions.'.$mat, [])`.
- Efeitos de cache: se múltiplos sistemas atualizam permissões, considere um mecanismo de publicação (webhook/pubsub) para invalidar cache remoto.

## Exemplo mínimo (pseudocódigo)

Middleware:
```php
$mat = request()->header('X-Matricula');
$perms = Usuario::ObterPermissoesMatricula(['matricula_cod' => $mat]);
session(["rh_permissions.$mat" => $perms, 'rh_matricula' => $mat]);
```

Gate (AppServiceProvider):
```php
Gate::before(function ($_user, $ability) {
    $mat = session('rh_matricula');
    if (empty($mat)) return null;
    $perms = session("rh_permissions.{$mat}", []);
    return in_array($ability, $perms) ? true : null;
});
```

Controller que altera permissões:
```php
// após alteração
Session::forget("rh_permissions.{$mat}");
```

---

Se quiser, eu posso:
- Gerar um diagrama simples (PNG/SVG) do fluxo;
- Converter este documento em `README` em português mais longo com exemplos de requests/cURL e testes automáticos.

Arquivo criado: `docs/rh-auth-flow.md` — verifique e diga se quer alterações. 

# Fluxo de Autenticação / Autorização (RH)

Este documento descreve, de forma concisa, o fluxo de autenticação/autorização implementado na aplicação — do model até o `Gate` e o middleware RH. 

## Objetivo
Centralizar a lógica de autorização para que as views e controllers apenas perguntem "o usuário tem a permissão X?" usando `@can('PERM_X')` (Blade) ou `Gate::allows('PERM_X')` (PHP), enquanto o middleware popula e mantém em cache as permissões por is_usuario na `session`.





## Formato / contrato dos dados
- session key: `list_Permissoes_session.{usuario}` → array de strings, ex: `['PERM_GERENCIAR_PERMISSOES', 'PERM_LER_RELATORIOS']`.
- session key: `id_Usuario_session` → string com a is_usuario atual.
- Model `usuario->ObterPermissoesUsuario` → retorno: `array` de linhas/strings com códigos de permissão (somente ativas: `dat_cancelamento_em IS NULL`).

## Invalidação de cache
- Controladores que criam/atualizam/removem permissões/grupos devem limpar a session relevante:
  - Se alteração afeta is_usuario específica: `Session::forget("list_Permissoes_session.{$mat}")`.
  - Se alteração é global (ex.: criar nova permissão): limpar `list_Permissoes_session.{mat}` para a is_usuario atual ou todas (conforme política).
- Essa invalidação garante que na próxima requisição o middleware recarregue permissões do BD.

## Boas práticas / recomendações
- Use `@can('PERM_X')` nas views e `Gate::allows('PERM_X')` ou `authorize()` nos controllers — evita lógica duplicada nas views.
- Middleware deve ser a fonte de verdade para popular a session; o Gate só consulta a session (ou delega para um serviço central se preferir).
- Cuidado ao acessar `request()` ou `session()` em `AppServiceProvider::boot()` — nem sempre essas estruturas estão disponíveis (console, queue). Prefira view composers ou o middleware para injetar dados por request.
- Mantenha correspondência exata entre os códigos usados em `@can('...')` e os valores em `cod_permissao` no banco.
- Para multi-tenant/concorrência: prefira invalidar por is_usuario em vez de varrer todas as sessions.



## Exemplo mínimo (pseudocódigo)

Middleware:
```php
$usuario = request()->header('X-id_Usuario');
$Permissao = Usuario::ObterPermissoesUsuario(['Usuario_id' => $usuario]);
session(["list_Permissoes_session.$usuario" => $Permissao, 'id_Usuario_session' => $usuario]);
```

Gate (AppServiceProvider):
```php
Gate::before(function ($_user, $ability) {
    $usuario = session('id_Usuario_session');
    if (empty($usuario)) return null;
    $permissao = session("list_Permissoes_session.{$usuario}", []);
    return in_array($ability, $permissao) ? true : null;
});
```

Controller que altera permissões:
```php
// após alteração
Session::forget("list_Permissoes_session.{$usuario}");
``` 

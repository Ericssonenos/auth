# Middleware de Controle de Acesso de Usuário

## Visão Geral

O `UsuarioMiddleware` é responsável por controlar o acesso a rotas baseado nas permissões do usuário autenticado. Ele utiliza o `usuarioServices` registrado como singleton no sistema.

## Características Principais

### 1. **Detecção Automática de Permissões**
O middleware pode detectar automaticamente qual permissão é necessária baseado na rota atual, seguindo esta ordem de prioridade:

1. **Método HTTP + URI** (fallback)
2. **URI formatada** (casos simples)

### 2. **Múltiplas Permissões**
Permite especificar múltiplas permissões, onde o usuário precisa ter pelo menos uma delas (operação OR).

### 3. **Respostas Contextualizadas**
- **API (JSON)**: Retorna resposta estruturada com detalhes da falha
- **Web**: Redireciona com mensagens na sessão

## Como Usar

### Detecção Automática
```php
// O middleware tentará detectar automaticamente a permissão necessária
Route::get('/usuarios', [UserController::class, 'index'])
    ->name('usuarios.listar')
    ->middleware('usuario');
// Tentará: 'N_USUARIOS.LISTA', 'R_GET_USUARIOS'
```


## Estratégias de Detecção

### 1. Nome da Rota (Prioridade Máxima)
```php
Route::post('/usuarios/criar', [UserController::class, 'create'])
    ->name('usuarios.criar')
    ->middleware('usuario');
// Procurará permissão: 'N_USUARIOS.CRIAR', 'R_POST_USUARIOS_CRIAR'
```


### 2. Método HTTP + URI
```php
// POST /api/usuarios/123/ativar
Route::post('/api/usuarios/{id}/ativar', [UserController::class, 'activate'])
    ->middleware('usuario');
// Procurará permissão: 'R_POST_API_USUARIOS_{ID}_ATIVAR'
```



## Formato das Respostas

### Para Respostas Negativas APIs(JSON) 
```json
{
    "mensagem": "Você não possui permissão para acessar estes dados da API: /api/usuarios/123/ativar",
    "permissoesNecessarias": ["N_USUARIOS.LISTA", "R_GET_USUARIOS"]
}    
status code = 403
```

### Para Retorno Negativo  Web (Redirect)
```php
// Redireciona para a página anterior com:
redirect()->back()
    ->with('dadosUsuario', 
        {
        "mensagem": "Você não possui permissão para acessar esta página.",
        "permissoesNecessarias": ["N_USUARIOS.LISTA", "R_GET_USUARIOS"],
        }
    )
```

### Para Retorno Positivo (Web)
```php
// Adiciona informações do usuário logado à requisição para uso posterior
$request
    ->merge(
        ['dadosUsuario' => {
            dados_do_usuario_logado = {
                id_Usuario: 123,
                nome_Completo: "João da Silva",
                email: "joao.silva@example.com"
            }
        }]
    );
return $next($request);
```php


## Blade Directives Disponíveis

### @temPermissao
```php
@temPermissao('V_USUARIOS_CRIAR')
    <a href="/usuarios/criar" class="btn btn-primary">Criar Usuário</a>
@endtemPermissao
```

### @possuiQualquerUmaDasPermissoes
```php
@possuiQualquerUmaDasPermissoes('V_USUARIOS_GERENCIAR', 'V_USUARIOS_MODERAR')
    <div class="admin-panel">
        <!-- Conteúdo administrativo -->
    </div>
@endpossuiQualquerUmaDasPermissoes
```

### @usuarioEstaAutenticado
```php
@usuarioEstaAutenticado
    <div class="user-menu">
        <!-- Menu do usuário -->
    </div>
@else
    <a href="/login">Fazer Login</a>
@endusuarioEstaAutenticado
```

## Debugging

Em ambiente de desenvolvimento (`APP_DEBUG=true`), as views recebem automaticamente:


```php
<!-- Na view -->
@if(config('app.debug'))
    <div class="debug-info">
        <h4>Permissões do Usuário:</h4>
        <pre>{{ print_r($servicoDoUsuario->permissoes(), true) }}</pre>

        <h4>Dados do Usuário:</h4>
        <pre>{{ print_r($servicoDoUsuario->usuario(), true) }}</pre>
    </div>
@endif
```


## Integração com o Sistema

O middleware utiliza o singleton `usuarioServices` registrado no `AppServiceProvider`, garantindo:

- **Consistência**: Mesmos dados em toda a aplicação
- **Performance**: Evita recriar o service a cada verificação
- **Centralização**: Único ponto de controle de permissões
- **Facilidade de Debug**: Informações detalhadas em caso de falha

## Casos de Uso Comuns

### Sistema de Permissões Hierárquicas
```php
// Administrador tem acesso total
Route::middleware(['usuarioMiddleware:G_USUARIOS_ADMIN'])->group(function () {
    Route::resource('usuarios', UserController::class);
    Route::resource('permissoes', PermissaoController::class);
});

// Gerentes têm acesso limitado
Route::middleware(['usuarioMiddleware:G_GERENTES'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
});

// Usuários comuns têm acesso básico
Route::middleware(['usuarioMiddleware:G_USUARIOS_COMUNS,G_GERENTES,G_USUARIOS_ADMIN'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/perfil', [UserController::class, 'perfil']);
});
```

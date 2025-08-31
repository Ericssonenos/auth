# Middleware de Controle de Acesso de Usuário

## Visão Geral

O `UsuarioMiddleware` é responsável por controlar o acesso a rotas baseado nas permissões do usuário autenticado. Ele utiliza o `usuarioServices` registrado como singleton no sistema.

## Características Principais

### 1. **Detecção Automática de Permissões**
O middleware pode detectar automaticamente qual permissão é necessária baseado na rota atual, seguindo esta ordem de prioridade:

1. **Nome da rota** (mais semântico)
2. **Controller.Action** (padrão REST)
3. **Método HTTP + URI** (fallback)
4. **URI formatada** (casos simples)

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
// Tentará: 'usuarios.listar', 'user.index', 'get.usuarios', 'usuarios'
```

### Permissão Específica
```php
// Define explicitamente qual permissão é necessária
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('usuario:administrador.painel');
```

### Múltiplas Permissões
```php
// Usuário precisa ter pelo menos uma das permissões listadas
Route::get('/relatorios', [RelatorioController::class, 'index'])
    ->middleware('usuario:relatorios.ver,admin.total,gerente.relatorios');
```

### Em Grupos de Rotas
```php
// Aplica o middleware a todas as rotas do grupo
Route::middleware(['usuario'])->group(function () {
    Route::resource('produtos', ProductController::class);
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

## Estratégias de Detecção

### 1. Nome da Rota (Prioridade Máxima)
```php
Route::get('/usuarios/criar', [UserController::class, 'create'])
    ->name('usuarios.criar')
    ->middleware('usuario');
// Procurará permissão: 'usuarios.criar'
```

### 2. Controller.Action
```php
// ProductController@store
Route::post('/produtos', [ProductController::class, 'store'])
    ->middleware('usuario');
// Procurará permissão: 'product.store'
```

### 3. Método HTTP + URI
```php
// POST /api/usuarios/123/ativar
Route::post('/api/usuarios/{id}/ativar', [UserController::class, 'activate'])
    ->middleware('usuario');
// Procurará permissão: 'post.api/usuarios/{id}/ativar'
```

### 4. URI Formatada
```php
// /usuarios/123/editar -> usuarios/{id}/editar
Route::get('/usuarios/{id}/editar', [UserController::class, 'edit'])
    ->middleware('usuario');
// Procurará permissão: 'usuarios/{id}/editar'
```

## Formato das Respostas

### Para APIs (JSON)
```json
{
    "mensagem": "Usuário não possui permissão necessária para acessar este recurso",
    "permissoes_do_usuario_atual": ["usuarios.listar", "produtos.ver"],
    "status_autenticacao": "autenticado",
    "permissoes_necessarias_para_acesso": ["admin.usuarios.gerenciar"]
}
```

### Para Web (Redirect)
```php
// Redireciona para a página anterior com:
redirect()->back()
    ->with('erro_de_acesso', 'Mensagem de erro')
    ->with('permissoes_necessarias', ['permissao1', 'permissao2'])
    ->with('usuario_nao_autorizado', true);
```

## Dados Adicionados à Requisição

O middleware adiciona os seguintes dados à requisição para uso posterior:

```php
$request->get('dados_do_usuario_logado');      // Array com dados do usuário
$request->get('permissoes_do_usuario_logado'); // Array com permissões do usuário
```

## Blade Directives Disponíveis

### @temPermissao
```php
@temPermissao('usuarios.criar')
    <a href="/usuarios/criar" class="btn btn-primary">Criar Usuário</a>
@endtemPermissao
```

### @possuiQualquerUmaDasPermissoes
```php
@possuiQualquerUmaDasPermissoes('admin.total', 'usuarios.gerenciar', 'moderador.usuarios')
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

- `debug_permissoes_usuario`: Array com todas as permissões do usuário
- `debug_dados_usuario`: Array com todos os dados do usuário

```php
<!-- Na view -->
@if(config('app.debug'))
    <div class="debug-info">
        <h4>Permissões do Usuário:</h4>
        <pre>{{ print_r($debug_permissoes_usuario, true) }}</pre>

        <h4>Dados do Usuário:</h4>
        <pre>{{ print_r($debug_dados_usuario, true) }}</pre>
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
Route::middleware(['usuario:admin.total'])->group(function () {
    Route::resource('usuarios', UserController::class);
    Route::resource('permissoes', PermissaoController::class);
});

// Gerentes têm acesso limitado
Route::middleware(['usuario:gerente.usuarios,admin.total'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
});

// Usuários comuns têm acesso básico
Route::middleware(['usuario:usuario.basico,gerente.usuarios,admin.total'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/perfil', [UserController::class, 'perfil']);
});
```

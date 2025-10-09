# 🔐 Requisições HTTP com Proxy e NTLM

Documentação de uso das funções para requisições HTTP com autenticação NTLM e proxy corporativo.

---

## 📋 Índice

1. [Configuração](#-configuração)
2. [Função requisicaoComNTLM()](#-função-requisicaocomntlm)
3. [Função requisicaoComProxy()](#-função-requisicaocomproxy)
4. [Exemplos de Uso](#-exemplos-de-uso)
5. [Troubleshooting](#-troubleshooting)

---

## ⚙️ Configuração

### Variáveis de Ambiente (.env)

```env
# Autenticação NTLM direta (servidor de destino)
NTLM_USER=DOMINIO\\usuario
NTLM_PASS=senha_segura

# Configuração do Proxy Corporativo
PROXY_URL=proxy.exemplo.com:8080
PROXY_USER=DOMINIO\\usuario_proxy
PROXY_PASS=senha_proxy
```

> ⚠️ **Importante**: 
> - Use dupla barra invertida (`\\`) para separar domínio e usuário no Windows
> - O `PROXY_URL` deve estar no formato `host:porta` (ex: `proxy.empresa.com:8080`)

---

## 🎯 Função requisicaoComNTLM()

### Descrição
Realiza requisição HTTP com autenticação NTLM **direta no servidor de destino** (sem proxy).

### Assinatura
```php
public static function requisicaoComNTLM(
    string $url,              // URL do endpoint
    string $method = 'GET',   // Método HTTP
    array $data = [],         // Dados (POST/PUT/PATCH)
    ?string $usuario = null,  // Usuário NTLM (opcional)
    ?string $senha = null     // Senha NTLM (opcional)
): array
```

### Retorno
```php
[
    'status' => 200,                    // Código HTTP
    'body' => '{"success": true}',      // Corpo da resposta (string)
    'headers' => [...],                 // Headers da resposta
    'json' => ['success' => true]       // Resposta parseada (se JSON)
]
```

### Quando Usar
✅ Servidor de destino requer autenticação NTLM  
✅ Acesso direto à internet (sem proxy corporativo)  
✅ APIs internas da empresa com Windows Authentication  

---

## 🌐 Função requisicaoComProxy()

### Descrição
Realiza requisição HTTP através de **proxy corporativo** com autenticação NTLM no proxy.

### Assinatura
```php
public static function requisicaoComProxy(
    string $url,                    // URL do endpoint
    string $method = 'GET',         // Método HTTP
    array $data = [],               // Dados (POST/PUT/PATCH)
    ?string $proxyUrl = null,       // URL do proxy (host:port)
    ?string $proxyUsuario = null,   // Usuário do proxy
    ?string $proxySenha = null      // Senha do proxy
): array
```

### Retorno
```php
[
    'status' => 200,                    // Código HTTP
    'body' => '{"data": [...]}',        // Corpo da resposta
    'headers' => [...],                 // Headers da resposta
    'json' => ['data' => [...]]         // Resposta parseada (se JSON)
]
```

### Quando Usar
✅ Rede corporativa com proxy obrigatório  
✅ Proxy requer autenticação NTLM  
✅ Acesso a APIs externas através do proxy  
✅ Servidor de destino pode ou não ter NTLM  

---

## 🚀 Exemplos de Uso

### 1. NTLM Direto - GET Simples

```php
use App\Services\Operacao;

// Usa credenciais do .env (NTLM_USER e NTLM_PASS)
$resultado = Operacao::requisicaoComNTLM(
    url: 'https://api.interna.empresa.com/usuarios'
);

if ($resultado['status'] === 200) {
    $usuarios = $resultado['json'];
    foreach ($usuarios as $usuario) {
        echo $usuario['nome'] . "\n";
    }
}
```

### 2. NTLM Direto - POST com Dados

```php
$novoProduto = [
    'nome' => 'Notebook Dell',
    'preco' => 3500.00,
    'categoria' => 'Eletrônicos'
];

$resultado = Operacao::requisicaoComNTLM(
    url: 'https://api.interna.empresa.com/produtos',
    method: 'POST',
    data: $novoProduto
);

if ($resultado['status'] === 201) {
    echo "Produto criado com ID: " . $resultado['json']['id'];
}
```

### 3. NTLM com Credenciais Personalizadas

```php
// Sobrescrever credenciais do .env
$resultado = Operacao::requisicaoComNTLM(
    url: 'https://api.interna.empresa.com/relatorio',
    method: 'GET',
    data: [],
    usuario: 'OUTRO_DOMINIO\\admin',
    senha: 'senha_admin_123'
);
```

### 4. Proxy - GET Simples

```php
// Usa configurações do .env (PROXY_URL, PROXY_USER, PROXY_PASS)
$resultado = Operacao::requisicaoComProxy(
    url: 'https://api.externa.com/dados'
);

if ($resultado['status'] === 200) {
    $dados = $resultado['json'];
    dd($dados);
}
```

### 5. Proxy - POST através do Proxy

```php
$payload = [
    'cliente_id' => 12345,
    'valor' => 1500.00,
    'descricao' => 'Pagamento mensal'
];

$resultado = Operacao::requisicaoComProxy(
    url: 'https://api.pagamentos.com/transacoes',
    method: 'POST',
    data: $payload
);

if ($resultado['status'] === 201) {
    echo "Transação criada: " . $resultado['json']['transacao_id'];
}
```

### 6. Proxy com Configurações Personalizadas

```php
// Usar proxy diferente do configurado no .env
$resultado = Operacao::requisicaoComProxy(
    url: 'https://api.externa.com/consulta',
    method: 'GET',
    data: [],
    proxyUrl: 'proxy2.empresa.com:3128',
    proxyUsuario: 'DOMINIO\\outro_usuario',
    proxySenha: 'outra_senha'
);
```

### 7. Proxy - PUT Atualizar Recurso

```php
$atualizacao = [
    'status' => 'ativo',
    'ultima_atualizacao' => now()->toIso8601String()
];

$resultado = Operacao::requisicaoComProxy(
    url: 'https://api.externa.com/recursos/123',
    method: 'PUT',
    data: $atualizacao
);

if ($resultado['status'] === 200) {
    echo "Recurso atualizado com sucesso!";
}
```

### 8. Proxy - DELETE Remover Recurso

```php
$resultado = Operacao::requisicaoComProxy(
    url: 'https://api.externa.com/recursos/456',
    method: 'DELETE'
);

if ($resultado['status'] === 204) {
    echo "Recurso deletado com sucesso!";
}
```

### 9. Tratamento de Erros

```php
use Exception;

try {
    $resultado = Operacao::requisicaoComProxy(
        url: 'https://api.externa.com/dados',
        method: 'GET'
    );

    switch ($resultado['status']) {
        case 200:
            // Sucesso
            $dados = $resultado['json'];
            break;

        case 401:
            // Não autorizado
            Log::error('Credenciais inválidas', [
                'mensagem' => $resultado['json']['message'] ?? 'Erro desconhecido'
            ]);
            break;

        case 403:
            // Proibido
            Log::error('Acesso negado');
            break;

        case 500:
            // Erro no servidor
            Log::error('Erro no servidor remoto', [
                'body' => $resultado['body']
            ]);
            break;

        default:
            Log::warning('Status inesperado: ' . $resultado['status']);
    }
} catch (Exception $e) {
    Log::error('Erro na requisição', [
        'erro' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

### 10. Uso em Controller

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Operacao;
use Illuminate\Http\JsonResponse;

class IntegracaoController extends Controller
{
    /**
     * Consultar dados de API externa através do proxy
     */
    public function consultarDadosExternos(): JsonResponse
    {
        try {
            $resultado = Operacao::requisicaoComProxy(
                url: 'https://api.externa.com/consulta',
                method: 'GET'
            );

            if ($resultado['status'] !== 200) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao consultar API externa',
                    'status' => $resultado['status']
                ], $resultado['status']);
            }

            return response()->json([
                'success' => true,
                'data' => $resultado['json']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar dados com API interna (NTLM)
     */
    public function sincronizarDadosInternos(): JsonResponse
    {
        try {
            $dados = [
                'ultima_sincronizacao' => now()->toIso8601String(),
                'registros' => 150
            ];

            $resultado = Operacao::requisicaoComNTLM(
                url: 'https://api.interna.empresa.com/sincronizar',
                method: 'POST',
                data: $dados
            );

            return response()->json([
                'success' => $resultado['status'] === 200,
                'data' => $resultado['json']
            ], $resultado['status']);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 🐛 Troubleshooting

### Erro: "Proxy authentication required"

**Causa**: Credenciais do proxy inválidas ou não configuradas.

**Solução**:
1. Verificar `.env`:
   ```env
   PROXY_URL=proxy.empresa.com:8080
   PROXY_USER=DOMINIO\\usuario
   PROXY_PASS=senha_correta
   ```
2. Limpar cache de configuração:
   ```bash
   php artisan config:clear
   ```

### Erro: "Failed to connect to proxy"

**Causa**: URL do proxy incorreta ou proxy inacessível.

**Solução**:
1. Verificar se o proxy está acessível:
   ```powershell
   Test-NetConnection proxy.empresa.com -Port 8080
   ```
2. Confirmar formato do `PROXY_URL`: `host:porta`

### Erro: "SSL certificate problem"

**Causa**: Certificado SSL inválido ou auto-assinado.

**Solução temporária (apenas desenvolvimento)**:
```php
// Desabilitar verificação SSL (REMOVER EM PRODUÇÃO!)
$resultado = Operacao::requisicaoComProxy(
    url: 'https://api.externa.com/dados',
    method: 'GET'
);

// Adicionar 'verify' => false nas opções da função
```

**Solução permanente**: Adicionar certificado raiz ao sistema.

### Erro: "NTLM authentication failed"

**Causa**: Credenciais NTLM inválidas.

**Solução**:
1. Verificar formato do usuário: `DOMINIO\\usuario`
2. Confirmar se a senha está correta
3. Testar credenciais manualmente:
   ```powershell
   # Windows
   runas /user:DOMINIO\usuario cmd
   ```

### Timeout na Requisição

**Causa**: Servidor lento ou rede instável.

**Solução**: Aumentar timeout (modificar a função para aceitar timeout):
```php
// Adicionar timeout nas opções cURL
'curl' => [
    CURLOPT_TIMEOUT => 60,        // 60 segundos
    CURLOPT_CONNECTTIMEOUT => 30, // 30 segundos
    // ... outras opções
]
```

---

## 📊 Comparação: NTLM vs Proxy

| Característica | requisicaoComNTLM() | requisicaoComProxy() |
|----------------|---------------------|----------------------|
| **Usa proxy** | ❌ Não | ✅ Sim |
| **NTLM no servidor** | ✅ Sim | 🔶 Opcional |
| **NTLM no proxy** | ❌ Não | ✅ Sim |
| **Rede corporativa** | 🔶 Depende | ✅ Recomendado |
| **APIs externas** | ❌ Não | ✅ Sim |
| **APIs internas** | ✅ Sim | 🔶 Pode usar |

---

## 🔒 Boas Práticas de Segurança

### 1. Nunca Expor Credenciais no Código
```php
// ❌ ERRADO
$resultado = Operacao::requisicaoComProxy(
    url: 'https://api.com',
    proxyUsuario: 'DOMINIO\\usuario',
    proxySenha: 'senha123'  // Credencial exposta!
);

// ✅ CORRETO
$resultado = Operacao::requisicaoComProxy(
    url: 'https://api.com'
    // Usa credenciais do .env automaticamente
);
```

### 2. Adicionar .env ao .gitignore
```gitignore
# .gitignore
.env
.env.backup
.env.production
```

### 3. Usar .env.example como Template
```env
# .env.example
PROXY_URL=seu_proxy.com:8080
PROXY_USER=DOMINIO\\seu_usuario
PROXY_PASS=sua_senha
```

### 4. Logs Seguros
```php
// ❌ ERRADO - Logar credenciais
Log::info('Requisição', ['senha' => $senha]);

// ✅ CORRETO - Não logar dados sensíveis
Log::info('Requisição', ['url' => $url, 'method' => $method]);
```

---

## 📚 Referências

- [Laravel HTTP Client](https://laravel.com/docs/11.x/http-client)
- [cURL NTLM Authentication](https://curl.se/docs/ntlm.html)
- [cURL Proxy Options](https://curl.se/libcurl/c/CURLOPT_PROXY.html)
- [RFC 4559 - NTLM HTTP Authentication](https://tools.ietf.org/html/rfc4559)

---

**Atualizado em**: 8 de outubro de 2025  
**Versão**: 1.0

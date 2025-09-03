# Mensagens de Alerta (alerta) — guia de integração

Este documento descreve, passo a passo, como integrar o componente de alertas (vanilla JS + CSS) que consome flashes de sessão do Laravel.

Visão rápida

- O componente é um módulo JS que expõe `window.alerta` com métodos `mostrar`, `sucesso` e `erro`.
- Os estilos ficam em `resources/css/mensagens_alerta.css`.
- O módulo JS fica em `resources/js/components/mensagens_alerta.js` e é importado em `resources/js/app.js`.
- As mensagens de sessão são expostas via Blade em `window.AppData` e consumidas pelo bundle.

Arquivos importantes

- `resources/css/mensagens_alerta.css` — estilos e posicionamento
- `resources/js/components/mensagens_alerta.js` — cria container e alertas, expõe `window.alerta`
- `resources/js/app.js` — importa o componente e processa `window.AppData`
- `resources/views/layout/main.blade.php` — exporta flashes para `window.AppData`

Passo-a-passo

1) Criar o CSS

Crie `resources/css/mensagens_alerta.css` com os estilos desejados. O arquivo deve definir:

- `#mensagens_alerta_container` — posição e layout do container
- `.mensagem-alerta` e `.mensagem-alerta.show` — aparência e animação
- `.mensagem-alerta__icone`, `.mensagem-alerta__conteudo`, `.mensagem-alerta .fechar` — subcomponentes

2) Criar o módulo JS

Crie `resources/js/components/mensagens_alerta.js` com a seguinte responsabilidade:

- criar/retornar o container: `criarContainer()`
- criar o alerta DOM (ícone, conteúdo, botão fechar): `criarAlerta(...)`
- remover o alerta com animação: `removerAlerta(...)`
- expor a API: `window.alerta = { mostrar, sucesso, erro }`

Exemplo mínimo (esqueleto):

```javascript
// importar CSS (se preferir importar aqui)
import '../../css/mensagens_alerta.css';

(function(){
  function criarContainer(){ /* cria #mensagens_alerta_container */ }
  function criarAlerta(heading,text,icon){ /* monta DOM */ }
  function removerAlerta(el){ /* anima e remove */ }
  function mostrar(opts){ /* combina tudo */ }

  window.alerta = { mostrar, sucesso(text){ return mostrar({icon:'success',text}); }, erro(text){ return mostrar({icon:'error',text}); } };
})();
```

3) Importar no entrypoint

Em `resources/js/app.js` importe o módulo para garantir `window.alerta`:

```javascript
import './components/mensagens_alerta';
```

4) Expor flashes no Blade

No layout (por exemplo `resources/views/layout/main.blade.php`) adicione:

```blade
<script>
 window.AppData = {
   session_status_autenticacao: @json(session('status_autenticacao')),
   session_permissoes_necessarias: @json(session('permissoes_necessarias', [])),
   session_usuario_autorizado: @json(session('usuario_autorizado', false))
 };
</script>
```

5) Consumir `window.AppData`

No `app.js` (após importar o componente) adicione algo como:

```javascript
function consumirAppDataSessao(){
  const app = window.AppData || {};
  if (app.session_status_autenticacao && window.alerta){
    const permissoes = app.session_permissoes_necessarias || [];
    let body = app.session_status_autenticacao;
    if (permissoes.length) body += '<br><small><strong>Permissões:</strong>'+permissoes.join(', ')+'</small>';
    window.alerta.erro(body,'Acesso negado',8000);
  } else if (app.session_usuario_autorizado == true && window.alerta){
    window.alerta.erro('Usuário não autorizado.','Não autorizado',5000);
  }
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', consumirAppDataSessao);
else consumirAppDataSessao();
```

6) Gerar as mensagens no servidor

No middleware ou controlador, retorne o redirect com flash:

```php
return redirect()->back()
  ->with('status_autenticacao', true)
  ->with('permissoes_necessarias',['perm.a','perm.b']);
```

Comandos úteis (PowerShell)

```powershell
npm install
npm run dev
php artisan serve
```

Boas práticas

- Importe o módulo antes de chamá-lo (ordem em `app.js`).
- Valide/escape mensagens com HTML quando necessário.
- Use `aria-live="polite"` no container para acessibilidade.

Perguntas frequentes

- Q: O componente usa jQuery? A: Não — é vanilla JS.
- Q: Onde chamar `window.alerta` manualmente? A: Em qualquer script carregado após o bundle (ex.: `resources/js/pages/...`) ou no console.

---

Se quiser, eu crio testes simples ou um exemplo de middleware no diretório `docs/examples`.

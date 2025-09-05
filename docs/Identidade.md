1) Documentação textual do que está nas imagens
Paleta RGB (cores-luz para ambiente digital)

Azul claro principal
HEX: #26b0e5 · RGB: 38, 176, 229
Tons previstos: 100%, 75%, 50%, 25%.

Azul profundo/escuro
HEX: #3e4398 · RGB: 62, 67, 152
Tons previstos: 100%, 75%, 50%, 25%.

Cinza metálico (base neutra)
HEX: #77797b · RGB: 119, 121, 123
Tons previstos: 100%, 75%, 50%, 25%.

Observação do manual: por se tratar de ambiente digital, priorizar RGB. Em substratos físicos (impressão), as cores perdem um pouco de brilho, mas mantêm fidelidade quando convertidas para CMYK/Pantone.

Paleta principal — CMYK e Pantone® (para impressão)

Azul claro (equivalente do #26b0e5)
Pantone: 2995 C · CMYK: 83, 23, 0, 10.

Azul profundo (equivalente do #3e4398)
Pantone: 7672 C · CMYK: 59, 56, 0, 40.

Cinza metálico (equivalente do #77797b)
Pantone: Cool Gray 9 C · CMYK: 3, 2, 0, 52.

Justificativas conceituais das cores

Azul claro: transmite tranquilidade, clareza e profissionalismo. No contexto industrial, comunica inovação e tecnologia.

Azul profundo: associado a confiança, credibilidade, segurança e competência, qualidades essenciais para fornecimento industrial.

Cinza metálico: remete à robustez das engrenagens/indústria, traduz neutralidade, estabilidade e eficiência, reforçando a solidez da marca.

Tipografia

Principal: Glacial Indifference (Regular, Italic, Bold).

Alternativa para documentos Office: Verdana.

2) Base CSS unificada (cabeçalho + navbar + tabelas)

Foco: azul “glossy” (seta) para realces/ativos e cinza metálico (engrenagem) para a barra de navegação, com brilho e relevo discretos. Tudo pensado para ser sóbrio e profissional, sem exageros.

/* ===========================
   TOKENS (cores e efeitos)
   =========================== */
:root{
  /* Paleta oficial Supplytek */
  --st-blue-1:#26b0e5;     /* azul claro (destaque) */
  --st-blue-2:#3e4398;     /* azul profundo (base do gradiente) */
  --st-gray:#77797b;       /* cinza metálico base */
  --st-gray-hi:#8f9295;    /* variação clara do metal */
  --st-gray-lo:#5f6467;    /* variação escura do metal */

  /* Neutros */
  --st-bg:#0f1318;
  --st-surface:#12161d;
  --st-text:#e6eaf0;
  --st-text-dim:#c6cbd4;

  /* Raio, sombra, transição */
  --st-r:12px;
  --st-shadow-1:0 2px 6px rgba(0,0,0,.20);
  --st-shadow-2:0 8px 24px rgba(0,0,0,.28);
  --st-t:.24s cubic-bezier(.22,.61,.36,1);
}

/* Reset mínimo */
*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0; background:var(--st-bg); color:var(--st-text);
  font:14px/1.45 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial;
}

/* ===========================
   GRADIENTES PRONTOS
   =========================== */

/* Azul glossy (brilho discreto, aspecto “verniz”) */
.st-glossy-blue{
  background:
    linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,0)) top/100% 40% no-repeat,
    linear-gradient(180deg, var(--st-blue-1), var(--st-blue-2));
  color:#fff;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.10), var(--st-shadow-1);
}

/* Metal escovado com leve relevo (engrenagem “3D” discreto) */
.st-metal{
  background:
    radial-gradient(120% 200% at 50% -40%, rgba(255,255,255,.08), transparent 60%) no-repeat,
    linear-gradient(180deg, var(--st-gray-hi), var(--st-gray) 45%, var(--st-gray-lo) 55%, var(--st-gray-hi) 100%);
  color:#fff;
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.06),
    inset 0 -1px 0 rgba(0,0,0,.35),
    var(--st-shadow-1);
  position:relative;
}
.st-metal::after{ /* brilho fino de superfície */
  content:""; position:absolute; inset:0; border-radius:inherit;
  background: linear-gradient(180deg, rgba(255,255,255,.06), transparent 35%, transparent 70%, rgba(0,0,0,.18));
  pointer-events:none;
}

/* ===========================
   CABEÇALHO + NAVBAR
   =========================== */

.header{
  display:flex; align-items:center; justify-content:space-between;
  padding:10px 16px; gap:16px;
  background:linear-gradient(180deg, #0f141b, #0c1016);
  box-shadow:var(--st-shadow-2);
  position:sticky; top:0; z-index:50;
}
.brand{ display:flex; align-items:center; gap:10px; }
.brand img{ height:28px; width:auto; display:block; }
.brand-name{ font-weight:700; letter-spacing:.3px; color:#e9eef6; }

.navbar{
  display:flex; align-items:center; gap:8px;
  padding:0 12px; height:46px;
  border-radius: var(--st-r) var(--st-r) 0 0;
}
.nav{ display:flex; gap:6px; height:100%; }
.nav a{
  display:flex; align-items:center; padding:0 14px; height:100%;
  border-radius:8px; color:#f4f7fb; text-decoration:none; font-weight:600;
  transition:transform var(--st-t), box-shadow var(--st-t), background var(--st-t), color var(--st-t);
}
.nav a:hover{ background:rgba(255,255,255,.06); box-shadow:inset 0 1px 0 rgba(255,255,255,.12); }
.nav a.active{
  background:
    linear-gradient(180deg, rgba(255,255,255,.18), rgba(255,255,255,0)) top/100% 35% no-repeat,
    linear-gradient(180deg, var(--st-blue-1), var(--st-blue-2));
  color:#fff;
  box-shadow:0 2px 8px rgba(38,176,229,.35), inset 0 -1px 0 rgba(0,0,0,.35);
}

/* Dropdown */
.dropdown{ position:relative; }
.dropdown-menu{
  position:absolute; top:calc(100% + 8px); left:0; min-width:220px;
  background:#141922; border-radius:12px; padding:8px;
  box-shadow:var(--st-shadow-2); border:1px solid rgba(255,255,255,.06);
  display:none;
}
.dropdown:hover .dropdown-menu{ display:block; }
.dropdown-menu a{
  display:flex; align-items:center; gap:8px;
  padding:10px 12px; border-radius:8px; color:#e7ebf3; text-decoration:none;
}
.dropdown-menu a:hover{ background:rgba(255,255,255,.07); }

/* Ações do usuário */
.nav-actions{ display:flex; align-items:center; gap:10px; }
.user-name{
  font-weight:600; color:#f8fafc; white-space:nowrap; max-width:220px; overflow:hidden; text-overflow:ellipsis;
}
.btn{ border:0; border-radius:10px; padding:9px 14px; font-weight:700; cursor:pointer;
      transition:transform var(--st-t), box-shadow var(--st-t), filter var(--st-t); }
.btn:active{ transform:translateY(1px); filter:saturate(1.05); }
.btn-primary{ composes: st-glossy-blue; }
.btn-ghost{ background:transparent; color:#e8ecf4; border:1px solid rgba(255,255,255,.12); }
.btn-ghost:hover{ background:rgba(255,255,255,.06); }

/* Toggle de filtros (lateral) */
.filters-toggle{
  display:flex; align-items:center; gap:8px;
  height:32px; padding:0 12px; border-radius:999px;
  background:linear-gradient(180deg,#1a2130,#141a26); color:#dfe6f1;
  border:1px solid rgba(255,255,255,.10); box-shadow:inset 0 1px 0 rgba(255,255,255,.06);
}
.filters-toggle.has-active::after{
  content:""; width:8px; height:8px; border-radius:50%;
  background:var(--st-blue-1); box-shadow:0 0 10px rgba(38,176,229,.8); margin-left:4px;
}

/* Containers e cartões */
.container{ padding:18px; }
.card{
  background:linear-gradient(180deg,#131923,#0f141c);
  border:1px solid rgba(255,255,255,.06);
  border-radius:var(--st-r); box-shadow:var(--st-shadow-1);
  padding:16px;
}

/* Tabelas (compatível com DataTables) */
.table{
  width:100%; border-collapse:separate; border-spacing:0;
  background:#0f141c; border:1px solid rgba(255,255,255,.06);
  border-radius:12px; overflow:hidden;
}
.table thead th{
  background:
    linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,0)) top/100% 35% no-repeat,
    linear-gradient(180deg,#1a2330,#171e2a);
  color:#e9eef6; text-align:left; font-weight:700; padding:12px;
  border-bottom:1px solid rgba(255,255,255,.08);
}
.table tbody td{
  padding:12px; color:#d9e0ea; border-bottom:1px solid rgba(255,255,255,.05);
}
.table tbody tr:hover{ background:rgba(255,255,255,.03); }
.table .badge{
  display:inline-block; padding:4px 8px; border-radius:8px; font-weight:700; font-size:12px;
  background:linear-gradient(180deg, var(--st-blue-1), var(--st-blue-2)); color:#fff;
}

/* Acessibilidade e tema claro opcional */
@media (prefers-reduced-motion: reduce){
  *{ transition:none !important; animation:none !important; }
}
@media (prefers-color-scheme: light){
  :root{ --st-bg:#f6f8fb; --st-surface:#ffffff; --st-text:#13161a; --st-text-dim:#4b525b; }
  .header{ background:linear-gradient(180deg,#ffffff,#f2f5f9); color:#1b2128; }
  .nav a{ color:#1a2230; }
  .dropdown-menu{ background:#fff; color:#101418; border-color:rgba(0,0,0,.06); }
  .table{ background:#fff; border-color:rgba(0,0,0,.08); }
  .table thead th{ background:linear-gradient(180deg,#f3f6fb,#e9eef6); color:#0e141b; }
}


Exemplo mínimo de uso (HTML/Blade):

<header class="header">
  <div class="brand">
    <img src="/assets/logo_flat.png" alt="Supplytek" />
    <span class="brand-name">SUPPLYTEK</span>
  </div>
  <div class="nav-actions">
    <button class="filters-toggle">🔎 Filtros</button>
    <span class="user-name">{{ $dadosUsuario->nome_Completo ?? 'Visitante' }}</span>
    @usuarioLogado
      <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf
        <button type="submit" class="btn btn-ghost">Sair</button>
      </form>
    @else
      <a href="{{ route('login') }}" class="btn btn-primary">Entrar</a>
    @endusuarioLogado
  </div>
</header>

<nav class="navbar st-metal">
  <div class="nav">
    <a class="active" href="#">Início</a>
    <div class="dropdown">
      <a href="#">Relatórios ▾</a>
      <div class="dropdown-menu">
        <a href="#">Vendas</a><a href="#">Logística</a><a href="#">Financeiro</a>
      </div>
    </div>
    <a href="#">Cadastros</a>
  </div>
</nav>

3) Estratégia (alinhada à documentação)

Objetivo visual
Transmitir profissionalismo, robustez e tecnologia discreta. Usar o azul como energia e direção (seta do logo) e o cinza metálico como estrutura (engrenagem).

Cabeçalho (topo)

Fundo escuro neutro para destacar marca e ações.

Área de usuário sempre visível, com botão primário em azul glossy para pontos de ação (Entrar/Salvar).

Navbar (faixa abaixo do topo)

Textura metálica escovada (classe .st-metal) com brilho sutil e leve relevo (sombras internas).

Item ativo recebe o gradiente azul (.st-glossy-blue) com micro-sombra externa para dar destaque sem exagero.

Dropdowns minimalistas, fundo escuro, bordas suaves, sem poluição.

Filtros (lateral)

Acesso por botão “🔎 Filtros” (padrão discreto).

Badge/indicador azul quando houver filtros ativos (sem ocupar espaço da navegação global).

Componentes e tabelas

Tabelas com cabeçalho sutilmente elevado (gradiente leve + separador), linhas com hover suave, badges de status no azul glossy.

Cartões e superfícies com sombras moderadas — aspecto premium sem “chegar chegando”.

Acessibilidade e consistência

Contraste garantido nos itens ativos e no texto.

Redução de movimento respeitada via prefers-reduced-motion.

Tokens (--st-...) centralizam cores/efeitos: fácil afinar “o quanto de brilho” global.

4) Mapa de uso das cores (quando usar cada uma)

#26b0e5 (Azul claro – energia/ação): botões primários, item de menu ativo, badges de status “positivo/primário”, hovers discretos.

#3e4398 (Azul profundo – base/contraste): parte inferior do gradiente “glossy”, focos/outline, barras de progresso densas.

#77797b (Cinza metálico – estrutura): fundo do navbar, divisores metálicos, bordas suaves.

Neutros escuros (#0f1318, #12161d): superfícies de fundo para dar palco ao azul.

Texto: use --st-text e --st-text-dim; evite cinza puro sem tokens (consistência de contraste).

5) Ajuste fino do “brilho”

Quer mais ou menos “lustre”? altere apenas estes valores:

:root{
  --gloss-intensity:.14;   /* reflexo do topo do botão */
  --metal-highlight:.06;   /* brilho do metal */
  --metal-shadow:.35;      /* sombra interna da barra */
}
.st-glossy-blue{
  background:
    linear-gradient(180deg, rgba(255,255,255,var(--gloss-intensity)), rgba(255,255,255,0)) top/100% 40% no-repeat,
    linear-gradient(180deg, var(--st-blue-1), var(--st-blue-2));
}
.st-metal{
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,var(--metal-highlight)),
    inset 0 -1px 0 rgba(0,0,0,var(--metal-shadow)),
    var(--st-shadow-1);
}


regra de ouro: nunca aumentar além de 0.2 nos highlights; fica “cheguei”.

6) Painel de filtros (lateral) — interação e CSS

Comportamento:

desktop: desliza da esquerda/direita; largura 340–380px; fecha com “esc”/overlay.

mobile: ocupa 100% (modal lateral).

indicador “filtro ativo” no botão.

/* Overlay e painel */
.filters-overlay{
  position:fixed; inset:0; background:rgba(0,0,0,.4);
  opacity:0; pointer-events:none; transition:opacity var(--st-t); z-index:60;
}
.filters-panel{
  position:fixed; top:0; right:-420px; width:360px; height:100%;
  background:linear-gradient(180deg,#121722,#0e141c);
  border-left:1px solid rgba(255,255,255,.08);
  box-shadow:var(--st-shadow-2); padding:16px; z-index:61;
  transition:transform var(--st-t);
  transform:translateX(0);
}
.filters-open .filters-overlay{ opacity:1; pointer-events:auto; }
.filters-open .filters-panel{ right:0; }

/* Itens de filtro */
.filter-group{ margin-bottom:14px; }
.filter-label{ font-weight:700; color:#e9eef6; margin-bottom:6px; }
.filter-input, .filter-select{
  width:100%; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.10);
  background:#0f141c; color:#e7ecf5; outline:0; transition:border-color var(--st-t), box-shadow var(--st-t);
}
.filter-input:focus, .filter-select:focus{
  border-color: rgba(38,176,229,.6);
  box-shadow: 0 0 0 4px rgba(38,176,229,.15);
}

7) Estados e microinterações

Hover: sempre aumentar contraste por fundo (não por saturação excessiva).

Active/Pressed: deslocar translateY(1px) e escurecer levemente.

Focus: anel de foco azul translúcido (box-shadow 0 0 0 4px rgba(38,176,229,.15)) — acessível.

8) DataTables — integração visual

Use as classes do tema para o container e direcione apenas seletores-chave do DataTables (não sobreescrever tudo).

/* Barra de busca e paginação */
.dataTables_wrapper .dataTables_filter input{
  @apply: none; /* (se não usa Tailwind, ignore) */
  background:#0f141c; color:#e7ecf5; border:1px solid rgba(255,255,255,.10);
  border-radius:10px; padding:8px 10px;
}
.dataTables_wrapper .dataTables_length select{
  background:#0f141c; color:#e7ecf5; border:1px solid rgba(255,255,255,.10);
  border-radius:10px; padding:6px 8px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button{
  background:transparent; color:#dfe6f1 !important; border:1px solid rgba(255,255,255,.08);
  border-radius:8px; margin:0 3px; padding:6px 10px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current{
  background:linear-gradient(180deg, var(--st-blue-1), var(--st-blue-2)) !important;
  color:#fff !important; border-color:transparent;
  box-shadow:0 2px 8px rgba(38,176,229,.25);
}

9) Iconografia e divisores metálicos

prefira ícones outline discretos (24px).

divisores do navbar: 1px rgba(255,255,255,.10) com backdrop-filter: blur(2px) opcional para dar “vidro industrial”.

.nav-divider{ width:1px; height:24px; background:linear-gradient(180deg, rgba(255,255,255,.18), rgba(255,255,255,.02)); }

10) Tipografia

usar Glacial Indifference para títulos/menu; corpo pode seguir sistema (performance).

pesos: 700 para títulos/menu; 400/500 para corpo.

espaçamento de letras em títulos: 0.2–0.3px.

11) Responsividade (breakpoints sugeridos)

≥1280 desktop amplo: navbar com rótulos + ícones; filtros dockáveis.

992–1279 desktop: igual, mas reduzir paddings em .nav a para 10–12px.

768–991 tablet: parte dos itens em dropdown “Mais”.

<768 mobile: navbar compacta; filtros como overlay 100%.

12) Performance

use SVG dos ícones (inline ou sprite).

combine gradientes sem imagens bitmap; tudo em CSS (menos rede, mais nitidez).

evite sombras enormes; elas custam renderização.

prefira transform para animações (GPU-friendly).

13) Do/Don’t (rápido)

Do

brilho controlado (< 0.2)

contrastes suficientes (verificar AA nas combinações)

itens ativos no azul glossy apenas onde houver intenção clara

Don’t

aplicar textura metálica em todo o app (use como elemento estrutural, não tema inteiro)

misturar muitos tons além dos oficiais

usar sombra dura (spread alto)

14) Checklist de QA visual

 Contraste mínimo 4.5:1 para textos essenciais.

 Hover/Focus visíveis em todos os links e botões.

 Navbar ativo destacando apenas a rota atual.

 Filtros: indicador azul quando houver filtros aplicados.

 DataTables: paginação coerente com tema, sem “saltos” de fonte/altura.

 Mobile: painel de filtros abre/fecha com esc, overlay clica-fora fecha.

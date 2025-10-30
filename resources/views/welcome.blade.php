@extends('layout.main')

@section('title', 'Supplytek — Soluções em Solda')

@section('content')
    <section class="st-glossy-blue" style="padding: 48px 0;">
        <div class="container">
            <h1 style="margin:0 0 8px">Máquinas de Solda Profissionais</h1>
            <p style="margin:0 0 18px;max-width:820px">
                Desempenho, robustez e acabamento clássico com a qualidade Supplytek. Soluções em soldagem para quem
                exige precisão, produtividade e segurança no dia a dia.
            </p>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a href="#catalogo" class="btn btn-warning" aria-label="Ver catálogo de máquinas">Ver catálogo</a>
                <a href="#orcamento" class="btn btn-primary" aria-label="Solicitar orçamento">Solicitar orçamento</a>
            </div>
        </div>
    </section>

    <section class="container" style="padding: 28px 0;">
        <div style="display:flex;flex-wrap:wrap;gap:14px;list-style:none;padding:0;margin:0">
            <div class="card" style="min-width:240px;flex:1">
                <h3 style="margin:0 0 8px">Solda MIG/MAG</h3>
                <p style="margin:0">Alta produtividade e estabilidade de arco para chapas finas e médias.</p>
            </div>
            <div class="card" style="min-width:240px;flex:1">
                <h3 style="margin:0 0 8px">Solda TIG</h3>
                <p style="margin:0">Acabamento impecável em inox e alumínio com controle preciso.</p>
            </div>
            <div class="card" style="min-width:240px;flex:1">
                <h3 style="margin:0 0 8px">Eletrodo (MMA)</h3>
                <p style="margin:0">Versatilidade e robustez em campo para manutenções e montagens.</p>
            </div>
            <div class="card" style="min-width:240px;flex:1">
                <h3 style="margin:0 0 8px">Plasma</h3>
                <p style="margin:0">Cortes limpos e rápidos com menor zona afetada pelo calor.</p>
            </div>
        </div>
    </section>

    <section id="catalogo" class="container" style="padding: 12px 0 28px;">
        <h2 style="margin:0 0 12px">Catálogo e Destaques</h2>
        <div style="display:flex;flex-wrap:wrap;gap:14px;">
            <a class="btn btn-secondary" href="#" aria-label="Ver modelos MIG/MAG"><i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>MIG/MAG</a>
            <a class="btn btn-secondary" href="#" aria-label="Ver modelos TIG"><i class="bi bi-droplet me-1" aria-hidden="true"></i>TIG</a>
            <a class="btn btn-secondary" href="#" aria-label="Ver modelos Eletrodo"><i class="bi bi-hammer me-1" aria-hidden="true"></i>MMA</a>
            <a class="btn btn-secondary" href="#" aria-label="Ver modelos Plasma"><i class="bi bi-scissors me-1" aria-hidden="true"></i>Plasma</a>
            <a class="btn btn-secondary" href="#" aria-label="Ver acessórios e consumíveis"><i class="bi bi-box-seam me-1" aria-hidden="true"></i>Acessórios</a>
        </div>
    </section>

    <section id="orcamento" class="container" style="padding: 12px 0 36px;">
        <div class="card">
            <h3 style="margin:0 0 10px">Pronto para elevar sua produção?</h3>
            <p style="margin:0 0 12px">Fale com nossos especialistas e receba uma proposta sob medida.</p>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a href="#contato" class="btn btn-primary" aria-label="Falar com especialista">Falar com especialista</a>
                <a href="#" class="btn btn-ghost" aria-label="Baixar catálogo em PDF">Baixar catálogo PDF</a>
            </div>
        </div>
    </section>
@endsection


@extends('layout.main')

@section('title', 'RH Access Demo')

@section('content')
    <h1>RH Access Demo</h1>
    <p>Matrícula: {{ $dados_Usuario['nome_Completo'] ?? 'N/A' }}</p>

    <hr />
    {{-- mostrar as permissões --}}
    <h2>Permissões do Usuário</h2>
    <ul>
        @foreach ($permissoes as $permissao)
            <li>{{ $permissao }}</li>
        @endforeach
    </ul>
    {{-- Exemplo: mostrar bloco somente se usuário tiver PERM_ATRIBUIR_GRUPO --}}
    @if ($permissoes && in_array('PERM_ATRIBUIR_GRUPO', $permissoes))
        <div style="padding:1rem;border:1px solid #2a9d8f;background:#e9f7f2">
            <h2>Área restrita</h2>

            <p>Você tem permissão para atribuir grupo PERM_ATRIBUIR_GRUPO.</p>
            {{-- aqui poderia vir um botão para abrir modal, etc. --}}
        </div>
    @else
        <div style="padding:1rem;border:1px solid #e76f51;background:#fff5f2">
            <p>Você não tem acesso a funcionalidades administrativas.</p>
        </div>
    @endif
@endsection


<script>
    // Scripts específicos da página welcome
    console.log('Página welcome carregada');
    //[ ] Retirar isso antes de mandar para produção
    // Exemplo de uso dos dados globais

    if (window.AppData?.dados_Usuario?.nome_Completo) {
        console.log('Usuário:', window.AppData.dados_Usuario.nome_Completo);
    }

    if (window.AppData?.permissoes?.includes('PERM_VISUALIZAR_RELATORIOS')) {
        console.log('Usuário tem permissão para relatórios');
    }
</script>

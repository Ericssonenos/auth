<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>RH Access Demo</title>
</head>
<body>
    <h1>RH Access Demo</h1>
    <p>Matrícula: {{ $usuario }}</p>

    <hr/>

    {{-- Exemplo: mostrar bloco somente se usuário tiver PERM_GERENCIAR_PERMISSOES --}}
    @can('PERM_GERENCIAR_PERMISSOES')
        <div style="padding:1rem;border:1px solid #2a9d8f;background:#e9f7f2">
            <h2>Área restrita</h2>
            <p>Você tem permissão para gerenciar permissões.</p>
            {{-- aqui poderia vir um botão para abrir modal, etc. --}}
        </div>
    @else
        <div style="padding:1rem;border:1px solid #e76f51;background:#fff5f2">
            <p>Você não tem acesso a funcionalidades administrativas.</p>
        </div>
    @endif

</body>
</html>

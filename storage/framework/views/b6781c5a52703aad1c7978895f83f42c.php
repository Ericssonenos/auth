<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $__env->yieldContent('title', 'Sistema rh'); ?></title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
     <script>
        window.AppErro = <?php echo json_encode(session('erro', (object) []), 512) ?>;
    </script>
    <?php echo $__env->yieldPushContent('styles'); ?>



    <script src="<?php echo e(asset('js/layout/setup-csrf.js')); ?>"></script>
    <script src="<?php echo e(asset('js/bibliotecas/datatables/datatables.js')); ?>"></script>
    <script src="<?php echo e(asset('js/bibliotecas/datatables/default.js')); ?>"></script>
    <link href="<?php echo e(asset('js/bibliotecas/datatables/datatables.css')); ?>" rel="stylesheet">


    <?php if(file_exists(public_path('build/manifest.json'))): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/css/main.css', 'resources/js/app.js']); ?>
    <?php else: ?>
        
        <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('css/main.css')); ?>">
        <script src="<?php echo e(asset('js/app.js')); ?>" defer></script>
    <?php endif; ?>

</head>

<body>

    <?php if (! empty(trim($__env->yieldContent('header')))): ?>
        <?php echo $__env->yieldContent('header'); ?>
    <?php else: ?>
        <header class="header">
            <div class="brand">
                <img src="<?php echo e(asset('favicon.png')); ?>" alt="Logo" class="brand-logo" />
                <span class="title-supplytek">SUPPLYTEK</span>
            </div>

            <div class="nav-actions d-flex align-items-center gap-2">
                <?php if (\Illuminate\Support\Facades\Blade::check('usuarioLogado')): ?>
                    <span class="user-name"><?php echo e($dadosUsuario->nome_completo ?? 'Usuário'); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-ghost">Sair</button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">Entrar</a>
                <?php endif; ?>
            </div>
        </header>

        <nav class="navbar navbar-expand-lg navbar-light bg-light" aria-label="Navegação principal">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('home.view') ? 'active' : ''); ?>"
                            href="<?php echo e(route('home.view')); ?>">
                            <i class="bi bi-house-door-fill me-1" aria-hidden="true"></i>
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?php echo e(route('em-desenvolvimento')); ?>"
                            id="navbarDropdownMaquinas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear-wide-connected me-1" aria-hidden="true"></i>
                            Máquinas
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMaquinas" role="menu">
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>"><i
                                        class="bi bi-lightning-charge me-2" aria-hidden="true"></i>MIG/MAG</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>"><i
                                        class="bi bi-droplet me-2" aria-hidden="true"></i>TIG</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>"><i
                                        class="bi bi-hammer me-2" aria-hidden="true"></i>MMA (Eletrodo)</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>"><i
                                        class="bi bi-layers me-2" aria-hidden="true"></i>Multiprocesso</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>"><i
                                        class="bi bi-scissors me-2" aria-hidden="true"></i>Plasma</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?php echo e(route('em-desenvolvimento')); ?>"
                            id="navbarDropdownCons" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-seam me-1" aria-hidden="true"></i>
                            Consumíveis
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownCons" role="menu">
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>">Arames</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>">Eletrodos</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>">Gases</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>">Bicos e Ponteiras</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?php echo e(route('em-desenvolvimento')); ?>"
                            id="navbarDropdownSol" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid-3x3-gap me-1" aria-hidden="true"></i>
                            Soluções
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownSol" role="menu">
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>">Automotiva</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>">Metalmecânica</a>
                            </li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>">Oil & Gas</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('em-desenvolvimento')); ?>">Construção</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?php echo e(route('em-desenvolvimento')); ?>"
                            id="navbarDropdownProc" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-diagram-3 me-1" aria-hidden="true"></i>
                            Processos Internos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownProc" role="menu">
                            <li><?php echo \App\Helpers\BladeHelpers::hrefPermissa('usuario.view', 'Cadastros', 'bi bi-people', 'dropdown-item'); ?></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('em-desenvolvimento')); ?>"><i class="bi bi-tools me-1"
                                aria-hidden="true"></i>Assistência Técnica</a>
                    </li>

                </ul>

            </div>

            <ul class="navbar-nav ms-auto gap-2 p-1" style="flex-direction: row;!important">
                <li class="nav-item  ">
                    <a class=" btn btn-outline-danger" href="<?php echo e(route('em-desenvolvimento')); ?>"><i class="bi bi-tag me-1"
                            aria-hidden="true"></i>Ofertas</a>
                </li>
                <li class="nav-item">
                     <a class=" btn btn-outline-warning" href="<?php echo e(route('orcamento.listar')); ?>" aria-label="Solicitar orçamento">

                        <i class="bi bi-clipboard-check me-1" aria-hidden="true"></i>
                        Solicitar Orçamento
                    </a>
                </li>
            </ul>

        </nav>
    <?php endif; ?>


    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer>
        <?php echo $__env->yieldContent('footer'); ?>
    </footer>

    
    <script>
        (function() {
            const btn = document.getElementById('filtersToggle');
            if (!btn) return;
            btn.addEventListener('click', () => {
                document.documentElement.classList.toggle('filters-open');
                btn.classList.toggle('has-active');
            });
            // fecha com Esc
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') document.documentElement.classList.remove('filters-open');
            });
        })();
    </script>

    <!-- Scripts das pǭginas -->
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\Repositorio\Mind\auth\resources\views/layout/main.blade.php ENDPATH**/ ?>
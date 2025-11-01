<?php $__env->startSection('title', 'Login'); ?>


<?php $__env->startSection('header'); ?>
    <header class="login-top">
        <div class="d-flex align-items-center justify-content-center gap-3 mx-auto">
            <img src="<?php echo e(asset('icon-512.png')); ?>" alt="Logo" class="logo-img" onerror="this.style.display='none'" />
        </div>
    </header>
    <style>
        main {
            margin-top: 0px !important;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/login.css']); ?>
<?php $__env->stopPush(); ?>

<!-- estilos movidos para resources/css/login.css e importados em app.css -->

<?php $__env->startSection('content'); ?>
    <div class="min-vh-100 d-flex align-items-center justify-content-center py-5 bg-login">
        <div class="shadow-sm w-100 bg-gradient-gear card" style="max-width:420px;">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <?php if(file_exists(public_path('images/apresentacao/logo.png'))): ?>
                        <img src="<?php echo e(asset('images/apresentacao/logo.png')); ?>" alt="Logo" class="logo-img me-3"
                            onerror="this.style.display='none'" />
                    <?php endif; ?>
                    <div>
                         <h3 id="login-title" class=" text-center text-warning card-title mb-0">
                            <i class="bi bi-shield-lock p-2 text-gradient-gear"></i>
                            <span class="title-supplytek">SUPPLYTEK</span>
                        </h3>
                    </div>
                </div>

                <?php if(session('status')): ?>
                    <div class="alert alert-success" role="status"><?php echo e(session('status')); ?></div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>


                <form method="POST" action="<?php echo e(url('/api/logar')); ?>" class="mt-3">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label for="email" class=" form-label">Email</label>
                        <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required autofocus
                            class="form-control" placeholder="seu.email@empresa.com" />
                    </div>

                    <div class="mb-3 w-100">
                        <label for="senha" class="form-label">Senha</label>
                        <input id="senha" name="senha" type="password" required class="form-control"
                            placeholder="Sua senha" />
                    </div>


                    <button type="submit" class="btn btn-primary w-100" aria-label="Entrar no sistema">Entrar no
                        Sistema</button>
                </form>

                <div class="text-center  mt-3 small">Precisa de ajuda? <a
                        href="mailto:suporte@empresa.com">suporte@empresa.com</a></div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Repositorio\Mind\auth\resources\views/auth/login.blade.php ENDPATH**/ ?>
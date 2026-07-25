<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title><?php echo e($title ?? 'Dashboard'); ?> - RIS UKRI</title>

    <link href="<?php echo e(asset('assets/img/Logo UKRI.png')); ?>" rel="icon">
    <link href="<?php echo e(asset('assets/img/Logo UKRI.png')); ?>" rel="apple-touch-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <link href="/assets/css/style.css" rel="stylesheet">

    <style>
        /* Compact Form Styling */
        .card-body {
            padding: 1rem 1.25rem !important;
        }
        .form-label {
            margin-bottom: 0.25rem !important;
            font-size: 0.85rem !important;
        }
        .form-control-sm, .form-select-sm {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.85rem !important;
        }
        .mb-3, .mb-4 {
            margin-bottom: 0.75rem !important;
        }
        .row.g-3 {
            --bs-gutter-y: 0.5rem !important;
            --bs-gutter-x: 0.75rem !important;
        }
        .card {
            margin-bottom: 0.75rem !important;
        }
        .pagetitle {
            margin-bottom: 0.5rem !important;
        }

        /* Spreadsheet-style direct editable cells */
        .table-editable-cell {
            border: 1px solid transparent !important;
            background-color: transparent !important;
            box-shadow: none !important;
            padding: 0.25rem 0.4rem !important;
            border-radius: 0.2rem !important;
            transition: all 0.15s ease-in-out;
        }
        .table-editable-cell:hover {
            border-color: #dee2e6 !important;
            background-color: #f8f9fa !important;
        }
        .table-editable-cell:focus {
            border-color: #86b7fe !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>

<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">

        <a href="<?php echo e(route('dashboard')); ?>" class="logo d-flex align-items-center">

            <img src="<?php echo e(asset('assets/img/Logo UKRI.png')); ?>">

            <span class="d-none d-lg-block">
                RIS UKRI
            </span>

        </a>

        <i class="bi bi-list toggle-sidebar-btn"></i>

    </div>

    <nav class="header-nav ms-auto">

        <ul class="d-flex align-items-center">

            <li class="nav-item dropdown pe-3">

                <a class="nav-link nav-profile d-flex align-items-center pe-0"
                   href="#"
                   data-bs-toggle="dropdown">

                    <img src="<?php echo e(asset('assets/img/Logo UKRI.png')); ?>"
                         class="rounded-circle">

                    <span class="d-none d-md-block dropdown-toggle ps-2">

                        <?php echo e(auth()->user()?->username ?? auth()->user()?->name ?? auth()->user()?->email ?? 'Guest User'); ?>


                    </span>

                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">

                    <li class="dropdown-header">

                        <h6>
                            <?php echo e(auth()->user()?->username ?? auth()->user()?->name ?? auth()->user()?->email ?? 'Guest User'); ?>

                        </h6>

                        <?php if(auth()->check() && auth()->user()->roles): ?>
                            <?php $__currentLoopData = auth()->user()->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span><?php echo e($role->name); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>

                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <?php if(Route::has('profile.edit')): ?>
                            <a class="dropdown-item d-flex align-items-center"
                            href="<?php echo e(route('profile.edit')); ?>">

                                <i class="bi bi-person"></i>
                                <span>Profile</span>

                            </a>
                        <?php endif; ?>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>

                        <?php if(auth()->guard()->check()): ?>
                            <form action="<?php echo e(route('logout')); ?>" method="POST">

                                <?php echo csrf_field(); ?>

                                <button type="submit"
                                        class="dropdown-item d-flex align-items-center">

                                    <i class="bi bi-box-arrow-right"></i>

                                    <span>Logout</span>

                                </button>

                            </form>
                        <?php else: ?>
                            <a class="dropdown-item d-flex align-items-center" href="<?php echo e(route('login')); ?>">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>Login</span>
                            </a>
                        <?php endif; ?>

                    </li>

                </ul>

            </li>

        </ul>

    </nav>

</header>

<?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main id="main" class="main">

    <?php echo $__env->yieldContent('content'); ?>

</main>

<footer id="footer" class="footer">

    <div class="copyright">

        &copy; <strong>RIS UKRI</strong>

    </div>

    <div class="credits">

        Universitas Kebangsaan Republik Indonesia

    </div>

</footer>

<script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="/assets/vendor/echarts/echarts.min.js"></script>
<script src="/assets/vendor/simple-datatables/simple-datatables.js"></script>

<script src="/assets/js/main.js"></script>

<?php echo $__env->yieldPushContent('scripts'); ?>

</body>

</html><?php /**PATH D:\project\simantap\ris-ukri\resources\views/layouts/app.blade.php ENDPATH**/ ?>
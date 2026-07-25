<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="<?php echo e(csrf_token()); ?>">

    <title>
        <?php echo e($title ?? 'UKRI SSO - Centralized Authentication'); ?>

    </title>

    <meta content=""
          name="description">

    <meta content=""
          name="keywords">

    <!-- Favicons -->
    <link href="<?php echo e(asset('assets/img/Logo UKRI.png')); ?>"
          rel="icon">

    <link href="<?php echo e(asset('assets/img/Logo UKRI.png')); ?>"
          rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link rel="preconnect"
          href="https://fonts.gstatic.com">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
          rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="<?php echo e(asset('assets/vendor/bootstrap/css/bootstrap.min.css')); ?>"
          rel="stylesheet">

    <link href="<?php echo e(asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')); ?>"
          rel="stylesheet">

    <link href="<?php echo e(asset('assets/vendor/boxicons/css/boxicons.min.css')); ?>"
          rel="stylesheet">

    <link href="<?php echo e(asset('assets/vendor/quill/quill.snow.css')); ?>"
          rel="stylesheet">

    <link href="<?php echo e(asset('assets/vendor/quill/quill.bubble.css')); ?>"
          rel="stylesheet">

    <link href="<?php echo e(asset('assets/vendor/remixicon/remixicon.css')); ?>"
          rel="stylesheet">

    <link href="<?php echo e(asset('assets/vendor/simple-datatables/style.css')); ?>"
          rel="stylesheet">

    <!-- Main CSS -->
    <link href="<?php echo e(asset('assets/css/style.css')); ?>"
          rel="stylesheet">

    <?php echo $__env->yieldPushContent('styles'); ?>

</head>

<body>

    <main>

        <div class="container">

            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">

                <div class="container">

                    <div class="row justify-content-center">

                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                            
                            <div class="w-100">

                                <?php echo e($slot); ?>


                            </div>

                        </div>

                    </div>

                </div>

            </section>

        </div>

    </main>

    <a href="#"
       class="back-to-top d-flex align-items-center justify-content-center">

        <i class="bi bi-arrow-up-short"></i>

    </a>

    <!-- Vendor JS Files -->
    <script src="<?php echo e(asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/vendor/apexcharts/apexcharts.min.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/vendor/chart.js/chart.umd.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/vendor/echarts/echarts.min.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/vendor/quill/quill.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/vendor/simple-datatables/simple-datatables.js')); ?>"></script>

    <script src="<?php echo e(asset('assets/vendor/tinymce/tinymce.min.js')); ?>"></script>

    <!-- Main JS -->
    <script src="<?php echo e(asset('assets/js/main.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>

</html><?php /**PATH D:\project\simantap\ris-ukri\resources\views/layouts/guest.blade.php ENDPATH**/ ?>
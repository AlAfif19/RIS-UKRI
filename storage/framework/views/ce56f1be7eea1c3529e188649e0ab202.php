<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">
        
        <li class="nav-item">

            <a class="nav-link <?php echo e(request()->routeIs('dashboard-analitik.index') ? '' : 'collapsed'); ?>"
            href="<?php echo e(route('dashboard-analitik.index')); ?>">

                <i class="bi bi-bar-chart-line"></i>
                <span>Dashboard Analitik</span>

            </a>

        </li>

        
        <li class="nav-item">

            <a class="nav-link <?php echo e(request()->routeIs('publikasi.*') ? '' : 'collapsed'); ?>"
            href="<?php echo e(route('publikasi.index')); ?>">

                <i class="bi bi-journal-text"></i>
                <span>Publikasi Karya</span>

            </a>

        </li>

        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'admin')): ?>
            
            <li class="nav-item">

                <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? '' : 'collapsed'); ?>"
                href="<?php echo e(route('dashboard')); ?>">

                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>

                </a>

            </li>
        <?php endif; ?>



    </ul>

</aside><?php /**PATH D:\project\simantap\ris-ukri\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>
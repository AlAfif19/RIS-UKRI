<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">
        {{-- Dashboard Analitik --}}
        <li class="nav-item">

            <a class="nav-link {{ request()->routeIs('dashboard-analitik.index') ? '' : 'collapsed' }}"
            href="{{ route('dashboard-analitik.index') }}">

                <i class="bi bi-bar-chart-line"></i>
                <span>Dashboard Analitik</span>

            </a>

        </li>

        {{-- Publikasi Karya --}}
        <li class="nav-item">

            <a class="nav-link {{ request()->routeIs('publikasi.*') ? '' : 'collapsed' }}"
            href="{{ route('publikasi.index') }}">

                <i class="bi bi-journal-text"></i>
                <span>Publikasi Karya</span>

            </a>

        </li>

        @role('admin')
            {{-- Dashboard --}}
            <li class="nav-item">

                <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}"
                href="{{ route('dashboard') }}">

                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>

                </a>

            </li>
        @endrole



    </ul>

</aside>
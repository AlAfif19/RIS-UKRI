<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">
        @role('admin|dosen')
            {{-- Dashboard Analitik --}}
            <li class="nav-item">

                <a class="nav-link {{ request()->routeIs('dashboard-analitik.index') ? '' : 'collapsed' }}"
                href="{{ route('dashboard-analitik.index') }}">

                    <i class="bi bi-bar-chart-line"></i>
                    <span>Dashboard Analitik</span>

                </a>

            </li>
        @endrole

        {{-- Publikasi Karya --}}
        <li class="nav-item">

            <a class="nav-link {{ request()->routeIs('publikasi.*') ? '' : 'collapsed' }}"
            href="{{ route('publikasi.index') }}">

                <i class="bi bi-journal-text"></i>
                <span>Publikasi Karya</span>

            </a>

        </li>



    </ul>

</aside>
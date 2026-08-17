@extends('layouts.app')

@if(auth()->user()->hasRole('admin'))
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush
@endif

@section('content')

    @php
        // Dosen (non-admin) tidak perlu melihat kartu/ranking yang isinya
        // seluruh dosen/mahasiswa (data dosen sudah dibatasi hanya ke
        // publikasi miliknya sendiri di DashboardAnalitikController, jadi
        // "Dosen Aktif", "Mahasiswa Aktif", dan ranking Top 10 tidak relevan
        // buat dia - selalu berisi dirinya sendiri saja).
        $isAdmin = auth()->user()->hasRole('admin');
    @endphp

    <div class="pagetitle">
        <h1>Dashboard Analitik Publikasi Karya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard-analitik.index') }}">Home</a></li>
                <li class="breadcrumb-item active">Dashboard Analitik</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <!-- Filter Global -->
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body py-3">
                <form id="filterForm" method="GET" action="{{ url()->current() }}" class="row g-2 align-items-end">
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Dari Tanggal</label>
                        <input type="date" name="tanggal_dari" id="filter-tanggal-dari" class="form-control form-control-sm"
                            value="{{ request('tanggal_dari') }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                        <input type="date" name="tanggal_sampai" id="filter-tanggal-sampai" class="form-control form-control-sm"
                            value="{{ request('tanggal_sampai') }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Afiliasi</label>
                        <select name="afiliasi" id="filter-afiliasi" class="form-select form-select-sm">
                            <option value="">-- Semua Afiliasi --</option>
                            @foreach($afiliasiList as $nama)
                                <option value="{{ $nama }}" {{ request('afiliasi') == $nama ? 'selected' : '' }}>
                                    {{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Program Studi</label>
                        <select name="prodi_id" id="filter-prodi" class="form-select form-select-sm">
                            <option value="">-- Semua Prodi --</option>
                            @foreach($prodiList as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label small fw-bold text-secondary">Kategori Capaian</label>
                        <select name="kategori_capaian" id="filter-kategori-capaian" class="form-select form-select-sm">
                            <option value="">-- Semua Kategori Capaian --</option>
                            <option value="Publikasi" {{ request('kategori_capaian') == 'Publikasi' ? 'selected' : '' }}>
                                Publikasi</option>
                            <option value="Produk Teknologi Tepat Guna" {{ request('kategori_capaian') == 'Produk Teknologi Tepat Guna' ? 'selected' : '' }}>Produk Teknologi Tepat Guna</option>
                            <option value="Jenis Luaran Lainnya" {{ request('kategori_capaian') == 'Jenis Luaran Lainnya' ? 'selected' : '' }}>Jenis Luaran Lainnya</option>
                            <option value="HKI" {{ request('kategori_capaian') == 'HKI' ? 'selected' : '' }}>HKI</option>
                            <option value="Buku" {{ request('kategori_capaian') == 'Buku' ? 'selected' : '' }}>Buku</option>
                            <option value="Pembicara" {{ request('kategori_capaian') == 'Pembicara' ? 'selected' : '' }}>
                                Pembicara</option>
                            <option value="Visiting Scientist" {{ request('kategori_capaian') == 'Visiting Scientist' ? 'selected' : '' }}>Visiting Scientist</option>
                        </select>
                    </div>
                    @if($isAdmin)
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small fw-bold text-secondary">Nama Dosen</label>
                            <select name="dosen_id" id="filter-nama-dosen" class="form-select form-select-sm">
                                <option value="">-- Semua Dosen --</option>
                                @foreach($dosenList as $dsn)
                                    <option value="{{ $dsn->id }}" {{ request('dosen_id') == $dsn->id ? 'selected' : '' }}>
                                        {{ $dsn->nama }}{{ $dsn->nidn ? " ({$dsn->nidn})" : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-2 col-sm-12 d-flex gap-1">
                        <a href="{{ url()->current() }}" id="filter-reset" class="btn btn-sm btn-outline-secondary flex-fill" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI Cards (Row Atas) -->
        <div class="row g-3 mb-3">
            <!-- 1. Total Publikasi -->
            <div class="col-lg-{{ $isAdmin ? '2' : '3' }} col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 4px solid #0d6efd !important;">
                    <div class="card-body py-3">
                        <div class="text-muted small fw-bold">TOTAL PUBLIKASI</div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="badge bg-primary-light text-primary p-2 rounded-circle me-3">
                                <i class="bi bi-journal-bookmark-fill fs-5"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="kpi-total-publikasi">{{ $totalPublikasi }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Publikasi Tahun Ini -->
            <div class="col-lg-{{ $isAdmin ? '2' : '3' }} col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body py-3">
                        <div class="text-muted small fw-bold">TAHUN INI</div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="badge bg-success-light text-success p-2 rounded-circle me-3">
                                <i class="bi bi-calendar-check fs-5"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="kpi-publikasi-tahun-ini">{{ $publikasiTahunIni }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Total Dokumen -->
            <div class="col-lg-{{ $isAdmin ? '2' : '3' }} col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 4px solid #0dcaf0 !important;">
                    <div class="card-body py-3">
                        <div class="text-muted small fw-bold">DOKUMEN</div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="badge bg-info-light text-info p-2 rounded-circle me-3">
                                <i class="bi bi-file-earmark-pdf fs-5"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="kpi-total-dokumen">{{ $totalDokumen }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($isAdmin)
            <!-- 4. Dosen Penulis Aktif (khusus admin) -->
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 4px solid #ffc107 !important;">
                    <div class="card-body py-3">
                        <div class="text-muted small fw-bold">DOSEN AKTIF</div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="badge bg-warning-light text-warning p-2 rounded-circle me-3">
                                <i class="bi bi-person-badge fs-5"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="kpi-dosen-aktif">{{ $dosenAktif }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Mahasiswa Penulis Aktif (khusus admin) -->
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 4px solid #6f42c1 !important;">
                    <div class="card-body py-3">
                        <div class="text-muted small fw-bold">MAHASISWA AKTIF</div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="badge bg-purple-light text-purple p-2 rounded-circle me-3">
                                <i class="bi bi-person-workspace fs-5"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="kpi-mahasiswa-aktif">{{ $mahasiswaAktif }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- 6. Rata-rata Penulis -->
            <div class="col-lg-{{ $isAdmin ? '2' : '3' }} col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100 bg-white" style="border-left: 4px solid #fd7e14 !important;">
                    <div class="card-body py-3">
                        <div class="text-muted small fw-bold">RATA PENULIS</div>
                        <div class="d-flex align-items-center mt-2">
                            <div class="badge bg-orange-light text-orange p-2 rounded-circle me-3">
                                <i class="bi bi-people fs-5"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold" id="kpi-rata-penulis">{{ $rataPenulis }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row g-3 mb-3">
            <!-- Line Chart: Tren Bulanan -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-graph-up text-primary"></i> Tren Publikasi
                            per Bulan</h6>
                        <div style="position: relative; height: 320px;">
                            <canvas id="trenChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donut Chart: Kategori Capaian -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-pie-chart text-primary"></i> Kategori
                            Capaian</h6>
                        <div
                            style="position: relative; height: 320px; display: flex; align-items: center; justify-content: center;">
                            <canvas id="capaianChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row g-3 mb-3">
            <!-- Bar Chart: Kategori Kegiatan Grouped -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-bar-chart-steps text-primary"></i> Publikasi
                            per Kategori Kegiatan</h6>
                        <div style="position: relative; height: 300px;">
                            <canvas id="kategoriChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donut Chart: Proporsi Penulis -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-people-fill text-primary"></i> Proporsi
                            Jenis Penulis</h6>
                        <div
                            style="position: relative; height: 300px; display: flex; align-items: center; justify-content: center;">
                            <canvas id="proporsiChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donut Chart: Distribusi Jenis Dokumen -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-file-earmark-bar-graph text-primary"></i>
                            Distribusi Jenis Dokumen</h6>
                        <div
                            style="position: relative; height: 300px; display: flex; align-items: center; justify-content: center;">
                            <canvas id="docDistChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 3 -->
        <div class="row g-3 mb-3">
            <!-- Bar Chart: Distribusi Publikasi Berdasarkan Jenis Publikasi -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-journal-text text-primary"></i> Distribusi
                            Publikasi Berdasarkan Jenis Publikasi</h6>
                        <div style="position: relative; height: 350px;">
                            <canvas id="jenisPublikasiChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($isAdmin)
        <!-- Leaderboards Row 1 (khusus admin - dosen hanya melihat datanya sendiri, jadi ranking Top 10 tidak relevan) -->
        <div class="row g-3 mb-3">
            <!-- 1. Dosen Paling Produktif -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-award text-warning"></i> Top 10 Dosen Paling
                            Produktif</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 8%">No</th>
                                        <th>NIDN</th>
                                        <th>Nama Dosen</th>
                                        <th class="text-end" style="width: 20%">Publikasi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-top-dosen">
                                    @forelse($topDosen as $idx => $d)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td><code>{{ $d->nidn }}</code></td>
                                            <td>{{ $d->nama }}</td>
                                            <td class="text-end fw-bold text-primary">{{ $d->jumlah }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada data kontribusi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Mahasiswa Paling Produktif -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-award text-success"></i> Top 10 Mahasiswa
                            Paling Produktif</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 8%">No</th>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Prodi</th>
                                        <th class="text-end" style="width: 15%">Publikasi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-top-mahasiswa">
                                    @forelse($topMahasiswa as $idx => $m)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td><code>{{ $m->nim }}</code></td>
                                            <td>{{ $m->nama }}</td>
                                            <td>{{ $m->prodi ?? '-' }}</td>
                                            <td class="text-end fw-bold text-success">{{ $m->jumlah }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Belum ada data kontribusi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Leaderboards Row 2 -->
        <div class="row g-3 mb-3">
            <!-- 3. Afiliasi Kontributor -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-building text-info"></i> Kontribusi per
                            Afiliasi</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 8%">No</th>
                                        <th>Afiliasi</th>
                                        <th class="text-end" style="width: 25%">Total Kontribusi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-top-afiliasi">
                                    @forelse($topAfiliasi as $idx => $afiliasi)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $afiliasi->afiliasi }}</td>
                                            <td class="text-end fw-bold text-info">{{ $afiliasi->jumlah }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada data kontribusi afiliasi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Jurnal Terpopuler -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 bg-white">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-bookmark-star text-danger"></i> Top 10
                            Jurnal Terpopuler</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 8%">No</th>
                                        <th>Nama Jurnal</th>
                                        <th class="text-end" style="width: 25%">Total Artikel</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-top-jurnal">
                                    @forelse($topJurnal as $idx => $j)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $j->nama_jurnal }}</td>
                                            <td class="text-end fw-bold text-danger">{{ $j->jumlah }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada data jurnal.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Kelengkapan Data -->
        <div class="card border-0 shadow-sm bg-white mt-3">
            <div class="card-body py-3">
                <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-shield-exclamation text-warning"></i> Kelengkapan
                    Data & Kualitas</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light-warning d-flex align-items-center">
                            <div class="fs-1 text-warning me-3"><i class="bi bi-link-45deg"></i></div>
                            <div>
                                <div class="small text-muted fw-bold">PUBLIKASI TANPA DOI</div>
                                <h3 class="mb-0 fw-bold" id="kpi-tanpa-doi">{{ $countTanpaDoi }}</h3>
                                <small class="text-secondary">Memerlukan input DOI untuk sitasi ilmiah.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold mb-2 small text-secondary">Daftar Publikasi Tanpa DOI (Maksimal 10 Terbaru)
                            </div>
                            <ul class="list-unstyled mb-0 small" id="list-tanpa-doi">
                                @forelse($publikasiTanpaDoi as $ptd)
                                    <li class="mb-1 d-flex justify-content-between align-items-center">
                                        <span class="text-truncate" style="max-width: 80%"><i
                                                class="bi bi-file-earmark-text me-1 text-secondary"></i>
                                            {{ $ptd->judul }}</span>
                                        <a href="{{ route('publikasi.edit', $ptd->id) }}"
                                            class="btn btn-sm btn-link py-0 fw-bold text-decoration-none">
                                            Lengkapi <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i> Semua
                                        publikasi sudah terisi DOI!</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom Styling for badge background -->
    <style>
        .bg-primary-light {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-info-light {
            background-color: rgba(13, 202, 240, 0.1);
        }

        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .bg-purple-light {
            background-color: rgba(111, 66, 193, 0.1);
        }

        .bg-orange-light {
            background-color: rgba(253, 126, 20, 0.1);
        }

        .bg-light-warning {
            background-color: #fffdf5;
            border-color: #ffecb5 !important;
        }

        .text-purple {
            color: #6f42c1;
        }

        .text-orange {
            color: #fd7e14;
        }
    </style>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @if(auth()->user()->hasRole('admin'))
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    @endif
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const charts = {};

            // Helper options for pie/doughnut charts
            const donutOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            };

            function initCharts(data) {
                // 1. Line Chart: Tren Bulanan
                charts.tren = new Chart(document.getElementById('trenChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.trenLabels,
                        datasets: [{
                            label: 'Jumlah Publikasi',
                            data: data.trenData,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.05)',
                            borderWidth: 3,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 4,
                            pointBackgroundColor: '#0d6efd'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });

                // 2. Doughnut Chart: Kategori Capaian
                charts.capaian = new Chart(document.getElementById('capaianChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.capaianLabels,
                        datasets: [{
                            data: data.capaianData,
                            backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#6f42c1', '#fd7e14', '#20c997', '#6c757d']
                        }]
                    },
                    options: donutOptions
                });

                // 3. Bar Chart: Kategori Kegiatan Grouped
                charts.kategori = new Chart(document.getElementById('kategoriChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(data.grupKategori),
                        datasets: [{
                            label: 'Artikel',
                            data: Object.values(data.grupKategori),
                            backgroundColor: ['#0d6efd', '#ffc107', '#fd7e14', '#198754'],
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { ticks: { font: { size: 9 } } }
                        }
                    }
                });

                // 4. Doughnut Chart: Proporsi Penulis
                charts.proporsi = new Chart(document.getElementById('proporsiChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.proporsiLabels,
                        datasets: [{
                            data: data.proporsiData,
                            backgroundColor: ['#ffc107', '#6f42c1', '#0dcaf0']
                        }]
                    },
                    options: donutOptions
                });

                // 5. Bar Chart: Distribusi Publikasi Berdasarkan Jenis Publikasi
                charts.jenisPublikasi = new Chart(document.getElementById('jenisPublikasiChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.jenisPublikasiLabels,
                        datasets: [{
                            label: 'Jumlah Publikasi',
                            data: data.jenisPublikasiData,
                            backgroundColor: '#0d6efd',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });

                // 6. Doughnut Chart: Distribusi Jenis Dokumen
                charts.docDist = new Chart(document.getElementById('docDistChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.docDistLabels,
                        datasets: [{
                            data: data.docDistData,
                            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#6c757d']
                        }]
                    },
                    options: donutOptions
                });
            }

            function updateCharts(data) {
                charts.tren.data.labels = data.trenLabels;
                charts.tren.data.datasets[0].data = data.trenData;
                charts.tren.update();

                charts.capaian.data.labels = data.capaianLabels;
                charts.capaian.data.datasets[0].data = data.capaianData;
                charts.capaian.update();

                charts.kategori.data.labels = Object.keys(data.grupKategori);
                charts.kategori.data.datasets[0].data = Object.values(data.grupKategori);
                charts.kategori.update();

                charts.proporsi.data.labels = data.proporsiLabels;
                charts.proporsi.data.datasets[0].data = data.proporsiData;
                charts.proporsi.update();

                charts.jenisPublikasi.data.labels = data.jenisPublikasiLabels;
                charts.jenisPublikasi.data.datasets[0].data = data.jenisPublikasiData;
                charts.jenisPublikasi.update();

                charts.docDist.data.labels = data.docDistLabels;
                charts.docDist.data.datasets[0].data = data.docDistData;
                charts.docDist.update();
            }

            // Initial render pakai data yang sudah dikirim controller saat load pertama (server-side render)
            initCharts({
                trenLabels: {!! json_encode($trenLabels) !!},
                trenData: {!! json_encode($trenData) !!},
                capaianLabels: {!! json_encode($capaianLabels) !!},
                capaianData: {!! json_encode($capaianData) !!},
                grupKategori: {!! json_encode($grupKategori) !!},
                proporsiLabels: {!! json_encode($proporsiLabels) !!},
                proporsiData: {!! json_encode($proporsiData) !!},
                jenisPublikasiLabels: {!! json_encode($jenisPublikasiLabels) !!},
                jenisPublikasiData: {!! json_encode($jenisPublikasiData) !!},
                docDistLabels: {!! json_encode($docDistLabels) !!},
                docDistData: {!! json_encode($docDistData) !!}
            });

            // ================= FILTER (AJAX, tanpa perlu tekan Enter) =================

            const form = document.getElementById('filterForm');
            const inputDari = document.getElementById('filter-tanggal-dari');
            const inputSampai = document.getElementById('filter-tanggal-sampai');
            const selectAfiliasi = document.getElementById('filter-afiliasi');
            const selectProdi = document.getElementById('filter-prodi');
            const selectCapaian = document.getElementById('filter-kategori-capaian');
            const selectDosenEl = document.getElementById('filter-nama-dosen'); // hanya ada untuk admin
            const resetLink = document.getElementById('filter-reset');

            // Tom Select untuk Nama Dosen (searchable) - dropdown ini bisa
            // berisi banyak dosen, jadi dibuat bisa dicari sama seperti pola
            // yang sudah dipakai di form Publikasi Karya (create/edit.blade.php).
            let tsDosen = null;
            if (selectDosenEl) {
                tsDosen = new TomSelect(selectDosenEl, {
                    placeholder: 'Cari nama dosen...',
                    allowEmptyOption: true,
                    maxOptions: null,
                    onChange: () => { clearTimeout(debounceTimer); fetchDashboard(); },
                });
            }
            let debounceTimer = null;
            let activeController = null; // untuk membatalkan request yang sudah usang
            let requestSeq = 0; // penjaga tambahan: kalau respons lama (usang) telat
                                 // datang SETELAH respons yang lebih baru (race
                                 // condition jaringan), abort() saja tidak cukup -
                                 // respons yang telat ini diabaikan lewat nomor urut,
                                 // sama seperti pola di initJurnalSelect/cek-judul.

            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                })[c]);
            }

            function buildParams() {
                const params = new URLSearchParams();
                if (inputDari.value) params.set('tanggal_dari', inputDari.value);
                if (inputSampai.value) params.set('tanggal_sampai', inputSampai.value);
                if (selectAfiliasi.value) params.set('afiliasi', selectAfiliasi.value);
                if (selectProdi.value) params.set('prodi_id', selectProdi.value);
                if (selectCapaian.value) params.set('kategori_capaian', selectCapaian.value);
                if (selectDosenEl && selectDosenEl.value) params.set('dosen_id', selectDosenEl.value);
                return params;
            }

            function setLoading(isLoading) {
                form.classList.toggle('opacity-50', isLoading);
                document.querySelector('.dashboard').style.pointerEvents = isLoading ? 'none' : '';
            }

            function fetchDashboard() {
                const params = buildParams();
                const url = `${window.location.pathname}?${params.toString()}`;

                // batalkan request sebelumnya kalau masih berjalan (hindari race condition antar filter)
                if (activeController) activeController.abort();
                activeController = new AbortController();
                const seq = ++requestSeq;

                setLoading(true);

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: activeController.signal
                })
                    .then(res => res.json())
                    .then(data => {
                        // Respons yang telat datang (nomor urutnya sudah dilewati
                        // request lain yang lebih baru) diabaikan, walau
                        // request-nya sendiri sempat lolos dari abort().
                        if (seq !== requestSeq) return;
                        updateDashboard(data);
                        window.history.replaceState(null, '', url);
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') console.error('Filter dashboard error:', err);
                    })
                    .finally(() => {
                        if (seq === requestSeq) setLoading(false);
                    });
            }

            function updateDashboard(data) {
                // KPI Cards
                // PERBAIKAN: pakai helper yang cek null dulu - beberapa kartu
                // (Dosen Aktif, Mahasiswa Aktif) sengaja tidak dirender sama
                // sekali di halaman untuk role dosen (lihat kondisi isAdmin di
                // atas), jadi getElementById-nya bisa null. Sebelumnya baris
                // ini langsung .textContent tanpa cek, jadi begitu ketemu
                // elemen yang null langsung error dan seluruh proses update
                // (termasuk chart & tabel di bawahnya) ikut berhenti.
                const setText = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val;
                };
                setText('kpi-total-publikasi', data.totalPublikasi);
                setText('kpi-publikasi-tahun-ini', data.publikasiTahunIni);
                setText('kpi-total-dokumen', data.totalDokumen);
                setText('kpi-dosen-aktif', data.dosenAktif);
                setText('kpi-mahasiswa-aktif', data.mahasiswaAktif);
                setText('kpi-rata-penulis', data.rataPenulis);
                setText('kpi-tanpa-doi', data.countTanpaDoi);

                // Charts
                updateCharts(data);

                // Top Dosen (tidak dirender untuk role dosen - lihat kondisi isAdmin)
                const tbodyDosen = document.getElementById('tbody-top-dosen');
                if (tbodyDosen) {
                    tbodyDosen.innerHTML = data.topDosen.length ? data.topDosen.map((d, idx) => `
                        <tr>
                            <td>${idx + 1}</td>
                            <td><code>${escapeHtml(d.nidn)}</code></td>
                            <td>${escapeHtml(d.nama)}</td>
                            <td class="text-end fw-bold text-primary">${d.jumlah}</td>
                        </tr>
                    `).join('') : `<tr><td colspan="4" class="text-center text-muted">Belum ada data kontribusi.</td></tr>`;
                }

                // Top Mahasiswa (tidak dirender untuk role dosen)
                const tbodyMhs = document.getElementById('tbody-top-mahasiswa');
                if (tbodyMhs) {
                    tbodyMhs.innerHTML = data.topMahasiswa.length ? data.topMahasiswa.map((m, idx) => `
                        <tr>
                            <td>${idx + 1}</td>
                            <td><code>${escapeHtml(m.nim)}</code></td>
                            <td>${escapeHtml(m.nama)}</td>
                            <td>${escapeHtml(m.prodi ?? '-')}</td>
                            <td class="text-end fw-bold text-success">${m.jumlah}</td>
                        </tr>
                    `).join('') : `<tr><td colspan="5" class="text-center text-muted">Belum ada data kontribusi.</td></tr>`;
                }

                // Top Afiliasi
                const tbodyAfiliasi = document.getElementById('tbody-top-afiliasi');
                tbodyAfiliasi.innerHTML = data.topAfiliasi.length ? data.topAfiliasi.map((a, idx) => `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${escapeHtml(a.afiliasi)}</td>
                        <td class="text-end fw-bold text-info">${a.jumlah}</td>
                    </tr>
                `).join('') : `<tr><td colspan="3" class="text-center text-muted">Belum ada data kontribusi afiliasi.</td></tr>`;

                // Top Jurnal
                const tbodyJurnal = document.getElementById('tbody-top-jurnal');
                tbodyJurnal.innerHTML = data.topJurnal.length ? data.topJurnal.map((j, idx) => `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${escapeHtml(j.nama_jurnal)}</td>
                        <td class="text-end fw-bold text-danger">${j.jumlah}</td>
                    </tr>
                `).join('') : `<tr><td colspan="3" class="text-center text-muted">Belum ada data jurnal.</td></tr>`;

                // Publikasi Tanpa DOI
                const listDoi = document.getElementById('list-tanpa-doi');
                listDoi.innerHTML = data.publikasiTanpaDoi.length ? data.publikasiTanpaDoi.map(ptd => `
                    <li class="mb-1 d-flex justify-content-between align-items-center">
                        <span class="text-truncate" style="max-width: 80%"><i class="bi bi-file-earmark-text me-1 text-secondary"></i> ${escapeHtml(ptd.judul)}</span>
                        <a href="${ptd.edit_url}" class="btn btn-sm btn-link py-0 fw-bold text-decoration-none">Lengkapi <i class="bi bi-arrow-right"></i></a>
                    </li>
                `).join('') : `<li class="text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i> Semua publikasi sudah terisi DOI!</li>`;
            }

            // Submit tombol "Filter" tetap berfungsi, tapi lewat AJAX (tidak reload halaman)
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                clearTimeout(debounceTimer);
                fetchDashboard();
            });

            // Select langsung memicu filter begitu dipilih (tanpa perlu klik apapun)
            selectAfiliasi.addEventListener('change', () => {
                clearTimeout(debounceTimer);
                fetchDashboard();
            });
            selectProdi.addEventListener('change', () => {
                clearTimeout(debounceTimer);
                fetchDashboard();
            });
            selectCapaian.addEventListener('change', () => {
                clearTimeout(debounceTimer);
                fetchDashboard();
            });

            // Tanggal: debounce ringan supaya tidak fetch di setiap ketikan/geser kalender,
            // tapi tetap tanpa perlu menekan Enter atau tombol Filter
            [inputDari, inputSampai].forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(fetchDashboard, 400);
                });
                // Perubahan lewat date picker (klik tanggal) langsung memicu filter
                input.addEventListener('change', () => {
                    clearTimeout(debounceTimer);
                    fetchDashboard();
                });
            });

            // Reset filter tanpa reload halaman
            resetLink.addEventListener('click', (e) => {
                e.preventDefault();
                inputDari.value = '';
                inputSampai.value = '';
                selectAfiliasi.value = '';
                selectProdi.value = '';
                selectCapaian.value = '';
                if (tsDosen) {
                    tsDosen.clear(true); // true = jangan trigger onChange ganda, fetch manual di bawah
                } else if (selectDosenEl) {
                    selectDosenEl.value = '';
                }
                clearTimeout(debounceTimer);
                fetchDashboard();
            });
        });
    </script>
@endpush
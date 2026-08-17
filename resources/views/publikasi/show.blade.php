@extends('layouts.app')

@section('content')

<div class="pagetitle">
    <h1>Detail Publikasi Karya</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard-analitik.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('publikasi.index') }}">Publikasi Karya</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title p-0 m-0"><i class="bi bi-file-earmark-text"></i> {{ $publikasi->judul }}</h5>
                    <div>
                        <a href="{{ route('publikasi.edit', $publikasi->id) }}" class="btn btn-warning btn-sm text-white">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('publikasi.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body mt-3">

                    <table class="table table-bordered align-middle">
                        <tbody>
                            <tr>
                                <th style="width: 25%" class="table-light">Kategori Kegiatan</th>
                                <td>{{ $publikasi->kategori_kegiatan }}</td>
                            </tr>
                            <tr>
                                <th class="table-light">Jenis</th>
                                <td><span class="badge bg-info text-dark">{{ $publikasi->jenis ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <th class="table-light">Kategori Capaian</th>
                                <td>{{ $publikasi->kategori_capaian ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="table-light">Aktivitas Litabmas</th>
                                <td>{{ $publikasi->aktivitasLitabmas->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="table-light">Nama Jurnal</th>
                                <td>{{ $publikasi->nama_jurnal ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="table-light">Tautan Laman Jurnal</th>
                                <td>
                                    @if($publikasi->tautan_jurnal)
                                        <a href="{{ $publikasi->tautan_jurnal }}" target="_blank"><i class="bi bi-box-arrow-up-right"></i> {{ $publikasi->tautan_jurnal }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="table-light">Tanggal Terbit</th>
                                <td>{{ \Carbon\Carbon::parse($publikasi->tanggal_terbit)->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th class="table-light">Volume / Nomor / Halaman</th>
                                <td>Vol. {{ $publikasi->volume ?? '-' }} / No. {{ $publikasi->nomor ?? '-' }} / Hal. {{ $publikasi->halaman ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="table-light">Penerbit / Penyelenggara</th>
                                <td>{{ $publikasi->penerbit ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="table-light">DOI / ISSN / ISBN</th>
                                <td>DOI: {{ $publikasi->doi ?? '-' }} | ISSN / ISBN: {{ $publikasi->issn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="table-light">Tautan Eksternal</th>
                                <td>
                                    @if($publikasi->tautan_eksternal)
                                        <a href="{{ $publikasi->tautan_eksternal }}" target="_blank"><i class="bi bi-link-45deg"></i> {{ $publikasi->tautan_eksternal }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="table-light">Keterangan / Petunjuk Akses</th>
                                <td>{{ $publikasi->keterangan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h5 class="mt-4 mb-3"><i class="bi bi-people"></i> Daftar Penulis</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Urutan</th>
                                    <th>Kategori</th>
                                    <th>Nama Penulis</th>
                                    <th>Perguruan Tinggi / Afiliasi</th>
                                    <th>Peran</th>
                                    <th>Corresponding Author</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($publikasi->penulisDosen as $pd)
                                    <tr>
                                        <td>{{ $pd->urutan }}</td>
                                        <td><span class="badge bg-primary">Dosen</span></td>
                                        <td>{{ $pd->dosen->nama ?? '-' }} (NIDN: {{ $pd->dosen->nidn ?? '-' }})</td>
                                        <td>{{ $pd->dosen->perguruanTinggi->nama_pt ?? $pd->afiliasi }}</td>
                                        <td>{{ $pd->peran }}</td>
                                        <td>
                                            @if($pd->is_corresponding)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-check-circle-fill"></i> Ya</span>
                                            @else
                                                <span class="text-muted">Tidak</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach($publikasi->penulisMahasiswa as $pm)
                                    <tr>
                                        <td>{{ $pm->urutan }}</td>
                                        <td><span class="badge bg-success">Mahasiswa</span></td>
                                        <td>{{ $pm->mahasiswa->nama ?? '-' }} (NIM: {{ $pm->mahasiswa->nim ?? '-' }})</td>
                                        <td>{{ $pm->mahasiswa->perguruanTinggi->nama_pt ?? $pm->afiliasi }}</td>
                                        <td>{{ $pm->peran }}</td>
                                        <td>
                                            @if($pm->is_corresponding)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-check-circle-fill"></i> Ya</span>
                                            @else
                                                <span class="text-muted">Tidak</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach($publikasi->penulisLain as $pl)
                                    <tr>
                                        <td>{{ $pl->urutan }}</td>
                                        <td><span class="badge bg-secondary">Eksternal</span></td>
                                        <td>{{ $pl->nama }}</td>
                                        <td>{{ $pl->afiliasi ?? '-' }}</td>
                                        <td>{{ $pl->peran }}</td>
                                        <td>
                                            @if($pl->is_corresponding)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-check-circle-fill"></i> Ya</span>
                                            @else
                                                <span class="text-muted">Tidak</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-4 mb-3"><i class="bi bi-file-earmark-pdf"></i> Dokumen Lampiran</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Dokumen</th>
                                    <th>Nama File</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Tanggal Upload</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($publikasi->dokumen as $idx => $doc)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $doc->nama_dokumen }}</td>
                                        <td>{{ $doc->nama_file ?? '-' }}</td>
                                        <td>{{ $doc->jenis_dokumen }}</td>
                                        <td>{{ $doc->tanggal_upload }}</td>
                                        <td>
                                            @if($doc->path_file)
                                                @php
                                                    $isPdf = strtolower(pathinfo($doc->path_file, PATHINFO_EXTENSION)) === 'pdf';
                                                @endphp
                                                @if($isPdf)
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#previewDokumenModal"
                                                        data-file-url="{{ asset('storage/' . $doc->path_file) }}"
                                                        data-file-name="{{ $doc->nama_dokumen }}">
                                                        <i class="bi bi-eye"></i> Lihat
                                                    </button>
                                                @endif
                                                <a href="{{ asset('storage/' . $doc->path_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download"></i> Unduh File
                                                </a>
                                            @elseif($doc->tautan_dokumen)
                                                <a href="{{ $doc->tautan_dokumen }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-box-arrow-up-right"></i> Buka Tautan
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada dokumen lampiran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

{{-- Modal Preview Dokumen (PDF) --}}
<div class="modal fade" id="previewDokumenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="height: 85vh;">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="previewDokumenModalLabel">
                    <i class="bi bi-file-earmark-pdf text-danger"></i> <span id="previewDokumenNama"></span>
                </h6>
                <a href="#" id="previewDokumenBukaTab" target="_blank" class="btn btn-sm btn-outline-primary ms-auto me-2">
                    <i class="bi bi-box-arrow-up-right"></i> Buka di tab baru
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="previewDokumenIframe" src="" style="width: 100%; height: 100%; border: 0;"></iframe>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const modalEl = document.getElementById('previewDokumenModal');
        if (!modalEl) return;

        const iframe = document.getElementById('previewDokumenIframe');
        const namaEl = document.getElementById('previewDokumenNama');
        const bukaTabBtn = document.getElementById('previewDokumenBukaTab');

        modalEl.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const url = trigger.getAttribute('data-file-url');
            const nama = trigger.getAttribute('data-file-name');

            iframe.src = url;
            namaEl.textContent = nama || 'Dokumen';
            bukaTabBtn.href = url;
        });

        // Hentikan load PDF saat modal ditutup, biar tidak tetap jalan di background.
        modalEl.addEventListener('hidden.bs.modal', function () {
            iframe.src = '';
        });
    })();
</script>
@endpush

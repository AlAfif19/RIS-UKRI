@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Dropdown pencarian mahasiswa: defaultnya cuma ~200px (kelihatan
           sebagian) - dibesarkan supaya lebih mudah dibaca & diklik. */
        .ts-dropdown .ts-dropdown-content {
            max-height: 320px;
        }
        .ts-dropdown .option {
            padding: 10px 14px;
            font-size: 0.95rem;
        }
        .ts-dropdown {
            z-index: 9999;
        }
    </style>
@endpush

@section('content')

<div class="pagetitle">
    <h1>Edit Publikasi Karya</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('publikasi.index') }}">Publikasi Karya</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Terdapat kesalahan pengisian form:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('publikasi.update', $publikasi->id) }}" method="POST" enctype="multipart/form-data" id="formPublikasiEdit">
                @csrf
                @method('PUT')

                {{-- Halaman & kata pencarian asal (dari daftar Publikasi Karya) -
                     dibawa balik ke update() supaya setelah simpan, user kembali
                     ke halaman yang sama, bukan selalu ke page 1. --}}
                <input type="hidden" name="return_page" value="{{ $returnPage }}">
                <input type="hidden" name="return_search" value="{{ $returnSearch }}">

                <!-- CARD 1: Kategori & Detail Publikasi -->
                <div class="card mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-journal-bookmark me-1"></i> Detail Publikasi Karya
                    </div>
                    <div class="card-body mt-3">

                        <!-- 1. Kategori Kegiatan Dropdown -->
                        <div class="mb-3">
                            <label for="kategori_kegiatan" class="form-label fw-bold">Kategori Kegiatan <span class="text-danger">*</span></label>
                            <select name="kategori_kegiatan" id="kategori_kegiatan" class="form-select form-select-sm" required onchange="syncJenis(this.value)">
                                <option value="">-- Pilih Kategori Kegiatan --</option>

                                <optgroup label="Pelaksanaan Penelitian › Menghasilkan Karya Ilmiah sesuai dengan bidangnya">
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk monograf" data-jenis="Monograf" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk monograf' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk monograf</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk buku referensi" data-jenis="Buku Referensi" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk buku referensi' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk buku referensi</option>
                                    <option value="Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) internasional" data-jenis="Book Chapter Internasional" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) internasional' ? 'selected' : '' }}>Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) internasional</option>
                                    <option value="Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) nasional" data-jenis="Book Chapter Nasional" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) nasional' ? 'selected' : '' }}>Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) nasional</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional bereputasi" data-jenis="Jurnal Internasional Bereputasi" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional bereputasi' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional bereputasi</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada database internasional bereputasi" data-jenis="Jurnal Internasional Terindeks Database Bereputasi" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada database internasional bereputasi' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada database internasional bereputasi</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada basis data internasional" data-jenis="Jurnal Internasional Terindeks Basis Data" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada basis data internasional' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada basis data internasional</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti" data-jenis="Jurnal Nasional Terakreditasi" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional berbahasa Indonesia terindeks pada DOAJ" data-jenis="Jurnal Nasional Bahasa Indonesia DOAJ" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional berbahasa Indonesia terindeks pada DOAJ' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional berbahasa Indonesia terindeks pada DOAJ</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional berbahasa Inggris atau bahasa resmi PBB terindeks pada DOAJ" data-jenis="Jurnal Nasional Bahasa Inggris/PBB DOAJ" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional berbahasa Inggris atau bahasa resmi PBB terindeks pada DOAJ' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional berbahasa Inggris atau bahasa resmi PBB terindeks pada DOAJ</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional" data-jenis="Jurnal Nasional" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional</option>
                                    <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal ilmiah yang ditulis dalam Bahasa Resmi PBB namun tidak memenuhi syarat-syarat sebagai jurnal ilmiah internasional" data-jenis="Jurnal Bahasa Resmi PBB Non-Internasional" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal ilmiah yang ditulis dalam Bahasa Resmi PBB namun tidak memenuhi syarat-syarat sebagai jurnal ilmiah internasional' ? 'selected' : '' }}>Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal ilmiah yang ditulis dalam Bahasa Resmi PBB namun tidak memenuhi syarat-syarat sebagai jurnal ilmiah internasional</option>
                                </optgroup>

                                <optgroup label="Pelaksanaan Penelitian › Lainnya">
                                    <option value="Menerjemahkan/menyadur buku ilmiah yang diterbitkan (ber ISBN)" data-jenis="Menerjemahkan/menyadur buku" {{ $publikasi->kategori_kegiatan == 'Menerjemahkan/menyadur buku ilmiah yang diterbitkan (ber ISBN)' ? 'selected' : '' }}>Menerjemahkan/menyadur buku ilmiah yang diterbitkan (ber ISBN)</option>
                                    <option value="Mengedit/menyunting karya ilmiah dalam bentuk buku yang diterbitkan (ber ISBN)" data-jenis="Mengedit/menyunting buku" {{ $publikasi->kategori_kegiatan == 'Mengedit/menyunting karya ilmiah dalam bentuk buku yang diterbitkan (ber ISBN)' ? 'selected' : '' }}>Mengedit/menyunting karya ilmiah dalam bentuk buku yang diterbitkan (ber ISBN)</option>
                                </optgroup>

                                <optgroup label="Pelaksanaan Penelitian › Hasil penelitian/pemikiran yang didesiminasikan">
                                    <option value="Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional terindeks pada Scimagojr dan Scopus" data-jenis="Prosiding Internasional Scimago/Scopus" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional terindeks pada Scimagojr dan Scopus' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional terindeks pada Scimagojr dan Scopus</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional terindeks Scopus, IEEE Explore, SPIE" data-jenis="Prosiding Internasional Scopus/IEEE/SPIE" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional terindeks Scopus, IEEE Explore, SPIE' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional terindeks Scopus, IEEE Explore, SPIE</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional" data-jenis="Prosiding Internasional" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Nasional" data-jenis="Prosiding Nasional" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Nasional' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Nasional</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang dipublikasikan dalam seminar internasional" data-jenis="Poster Prosiding Internasional" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang dipublikasikan dalam seminar internasional' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang dipublikasikan dalam seminar internasional</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang dipublikasikan dalam seminar nasional" data-jenis="Poster Prosiding Nasional" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang dipublikasikan dalam seminar nasional' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang dipublikasikan dalam seminar nasional</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak dimuat dalam prosiding yang dipublikasikan: Internasional" data-jenis="Seminar/Lokakarya Internasional Non-Prosiding" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak dimuat dalam prosiding yang dipublikasikan: Internasional' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak dimuat dalam prosiding yang dipublikasikan: Internasional</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak dimuat dalam prosiding yang dipublikasikan: Nasional" data-jenis="Seminar/Lokakarya Nasional Non-Prosiding" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak dimuat dalam prosiding yang dipublikasikan: Nasional' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak dimuat dalam prosiding yang dipublikasikan: Nasional</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat dalam prosiding: Internasional" data-jenis="Prosiding Internasional Tanpa Seminar" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat dalam prosiding: Internasional' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat dalam prosiding: Internasional</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat dalam prosiding: Nasional" data-jenis="Prosiding Nasional Tanpa Seminar" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat dalam prosiding: Nasional' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat dalam prosiding: Nasional</option>
                                    <option value="Hasil penelitian atau hasil pemikiran yang disajikan dalam koran/majalah populer/umum" data-jenis="Artikel Koran/Majalah Populer" {{ $publikasi->kategori_kegiatan == 'Hasil penelitian atau hasil pemikiran yang disajikan dalam koran/majalah populer/umum' ? 'selected' : '' }}>Hasil penelitian atau hasil pemikiran yang disajikan dalam koran/majalah populer/umum</option>
                                </optgroup>

                                <optgroup label="Pelaksanaan Pengabdian Kepada Masyarakat">
                                    <option value="Membuat/menulis karya pengabdian pada masyarakat yang tidak dipublikasikan" data-jenis="Karya Pengabdian Non-Publikasi" {{ $publikasi->kategori_kegiatan == 'Membuat/menulis karya pengabdian pada masyarakat yang tidak dipublikasikan' ? 'selected' : '' }}>Membuat/menulis karya pengabdian pada masyarakat yang tidak dipublikasikan</option>
                                    <option value="Hasil kegiatan pengabdian kepada masyarakat yang dipublikasikan di sebuah berkala/jurnal ilmiah pengabdian kepada masyarakat atau teknologi tepat guna, merupakan diseminasi dari luaran program kegiatan pengabdian kepada masyarakat, tiap karya" data-jenis="Jurnal Pengabdian Masyarakat / TTG" {{ $publikasi->kategori_kegiatan == 'Hasil kegiatan pengabdian kepada masyarakat yang dipublikasikan di sebuah berkala/jurnal ilmiah pengabdian kepada masyarakat atau teknologi tepat guna, merupakan diseminasi dari luaran program kegiatan pengabdian kepada masyarakat, tiap karya' ? 'selected' : '' }}>Hasil kegiatan pengabdian kepada masyarakat yang dipublikasikan di sebuah berkala/jurnal ilmiah pengabdian kepada masyarakat atau teknologi tepat guna, merupakan diseminasi dari luaran program kegiatan pengabdian kepada masyarakat, tiap karya</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="jenis" class="form-label">Jenis</label>
                                <input type="text" name="jenis" id="jenis" class="form-control form-control-sm bg-light" readonly value="{{ $publikasi->jenis }}">
                            </div>

                            <div class="col-md-6">
                                <label for="kategori_capaian" class="form-label">Kategori Capaian</label>
                                <select name="kategori_capaian" id="kategori_capaian" class="form-select form-select-sm">
                                    <option value="Publikasi" {{ $publikasi->kategori_capaian == 'Publikasi' ? 'selected' : '' }}>Publikasi</option>
                                    <option value="Produk Teknologi Tepat Guna" {{ $publikasi->kategori_capaian == 'Produk Teknologi Tepat Guna' ? 'selected' : '' }}>Produk Teknologi Tepat Guna</option>
                                    <option value="Jenis Luaran Lainnya" {{ $publikasi->kategori_capaian == 'Jenis Luaran Lainnya' ? 'selected' : '' }}>Jenis Luaran Lainnya</option>
                                    <option value="HKI" {{ $publikasi->kategori_capaian == 'HKI' ? 'selected' : '' }}>HKI</option>
                                    <option value="Buku" {{ $publikasi->kategori_capaian == 'Buku' ? 'selected' : '' }}>Buku</option>
                                    <option value="Pembicara" {{ $publikasi->kategori_capaian == 'Pembicara' ? 'selected' : '' }}>Pembicara</option>
                                    <option value="Visiting Scientist" {{ $publikasi->kategori_capaian == 'Visiting Scientist' ? 'selected' : '' }}>Visiting Scientist</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="aktivitas_litabmas_id" class="form-label">Aktivitas Litabmas</label>
                                <select name="aktivitas_litabmas_id" id="aktivitas_litabmas_id" class="form-select form-select-sm">
                                    <option value="">Pilih...</option>
                                    @foreach($aktivitasLitabmas as $lit)
                                        <option value="{{ $lit->id }}" {{ $publikasi->aktivitas_litabmas_id == $lit->id ? 'selected' : '' }}>{{ $lit->kode }} - {{ $lit->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="judul" class="form-label fw-bold">Judul Artikel <span class="text-danger">*</span></label>
                                <input type="text" name="judul" id="judul" class="form-control form-control-sm" required value="{{ $publikasi->judul }}">
                            </div>

                            <div class="col-md-6">
                                <label for="nama_jurnal" class="form-label">Nama Jurnal</label>
                                <input type="text" name="nama_jurnal" id="nama_jurnal" class="form-control form-control-sm" value="{{ $publikasi->nama_jurnal }}">
                            </div>

                            <div class="col-md-6">
                                <label for="tautan_jurnal" class="form-label">Tautan Laman Jurnal</label>
                                <input type="url" name="tautan_jurnal" id="tautan_jurnal" class="form-control form-control-sm" value="{{ $publikasi->tautan_jurnal }}">
                            </div>

                            <div class="col-md-4">
                                <label for="tanggal_terbit" class="form-label fw-bold">Tanggal Terbit <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_terbit" id="tanggal_terbit" class="form-control form-control-sm" required value="{{ $publikasi->tanggal_terbit }}">
                            </div>

                            <div class="col-md-4">
                                <label for="volume" class="form-label">Volume</label>
                                <input type="number" name="volume" id="volume" class="form-control form-control-sm" value="{{ $publikasi->volume }}" min="1">
                            </div>

                            <div class="col-md-4">
                                <label for="nomor" class="form-label">Nomor</label>
                                <input type="number" name="nomor" id="nomor" class="form-control form-control-sm" value="{{ $publikasi->nomor }}" min="1">
                            </div>

                            <div class="col-md-4">
                                <label for="halaman" class="form-label">Halaman</label>
                                <input type="text" name="halaman" id="halaman" class="form-control form-control-sm" value="{{ $publikasi->halaman }}">
                            </div>

                            <div class="col-md-4">
                                <label for="penerbit" class="form-label">Penerbit/Penyelenggara</label>
                                <input type="text" name="penerbit" id="penerbit" class="form-control form-control-sm" value="{{ $publikasi->penerbit }}">
                            </div>

                            <div class="col-md-4">
                                <label for="doi" class="form-label">DOI</label>
                                <input type="text" name="doi" id="doi" class="form-control form-control-sm" value="{{ $publikasi->doi }}">
                            </div>

                            <div class="col-md-6">
                                <label for="issn" class="form-label">ISSN</label>
                                <input type="text" name="issn" id="issn" class="form-control form-control-sm" value="{{ $publikasi->issn }}">
                            </div>

                            <div class="col-md-6">
                                <label for="tautan_eksternal" class="form-label">Tautan Eksternal</label>
                                <input type="url" name="tautan_eksternal" id="tautan_eksternal" class="form-control form-control-sm" value="{{ $publikasi->tautan_eksternal }}">
                            </div>

                            <div class="col-md-12">
                                <label for="keterangan" class="form-label">Keterangan / Petunjuk Akses</label>
                                <textarea name="keterangan" id="keterangan" class="form-control form-control-sm" rows="3">{{ $publikasi->keterangan }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- CARD 2: Dokumen Tersimpan -->
                <div class="card mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Dokumen Tersimpan & Upload Dokumen Baru
                    </div>
                    <div class="card-body mt-3">

                        <!-- Tabel Dokumen Tersimpan -->
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Dokumen</th>
                                        <th>Nama File</th>
                                        <th>Jenis File</th>
                                        <th>Tanggal Upload</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($publikasi->dokumen as $idx => $doc)
                                        <tr id="doc_row_{{ $doc->id }}">
                                            <td>{{ $idx + 1 }}</td>
                                            <td><strong>{{ $doc->nama_dokumen }}</strong></td>
                                            <td>{{ $doc->nama_file ?? '-' }}</td>
                                            <td><small class="badge bg-secondary">{{ $doc->jenis_file ?? 'URL Link' }}</small></td>
                                            <td>{{ $doc->tanggal_upload }}</td>
                                            <td>{{ $doc->jenis_dokumen }}</td>
                                            <td>
                                                @if($doc->path_file)
                                                    <a href="{{ asset('storage/' . $doc->path_file) }}" target="_blank" class="btn btn-sm btn-info text-white" title="Lihat/Download">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @elseif($doc->tautan_dokumen)
                                                    <a href="{{ $doc->tautan_dokumen }}" target="_blank" class="btn btn-sm btn-info text-white" title="Buka Link">
                                                        <i class="bi bi-link-45deg"></i>
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteExistingDoc({{ $doc->id }})" title="Hapus Dokumen">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Belum ada dokumen tersimpan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-3"><i class="bi bi-cloud-upload"></i> Upload Dokumen Tambahan:</h6>

                        <!-- Preview Form Input Dokumen Baru -->
                        <div class="border rounded p-3 mb-4 bg-light">
                            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-plus-circle-fill"></i> Tambah Dokumen Tambahan Terlebih Dahulu</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Pilih File</label>
                                    <div id="new_doc_file_wrapper">
                                        <input type="file" id="new_doc_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Nama Dokumen <span class="text-danger">*</span></label>
                                    <input type="text" id="new_doc_name" class="form-control form-control-sm" placeholder="Nama Dokumen (misal: Peer Review)">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Jenis Dokumen</label>
                                    <select id="new_doc_type" class="form-select form-select-sm">
                                        <option value="Publikasi">Publikasi</option>
                                        <option value="Lembar Pengesahan">Lembar Pengesahan</option>
                                        <option value="Peer Review">Peer Review</option>
                                        <option value="Bukti Korespondensi">Bukti Korespondensi</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Keterangan (Opsional)</label>
                                    <input type="text" id="new_doc_desc" class="form-control form-control-sm" placeholder="Keterangan tambahan...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Tautan Dokumen (Alternatif Link)</label>
                                    <input type="url" id="new_doc_link" class="form-control form-control-sm" placeholder="https://example.com/document">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-sm btn-primary px-3 fw-bold" onclick="addDokumenToList()">
                                        <i class="bi bi-plus-lg"></i> Tambahkan ke Tabel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Tampilan Dokumen Tambahan -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">No</th>
                                        <th style="width: 25%">Nama Dokumen</th>
                                        <th style="width: 20%">Nama File</th>
                                        <th style="width: 15%">Jenis File</th>
                                        <th style="width: 15%">Tanggal Upload</th>
                                        <th style="width: 15%">Jenis Dokumen</th>
                                        <th style="width: 5%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="containerDokumen">
                                    <tr id="empty-doc-row">
                                        <td colspan="7" class="text-center text-muted py-3">Belum ada dokumen baru ditambahkan. Gunakan form di atas jika ingin menambahkan dokumen lampiran baru.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- CARD 3: Penulis Dosen -->
                <div class="card mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-person-badge me-1"></i> Penulis Dosen
                    </div>
                    <div class="card-body mt-3">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%">NIDN</th>
                                        <th style="width: 20%">Nama Dosen</th>
                                        <th style="width: 15%">Perguruan Tinggi</th>
                                        <th style="width: 8%">Urutan</th>
                                        <th style="width: 12%">Afiliasi</th>
                                        <th style="width: 10%">Peran</th>
                                        <th style="width: 10%" class="text-center">Corresponding</th>
                                        <th style="width: 10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="containerPenulisDosen">
                                    @foreach($publikasi->penulisDosen as $idx => $pd)
                                        <tr class="row-penulis-dosen">
                                            <td>
                                                <input type="text" name="penulis_dosen[{{ $idx }}][nidn]" class="form-control form-control-sm table-editable-cell" placeholder="NIDN Dosen" value="{{ $pd->dosen->nidn ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_dosen[{{ $idx }}][nama_dosen]" class="form-control form-control-sm table-editable-cell" placeholder="Nama Lengkap Dosen" value="{{ $pd->dosen->nama ?? '' }}" required>
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_dosen[{{ $idx }}][nama_pt]" class="form-control form-control-sm table-editable-cell" placeholder="Nama PT / Univ" value="{{ $pd->dosen->perguruanTinggi->nama_pt ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" name="penulis_dosen[{{ $idx }}][urutan]" class="form-control form-control-sm input-urutan table-editable-cell" value="{{ $pd->urutan }}">
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_dosen[{{ $idx }}][afiliasi]" class="form-control form-control-sm table-editable-cell" placeholder="Afiliasi" value="{{ $pd->afiliasi }}">
                                            </td>
                                            <td>
                                                <select name="penulis_dosen[{{ $idx }}][peran]" class="form-select form-select-sm table-editable-cell">
                                                    <option value="Penulis" {{ $pd->peran == 'Penulis' ? 'selected' : '' }}>Penulis</option>
                                                    <option value="Editor" {{ $pd->peran == 'Editor' ? 'selected' : '' }}>Editor</option>
                                                    <option value="Penerjemah" {{ $pd->peran == 'Penerjemah' ? 'selected' : '' }}>Penerjemah</option>
                                                    <option value="Penemu/Inventor" {{ $pd->peran == 'Penemu/Inventor' ? 'selected' : '' }}>Penemu/Inventor</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" name="corresponding_author" value="dosen_{{ $idx }}" class="form-check-input" {{ $pd->is_corresponding ? 'checked' : '' }} required>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowUp(this)" title="Naikkan"><i class="bi bi-arrow-up"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowDown(this)" title="Turunkan"><i class="bi bi-arrow-down"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-sm btn-success mt-2" onclick="addPenulisDosenRow()">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>

                    </div>
                </div>

                <!-- CARD 4: Penulis Mahasiswa -->
                <div class="card mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-person-workspace me-1"></i> Penulis Mahasiswa
                    </div>
                    <div class="card-body mt-3">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Nama Mahasiswa</th>
                                        <th style="width: 10%">Urutan</th>
                                        <th style="width: 15%">Afiliasi</th>
                                        <th style="width: 15%">Peran</th>
                                        <th style="width: 15%" class="text-center">Corresponding Author</th>
                                        <th style="width: 10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="containerPenulisMahasiswa">
                                    @foreach($publikasi->penulisMahasiswa as $idx => $pm)
                                        <tr class="row-penulis-mahasiswa">
                                            <td>
                                                <select name="penulis_mahasiswa[{{ $idx }}][mahasiswa_id]" class="form-select form-select-sm table-editable-cell mahasiswa-select" required>
                                                    @if($pm->mahasiswa)
                                                        <option value="{{ $pm->mahasiswa->id }}" selected>{{ $pm->mahasiswa->nim }} - {{ $pm->mahasiswa->nama }}</option>
                                                    @else
                                                        <option value="">-- Pilih Mahasiswa --</option>
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="penulis_mahasiswa[{{ $idx }}][urutan]" class="form-control form-control-sm input-urutan table-editable-cell" value="{{ $pm->urutan }}">
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_mahasiswa[{{ $idx }}][afiliasi]" class="form-control form-control-sm table-editable-cell" value="{{ $pm->afiliasi }}">
                                            </td>
                                            <td>
                                                <select name="penulis_mahasiswa[{{ $idx }}][peran]" class="form-select form-select-sm table-editable-cell">
                                                    <option value="Penulis" {{ $pm->peran == 'Penulis' ? 'selected' : '' }}>Penulis</option>
                                                    <option value="Editor" {{ $pm->peran == 'Editor' ? 'selected' : '' }}>Editor</option>
                                                    <option value="Penerjemah" {{ $pm->peran == 'Penerjemah' ? 'selected' : '' }}>Penerjemah</option>
                                                    <option value="Penemu/Inventor" {{ $pm->peran == 'Penemu/Inventor' ? 'selected' : '' }}>Penemu/Inventor</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" name="corresponding_author" value="mahasiswa_{{ $idx }}" class="form-check-input" {{ $pm->is_corresponding ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowUp(this)" title="Naikkan"><i class="bi bi-arrow-up"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowDown(this)" title="Turunkan"><i class="bi bi-arrow-down"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-sm btn-success mt-2" onclick="addPenulisMahasiswaRow()">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>

                    </div>
                </div>

                <!-- CARD 5: Penulis Lain -->
                <div class="card mb-4">
                    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-people me-1"></i> Penulis Lain (Kolaborator Eksternal)</span>
                    </div>
                    <div class="card-body mt-3">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">Nama Kolaborator</th>
                                        <th style="width: 10%">Urutan</th>
                                        <th style="width: 15%">Afiliasi</th>
                                        <th style="width: 15%">Peran</th>
                                        <th style="width: 15%" class="text-center">Corresponding Author</th>
                                        <th style="width: 10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="containerPenulisLain">
                                    @foreach($publikasi->penulisLain as $idx => $pl)
                                        <tr class="row-penulis-lain">
                                            <td>
                                                <input type="text" name="penulis_lain[{{ $idx }}][nama]" class="form-control form-control-sm table-editable-cell" value="{{ $pl->nama }}" required>
                                            </td>
                                            <td>
                                                <input type="number" name="penulis_lain[{{ $idx }}][urutan]" class="form-control form-control-sm input-urutan table-editable-cell" value="{{ $pl->urutan }}">
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_lain[{{ $idx }}][afiliasi]" class="form-control form-control-sm table-editable-cell" value="{{ $pl->afiliasi }}">
                                            </td>
                                            <td>
                                                <select name="penulis_lain[{{ $idx }}][peran]" class="form-select form-select-sm table-editable-cell">
                                                    <option value="Penulis" {{ $pl->peran == 'Penulis' ? 'selected' : '' }}>Penulis</option>
                                                    <option value="Editor" {{ $pl->peran == 'Editor' ? 'selected' : '' }}>Editor</option>
                                                    <option value="Penerjemah" {{ $pl->peran == 'Penerjemah' ? 'selected' : '' }}>Penerjemah</option>
                                                    <option value="Penemu/Inventor" {{ $pl->peran == 'Penemu/Inventor' ? 'selected' : '' }}>Penemu/Inventor</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" name="corresponding_author" value="lain_{{ $idx }}" class="form-check-input" {{ $pl->is_corresponding ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowUp(this)" title="Naikkan"><i class="bi bi-arrow-up"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowDown(this)" title="Turunkan"><i class="bi bi-arrow-down"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-sm btn-success mt-2" onclick="addPenulisLainRow()">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>

                    </div>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="card mb-4">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <a href="{{ route('publikasi.index', array_filter(['page' => $returnPage, 'search' => $returnSearch])) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Update Publikasi Karya
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    let dokCount = 0;
    let dosenCount = {{ $publikasi->penulisDosen->count() }};
    let mhsCount = {{ $publikasi->penulisMahasiswa->count() }};
    let lainCount = {{ $publikasi->penulisLain->count() }};

    // Semua mahasiswa dimuat SEKALI ke memori browser (bukan per-baris,
    // bukan per-huruf yang diketik), lalu pencariannya dilakukan lokal oleh
    // Tom Select - jadi instan, tidak ada request ke server tiap mengetik.
    let mahasiswaOptionsPromise = null;

    function loadAllMahasiswaOptions() {
        if (!mahasiswaOptionsPromise) {
            mahasiswaOptionsPromise = fetch(`{{ route('api.mahasiswa.all') }}`)
                .then((r) => r.json())
                .then((data) => data.map((m) => ({
                    value: String(m.id),
                    text: m.nim + ' - ' + m.nama + (m.prodi ? ' (' + m.prodi + ')' : ''),
                })))
                .catch(() => []);
        }
        return mahasiswaOptionsPromise;
    }

    // Baris yang sudah ada (hasil render server) hanya membawa satu
    // <option> terpilih - Tom Select otomatis mempertahankannya sebagai
    // item awal begitu daftar lengkapnya selesai dimuat di belakang layar.
    function initMahasiswaSelect(selectEl) {
        if (!selectEl || selectEl.tomselect) return;
        const ts = new TomSelect(selectEl, {
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            create: false,
            maxItems: 1,
            // Semua data mahasiswa memang dimuat, tapi jumlah yang dirender
            // ke DOM tetap dibatasi supaya tidak lag kalau datanya ribuan
            // baris - begitu diketik, pencarian tetap menjangkau SELURUH
            // data (bukan cuma yang kebatas ini), hasilnya makin sedikit &
            // makin relevan sesuai ketikan.
            maxOptions: 100,
            dropdownParent: 'body',
            render: {
                no_results: function (data, escape) {
                    return `<div class="no-results">Mahasiswa "${escape(data.input)}" tidak ditemukan</div>`;
                },
            },
        });

        loadAllMahasiswaOptions().then((options) => {
            ts.addOptions(options);
            ts.refreshOptions(false);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#containerPenulisMahasiswa .mahasiswa-select').forEach(initMahasiswaSelect);
    });

    // Escape HTML string
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function syncJenis(kategoriValue) {
        const select = document.getElementById('kategori_kegiatan');
        const selectedOption = select.options[select.selectedIndex];
        const jenisInput = document.getElementById('jenis');
        if (selectedOption && selectedOption.dataset.jenis) {
            jenisInput.value = selectedOption.dataset.jenis;
        }
    }

    function deleteExistingDoc(docId) {
        if (!confirm('Yakin hapus dokumen ini?')) return;
        fetch(`/publikasi-dokumen/${docId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            if(data.success) {
                document.getElementById(`doc_row_${docId}`).remove();
            }
        });
    }

    // 2. Multi-Dokumen Rows (Preview-then-list style for edit)
    function addDokumenToList() {
        const nameInput = document.getElementById('new_doc_name');
        const fileInput = document.getElementById('new_doc_file');
        const typeSelect = document.getElementById('new_doc_type');
        const descInput = document.getElementById('new_doc_desc');
        const linkInput = document.getElementById('new_doc_link');
        
        const name = nameInput.value.trim();
        const type = typeSelect.value;
        const desc = descInput.value.trim();
        const link = linkInput.value.trim();
        const file = fileInput.files[0];
        
        if (!name) {
            alert('Nama dokumen wajib diisi!');
            return;
        }
        if (!file && !link) {
            alert('Wajib memilih file untuk diupload atau memasukkan tautan dokumen!');
            return;
        }
        
        const container = document.getElementById('containerDokumen');
        
        // Remove empty placeholder row if exists
        const emptyRow = document.getElementById('empty-doc-row');
        if (emptyRow) {
            emptyRow.remove();
        }
        
        const idx = dokCount++;
        const row = document.createElement('tr');
        row.className = 'row-dokumen-item';
        
        let fileName = '-';
        let fileType = 'Link';
        if (file) {
            fileName = file.name;
            const ext = file.name.split('.').pop().toUpperCase();
            fileType = ext + ' File';
        } else if (link) {
            fileName = `<a href="${link}" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">${link}</a>`;
        }
        
        const today = new Date().toISOString().split('T')[0]; // yyyy-mm-dd
        
        // Move the file input to this row, and replace it in the preview form
        const hiddenFileContainer = document.createElement('div');
        hiddenFileContainer.style.display = 'none';
        if (file) {
            fileInput.name = `dokumen[${idx}][file]`;
            fileInput.id = ''; // clear ID
            hiddenFileContainer.appendChild(fileInput);
            
            // Recreate the file input in the form
            const newFileInput = document.createElement('input');
            newFileInput.type = 'file';
            newFileInput.id = 'new_doc_file';
            newFileInput.className = 'form-control form-control-sm';
            newFileInput.accept = '.pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt';
            document.getElementById('new_doc_file_wrapper').innerHTML = '';
            document.getElementById('new_doc_file_wrapper').appendChild(newFileInput);
        }
        
        row.innerHTML = `
            <td class="text-center doc-row-num"></td>
            <td>
                <strong>${escapeHtml(name)}</strong>
                ${desc ? `<div class="text-muted small">Ket: ${escapeHtml(desc)}</div>` : ''}
                <input type="hidden" name="dokumen[${idx}][nama_dokumen]" value="${escapeHtml(name)}">
                <input type="hidden" name="dokumen[${idx}][keterangan]" value="${escapeHtml(desc)}">
                <input type="hidden" name="dokumen[${idx}][jenis_dokumen]" value="${escapeHtml(type)}">
                <input type="hidden" name="dokumen[${idx}][tautan_dokumen]" value="${escapeHtml(link)}">
            </td>
            <td>${fileName}</td>
            <td><span class="badge bg-secondary">${fileType}</span></td>
            <td>${today}</td>
            <td>${escapeHtml(type)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeUploadedDocRow(this)" title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        row.appendChild(hiddenFileContainer);
        container.appendChild(row);
        
        // Reset inputs
        nameInput.value = '';
        descInput.value = '';
        linkInput.value = '';
        fileInput.value = '';
        
        updateDocNumbers();
    }

    function removeUploadedDocRow(btn) {
        btn.closest('tr').remove();
        updateDocNumbers();
    }

    function updateDocNumbers() {
        const rows = document.querySelectorAll('#containerDokumen tr.row-dokumen-item');
        rows.forEach((r, idx) => {
            r.querySelector('.doc-row-num').innerText = idx + 1;
        });
        
        if (rows.length === 0) {
            const container = document.getElementById('containerDokumen');
            container.innerHTML = `
                <tr id="empty-doc-row">
                    <td colspan="7" class="text-center text-muted py-3">Belum ada dokumen baru ditambahkan. Gunakan form di atas jika ingin menambahkan dokumen lampiran baru.</td>
                </tr>
            `;
        }
    }

    // 3. Penulis Dosen Rows (Manual inputs)
    function addPenulisDosenRow() {
        const container = document.getElementById('containerPenulisDosen');
        const row = document.createElement('tr');
        row.className = 'row-penulis-dosen';
        const nextIdx = dosenCount++;
        row.innerHTML = `
            <td>
                <input type="text" name="penulis_dosen[${nextIdx}][nidn]" class="form-control form-control-sm table-editable-cell" placeholder="NIDN Dosen">
            </td>
            <td>
                <input type="text" name="penulis_dosen[${nextIdx}][nama_dosen]" class="form-control form-control-sm table-editable-cell" placeholder="Nama Lengkap Dosen" required>
            </td>
            <td>
                <input type="text" name="penulis_dosen[${nextIdx}][nama_pt]" class="form-control form-control-sm table-editable-cell" placeholder="Nama PT / Univ">
            </td>
            <td>
                <input type="number" name="penulis_dosen[${nextIdx}][urutan]" class="form-control form-control-sm input-urutan table-editable-cell" value="${container.children.length + 1}">
            </td>
            <td>
                <input type="text" name="penulis_dosen[${nextIdx}][afiliasi]" class="form-control form-control-sm table-editable-cell" placeholder="Afiliasi">
            </td>
            <td>
                <select name="penulis_dosen[${nextIdx}][peran]" class="form-select form-select-sm table-editable-cell">
                    <option value="Penulis">Penulis</option>
                    <option value="Editor">Editor</option>
                    <option value="Penerjemah">Penerjemah</option>
                    <option value="Penemu/Inventor">Penemu/Inventor</option>
                </select>
            </td>
            <td class="text-center">
                <input type="radio" name="corresponding_author" value="dosen_${nextIdx}" class="form-check-input">
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowUp(this)" title="Naikkan"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowDown(this)" title="Turunkan"><i class="bi bi-arrow-down"></i></button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        `;
        container.appendChild(row);
    }

    // 4. Penulis Mahasiswa Rows
    function addPenulisMahasiswaRow() {
        const container = document.getElementById('containerPenulisMahasiswa');
        const row = document.createElement('tr');
        row.className = 'row-penulis-mahasiswa';
        const nextIdx = mhsCount++;
        row.innerHTML = `
            <td>
                <select name="penulis_mahasiswa[${nextIdx}][mahasiswa_id]" class="form-select form-select-sm table-editable-cell mahasiswa-select" required>
                    <option value="">-- Pilih Mahasiswa --</option>
                </select>
            </td>
            <td>
                <input type="number" name="penulis_mahasiswa[${nextIdx}][urutan]" class="form-control form-control-sm input-urutan table-editable-cell" value="${container.children.length + 1}">
            </td>
            <td>
                <input type="text" name="penulis_mahasiswa[${nextIdx}][afiliasi]" class="form-control form-control-sm table-editable-cell" placeholder="Afiliasi">
            </td>
            <td>
                <select name="penulis_mahasiswa[${nextIdx}][peran]" class="form-select form-select-sm table-editable-cell">
                    <option value="Penulis">Penulis</option>
                    <option value="Editor">Editor</option>
                    <option value="Penerjemah">Penerjemah</option>
                    <option value="Penemu/Inventor">Penemu/Inventor</option>
                </select>
            </td>
            <td class="text-center">
                <input type="radio" name="corresponding_author" value="mahasiswa_${nextIdx}" class="form-check-input">
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowUp(this)" title="Naikkan"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowDown(this)" title="Turunkan"><i class="bi bi-arrow-down"></i></button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        `;
        container.appendChild(row);
        initMahasiswaSelect(row.querySelector('.mahasiswa-select'));
    }

    // 5. Penulis Lain Rows
    function addPenulisLainRow() {
        const container = document.getElementById('containerPenulisLain');
        const row = document.createElement('tr');
        row.className = 'row-penulis-lain';
        const nextIdx = lainCount++;
        row.innerHTML = `
            <td>
                <input type="text" name="penulis_lain[${nextIdx}][nama]" class="form-control form-control-sm table-editable-cell" placeholder="Nama Lengkap Kolaborator" required>
            </td>
            <td>
                <input type="number" name="penulis_lain[${nextIdx}][urutan]" class="form-control form-control-sm input-urutan table-editable-cell" value="${container.children.length + 1}">
            </td>
            <td>
                <input type="text" name="penulis_lain[${nextIdx}][afiliasi]" class="form-control form-control-sm table-editable-cell" placeholder="Afiliasi">
            </td>
            <td>
                <select name="penulis_lain[${nextIdx}][peran]" class="form-select form-select-sm table-editable-cell">
                    <option value="Penulis">Penulis</option>
                    <option value="Editor">Editor</option>
                    <option value="Penerjemah">Penerjemah</option>
                    <option value="Penemu/Inventor">Penemu/Inventor</option>
                </select>
            </td>
            <td class="text-center">
                <input type="radio" name="corresponding_author" value="lain_${nextIdx}" class="form-check-input">
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowUp(this)" title="Naikkan"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveRowDown(this)" title="Turunkan"><i class="bi bi-arrow-down"></i></button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        `;
        container.appendChild(row);
    }

    function removeRow(btn) {
        const tbody = btn.closest('tbody');
        btn.closest('tr').remove();
        reorderUrutan(tbody);
    }

    // Row reordering functions
    function moveRowUp(btn) {
        const row = btn.closest('tr');
        const sibling = row.previousElementSibling;
        if (sibling) {
            row.parentNode.insertBefore(row, sibling);
            reorderUrutan(row.parentNode);
        }
    }

    function moveRowDown(btn) {
        const row = btn.closest('tr');
        const sibling = row.nextElementSibling;
        if (sibling) {
            row.parentNode.insertBefore(sibling, row);
            reorderUrutan(row.parentNode);
        }
    }

    function reorderUrutan(tbody) {
        const rows = tbody.querySelectorAll('tr');
        rows.forEach((row, idx) => {
            const urutanInput = row.querySelector('.input-urutan');
            if (urutanInput) {
                urutanInput.value = idx + 1;
            }
        });
    }

    // Event delegation for numeric input constraints
    document.addEventListener('keydown', function(e) {
        if (e.target && e.target.type === 'number') {
            if (['e', 'E', '-', '+', '.', ','].includes(e.key)) {
                e.preventDefault();
            }
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target && e.target.type === 'number') {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target && e.target.type === 'number') {
            const val = parseInt(e.target.value);
            if (isNaN(val) || val < 1) {
                e.target.value = 1;
            }
        }
    });
</script>
@endpush

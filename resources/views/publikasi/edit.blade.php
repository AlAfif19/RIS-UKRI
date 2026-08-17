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

        /* Dropdown "judul mirip" - senada dengan dropdown Tom Select di atas,
           lihat catatan yang sama di resources/views/publikasi/create.blade.php */
        #judul-wrap {
            position: relative;
        }
        #judul-mirip-hasil {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 20;
            margin-top: 2px;
            background: #fff;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            max-height: 260px;
            overflow-y: auto;
        }
        #judul-mirip-hasil .judul-mirip-header {
            padding: 8px 14px;
            font-size: 0.8rem;
            border-bottom: 1px solid #eef0f3;
            color: #6c757d;
        }
        #judul-mirip-hasil .judul-mirip-item {
            padding: 8px 14px;
            font-size: 0.88rem;
            border-bottom: 1px solid #f5f6f8;
            cursor: default;
        }
        #judul-mirip-hasil .judul-mirip-item:last-child {
            border-bottom: none;
        }
        #judul-mirip-hasil .judul-mirip-item:hover {
            background: #f5f8ff;
        }
        #judul-mirip-hasil .judul-mirip-item mark {
            background: #fff3ac;
            padding: 0;
            color: inherit;
        }
        #judul-mirip-hasil .no-results,
        #judul-mirip-hasil .create {
            padding: 10px 14px;
            font-size: 0.88rem;
        }
    </style>
@endpush

@section('content')

<div class="pagetitle">
    <h1>Edit Publikasi Karya</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard-analitik.index') }}">Home</a></li>
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
                                <div id="judul-wrap">
                                    <input type="text" name="judul" id="judul" class="form-control form-control-sm" required value="{{ $publikasi->judul }}" autocomplete="off">
                                    <div id="judul-mirip-hasil" style="display:none;"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="nama_jurnal" class="form-label">Nama Jurnal</label>
                                <input type="text" name="nama_jurnal" id="nama_jurnal" class="form-control form-control-sm" value="{{ $publikasi->nama_jurnal }}">
                            </div>

                            <div class="col-md-6">
                                <label for="issn" class="form-label">ISSN / ISBN</label>
                                <input type="text" name="issn" id="issn" class="form-control form-control-sm" value="{{ $publikasi->issn }}">
                            </div>

                            <div class="col-md-6">
                                <label for="tautan_jurnal" class="form-label">Tautan Laman Jurnal</label>
                                <input type="url" name="tautan_jurnal" id="tautan_jurnal" class="form-control form-control-sm" value="{{ $publikasi->tautan_jurnal }}">
                            </div>

                            <div class="col-md-6">
                                <label for="tautan_eksternal" class="form-label">Tautan Eksternal</label>
                                <input type="url" name="tautan_eksternal" id="tautan_eksternal" class="form-control form-control-sm" value="{{ $publikasi->tautan_eksternal }}">
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
                                        <th style="width: 35%">Nama Dosen</th>
                                        <th style="width: 10%">Urutan</th>
                                        <th style="width: 15%">Afiliasi</th>
                                        <th style="width: 15%">Peran</th>
                                        <th style="width: 15%" class="text-center">Corresponding Author</th>
                                        <th style="width: 10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="containerPenulisDosen">
                                    @foreach($publikasi->penulisDosen as $idx => $pd)
                                        <tr class="row-penulis-dosen">
                                            <td>
                                                <select name="penulis_dosen[{{ $idx }}][dosen_id]" class="form-select form-select-sm table-editable-cell dosen-select" required>
                                                    @if($pd->dosen)
                                                        <option value="{{ $pd->dosen->id }}" selected>{{ $pd->dosen->nidn }} - {{ $pd->dosen->nama }}</option>
                                                    @else
                                                        <option value="">-- Pilih Dosen --</option>
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="penulis_dosen[{{ $idx }}][urutan]" class="form-control form-control-sm input-urutan table-editable-cell" value="{{ $pd->urutan }}">
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_dosen[{{ $idx }}][afiliasi]" class="form-control form-control-sm table-editable-cell" placeholder="Afiliasi" value="{{ $pd->afiliasi ?: 'Universitas Kebangsaan Republik Indonesia' }}">
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
                                                <input type="text" name="penulis_mahasiswa[{{ $idx }}][afiliasi]" class="form-control form-control-sm table-editable-cell" value="{{ $pm->afiliasi ?: 'Universitas Kebangsaan Republik Indonesia' }}">
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
                                                <input type="text" name="penulis_lain[{{ $idx }}][nama]" id="penulis_lain_nama_{{ $idx }}" class="form-control form-control-sm table-editable-cell penulis-lain-nama-select" value="{{ $pl->nama }}" required>
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

    // Afiliasi Penulis Dosen & Mahasiswa otomatis terisi nama kampus sendiri
    // (Penulis Lain / kolaborator eksternal tetap kosong karena memang dari
    // instansi lain).
    const DEFAULT_AFILIASI = 'Universitas Kebangsaan Republik Indonesia';

    // Semua dosen dimuat SEKALI ke memori browser, sama seperti mahasiswa
    // di bawah - pencariannya dilakukan lokal oleh Tom Select.
    let dosenOptionsPromise = null;

    function loadAllDosenOptions() {
        if (!dosenOptionsPromise) {
            dosenOptionsPromise = fetch(`{{ route('api.dosen.all') }}`)
                .then((r) => r.json())
                .then((data) => data.map((d) => ({
                    value: String(d.id),
                    text: d.nidn + ' - ' + d.nama + (d.prodi ? ' (' + d.prodi.nama_prodi + ')' : ''),
                })))
                .catch(() => []);
        }
        return dosenOptionsPromise;
    }

    // Baris yang sudah ada (hasil render server) hanya membawa satu
    // <option> terpilih - Tom Select otomatis mempertahankannya sebagai
    // item awal begitu daftar lengkapnya selesai dimuat di belakang layar.
    function initDosenSelect(selectEl) {
        if (!selectEl || selectEl.tomselect) return;
        const ts = new TomSelect(selectEl, {
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            create: false,
            maxItems: 1,
            maxOptions: 100,
            dropdownParent: 'body',
            render: {
                no_results: function (data, escape) {
                    return `<div class="no-results">Dosen "${escape(data.input)}" tidak ditemukan</div>`;
                },
            },
        });

        loadAllDosenOptions().then((options) => {
            ts.addOptions(options);
            ts.refreshOptions(false);
        });
    }

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

    // Nama Jurnal - semua nama jurnal yang PERNAH dientri di seluruh
    // publikasi (bukan cuma milik dosen yang sedang login) dimuat SEKALI ke
    // memori browser, sama seperti dosen/mahasiswa di atas. Tom Select
    // mencocokkan ketikan dengan yang paling mirip di daftar itu (kalau
    // ketemu berarti jurnalnya sudah pernah dientri sebelumnya - tinggal
    // pilih, bukan bikin baris data baru yang beda kapitalisasi/spasi dari
    // yang sudah ada); kalau tidak ada yang mirip sama sekali, create:true
    // mengizinkan ketikan itu langsung dipakai sebagai nama jurnal BARU.
    let jurnalOptionsPromise = null;

    function loadAllJurnalOptions() {
        if (!jurnalOptionsPromise) {
            jurnalOptionsPromise = fetch(`{{ route('api.jurnal.all') }}`)
                .then((r) => r.json())
                .then((data) => data.map((nama) => ({ value: nama, text: nama })))
                .catch(() => []);
        }
        return jurnalOptionsPromise;
    }

    function initJurnalSelect(inputEl) {
        if (!inputEl || inputEl.tomselect) return;
        const initialValue = inputEl.value;
        const ts = new TomSelect(inputEl, {
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            create: true,
            createOnBlur: true,
            persist: false,
            maxItems: 1,
            maxOptions: 100,
            dropdownParent: 'body',
            render: {
                option_create: function (data, escape) {
                    return `<div class="create">Belum ada yang mirip - pakai sebagai jurnal baru: <strong>"${escape(data.input)}"</strong></div>`;
                },
                no_results: function (data, escape) {
                    return `<div class="no-results">Tidak ada jurnal tersimpan yang mirip "${escape(data.input)}"</div>`;
                },
            },
        });

        loadAllJurnalOptions().then((options) => {
            ts.addOptions(options);
            ts.refreshOptions(false);
            // Nilai yang sudah tersimpan sebelumnya (dari database) tetap
            // dipertahankan sebagai item terpilih, walau kebetulan tidak ada
            // di daftar (misal sudah diedit manual di DB).
            if (initialValue) {
                ts.addOption({ value: initialValue, text: initialValue });
                ts.setValue(initialValue, true);
            }
        });
    }

    // Penerbit/Penyelenggara - pola sama persis dengan Nama Jurnal di atas:
    // daftar semua penerbit yang PERNAH dientri dimuat sekali, lalu Tom
    // Select mempersempit dropdown sesuai ketikan; nilai yang sudah
    // tersimpan sebelumnya di publikasi ini tetap dipertahankan sebagai item
    // terpilih walau kebetulan tidak ada di daftar.
    let penerbitOptionsPromise = null;

    function loadAllPenerbitOptions() {
        if (!penerbitOptionsPromise) {
            penerbitOptionsPromise = fetch(`{{ route('api.penerbit.all') }}`)
                .then((r) => r.json())
                .then((data) => data.map((nama) => ({ value: nama, text: nama })))
                .catch(() => []);
        }
        return penerbitOptionsPromise;
    }

    function initPenerbitSelect(inputEl) {
        if (!inputEl || inputEl.tomselect) return;
        const initialValue = inputEl.value;
        const ts = new TomSelect(inputEl, {
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            create: true,
            createOnBlur: true,
            persist: false,
            maxItems: 1,
            maxOptions: 100,
            dropdownParent: 'body',
            render: {
                option_create: function (data, escape) {
                    return `<div class="create">Belum ada yang mirip - pakai sebagai penerbit baru: <strong>"${escape(data.input)}"</strong></div>`;
                },
                no_results: function (data, escape) {
                    return `<div class="no-results">Tidak ada penerbit tersimpan yang mirip "${escape(data.input)}"</div>`;
                },
            },
        });

        loadAllPenerbitOptions().then((options) => {
            ts.addOptions(options);
            ts.refreshOptions(false);
            if (initialValue) {
                ts.addOption({ value: initialValue, text: initialValue });
                ts.setValue(initialValue, true);
            }
        });
    }

    // ISSN / ISBN - pola sama dengan create.blade.php: hanya membatasi
    // karakter ke digit, X/x, dan strip, tanpa memaksa format 8 digit ISSN
    // supaya ISBN (10-13 digit) juga bisa diketik dengan benar.
    (function () {
        const issnInput = document.getElementById('issn');
        if (!issnInput) return;
        issnInput.setAttribute('maxlength', 20);
        issnInput.addEventListener('input', function () {
            issnInput.value = issnInput.value.toUpperCase().replace(/[^0-9X-]/g, '');
        });
    })();

    // Nama Kolaborator (Penulis Lain) - pola sama persis dengan
    // create.blade.php: dropdown autocomplete dari nama-nama yang pernah
    // diinput sebelumnya (lintas SEMUA publikasi, tidak dibatasi per
    // dosen), tapi tetap boleh mengetik nama baru kalau memang belum
    // pernah ada (create:true). Sebelumnya field ini di edit.blade.php
    // cuma <input type="text"> polos tanpa Tom Select sama sekali -
    // beda dengan create.blade.php - jadi tidak selaras antara form
    // tambah & edit, baik untuk role admin maupun dosen (endpoint
    // api.penulis-lain.all sendiri sudah bisa diakses keduanya, lihat
    // grup middleware 'auth','role:admin|dosen' di routes/web.php).
    let penulisLainOptionsPromise = null;

    function loadAllPenulisLainOptions() {
        if (!penulisLainOptionsPromise) {
            penulisLainOptionsPromise = fetch(`{{ route('api.penulis-lain.all') }}`)
                .then((r) => r.json())
                .then((data) => data.map((row) => ({
                    value: row.nama,
                    text: row.nama,
                    afiliasi: row.afiliasi,
                })))
                .catch(() => []);
        }
        return penulisLainOptionsPromise;
    }

    function initPenulisLainSelect(inputEl) {
        if (!inputEl || inputEl.tomselect) return;
        const initialValue = inputEl.value;
        const ts = new TomSelect(inputEl, {
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            create: true,
            createOnBlur: true,
            persist: false,
            maxItems: 1,
            maxOptions: 100,
            dropdownParent: 'body',
            render: {
                option_create: function (data, escape) {
                    return `<div class="create">Belum pernah ada - pakai sebagai kolaborator baru: <strong>"${escape(data.input)}"</strong></div>`;
                },
                no_results: function (data, escape) {
                    return `<div class="no-results">Tidak ada kolaborator tersimpan yang mirip "${escape(data.input)}"</div>`;
                },
            },
            onItemAdd: function (value) {
                // Kalau yang dipilih adalah kolaborator LAMA (bukan hasil
                // ketik nama baru), isikan otomatis Afiliasi di baris
                // yang sama dari data terakhir kali dia diinput - tapi
                // cuma kalau field Afiliasi baris itu masih kosong,
                // supaya tidak menimpa yang sudah sengaja diisi manual.
                const opt = ts.options[value];
                if (!opt || !opt.afiliasi) return;
                const row = inputEl.closest('tr');
                const afiliasiInput = row ? row.querySelector('input[name*="[afiliasi]"]') : null;
                if (afiliasiInput && !afiliasiInput.value.trim()) {
                    afiliasiInput.value = opt.afiliasi;
                }
            },
        });

        loadAllPenulisLainOptions().then((options) => {
            ts.addOptions(options);
            ts.refreshOptions(false);
            // Nilai yang sudah tersimpan sebelumnya (dari database) tetap
            // dipertahankan sebagai item terpilih, walau kebetulan tidak ada
            // di daftar (misal sudah diedit manual di DB).
            if (initialValue) {
                ts.addOption({ value: initialValue, text: initialValue });
                ts.setValue(initialValue, true);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#containerPenulisDosen .dosen-select').forEach(initDosenSelect);
        document.querySelectorAll('#containerPenulisMahasiswa .mahasiswa-select').forEach(initMahasiswaSelect);
        initJurnalSelect(document.getElementById('nama_jurnal'));
        initPenerbitSelect(document.getElementById('penerbit'));
        document.querySelectorAll('#containerPenulisLain .penulis-lain-nama-select').forEach(initPenulisLainSelect);
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
        appConfirm({
            title: 'Hapus Dokumen',
            message: 'Yakin hapus dokumen ini? Dokumen yang sudah dihapus tidak bisa dikembalikan.',
            confirmText: 'Ya, Hapus',
            variant: 'danger',
        }).then((ok) => {
            if (!ok) return;
            fetch(`/publikasi-dokumen/${docId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    document.getElementById(`doc_row_${docId}`).remove();
                    appNotify('success', 'Dokumen berhasil dihapus.');
                } else {
                    appNotify('error', 'Dokumen gagal dihapus. Silakan coba lagi.');
                }
            }).catch(() => {
                appNotify('error', 'Dokumen gagal dihapus. Silakan coba lagi.');
            });
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
            appNotify('warning', 'Nama dokumen wajib diisi!', 'Validasi Dokumen');
            return;
        }
        if (!file && !link) {
            appNotify('warning', 'Wajib memilih file untuk diupload atau memasukkan tautan dokumen!', 'Validasi Dokumen');
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

        if (file) {
            fileInput.name = `dokumen[${idx}][file]`;
            fileInput.id = ''; // clear ID
            fileInput.style.display = 'none';
            row.children[1].appendChild(fileInput);

            // Recreate the file input in the form
            const newFileInput = document.createElement('input');
            newFileInput.type = 'file';
            newFileInput.id = 'new_doc_file';
            newFileInput.className = 'form-control form-control-sm';
            newFileInput.accept = '.pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt';
            document.getElementById('new_doc_file_wrapper').innerHTML = '';
            document.getElementById('new_doc_file_wrapper').appendChild(newFileInput);
        }

        container.appendChild(row);

        // Reset inputs
        nameInput.value = '';
        descInput.value = '';
        linkInput.value = '';
        const activeFileInput = document.getElementById('new_doc_file');
        if (activeFileInput) {
            activeFileInput.value = '';
        }

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

    // 3. Penulis Dosen Rows (dropdown pencarian dosen, sama seperti Penulis Mahasiswa)
    function addPenulisDosenRow() {
        const container = document.getElementById('containerPenulisDosen');
        const row = document.createElement('tr');
        row.className = 'row-penulis-dosen';
        const nextIdx = dosenCount++;
        row.innerHTML = `
            <td>
                <select name="penulis_dosen[${nextIdx}][dosen_id]" class="form-select form-select-sm table-editable-cell dosen-select" required>
                    <option value="">-- Pilih Dosen --</option>
                </select>
            </td>
            <td>
                <input type="number" name="penulis_dosen[${nextIdx}][urutan]" class="form-control form-control-sm input-urutan table-editable-cell" value="${container.children.length + 1}">
            </td>
            <td>
                <input type="text" name="penulis_dosen[${nextIdx}][afiliasi]" class="form-control form-control-sm table-editable-cell" placeholder="Afiliasi" value="${DEFAULT_AFILIASI}">
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
        initDosenSelect(row.querySelector('.dosen-select'));
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
                <input type="text" name="penulis_mahasiswa[${nextIdx}][afiliasi]" class="form-control form-control-sm table-editable-cell" placeholder="Afiliasi" value="${DEFAULT_AFILIASI}">
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
                <input type="text" name="penulis_lain[${nextIdx}][nama]" id="penulis_lain_nama_${nextIdx}" class="form-control form-control-sm table-editable-cell penulis-lain-nama-select" placeholder="Nama Lengkap Kolaborator" required>
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
        initPenulisLainSelect(row.querySelector('.penulis-lain-nama-select'));
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

    // Cek Judul Artikel mirip - sama seperti di form Tambah, dicari lintas
    // SEMUA publikasi (bukan cuma milik dosen ybs), tapi judul publikasi ini
    // sendiri dikecualikan (exclude_id) supaya tidak "mirip" dengan dirinya
    // sendiri.
    (function () {
        const judulInput = document.getElementById('judul');
        const hasilBox = document.getElementById('judul-mirip-hasil');
        if (!judulInput || !hasilBox) return;

        const excludeId = {{ $publikasi->id }};
        const MIN_CHARS = 3;
        let debounceTimer = null;
        let requestSeq = 0;

        function escapeHtmlLocal(text) {
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function highlight(text, q) {
            const escaped = escapeHtmlLocal(text);
            if (!q) return escaped;
            const idx = text.toLowerCase().indexOf(q.toLowerCase());
            if (idx === -1) return escaped;
            const before = escapeHtmlLocal(text.slice(0, idx));
            const match = escapeHtmlLocal(text.slice(idx, idx + q.length));
            const after = escapeHtmlLocal(text.slice(idx + q.length));
            return `${before}<mark>${match}</mark>${after}`;
        }

        function hide() {
            hasilBox.style.display = 'none';
            hasilBox.innerHTML = '';
        }

        judulInput.addEventListener('input', function () {
            const q = judulInput.value.trim();
            clearTimeout(debounceTimer);

            if (q.length < MIN_CHARS) {
                hide();
                return;
            }

            debounceTimer = setTimeout(() => {
                const seq = ++requestSeq;
                const url = `{{ route('api.publikasi.cek-judul') }}?q=${encodeURIComponent(q)}&exclude_id=${excludeId}`;

                fetch(url)
                    .then((r) => r.json())
                    .then((data) => {
                        if (seq !== requestSeq) return;
                        if (judulInput.value.trim().length < MIN_CHARS) return hide();

                        if (!data.length) {
                            hasilBox.style.display = 'block';
                            hasilBox.innerHTML = `<div class="no-results"><i class="bi bi-check-circle text-success"></i> Tidak ada judul mirip yang ditemukan untuk "${escapeHtmlLocal(q)}".</div>`;
                            return;
                        }

                        const header = `<div class="judul-mirip-header"><i class="bi bi-exclamation-triangle text-warning"></i> ${data.length} judul mirip ditemukan di database:</div>`;
                        const items = data.map((p) => {
                            const tanggal = p.tanggal_terbit ? ` &middot; ${escapeHtmlLocal(p.tanggal_terbit)}` : '';
                            const jurnal = p.nama_jurnal ? ` &middot; ${escapeHtmlLocal(p.nama_jurnal)}` : '';
                            return `<div class="judul-mirip-item">${highlight(p.judul, q)}<div class="text-muted">${jurnal}${tanggal}</div></div>`;
                        }).join('');

                        hasilBox.style.display = 'block';
                        hasilBox.innerHTML = header + items;
                    })
                    .catch(() => {
                        if (seq !== requestSeq) return;
                        hide();
                    });
            }, 300);
        });

        judulInput.addEventListener('blur', function () {
            setTimeout(hide, 150);
        });
    })();
</script>
@endpush

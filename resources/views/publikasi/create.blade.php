@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Dropdown pencarian mahasiswa: defaultnya cuma ~200px (kelihatan
           sebagian) - dibesarkan supaya lebih mudah dibaca & diklik.
           Selector tidak di-scope ke .mahasiswa-select karena Tom Select
           merender dropdown-nya di dalam .ts-wrapper, bukan sebagai
           sibling langsung dari elemen <select> aslinya. */
        .ts-dropdown .ts-dropdown-content {
            max-height: 320px;
        }
        .ts-dropdown .option {
            padding: 10px 14px;
            font-size: 0.95rem;
        }
        /* Dropdown-nya di-pindah ke <body> (lihat dropdownParent di JS) supaya
           tidak kepotong oleh overflow tabel / ketutup baris di bawahnya -
           makanya perlu z-index tinggi di sini. Aman dibikin paling atas
           karena dropdown ini cuma muncul sesaat lalu otomatis hilang begitu
           satu mahasiswa dipilih. */
        .ts-dropdown {
            z-index: 9999;
        }

        /* Dropdown "judul mirip" - dibikin senada dengan tampilan dropdown
           Tom Select di atas (Nama Jurnal/Dosen/Mahasiswa): nempel persis di
           bawah input, daftarnya scrollable, tiap baris ada hover, dan makin
           panjang/spesifik ketikannya makin sedikit (bahkan bisa kosong)
           barisnya yang muncul - karena memang query ke server per ketikan. */
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
        <h1>Tambah Publikasi Karya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard-analitik.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('publikasi.index') }}">Publikasi Karya</a></li>
                <li class="breadcrumb-item active">Tambah</li>
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

                <!-- PERBAIKAN: sebelumnya kalau validasi gagal (mis. lupa
                     tambah dokumen/penulis/corresponding author), form
                     dikirim ke server dulu baru redirect-back dengan error -
                     itu artinya HALAMAN REFRESH dan SEMUA baris dinamis yang
                     sudah diisi (tabel dokumen, penulis dosen/mahasiswa/lain)
                     ikut hilang karena baris-baris itu murni hasil JS, tidak
                     ada logic untuk membangun ulang dari old() input kalau
                     gagal. Sekarang field-field wajib dicek dulu di
                     JavaScript SEBELUM form benar-benar dikirim (lihat
                     validateFormPublikasi() di bagian scripts) - kalau ada
                     yang belum lengkap, submit dibatalkan (tidak ada request
                     ke server sama sekali, jadi tidak ada refresh), pesannya
                     ditampilkan di sini, dan tombol Simpan di-disable sampai
                     bagian yang kurang itu dilengkapi. -->
                <div id="clientValidationErrors" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Lengkapi dulu bagian yang wajib diisi:</strong>
                    <ul class="mb-0 mt-1" id="clientValidationErrorsList"></ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <form action="{{ route('publikasi.store') }}" method="POST" enctype="multipart/form-data"
                    id="formPublikasi">
                    @csrf

                    <!-- CARD 1: Kategori & Detail Publikasi -->
                    <div class="card mb-4">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-journal-bookmark me-1"></i> Detail Publikasi Karya
                        </div>
                        <div class="card-body mt-3">

                            <!-- 1. Kategori Kegiatan Dropdown -->
                            <div class="mb-3">
                                <label for="kategori_kegiatan" class="form-label fw-bold">Kategori Kegiatan <span
                                        class="text-danger">*</span></label>
                                <select name="kategori_kegiatan" id="kategori_kegiatan" class="form-select form-select-sm"
                                    required onchange="syncJenis(this.value)">
                                    <option value="">-- Pilih Kategori Kegiatan --</option>

                                    <optgroup
                                        label="Pelaksanaan Penelitian › Menghasilkan Karya Ilmiah sesuai dengan bidangnya">
                                        <option value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk monograf"
                                            data-jenis="Monograf">Hasil penelitian/pemikiran yang dipublikasikan dalam
                                            bentuk monograf</option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk buku referensi"
                                            data-jenis="Buku Referensi">Hasil penelitian/pemikiran yang dipublikasikan dalam
                                            bentuk buku referensi</option>
                                        <option
                                            value="Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) internasional"
                                            data-jenis="Book Chapter Internasional">Hasil penelitian/hasil pemikiran dalam
                                            buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book
                                            chapter) internasional</option>
                                        <option
                                            value="Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) nasional"
                                            data-jenis="Book Chapter Nasional">Hasil penelitian/hasil pemikiran dalam buku
                                            yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book
                                            chapter) nasional</option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional bereputasi"
                                            data-jenis="Jurnal Internasional Bereputasi">Hasil penelitian/pemikiran yang
                                            dipublikasikan dalam bentuk jurnal internasional bereputasi</option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada database internasional bereputasi"
                                            data-jenis="Jurnal Internasional Terindeks Database Bereputasi">Hasil
                                            penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional
                                            terindeks pada database internasional bereputasi</option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada basis data internasional"
                                            data-jenis="Jurnal Internasional Terindeks Basis Data">Hasil
                                            penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional
                                            terindeks pada basis data internasional</option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti"
                                            data-jenis="Jurnal Nasional Terakreditasi">Hasil penelitian/pemikiran yang
                                            dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti
                                        </option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional berbahasa Indonesia terindeks pada DOAJ"
                                            data-jenis="Jurnal Nasional Bahasa Indonesia DOAJ">Hasil penelitian/pemikiran
                                            yang dipublikasikan dalam bentuk jurnal nasional berbahasa Indonesia terindeks
                                            pada DOAJ</option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional berbahasa Inggris atau bahasa resmi PBB terindeks pada DOAJ"
                                            data-jenis="Jurnal Nasional Bahasa Inggris/PBB DOAJ">Hasil penelitian/pemikiran
                                            yang dipublikasikan dalam bentuk jurnal nasional berbahasa Inggris atau bahasa
                                            resmi PBB terindeks pada DOAJ</option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional"
                                            data-jenis="Jurnal Nasional">Hasil penelitian/pemikiran yang dipublikasikan
                                            dalam bentuk jurnal nasional</option>
                                        <option
                                            value="Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal ilmiah yang ditulis dalam Bahasa Resmi PBB namun tidak memenuhi syarat-syarat sebagai jurnal ilmiah internasional"
                                            data-jenis="Jurnal Bahasa Resmi PBB Non-Internasional">Hasil
                                            penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal ilmiah yang ditulis
                                            dalam Bahasa Resmi PBB namun tidak memenuhi syarat-syarat sebagai jurnal ilmiah
                                            internasional</option>
                                    </optgroup>

                                    <optgroup label="Pelaksanaan Penelitian › Lainnya">
                                        <option value="Menerjemahkan/menyadur buku ilmiah yang diterbitkan (ber ISBN)"
                                            data-jenis="Menerjemahkan/menyadur buku">Menerjemahkan/menyadur buku ilmiah yang
                                            diterbitkan (ber ISBN)</option>
                                        <option
                                            value="Mengedit/menyunting karya ilmiah dalam bentuk buku yang diterbitkan (ber ISBN)"
                                            data-jenis="Mengedit/menyunting buku">Mengedit/menyunting karya ilmiah dalam
                                            bentuk buku yang diterbitkan (ber ISBN)</option>
                                    </optgroup>

                                    <optgroup
                                        label="Pelaksanaan Penelitian › Hasil penelitian/pemikiran yang didesiminasikan">
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional terindeks pada Scimagojr dan Scopus"
                                            data-jenis="Prosiding Internasional Scimago/Scopus">Hasil penelitian atau hasil
                                            pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang
                                            dipublikasikan (ber ISSN/ISBN): Internasional terindeks pada Scimagojr dan
                                            Scopus</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional terindeks Scopus, IEEE Explore, SPIE"
                                            data-jenis="Prosiding Internasional Scopus/IEEE/SPIE">Hasil penelitian atau
                                            hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang
                                            dipublikasikan (ber ISSN/ISBN): Internasional terindeks Scopus, IEEE Explore,
                                            SPIE</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional"
                                            data-jenis="Prosiding Internasional">Hasil penelitian atau hasil pemikiran yang
                                            Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber
                                            ISSN/ISBN): Internasional</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Nasional"
                                            data-jenis="Prosiding Nasional">Hasil penelitian atau hasil pemikiran yang
                                            Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber
                                            ISSN/ISBN): Nasional</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang dipublikasikan dalam seminar internasional"
                                            data-jenis="Poster Prosiding Internasional">Hasil penelitian atau hasil
                                            pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang
                                            dipublikasikan dalam seminar internasional</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang dipublikasikan dalam seminar nasional"
                                            data-jenis="Poster Prosiding Nasional">Hasil penelitian atau hasil pemikiran
                                            yang disajikan dalam bentuk poster dan dimuat dalam prosiding yang
                                            dipublikasikan dalam seminar nasional</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak dimuat dalam prosiding yang dipublikasikan: Internasional"
                                            data-jenis="Seminar/Lokakarya Internasional Non-Prosiding">Hasil penelitian atau
                                            hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak
                                            dimuat dalam prosiding yang dipublikasikan: Internasional</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak dimuat dalam prosiding yang dipublikasikan: Nasional"
                                            data-jenis="Seminar/Lokakarya Nasional Non-Prosiding">Hasil penelitian atau
                                            hasil pemikiran yang Disajikan dalam seminar/simposium/lokakarya, tetapi tidak
                                            dimuat dalam prosiding yang dipublikasikan: Nasional</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat dalam prosiding: Internasional"
                                            data-jenis="Prosiding Internasional Tanpa Seminar">Hasil penelitian atau hasil
                                            pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat
                                            dalam prosiding: Internasional</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat dalam prosiding: Nasional"
                                            data-jenis="Prosiding Nasional Tanpa Seminar">Hasil penelitian atau hasil
                                            pemikiran yang tidak disajikan dalam seminar/simposium/lokakarya, tetapi dimuat
                                            dalam prosiding: Nasional</option>
                                        <option
                                            value="Hasil penelitian atau hasil pemikiran yang disajikan dalam koran/majalah populer/umum"
                                            data-jenis="Artikel Koran/Majalah Populer">Hasil penelitian atau hasil pemikiran
                                            yang disajikan dalam koran/majalah populer/umum</option>
                                    </optgroup>

                                    <optgroup label="Pelaksanaan Pengabdian Kepada Masyarakat">
                                        <option
                                            value="Membuat/menulis karya pengabdian pada masyarakat yang tidak dipublikasikan"
                                            data-jenis="Karya Pengabdian Non-Publikasi">Membuat/menulis karya pengabdian
                                            pada masyarakat yang tidak dipublikasikan</option>
                                        <option
                                            value="Hasil kegiatan pengabdian kepada masyarakat yang dipublikasikan di sebuah berkala/jurnal ilmiah pengabdian kepada masyarakat atau teknologi tepat guna, merupakan diseminasi dari luaran program kegiatan pengabdian kepada masyarakat, tiap karya"
                                            data-jenis="Jurnal Pengabdian Masyarakat / TTG">Hasil kegiatan pengabdian kepada
                                            masyarakat yang dipublikasikan di sebuah berkala/jurnal ilmiah pengabdian kepada
                                            masyarakat atau teknologi tepat guna, merupakan diseminasi dari luaran program
                                            kegiatan pengabdian kepada masyarakat, tiap karya</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="row g-3">
                                <!-- Jenis -->
                                <div class="col-md-6">
                                    <label for="jenis" class="form-label">Jenis</label>
                                    <input type="text" name="jenis" id="jenis" class="form-control form-control-sm bg-light"
                                        readonly placeholder="Otomatis terisi dari Kategori Kegiatan">
                                </div>

                                <!-- Kategori Capaian -->
                                <div class="col-md-6">
                                    <label for="kategori_capaian" class="form-label">Kategori Capaian</label>
                                    <select name="kategori_capaian" id="kategori_capaian"
                                        class="form-select form-select-sm">
                                        <option value="Publikasi" selected>Publikasi</option>
                                        <option value="Produk Teknologi Tepat Guna">Produk Teknologi Tepat Guna</option>
                                        <option value="Jenis Luaran Lainnya">Jenis Luaran Lainnya</option>
                                        <option value="HKI">HKI</option>
                                        <option value="Buku">Buku</option>
                                        <option value="Pembicara">Pembicara</option>
                                        <option value="Visiting Scientist">Visiting Scientist</option>
                                    </select>
                                    <small class="text-muted"><i class="bi bi-info-circle"></i> TODO: Lengkapi opsi lain
                                        bila ada spesifikasi tambahan.</small>
                                </div>

                                <!-- Aktivitas Litabmas -->
                                <div class="col-md-12">
                                    <label for="aktivitas_litabmas_id" class="form-label">Aktivitas Litabmas</label>
                                    <select name="aktivitas_litabmas_id" id="aktivitas_litabmas_id"
                                        class="form-select form-select-sm">
                                        <option value="">Pilih...</option>
                                        @foreach($aktivitasLitabmas as $lit)
                                            <option value="{{ $lit->id }}">{{ $lit->kode }} - {{ $lit->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Judul Artikel -->
                                <div class="col-md-12">
                                    <label for="judul" class="form-label fw-bold">Judul Artikel <span
                                            class="text-danger">*</span></label>
                                    <div id="judul-wrap">
                                        <input type="text" name="judul" id="judul" class="form-control form-control-sm" required
                                            placeholder="Masukkan judul artikel publikasi" autocomplete="off">
                                        <div id="judul-mirip-hasil" style="display:none;"></div>
                                    </div>
                                </div>

                                <!-- Nama Jurnal -->
                                <div class="col-md-6">
                                    <label for="nama_jurnal" class="form-label">Nama Jurnal</label>
                                    <input type="text" name="nama_jurnal" id="nama_jurnal"
                                        class="form-control form-control-sm"
                                        placeholder="Nama jurnal tempat dipublikasikan">
                                </div>

                                <!-- ISSN -->
                                <div class="col-md-6">
                                    <label for="issn" class="form-label">ISSN</label>
                                    <input type="text" name="issn" id="issn" class="form-control form-control-sm"
                                        placeholder="xxxx-xxxx">
                                </div>

                                <!-- Tautan Laman Jurnal -->
                                <div class="col-md-6">
                                    <label for="tautan_jurnal" class="form-label">Tautan Laman Jurnal</label>
                                    <input type="url" name="tautan_jurnal" id="tautan_jurnal"
                                        class="form-control form-control-sm" placeholder="https://example.com/journal">
                                </div>

                                <!-- Tautan Eksternal -->
                                <div class="col-md-6">
                                    <label for="tautan_eksternal" class="form-label">Tautan Eksternal</label>
                                    <input type="url" name="tautan_eksternal" id="tautan_eksternal"
                                        class="form-control form-control-sm"
                                        placeholder="https://repository.ukri.ac.id/handle/...">
                                </div>

                                <!-- Tanggal Terbit -->
                                <div class="col-md-4">
                                    <label for="tanggal_terbit" class="form-label fw-bold">Tanggal Terbit <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_terbit" id="tanggal_terbit"
                                        class="form-control form-control-sm" required>
                                </div>

                                <!-- Volume -->
                                <div class="col-md-4">
                                    <label for="volume" class="form-label">Volume</label>
                                    <input type="number" name="volume" id="volume" class="form-control form-control-sm"
                                        placeholder="Contoh: 4" min="1">
                                </div>

                                <!-- Nomor -->
                                <div class="col-md-4">
                                    <label for="nomor" class="form-label">Nomor</label>
                                    <input type="number" name="nomor" id="nomor" class="form-control form-control-sm"
                                        placeholder="Contoh: 2" min="1">
                                </div>

                                <!-- Halaman -->
                                <div class="col-md-4">
                                    <label for="halaman" class="form-label">Halaman</label>
                                    <input type="text" name="halaman" id="halaman" class="form-control form-control-sm"
                                        placeholder="Contoh: 14-30">
                                </div>

                                <!-- Penerbit/Penyelenggara -->
                                <div class="col-md-4">
                                    <label for="penerbit" class="form-label">Penerbit/Penyelenggara</label>
                                    <input type="text" name="penerbit" id="penerbit" class="form-control form-control-sm"
                                        placeholder="Nama Penerbit / Penyelenggara">
                                </div>

                                <!-- DOI -->
                                <div class="col-md-4">
                                    <label for="doi" class="form-label">DOI</label>
                                    <input type="text" name="doi" id="doi" class="form-control form-control-sm"
                                        placeholder="10.1000/182">
                                </div>

                                <!-- Keterangan -->
                                <div class="col-md-12">
                                    <label for="keterangan" class="form-label">Keterangan / Petunjuk Akses</label>
                                    <textarea name="keterangan" id="keterangan" class="form-control form-control-sm"
                                        rows="3" placeholder="Petunjuk atau catatan tambahan..."></textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- CARD 2: Upload Dokumen -->
                    <div class="card mb-4" id="cardDokumen">
                        <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-file-earmark-pdf me-1"></i> Upload Dokumen <span
                                    class="text-danger">*</span></span>
                            <small class="text-muted fw-normal">(Maksimal total ukuran 5 MB per file: pdf, jpg, jpeg, png,
                                doc, docx, xls, xlsx, txt)</small>
                        </div>
                        <div class="card-body mt-3">

                            <!-- Preview Form Input Dokumen -->
                            <div class="border rounded p-3 mb-4 bg-light">
                                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-plus-circle-fill"></i> Tambah Dokumen
                                    Terlebih Dahulu</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Pilih File</label>
                                        <div id="new_doc_file_wrapper">
                                            <input type="file" id="new_doc_file" class="form-control form-control-sm"
                                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.txt">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nama Dokumen <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="new_doc_name" class="form-control form-control-sm"
                                            placeholder="Nama Dokumen (misal: Peer Review)">
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
                                        <input type="text" id="new_doc_desc" class="form-control form-control-sm"
                                            placeholder="Keterangan tambahan...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tautan Dokumen (Alternatif Link)</label>
                                        <input type="url" id="new_doc_link" class="form-control form-control-sm"
                                            placeholder="https://example.com/document">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-sm btn-primary px-3 fw-bold"
                                            onclick="addDokumenToList()">
                                            <i class="bi bi-plus-lg"></i> Tambahkan ke Tabel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabel Tampilan Dokumen -->
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
                                            <td colspan="7" class="text-center text-muted py-3">Belum ada dokumen
                                                ditambahkan. Gunakan form di atas untuk menambahkan minimal 1 dokumen.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    <!-- CARD 3: Penulis Dosen -->
                    <div class="card mb-4" id="cardPenulisDosen">
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
                                        <!-- Baris pertama kosongan (opsional) -->
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
                                        <!-- Baris pertama kosongan (opsional) -->
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
                                        <!-- Dynamic rows -->
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
                            <a href="{{ route('publikasi.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" id="btnSubmitPublikasi" class="btn btn-primary px-4 py-2 fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Publikasi Karya
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
        let dosenCount = 0;
        let mhsCount = 0;
        let lainCount = 0;

        // Afiliasi Penulis Dosen & Mahasiswa otomatis terisi nama kampus
        // sendiri (Penulis Lain / kolaborator eksternal tetap kosong karena
        // memang dari instansi lain).
        const DEFAULT_AFILIASI = 'Universitas Kebangsaan Republik Indonesia';

        // Semua dosen dimuat SEKALI ke memori browser, sama seperti mahasiswa
        // di bawah - pencariannya dilakukan lokal oleh Tom Select, jadi
        // instan dan cuma 1x fetch ke server walau ada banyak baris penulis
        // dosen.
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
        // bukan per-huruf yang diketik), lalu pencariannya dilakukan lokal
        // oleh Tom Select - jadi instan, tidak ada request ke server tiap
        // mengetik. Daftarnya di-cache di variabel ini supaya walau ada
        // banyak baris penulis mahasiswa, cuma 1x fetch ke server.
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

        function initMahasiswaSelect(selectEl) {
            if (!selectEl || selectEl.tomselect) return;
            const ts = new TomSelect(selectEl, {
                valueField: 'value',
                labelField: 'text',
                searchField: ['text'],
                create: false,
                maxItems: 1,
                // Semua data mahasiswa memang dimuat, tapi jumlah yang
                // dirender ke DOM tetap dibatasi supaya tidak lag kalau
                // datanya ribuan baris - begitu diketik, pencarian tetap
                // menjangkau SELURUH data (bukan cuma yang kebatas ini),
                // hasilnya makin sedikit & makin relevan sesuai ketikan.
                maxOptions: 100,
                // Dropdown dipindah ke <body> supaya tidak kepotong oleh
                // overflow tabel (.table-responsive) atau ketutup baris di
                // bawahnya - lihat z-index .ts-dropdown di CSS atas.
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
        // publikasi (bukan cuma milik dosen yang sedang login) dimuat SEKALI
        // ke memori browser, sama seperti dosen/mahasiswa di atas. Tom Select
        // mencocokkan ketikan dengan yang paling mirip di daftar itu (kalau
        // ketemu berarti jurnalnya sudah pernah dientri sebelumnya - tinggal
        // pilih, bukan bikin baris data baru yang beda kapitalisasi/spasi
        // dari yang sudah ada); kalau tidak ada yang mirip sama sekali,
        // create:true mengizinkan ketikan itu langsung dipakai sebagai nama
        // jurnal BARU.
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
            });
        }

        initJurnalSelect(document.getElementById('nama_jurnal'));

        // Auto-fill baris pertama Penulis Dosen dengan data dosen yang
        // sedang login (kalau memang login sebagai Dosen, bukan admin -
        // lihat $loggedDosen di PublikasiController::create()). Baris yang
        // ditambahkan tetap baris NORMAL: field-nya tetap bisa diganti ke
        // dosen lain lewat dropdown, urutan/afiliasi/peran tetap bisa
        // diedit, dan baris ini tetap bisa dihapus lewat tombol hapus biasa
        // - bedanya cuma nilai AWAL-nya sudah keisi otomatis.
        const loggedDosen = @json($loggedDosen ?? null);

        function initDosenLoginAutofill() {
            if (!loggedDosen || !loggedDosen.id) return;

            addPenulisDosenRow();

            const row = document.querySelector('#containerPenulisDosen tr:last-child');
            if (!row) return;

            const selectEl = row.querySelector('.dosen-select');
            if (!selectEl) return;

            const label = loggedDosen.nidn + ' - ' + loggedDosen.nama +
                (loggedDosen.prodi ? ' (' + loggedDosen.prodi + ')' : '');

            loadAllDosenOptions().then(() => {
                const ts = selectEl.tomselect;
                if (!ts) return;
                // Pastikan opsinya ada (harusnya sudah ada dari daftar
                // semua dosen, tapi ditambahkan lagi jaga-jaga supaya tetap
                // terisi walau kebetulan belum sempat ke-load) lalu pilih.
                ts.addOption({ value: String(loggedDosen.id), text: label });
                ts.refreshOptions(false);
                ts.setValue(String(loggedDosen.id), true);
            });
        }

        // Dipanggil lewat setTimeout(..., 0) supaya baru jalan SETELAH
        // seluruh script di halaman ini selesai dieksekusi sekali (termasuk
        // deklarasi pernahCobaSubmit & fungsi revalidateAfterChange di
        // bagian bawah, yang dipakai juga oleh addPenulisDosenRow()) -
        // tanpa ini, pemanggilan langsung di sini akan error karena
        // pernahCobaSubmit belum sempat terdeklarasi.
        setTimeout(initDosenLoginAutofill, 0);

        // Penerbit/Penyelenggara - pola sama persis dengan Nama Jurnal di
        // atas: daftar semua penerbit yang PERNAH dientri dimuat sekali,
        // lalu Tom Select mempersempit dropdown sesuai ketikan (kalau
        // ketikan cocok dengan yang sudah pernah ada, tinggal pilih -
        // menghindari data ganda beda kapitalisasi/spasi); kalau belum
        // pernah ada sama sekali, create:true tetap mengizinkan diketik
        // bebas sebagai penerbit baru.
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
            });
        }

        initPenerbitSelect(document.getElementById('penerbit'));

        // ISSN - diformat otomatis jadi xxxx-xxxx sambil mengetik: begitu
        // karakter ke-5 diketik, dash disisipkan otomatis sebelum karakter
        // itu (jadi "1234" -> ketik "5" -> otomatis jadi "1234-5"). Huruf
        // "X"/"x" tetap diperbolehkan (dipakai di ISSN sebagai check digit)
        // dan otomatis di-uppercase; total 8 karakter (belum termasuk dash).
        (function () {
            const issnInput = document.getElementById('issn');
            if (!issnInput) return;
            issnInput.setAttribute('maxlength', 9);
            issnInput.addEventListener('input', function () {
                let raw = issnInput.value.toUpperCase().replace(/[^0-9X]/g, '').slice(0, 8);
                issnInput.value = raw.length > 4 ? raw.slice(0, 4) + '-' + raw.slice(4) : raw;
            });
        })();

        // Nama Kolaborator (Penulis Lain) - pola sama persis dengan Nama
        // Jurnal di atas: dropdown autocomplete dari nama-nama yang pernah
        // diinput sebelumnya (lintas SEMUA publikasi, tidak dibatasi per
        // dosen), tapi tetap boleh mengetik nama baru kalau memang belum
        // pernah ada (create:true). Bedanya, field ini dirender ulang tiap
        // kali baris baru ditambahkan lewat addPenulisLainRow() - jadi
        // initPenulisLainSelect() dipanggil di situ, bukan sekali saat
        // halaman dibuka.
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
            });
        }

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

        // 1. Sync Jenis from Kategori Kegiatan
        function syncJenis(kategoriValue) {
            const select = document.getElementById('kategori_kegiatan');
            const selectedOption = select.options[select.selectedIndex];
            const jenisInput = document.getElementById('jenis');
            if (selectedOption && selectedOption.dataset.jenis) {
                jenisInput.value = selectedOption.dataset.jenis;
            } else {
                jenisInput.value = '';
            }
        }

        // 2. Multi-Dokumen Rows (Preview-then-list style)
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
            revalidateAfterChange();
        }

        function removeUploadedDocRow(btn) {
            btn.closest('tr').remove();
            updateDocNumbers();
            revalidateAfterChange();
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
                        <td colspan="7" class="text-center text-muted py-3">Belum ada dokumen ditambahkan. Gunakan form di atas untuk menambahkan minimal 1 dokumen.</td>
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
                    <input type="radio" name="corresponding_author" value="dosen_${nextIdx}" class="form-check-input check-corresponding">
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
            revalidateAfterChange();
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
                    <input type="radio" name="corresponding_author" value="mahasiswa_${nextIdx}" class="form-check-input check-corresponding">
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
            revalidateAfterChange();
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
                    <input type="text" name="penulis_lain[${nextIdx}][afiliasi]" class="form-control form-control-sm table-editable-cell" placeholder="Afiliasi / Instansi">
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
                    <input type="radio" name="corresponding_author" value="lain_${nextIdx}" class="form-check-input check-corresponding">
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
            revalidateAfterChange();
        }

        function removeRow(btn) {
            const tbody = btn.closest('tbody');
            btn.closest('tr').remove();
            reorderUrutan(tbody);
            revalidateAfterChange();
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
        document.addEventListener('keydown', function (e) {
            if (e.target && e.target.type === 'number') {
                if (['e', 'E', '-', '+', '.', ','].includes(e.key)) {
                    e.preventDefault();
                }
            }
        });

        document.addEventListener('input', function (e) {
            if (e.target && e.target.type === 'number') {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target && e.target.type === 'number') {
                const val = parseInt(e.target.value);
                if (isNaN(val) || val < 1) {
                    e.target.value = 1;
                }
            }
        });

        // Cek Judul Artikel mirip - dicari lintas SEMUA publikasi di DB
        // (bukan cuma judul dosen yang dipilih di form ini), soalnya judul
        // yang sama bisa saja pernah diinput oleh dosen lain. Dibikin senada
        // dengan dropdown Nama Jurnal/Dosen/Mahasiswa: nempel di bawah input,
        // dan makin panjang/spesifik ketikannya, daftar yang tampil makin
        // sedikit/makin mirip (backend sudah urutkan yang judulnya paling
        // pendek/paling dekat duluan) - kalau sampai tidak ada satupun yang
        // cocok, dropdown menampilkan pesan "tidak ditemukan" (bukan
        // menyembunyikan begitu saja), sama seperti perilaku Nama Jurnal.
        (function () {
            const judulInput = document.getElementById('judul');
            const hasilBox = document.getElementById('judul-mirip-hasil');
            if (!judulInput || !hasilBox) return;

            const MIN_CHARS = 3;
            let debounceTimer = null;
            let requestSeq = 0;

            function escapeHtmlLocal(text) {
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            // Menebalkan bagian judul yang persis cocok dengan ketikan, biar
            // kelihatan jelas "mirip di huruf/kata yang mana".
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
                    const url = `{{ route('api.publikasi.cek-judul') }}?q=${encodeURIComponent(q)}`;

                    fetch(url)
                        .then((r) => r.json())
                        .then((data) => {
                            if (seq !== requestSeq) return; // respons basi (ketikan sudah berubah lagi)
                            if (judulInput.value.trim().length < MIN_CHARS) return hide();

                            if (!data.length) {
                                hasilBox.style.display = 'block';
                                hasilBox.innerHTML = `<div class="no-results"><i class="bi bi-check-circle text-success"></i> Tidak ada judul mirip yang ditemukan untuk "${escapeHtmlLocal(q)}" - aman dilanjutkan sebagai publikasi baru.</div>`;
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
                // Kasih jeda dikit sebelum ditutup, supaya kalau ada klik di
                // dalam dropdown (mis. suatu saat mau ditambah aksi) tidak
                // keburu hilang duluan.
                setTimeout(hide, 150);
            });
        })();

        // ------------------------------------------------------------------
        // Validasi wajib-isi di sisi browser (mirror dari aturan di
        // PublikasiController@store) SEBELUM form dikirim ke server.
        //
        // Kenapa: form ini murni JS untuk baris dinamisnya (dokumen, penulis
        // dosen/mahasiswa/lain) - tidak ada logic untuk membangun ulang
        // baris-baris itu dari old() kalau request ditolak validasi server.
        // Jadi kalau submit tetap dikirim ke server lalu server yang
        // menolak, halaman refresh dan SEMUA baris yang sudah diisi user
        // (termasuk file yang sudah dipilih untuk diupload) hilang - user
        // harus mengulang dari nol. Dengan mengecek dulu di browser, submit
        // yang jelas-jelas belum lengkap dibatalkan SEBELUM sempat mengirim
        // apapun ke server (tidak ada request, tidak ada refresh), baris
        // yang sudah diisi tetap utuh, dan tombol Simpan otomatis
        // di-nonaktifkan sampai bagian yang kurang itu dilengkapi.
        // ------------------------------------------------------------------
        const formPublikasi = document.getElementById('formPublikasi');
        const btnSubmitPublikasi = document.getElementById('btnSubmitPublikasi');
        let pernahCobaSubmit = false;

        function cekFieldWajib() {
            const daftarKurang = [];

            const kategori = document.getElementById('kategori_kegiatan');
            if (kategori && !kategori.value) {
                daftarKurang.push({ pesan: 'Kategori Kegiatan wajib dipilih.', el: kategori });
            }

            const judul = document.getElementById('judul');
            if (judul && !judul.value.trim()) {
                daftarKurang.push({ pesan: 'Judul Artikel wajib diisi.', el: judul });
            }

            const tanggal = document.getElementById('tanggal_terbit');
            if (tanggal && !tanggal.value) {
                daftarKurang.push({ pesan: 'Tanggal Terbit wajib diisi.', el: tanggal });
            }

            // Minimal 1 dokumen sudah ditambahkan ke tabel (baris placeholder
            // "Belum ada dokumen..." tidak dihitung).
            const jumlahDokumen = document.querySelectorAll('#containerDokumen tr.row-dokumen-item').length;
            if (jumlahDokumen === 0) {
                daftarKurang.push({
                    pesan: 'Minimal 1 dokumen wajib ditambahkan ke tabel dokumen (klik "Tambahkan ke Tabel" setelah mengisi form di atasnya).',
                    card: document.getElementById('cardDokumen'),
                });
            }

            // Minimal 1 penulis dari Dosen ATAU Mahasiswa (sama seperti
            // aturan server - Penulis Lain saja tidak cukup).
            const dosenTerpilih = Array.from(document.querySelectorAll('#containerPenulisDosen .dosen-select'))
                .some((s) => s.value);
            const mahasiswaTerpilih = Array.from(document.querySelectorAll('#containerPenulisMahasiswa .mahasiswa-select'))
                .some((s) => s.value);
            if (!dosenTerpilih && !mahasiswaTerpilih) {
                daftarKurang.push({
                    pesan: 'Minimal 1 Penulis Dosen atau Penulis Mahasiswa wajib dipilih.',
                    card: document.getElementById('cardPenulisDosen'),
                });
            }

            // Wajib ada 1 Corresponding Author (radio di salah satu baris
            // penulis - dosen, mahasiswa, atau lain).
            const adaCorresponding = !!document.querySelector('input[name="corresponding_author"]:checked');
            if (!adaCorresponding) {
                daftarKurang.push({ pesan: 'Wajib memilih 1 Corresponding Author pada salah satu baris penulis.' });
            }

            return daftarKurang;
        }

        function bersihkanTandaError() {
            document.querySelectorAll('#formPublikasi .is-invalid').forEach((el) => el.classList.remove('is-invalid'));
            document.querySelectorAll('#formPublikasi .border-danger').forEach((el) => el.classList.remove('border-danger'));
        }

        function tampilkanFieldWajib(daftarKurang) {
            const box = document.getElementById('clientValidationErrors');
            const list = document.getElementById('clientValidationErrorsList');

            if (daftarKurang.length === 0) {
                box.classList.add('d-none');
                list.innerHTML = '';
                return;
            }

            list.innerHTML = daftarKurang.map((item) => `<li>${escapeHtml(item.pesan)}</li>`).join('');
            box.classList.remove('d-none');

            daftarKurang.forEach((item) => {
                if (item.el) item.el.classList.add('is-invalid');
                if (item.card) item.card.classList.add('border-danger');
            });

            box.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Dipanggil ulang tiap ada perubahan (tambah/hapus baris dokumen
        // atau penulis) - TAPI hanya benar-benar mengecek & menahan tombol
        // kalau user sudah pernah mencoba submit sekali. Sebelum submit
        // pertama, tombol tetap aktif seperti biasa supaya tidak terkesan
        // "macet" tanpa penjelasan begitu halaman pertama dibuka.
        function revalidateAfterChange() {
            if (!pernahCobaSubmit) return;
            bersihkanTandaError();
            const daftarKurang = cekFieldWajib();
            tampilkanFieldWajib(daftarKurang);
            if (btnSubmitPublikasi) btnSubmitPublikasi.disabled = daftarKurang.length > 0;
        }

        if (formPublikasi && btnSubmitPublikasi) {
            formPublikasi.addEventListener('submit', function (e) {
                pernahCobaSubmit = true;
                bersihkanTandaError();
                const daftarKurang = cekFieldWajib();

                if (daftarKurang.length > 0) {
                    // Batalkan submit SEBELUM sempat mengirim apapun ke
                    // server - jadi tidak ada request, tidak ada refresh,
                    // dan seluruh baris dokumen/penulis yang sudah diisi
                    // user tetap ada persis seperti sebelumnya.
                    e.preventDefault();
                    tampilkanFieldWajib(daftarKurang);
                    btnSubmitPublikasi.disabled = true;
                    return false;
                }

                // Lolos validasi browser - tombol di-nonaktifkan supaya
                // tidak ke-klik dobel selagi request beneran (termasuk
                // upload file) masih berjalan ke server.
                btnSubmitPublikasi.disabled = true;
                btnSubmitPublikasi.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
            });

            // Field-field sederhana ini cukup didengarkan langsung supaya
            // tombol bisa langsung ter-enable lagi begitu diisi, tanpa perlu
            // menunggu ada perubahan baris dokumen/penulis.
            ['kategori_kegiatan', 'judul', 'tanggal_terbit'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', revalidateAfterChange);
                if (el) el.addEventListener('change', revalidateAfterChange);
            });

            // Delegasi di level form supaya baris yang ditambahkan belakangan
            // (dokumen, penulis dosen/mahasiswa/lain, radio corresponding
            // author) otomatis ikut kepantau tanpa perlu daftar listener
            // satu-satu per baris.
            formPublikasi.addEventListener('change', revalidateAfterChange);
        }
    </script>
@endpush
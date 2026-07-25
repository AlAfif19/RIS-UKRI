<?php $__env->startSection('content'); ?>

    <div class="pagetitle">
        <h1>Tambah Publikasi Karya</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('publikasi.index')); ?>">Publikasi Karya</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Terdapat kesalahan pengisian form:</strong>
                        <ul class="mb-0 mt-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('publikasi.store')); ?>" method="POST" enctype="multipart/form-data"
                    id="formPublikasi">
                    <?php echo csrf_field(); ?>

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
                                        <?php $__currentLoopData = $aktivitasLitabmas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($lit->id); ?>"><?php echo e($lit->kode); ?> - <?php echo e($lit->nama); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <!-- Judul Artikel -->
                                <div class="col-md-12">
                                    <label for="judul" class="form-label fw-bold">Judul Artikel <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="judul" id="judul" class="form-control form-control-sm" required
                                        placeholder="Masukkan judul artikel publikasi">
                                </div>

                                <!-- Nama Jurnal -->
                                <div class="col-md-6">
                                    <label for="nama_jurnal" class="form-label">Nama Jurnal</label>
                                    <input type="text" name="nama_jurnal" id="nama_jurnal"
                                        class="form-control form-control-sm"
                                        placeholder="Nama jurnal tempat dipublikasikan">
                                </div>

                                <!-- Tautan Laman Jurnal -->
                                <div class="col-md-6">
                                    <label for="tautan_jurnal" class="form-label">Tautan Laman Jurnal</label>
                                    <input type="url" name="tautan_jurnal" id="tautan_jurnal"
                                        class="form-control form-control-sm" placeholder="https://example.com/journal">
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

                                <!-- ISSN -->
                                <div class="col-md-6">
                                    <label for="issn" class="form-label">ISSN</label>
                                    <input type="text" name="issn" id="issn" class="form-control form-control-sm"
                                        placeholder="xxxx-xxxx">
                                </div>

                                <!-- Tautan Eksternal -->
                                <div class="col-md-6">
                                    <label for="tautan_eksternal" class="form-label">Tautan Eksternal</label>
                                    <input type="url" name="tautan_eksternal" id="tautan_eksternal"
                                        class="form-control form-control-sm"
                                        placeholder="https://repository.ukri.ac.id/handle/...">
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
                    <div class="card mb-4">
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
                                        <tr class="row-penulis-dosen" data-index="0">
                                            <td>
                                                <input type="text" name="penulis_dosen[0][nidn]"
                                                    class="form-control form-control-sm table-editable-cell"
                                                    placeholder="NIDN Dosen">
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_dosen[0][nama_dosen]"
                                                    class="form-control form-control-sm table-editable-cell"
                                                    placeholder="Nama Lengkap Dosen" required>
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_dosen[0][nama_pt]"
                                                    class="form-control form-control-sm table-editable-cell"
                                                    placeholder="Nama PT / Univ">
                                            </td>
                                            <td>
                                                <input type="number" name="penulis_dosen[0][urutan]"
                                                    class="form-control form-control-sm input-urutan table-editable-cell"
                                                    value="1">
                                            </td>
                                            <td>
                                                <input type="text" name="penulis_dosen[0][afiliasi]"
                                                    class="form-control form-control-sm table-editable-cell"
                                                    placeholder="Afiliasi">
                                            </td>
                                            <td>
                                                <select name="penulis_dosen[0][peran]"
                                                    class="form-select form-select-sm table-editable-cell">
                                                    <option value="Penulis">Penulis</option>
                                                    <option value="Editor">Editor</option>
                                                    <option value="Penerjemah">Penerjemah</option>
                                                    <option value="Penemu/Inventor">Penemu/Inventor</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" name="corresponding_author" value="dosen_0"
                                                    class="form-check-input check-corresponding" required>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="moveRowUp(this)" title="Naikkan"><i
                                                            class="bi bi-arrow-up"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="moveRowDown(this)" title="Turunkan"><i
                                                            class="bi bi-arrow-down"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="removeRow(this)" title="Hapus"><i
                                                            class="bi bi-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
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
                                            <th style="width: 35%">Nama Mahasiswa (Pilih dari DB Mahasiswa)</th>
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
                            <a href="<?php echo e(route('publikasi.index')); ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Publikasi Karya
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        let dokCount = 0;
        let dosenCount = 1;
        let mhsCount = 0;
        let lainCount = 0;

        const masterMahasiswaOptions = `<?php $__currentLoopData = $mahasiswaList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mhs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($mhs->id); ?>"><?php echo e($mhs->nim); ?> - <?php echo e($mhs->nama); ?> (<?php echo e($mhs->prodi); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>`;

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
                        <td colspan="7" class="text-center text-muted py-3">Belum ada dokumen ditambahkan. Gunakan form di atas untuk menambahkan minimal 1 dokumen.</td>
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
        }

        // 4. Penulis Mahasiswa Rows
        function addPenulisMahasiswaRow() {
            const container = document.getElementById('containerPenulisMahasiswa');
            const row = document.createElement('tr');
            row.className = 'row-penulis-mahasiswa';
            const nextIdx = mhsCount++;
            row.innerHTML = `
                <td>
                    <select name="penulis_mahasiswa[${nextIdx}][mahasiswa_id]" class="form-select form-select-sm table-editable-cell" required>
                        <option value="">Cari / Pilih Mahasiswa (NIM - Nama)...</option>
                        ${masterMahasiswaOptions}
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
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project\simantap\ris-ukri\resources\views/publikasi/create.blade.php ENDPATH**/ ?>
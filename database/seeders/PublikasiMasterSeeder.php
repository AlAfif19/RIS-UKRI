<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterPerguruanTinggi;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MasterAktivitasLitabmas;

class PublikasiMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Perguruan Tinggi
        $pt1 = MasterPerguruanTinggi::firstOrCreate(['kode_pt' => '041065'], ['nama_pt' => 'Universitas Kebangsaan Republik Indonesia']);
        $pt2 = MasterPerguruanTinggi::firstOrCreate(['kode_pt' => '071020'], ['nama_pt' => 'Universitas PGRI Ronggolawe']);
        $pt3 = MasterPerguruanTinggi::firstOrCreate(['kode_pt' => '041012'], ['nama_pt' => 'Universitas Nurtanio Bandung']);
        $pt4 = MasterPerguruanTinggi::firstOrCreate(['kode_pt' => '111005'], ['nama_pt' => 'Universitas Muhammadiyah Banjarmasin']);

        // 2. Seed Dosen
        Dosen::firstOrCreate(['nidn' => '0454768669130262'], [
            'master_perguruan_tinggi_id' => $pt1->id,
            'nama' => 'SUBHANJAYA ANGGA ATMAJA',
            'email' => 'subhanjaya@ukri.ac.id'
        ]);
        Dosen::firstOrCreate(['nidn' => '6955745646230092'], [
            'master_perguruan_tinggi_id' => $pt2->id,
            'nama' => 'CANDRA AENI',
            'email' => 'candra@unirow.ac.id'
        ]);
        Dosen::firstOrCreate(['nidn' => '6338746647130093'], [
            'master_perguruan_tinggi_id' => $pt3->id,
            'nama' => 'ENCEP SOPANDI',
            'email' => 'encep@nurtanio.ac.id'
        ]);
        Dosen::firstOrCreate(['nidn' => '8736763665300082'], [
            'master_perguruan_tinggi_id' => $pt4->id,
            'nama' => 'DEWI MAHARANI',
            'email' => 'dewi@umbjm.ac.id'
        ]);

        // 3. Seed Mahasiswa
        Mahasiswa::firstOrCreate(['nim' => '20221310001'], [
            'master_perguruan_tinggi_id' => $pt1->id,
            'nama' => 'Ahmad Rizky Pratama',
            'prodi' => 'Teknik Informatika',
            'email' => 'ahmad.rizky@student.ukri.ac.id'
        ]);
        Mahasiswa::firstOrCreate(['nim' => '20221310002'], [
            'master_perguruan_tinggi_id' => $pt1->id,
            'nama' => 'Siti Nurhaliza',
            'prodi' => 'Sistem Informasi',
            'email' => 'siti.nur@student.ukri.ac.id'
        ]);
        Mahasiswa::firstOrCreate(['nim' => '20221310003'], [
            'master_perguruan_tinggi_id' => $pt1->id,
            'nama' => 'Budi Santoso',
            'prodi' => 'Teknik Informatika',
            'email' => 'budi.santoso@student.ukri.ac.id'
        ]);

        // 4. Seed Aktivitas Litabmas
        MasterAktivitasLitabmas::firstOrCreate(['kode' => 'LIT-001'], ['nama' => 'Penelitian Dasar Kemdiktisaintek', 'deskripsi' => 'Program skema hibah penelitian dasar']);
        MasterAktivitasLitabmas::firstOrCreate(['kode' => 'LIT-002'], ['nama' => 'Penelitian Terapan Unggulan PT', 'deskripsi' => 'Program skema terapan perguruan tinggi']);
        MasterAktivitasLitabmas::firstOrCreate(['kode' => 'PKM-001'], ['nama' => 'Pengabdian Masyarakat Berbasis Mitra', 'deskripsi' => 'Program pengabdian masyarakat masyarakat berdikari']);

        // 5. Seed 5 Publikasi Karya
        $pub1 = \App\Models\Publikasi::firstOrCreate(['judul' => 'Sistem Informasi Geografis untuk Pemetaan Lokasi Bencana Alam'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'aktivitas_litabmas_id' => \App\Models\MasterAktivitasLitabmas::where('kode', 'PKM-001')->first()->id,
            'nama_jurnal' => 'Jurnal Teknik Informatika (JTI)',
            'tautan_jurnal' => 'https://jurnal.ukri.ac.id/index.php/jti',
            'tanggal_terbit' => '2026-02-15',
            'volume' => 5,
            'nomor' => 2,
            'halaman' => '110-120',
            'penerbit' => 'UKRI Press',
            'doi' => '10.22441/jti.v5i2.102',
            'issn' => '2301-7890',
            'tautan_eksternal' => 'https://repository.ukri.ac.id/handle/12345/67',
            'keterangan' => 'Didanai oleh Hibah Internal UKRI'
        ]);

        \App\Models\PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub1->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'master_perguruan_tinggi_id' => $pt1->id,
            'urutan' => 1,
            'afiliasi' => 'Universitas Kebangsaan',
            'peran' => 'Penulis',
            'is_corresponding' => true
        ]);

        \App\Models\PublikasiPenulisMahasiswa::firstOrCreate([
            'publikasi_id' => $pub1->id,
            'mahasiswa_id' => Mahasiswa::where('nama', 'Ahmad Rizky Pratama')->first()->id,
        ], [
            'urutan' => 2,
            'afiliasi' => 'Universitas Kebangsaan',
            'peran' => 'Penulis',
            'is_corresponding' => false
        ]);

        \App\Models\PublikasiDokumen::firstOrCreate([
            'publikasi_id' => $pub1->id,
            'nama_dokumen' => 'Hasil Peer Review',
        ], [
            'nama_file' => 'peer_review_sig.pdf',
            'path_file' => 'dokumen/peer_review_sig.pdf',
            'jenis_file' => 'PDF File',
            'tanggal_upload' => '2026-02-20',
            'jenis_dokumen' => 'Peer Review',
            'tautan_dokumen' => null,
            'keterangan' => 'Lengkap ttd reviewer'
        ]);

        // Publikasi 2
        $pub2 = \App\Models\Publikasi::firstOrCreate(['judul' => 'Deep Learning for Automated Diagnosis of Skin Lesions'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional bereputasi',
            'jenis' => 'Jurnal Internasional Bereputasi',
            'kategori_capaian' => 'Publikasi',
            'aktivitas_litabmas_id' => \App\Models\MasterAktivitasLitabmas::where('kode', 'LIT-001')->first()->id,
            'nama_jurnal' => 'IEEE Transactions on Medical Imaging',
            'tautan_jurnal' => 'https://ieeexplore.ieee.org/xpl/RecentIssue.jsp?punumber=42',
            'tanggal_terbit' => '2026-05-10',
            'volume' => 12,
            'nomor' => 1,
            'halaman' => '45-60',
            'penerbit' => 'IEEE',
            'doi' => '10.1109/tmi.2026.304523',
            'issn' => '0278-0062',
            'tautan_eksternal' => 'https://arxiv.org/abs/2605.304523',
            'keterangan' => 'Kolaborasi dengan PGRI Ronggolawe'
        ]);

        \App\Models\PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'dosen_id' => Dosen::where('nama', 'CANDRA AENI')->first()->id,
        ], [
            'master_perguruan_tinggi_id' => $pt2->id,
            'urutan' => 1,
            'afiliasi' => 'Universitas PGRI Ronggolawe',
            'peran' => 'Penulis',
            'is_corresponding' => true
        ]);

        \App\Models\PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'master_perguruan_tinggi_id' => $pt1->id,
            'urutan' => 2,
            'afiliasi' => 'Universitas Kebangsaan',
            'peran' => 'Penulis',
            'is_corresponding' => false
        ]);

        \App\Models\PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'nama' => 'Dr. Alan Turing',
        ], [
            'urutan' => 3,
            'afiliasi' => 'University of Cambridge',
            'peran' => 'Penulis',
            'is_corresponding' => false
        ]);

        \App\Models\PublikasiDokumen::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'nama_dokumen' => 'Naskah Artikel Jurnal',
        ], [
            'nama_file' => 'deep_learning_lesions.pdf',
            'path_file' => 'dokumen/deep_learning_lesions.pdf',
            'jenis_file' => 'PDF File',
            'tanggal_upload' => '2026-05-15',
            'jenis_dokumen' => 'Naskah Jurnal',
        ]);

        // Publikasi 3
        $pub3 = \App\Models\Publikasi::firstOrCreate(['judul' => 'Buku Ajar: Pemrograman Web Dinamis Menggunakan Laravel 11'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk buku referensi',
            'jenis' => 'Buku Referensi',
            'kategori_capaian' => 'Buku',
            'aktivitas_litabmas_id' => \App\Models\MasterAktivitasLitabmas::where('kode', 'LIT-002')->first()->id,
            'nama_jurnal' => null,
            'tautan_jurnal' => null,
            'tanggal_terbit' => '2026-01-20',
            'volume' => null,
            'nomor' => null,
            'halaman' => '250',
            'penerbit' => 'UKRI Press',
            'doi' => '10.9876/ukri.laravel11',
            'issn' => '978-602-1234-56-7',
            'tautan_eksternal' => 'https://books.google.com',
            'keterangan' => 'Referensi Mata Kuliah Pemrograman Web'
        ]);

        \App\Models\PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub3->id,
            'dosen_id' => Dosen::where('nama', 'ENCEP SOPANDI')->first()->id,
        ], [
            'master_perguruan_tinggi_id' => $pt3->id,
            'urutan' => 1,
            'afiliasi' => 'Universitas Nurtanio',
            'peran' => 'Penulis',
            'is_corresponding' => true
        ]);

        \App\Models\PublikasiPenulisMahasiswa::firstOrCreate([
            'publikasi_id' => $pub3->id,
            'mahasiswa_id' => Mahasiswa::where('nama', 'Siti Nurhaliza')->first()->id,
        ], [
            'urutan' => 2,
            'afiliasi' => 'Universitas Kebangsaan',
            'peran' => 'Penulis',
            'is_corresponding' => false
        ]);

        \App\Models\PublikasiDokumen::firstOrCreate([
            'publikasi_id' => $pub3->id,
            'nama_dokumen' => 'Bukti Cover Buku',
        ], [
            'nama_file' => 'cover_buku_laravel11.png',
            'path_file' => 'dokumen/cover_buku_laravel11.png',
            'jenis_file' => 'PNG Image',
            'tanggal_upload' => '2026-01-22',
            'jenis_dokumen' => 'Cover Buku',
        ]);

        // Publikasi 4
        $pub4 = \App\Models\Publikasi::firstOrCreate(['judul' => 'Pemberdayaan UMKM Melalui Digital Marketing di Jawa Barat'], [
            'kategori_kegiatan' => 'Hasil kegiatan pengabdian kepada masyarakat yang dipublikasikan di sebuah berkala/jurnal ilmiah pengabdian kepada masyarakat atau teknologi tepat guna, merupakan diseminasi dari luaran program kegiatan pengabdian kepada masyarakat, tiap karya',
            'jenis' => 'Jurnal Pengabdian Masyarakat / TTG',
            'kategori_capaian' => 'Publikasi',
            'aktivitas_litabmas_id' => \App\Models\MasterAktivitasLitabmas::where('kode', 'PKM-001')->first()->id,
            'nama_jurnal' => 'Jurnal Pengabdian Masyarakat Terpadu',
            'tautan_jurnal' => 'https://jurnal.mitra.ac.id/jpmt',
            'tanggal_terbit' => '2026-06-05',
            'volume' => 3,
            'nomor' => 1,
            'halaman' => '12-25',
            'penerbit' => 'Mitra Mandiri',
            'doi' => null, // empty DOI to trigger DOI warning in quality check
            'issn' => '2715-9988',
            'tautan_eksternal' => 'https://jurnal.mitra.ac.id/jpmt/article/view/99',
            'keterangan' => 'Pengabdian masyarakat didanai kementerian'
        ]);

        \App\Models\PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub4->id,
            'dosen_id' => Dosen::where('nama', 'DEWI MAHARANI')->first()->id,
        ], [
            'master_perguruan_tinggi_id' => $pt4->id,
            'urutan' => 1,
            'afiliasi' => 'Universitas Muhammadiyah Banjarmasin',
            'peran' => 'Penulis',
            'is_corresponding' => true
        ]);

        \App\Models\PublikasiPenulisMahasiswa::firstOrCreate([
            'publikasi_id' => $pub4->id,
            'mahasiswa_id' => Mahasiswa::where('nama', 'Budi Santoso')->first()->id,
        ], [
            'urutan' => 2,
            'afiliasi' => 'Universitas Kebangsaan',
            'peran' => 'Penulis',
            'is_corresponding' => false
        ]);

        \App\Models\PublikasiDokumen::firstOrCreate([
            'publikasi_id' => $pub4->id,
            'nama_dokumen' => 'Tautan Jurnal Pengabdian',
        ], [
            'nama_file' => null,
            'path_file' => null,
            'jenis_file' => 'URL Link',
            'tanggal_upload' => '2026-06-10',
            'jenis_dokumen' => 'Tautan Luar',
            'tautan_dokumen' => 'https://jurnal.mitra.ac.id/jpmt/article/view/99',
        ]);

        // Publikasi 5
        $pub5 = \App\Models\Publikasi::firstOrCreate(['judul' => 'Penerjemahan Buku: Artificial Intelligence Modern Approach (4th Edition)'], [
            'kategori_kegiatan' => 'Menerjemahkan/menyadur buku ilmiah yang diterbitkan (ber ISBN)',
            'jenis' => 'Menerjemahkan/menyadur buku',
            'kategori_capaian' => 'Buku',
            'aktivitas_litabmas_id' => \App\Models\MasterAktivitasLitabmas::where('kode', 'LIT-002')->first()->id,
            'nama_jurnal' => null,
            'tautan_jurnal' => null,
            'tanggal_terbit' => '2026-04-12',
            'volume' => null,
            'nomor' => null,
            'halaman' => '1120',
            'penerbit' => 'Andi Offset',
            'doi' => '10.9876/andi.ai4',
            'issn' => '978-979-29-9876-5',
            'tautan_eksternal' => 'https://andipublisher.com',
            'keterangan' => 'Edisi terjemahan bahasa indonesia resmi'
        ]);

        \App\Models\PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub5->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'master_perguruan_tinggi_id' => $pt1->id,
            'urutan' => 1,
            'afiliasi' => 'Universitas Kebangsaan',
            'peran' => 'Penerjemah',
            'is_corresponding' => true
        ]);

        \App\Models\PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub5->id,
            'dosen_id' => Dosen::where('nama', 'ENCEP SOPANDI')->first()->id,
        ], [
            'master_perguruan_tinggi_id' => $pt3->id,
            'urutan' => 2,
            'afiliasi' => 'Universitas Nurtanio',
            'peran' => 'Editor',
            'is_corresponding' => false
        ]);

        \App\Models\PublikasiDokumen::firstOrCreate([
            'publikasi_id' => $pub5->id,
            'nama_dokumen' => 'Bukti Kontrak Penerjemahan',
        ], [
            'nama_file' => 'kontrak_terjemah_ai.pdf',
            'path_file' => 'dokumen/kontrak_terjemah_ai.pdf',
            'jenis_file' => 'PDF File',
            'tanggal_upload' => '2026-04-15',
            'jenis_dokumen' => 'Kontrak Kerja',
        ]);
    }
}

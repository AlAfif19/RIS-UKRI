<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\MasterPerguruanTinggi;
use App\Models\Publikasi;
use App\Models\PublikasiPenulisDosen;
use App\Models\PublikasiPenulisLain;

class PublikasiImportSeeder extends Seeder
{
    /**
     * Import data publikasi dari Data_Penelitian.xlsx (sheet Jurnal & Book).
     *
     * Penulis yang namanya cocok persis dengan dosen di Master Data API UKRI
     * (SUBHANJAYA ANGGA ATMAJA, IIM ABDURROHIM, IRMAN HARIMAN,
     * OSCAR HADIKARYANA, DENI SUPRIHADI, YASRI, ALKAUTSAR RAHMAN,
     * ERWIN TEGUH ARUJISAPUTRA - lihat seedDosenFromMasterData()) dimasukkan
     * sebagai PublikasiPenulisDosen dengan NIDN sesuai master. Sisanya
     * (belum ada di master, mis. mahasiswa/co-author eksternal) tetap
     * dimasukkan sebagai Kolaborator Eksternal (publikasi_penulis_lain).
     */
    public function run(): void
    {
        $this->seedDosenFromMasterData();

        // ==================== SHEET: Jurnal ====================
        $pub1 = Publikasi::firstOrCreate(['judul' => 'Blockchain Integration in Cybersecurity: A Novel Approach to Enhancing Data Privacy and Integrity in Digital Transactions'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada basis data internasional',
            'jenis' => 'Jurnal Internasional Terindeks Basis Data',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'The Journal of Academic Science',
            'tautan_jurnal' => 'https://thejoas.com/index.php/thejoas/article/view/128',
            'tanggal_terbit' => '2024-11-30',
            'penerbit' => null,
            'issn' => '2997-7258',
            'tautan_eksternal' => 'https://thejoas.com/index.php/thejoas/article/view/128',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub1->id,
            'nama' => 'ABDURROHMAN',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub1->id,
            'nama' => 'FEGIE YOANTI WATTIMENA',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub1->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub1->id,
            'nama' => 'BAHARUDDIN',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub1->id,
            'dosen_id' => Dosen::where('nidn', '0421057401')->first()->id,
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub2 = Publikasi::firstOrCreate(['judul' => 'AI-Powered Predictive Analytics in IT: Enhancing System Security and Performance Optimization'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada basis data internasional',
            'jenis' => 'Jurnal Internasional Terindeks Basis Data',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal of Social Science',
            'tautan_jurnal' => 'https://ejournal.mellbaou.com/index.php/join/article/view/153',
            'tanggal_terbit' => '2024-11-30',
            'penerbit' => null,
            'issn' => '2723-4673',
            'tautan_eksternal' => 'https://ejournal.mellbaou.com/index.php/join/article/view/153',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'nama' => 'EDDY SUMARTONO',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'nama' => 'BADIE UDDIN',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'nama' => 'IKA MAYLANI',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub2->id,
            'nama' => 'DEVI SARTIKA',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub3 = Publikasi::firstOrCreate(['judul' => 'Blockchain-Based Framework for Enhancing Data Security in IoT Systems'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional terindeks pada basis data internasional',
            'jenis' => 'Jurnal Internasional Terindeks Basis Data',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'The Journal of Academic Science',
            'tautan_jurnal' => 'http://thejoas.com/index.php/thejoas/article/view/176/197',
            'tanggal_terbit' => '2024-12-20',
            'penerbit' => null,
            'issn' => '2997-7258',
            'tautan_eksternal' => 'http://thejoas.com/index.php/thejoas/article/view/176/197',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub3->id,
            'dosen_id' => Dosen::where('nidn', '0413107002')->first()->id,
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub3->id,
            'nama' => 'BADIE UDDIN',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub3->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub3->id,
            'nama' => 'ASEP SAEFUL MILLAH',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub3->id,
            'nama' => 'RIZQIYATUL KHOIRIYAH',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub4 = Publikasi::firstOrCreate(['judul' => 'Smart HVAC: Monitoring dan Kontrol Berbasis IoT dengan Fuzzy Logic Mamdani'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'JuSTISe: Journal Data Science, Technology, Informatics and Security',
            'tautan_jurnal' => 'https://e-journal.ukri.ac.id/justise/article/view/4309',
            'tanggal_terbit' => '2024-12-26',
            'penerbit' => null,
            'issn' => '3163-4230',
            'tautan_eksternal' => 'https://e-journal.ukri.ac.id/justise/article/view/4309',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub4->id,
            'nama' => 'ESA APRILLAH',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub4->id,
            'dosen_id' => Dosen::where('nidn', '0303116708')->first()->id,
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub4->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub5 = Publikasi::firstOrCreate(['judul' => 'Ethical Considerations in Algorithmic Decision-making: Towards Fair and Transparent AI Systems'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Riwayat: Educatioanl Journal of History and Humanities',
            'tautan_jurnal' => 'https://jurnal.usk.ac.id/riwayat/article/view/44112',
            'tanggal_terbit' => '2025-01-30',
            'penerbit' => null,
            'issn' => '2775-5037',
            'tautan_eksternal' => 'https://jurnal.usk.ac.id/riwayat/article/view/44112',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub5->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);

        $pub6 = Publikasi::firstOrCreate(['judul' => 'Integration of Virtual Reality in STEM to Enhance Problem Solving Skills in Science Learning in the 21st Century: A Review'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Penelitian Pendidikan IPA',
            'tautan_jurnal' => 'https://jppipa.unram.ac.id/index.php/jppipa/article/view/10483',
            'tanggal_terbit' => '2025-03-25',
            'penerbit' => null,
            'issn' => '2407-795X',
            'tautan_eksternal' => 'https://jppipa.unram.ac.id/index.php/jppipa/article/view/10483',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub6->id,
            'nama' => 'AAH AHMAD ALMULQU',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub6->id,
            'nama' => 'RUTH RIZE PAAS MEGAHATI.S',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub6->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub7 = Publikasi::firstOrCreate(['judul' => 'Pemanfaatan Google Form dan Koneksi ke Excel dalam Pengajaran di SMA MA’ARIF Bandung'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Pengabdian kepada Masyarakat Kebangsaan',
            'tautan_jurnal' => 'https://e-journal.ukri.ac.id/jpkm-kebangsaan/article/view/4037',
            'tanggal_terbit' => '2025-04-24',
            'penerbit' => null,
            'issn' => '3090-6881',
            'tautan_eksternal' => 'https://e-journal.ukri.ac.id/jpkm-kebangsaan/article/view/4037',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub7->id,
            'dosen_id' => Dosen::where('nidn', '0413107002')->first()->id,
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub7->id,
            'dosen_id' => Dosen::where('nidn', '0407048401')->first()->id,
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub7->id,
            'dosen_id' => Dosen::where('nidn', '0303116708')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub7->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub7->id,
            'dosen_id' => Dosen::where('nidn', '0413097602')->first()->id,
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub7->id,
            'dosen_id' => Dosen::where('nidn', '0418036802')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub7->id,
            'dosen_id' => Dosen::where('nidn', '0411117602')->first()->id,
        ], [
            'urutan' => 7,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub8 = Publikasi::firstOrCreate(['judul' => 'Analisis Kinerja Fungsional pada Aplikasi Mobile JKN Melalui Pengujian Black Box Testing'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal on Pustaka Cendekia Informatika',
            'tautan_jurnal' => 'https://pcinformatika.org/index.php/pcif/article/view/47',
            'tanggal_terbit' => '2025-06-16',
            'penerbit' => null,
            'issn' => '2987-1891',
            'tautan_eksternal' => 'https://pcinformatika.org/index.php/pcif/article/view/47',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub8->id,
            'nama' => 'RATNA SANTIKA',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub8->id,
            'nama' => 'SANTI FEBRIANTI',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub8->id,
            'nama' => 'SAM\'UN HAKEKI MUCHLIS',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub8->id,
            'nama' => 'UDAN',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub8->id,
            'nama' => 'YUSUP SUPRIYANTO',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub8->id,
            'nama' => 'ZIDAN FAJAR ABDILLAH',
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub8->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 7,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub9 = Publikasi::firstOrCreate(['judul' => 'Penerapan Black Box Testing Menggunakan Teknik Equivalence Partitioning pada Sistem Informasi Akademik (SIAKAD) Kampus UKRI'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal on Pustaka Cendekia Informatika',
            'tautan_jurnal' => 'https://pcinformatika.org/index.php/pcif/article/view/49',
            'tanggal_terbit' => '2025-06-19',
            'penerbit' => null,
            'issn' => '2987-1891',
            'tautan_eksternal' => 'https://pcinformatika.org/index.php/pcif/article/view/49',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub9->id,
            'nama' => 'DESTYAN FADILAH AKBAR',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub9->id,
            'nama' => 'LEO RESTU SEPTIAN RHAKA NUGRAHA',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub9->id,
            'nama' => 'PRAYOGI NUR ALDI',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub9->id,
            'nama' => 'ALIF PRAMBUDI FADILLAH AKBAR',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub9->id,
            'nama' => 'FARID ZIA ULHAQ',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub9->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub10 = Publikasi::firstOrCreate(['judul' => 'Prototipe Sistem Penampungan Air Otomatis Berbasis IoT dengan Logika Fuzzy untuk Pertanian Cerdas'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'JuSTISe: Journal Data Science, Technology, Informatics and Security',
            'tautan_jurnal' => 'https://e-journal.ukri.ac.id/justise/article/view/4310',
            'tanggal_terbit' => '2025-06-26',
            'penerbit' => null,
            'issn' => '3163-4230',
            'tautan_eksternal' => 'https://e-journal.ukri.ac.id/justise/article/view/4310',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub10->id,
            'nama' => 'ADHIN LUTHFI INDRAWAN',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub10->id,
            'dosen_id' => Dosen::where('nidn', '0418036802')->first()->id,
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub10->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub11 = Publikasi::firstOrCreate(['judul' => 'Functional Testing of The Dana E-Wallet Transaction Features Using Black Box Testing'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Info Sains : Informatika dan Sains',
            'tautan_jurnal' => 'https://ejournal.seaninstitute.or.id/index.php/InfoSains/article/view/6802',
            'tanggal_terbit' => '2025-06-26',
            'penerbit' => null,
            'issn' => '2797-7889',
            'tautan_eksternal' => 'https://ejournal.seaninstitute.or.id/index.php/InfoSains/article/view/6802',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub11->id,
            'nama' => 'NENG EVA MASLIAH',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub11->id,
            'nama' => 'RIFA VIDA ZAHRANI',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub11->id,
            'nama' => 'KHAIRUNNISA DWI WAHYUNINGTYAS',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub11->id,
            'nama' => 'TIARA PUTRI LATIFANI DIANATA',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub11->id,
            'nama' => 'MUHAMAD ADITYA SUHENDAR',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub11->id,
            'nama' => 'MUHAMAD NABIL ARRAFI',
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub11->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 7,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub12 = Publikasi::firstOrCreate(['judul' => 'Pengujian Black Box pada Fitur Pendaftaran Online RSUD Kota Bandung dengan Teknik Boundary Value Analysis'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal on Pustaka Cendekia Informatika',
            'tautan_jurnal' => 'https://pcinformatika.org/index.php/pcif/article/view/56',
            'tanggal_terbit' => '2025-06-29',
            'penerbit' => null,
            'issn' => '2987-1891',
            'tautan_eksternal' => 'https://pcinformatika.org/index.php/pcif/article/view/56',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub12->id,
            'nama' => 'CAHYA PURNAMA AJI',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub12->id,
            'nama' => 'AZHAR HAVIS',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub12->id,
            'nama' => 'RIDWAN',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub12->id,
            'nama' => 'PIQRI NABILA MULIA',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub12->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub13 = Publikasi::firstOrCreate(['judul' => 'Penerapan Black Box Testing pada Website Cobalt.tools untuk Pengujian Fungsionalitas Pengunduhan Media'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal on Pustaka Cendekia Informatika',
            'tautan_jurnal' => 'https://pcinformatika.org/index.php/pcif/article/view/55',
            'tanggal_terbit' => '2025-06-29',
            'penerbit' => null,
            'issn' => '2987-1891',
            'tautan_eksternal' => 'https://pcinformatika.org/index.php/pcif/article/view/55',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub13->id,
            'nama' => 'M. SUNANDI',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub13->id,
            'nama' => 'DEVAN ZULFANGGA',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub13->id,
            'nama' => 'DEDE ARDIANSAH',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub13->id,
            'nama' => 'MUHAMMAD RASYID SHIDDIQ',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub13->id,
            'nama' => 'SITI RAHMAH',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub13->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub14 = Publikasi::firstOrCreate(['judul' => 'Pengujian Black Box Testing Pada Fitur Permohonan Informasi Publik Melalui Website Pemerintah Jawa Barat: Penelitian'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Pengabdian Masyarakat dan Riset Pendidikan',
            'tautan_jurnal' => 'https://jerkin.org/index.php/jerkin/article/view/1520',
            'tanggal_terbit' => '2025-06-30',
            'penerbit' => null,
            'issn' => '2961-9890',
            'tautan_eksternal' => 'https://jerkin.org/index.php/jerkin/article/view/1520',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub14->id,
            'nama' => 'ARYA SULTANSYAH',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub14->id,
            'nama' => 'ASTRI SRI RAHAYU',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub14->id,
            'nama' => 'IQBAL YUDIANA',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub14->id,
            'nama' => 'PADJRIN FAUZI',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub14->id,
            'nama' => 'ELSA NUR ARIPIN',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub14->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub15 = Publikasi::firstOrCreate(['judul' => 'Pengujian Aplikasi Mobile Gopay Menggunakan Equivalen Partitioning Metode BlackBox Testing: Penelitian'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Pengabdian Masyarakat dan Riset Pendidikan',
            'tautan_jurnal' => 'https://jerkin.org/index.php/jerkin/article/view/1518',
            'tanggal_terbit' => '2025-06-30',
            'penerbit' => null,
            'issn' => '2961-9890',
            'tautan_eksternal' => 'https://jerkin.org/index.php/jerkin/article/view/1518',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub15->id,
            'nama' => 'AL AFIF ABDURRAHMAN',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub15->id,
            'nama' => 'ARYA ABDUL MUGHNI',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub15->id,
            'nama' => 'AIDA SUCIA',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub15->id,
            'nama' => 'MUHAMMAD GHOZALI NUR HIDAYATULLAH',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub15->id,
            'nama' => 'MUHAMMAD RIDHO',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub15->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub16 = Publikasi::firstOrCreate(['judul' => 'Pengujian Black Box Shopee PayLater dengan Boundary Value, Equivalence Partitioning, dan Use Case: Penelitian'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal on Pustaka Cendekia Informatika',
            'tautan_jurnal' => 'https://pcinformatika.org/index.php/pcif/article/view/64',
            'tanggal_terbit' => '2025-07-09',
            'penerbit' => null,
            'issn' => '2987-1891',
            'tautan_eksternal' => 'https://pcinformatika.org/index.php/pcif/article/view/64',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub16->id,
            'nama' => 'AJI NATA SOBARI',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub16->id,
            'nama' => 'ARYA NUGRAHA',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub16->id,
            'nama' => 'HAEKAL ABDULAH ALI AKBAR FAJAR RHAMADHAN',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub16->id,
            'nama' => 'MUHAMMAD LUKMAN ABDURAHMAN',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub16->id,
            'nama' => 'MUHAMMAD SUPYAN',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub16->id,
            'nama' => 'TRISNA PRAWIJAYA',
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub16->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 7,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub17 = Publikasi::firstOrCreate(['judul' => 'Pengujian Aplikasi KAI Access Menggunakan Black Box Testing Boundary Value Analysis (BVA)'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal on Pustaka Cendekia Informatika',
            'tautan_jurnal' => 'https://pcinformatika.org/index.php/pcif/article/view/48',
            'tanggal_terbit' => '2025-07-09',
            'penerbit' => null,
            'issn' => '2987-1891',
            'tautan_eksternal' => 'https://pcinformatika.org/index.php/pcif/article/view/48',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub17->id,
            'nama' => 'MIFTAH NUR ROHMAN',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub17->id,
            'nama' => 'RAHMAN FAUZA',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub17->id,
            'nama' => 'DENITA ALHAMDINA PUTRI ARISANDI',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub17->id,
            'nama' => 'NABILA DESIANA',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub17->id,
            'nama' => 'ALIYA TAZKIYA FAJRIYATI',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub17->id,
            'nama' => 'CHRIS HENDRY CHOONG',
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub17->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 7,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub18 = Publikasi::firstOrCreate(['judul' => 'Analisis Kerentanan Web Menggunakan ZAP oleh Checkmarx pada Situs Kuliah Daring LMS Universitas Kebangsaan Republik Indonesia: Penelitian'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal on Pustaka Cendekia Informatika',
            'tautan_jurnal' => 'https://pcinformatika.org/index.php/pcif/article/view/63',
            'tanggal_terbit' => '2025-07-09',
            'penerbit' => null,
            'issn' => '2987-1891',
            'tautan_eksternal' => 'https://pcinformatika.org/index.php/pcif/article/view/63',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub18->id,
            'nama' => 'MUGHNI AL MUZAKI',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub18->id,
            'nama' => 'REKSI ZENDER PERDIAN',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub18->id,
            'nama' => 'ROHMAN FAJAR MUHAMAD',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub18->id,
            'nama' => 'SARIPAH',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub18->id,
            'nama' => 'SYIFA KHOFIFAH',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub18->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub19 = Publikasi::firstOrCreate(['judul' => 'Testing Website Game Point Blank Menggunakan Metode Blackbox: Penelitian'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Pengabdian Masyarakat dan Riset Pendidikan',
            'tautan_jurnal' => 'https://jerkin.org/index.php/jerkin/article/view/1665',
            'tanggal_terbit' => '2025-07-10',
            'penerbit' => null,
            'issn' => '2961-9890',
            'tautan_eksternal' => 'https://jerkin.org/index.php/jerkin/article/view/1665',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub19->id,
            'nama' => 'ASGAR MAULANA',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub19->id,
            'nama' => 'EKI MUHAMMAD HERIS',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub19->id,
            'nama' => 'LAKSAMANA RAIHAN NUSABAGJA',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub19->id,
            'nama' => 'RIDWAN SAOBANDRI',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub19->id,
            'nama' => 'RIZQIA NOERLIDHA RAIHANIE',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub19->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub20 = Publikasi::firstOrCreate(['judul' => 'Pengujian Blackbox Testing terhadap Simbs: Website Travel Booking: Penelitian'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Pengabdian Masyarakat dan Riset Pendidikan',
            'tautan_jurnal' => 'https://jerkin.org/index.php/jerkin/article/view/1690',
            'tanggal_terbit' => '2025-07-10',
            'penerbit' => null,
            'issn' => '2961-9890',
            'tautan_eksternal' => 'https://jerkin.org/index.php/jerkin/article/view/1690',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub20->id,
            'nama' => 'DANTIC ROSDIANTI',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub20->id,
            'nama' => 'RAAFI SYARAHIL AZHAR',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub20->id,
            'nama' => 'RAHMAT HIDAYAT',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub20->id,
            'nama' => 'RAHMAT HIDAYAT',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub20->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub21 = Publikasi::firstOrCreate(['judul' => 'Analisis Kerentanan Web Menggunakan ZAP oleh Checkmarx pada Website FIKSI (Fakultas Ilmu Komputer dan Sistem Informasi) Universitas Kebangsaan Republik Indonesia'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal on Pustaka Cendekia Informatika',
            'tautan_jurnal' => 'https://pcinformatika.org/index.php/pcif/article/view/68',
            'tanggal_terbit' => '2025-07-10',
            'penerbit' => null,
            'issn' => '2987-1891',
            'tautan_eksternal' => 'https://pcinformatika.org/index.php/pcif/article/view/68',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub21->id,
            'nama' => 'M. ABIE RAFDI FAUZY',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub21->id,
            'nama' => 'RESTU RAHMAT FAJRI',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub21->id,
            'nama' => 'RIYAN HIDAYAT',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub21->id,
            'nama' => 'SALSABILA ROSNIE',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub21->id,
            'nama' => 'THOMAS ALDI FIQRI',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub21->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub22 = Publikasi::firstOrCreate(['judul' => 'PENGUJIAN BLACK BOX PADA FITUR PEMINJAMAN BUKU DI APLIKASI IPUSNAS'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Teknologi Informasi dan Komunikasi',
            'tautan_jurnal' => 'https://jurnal.unnur.ac.id/index.php/jurnalfiki/article/view/999',
            'tanggal_terbit' => '2025-08-08',
            'penerbit' => null,
            'issn' => '2656-7458',
            'tautan_eksternal' => 'https://jurnal.unnur.ac.id/index.php/jurnalfiki/article/view/999',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub22->id,
            'nama' => 'ARIEL ABDURROZAK',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub22->id,
            'nama' => 'PUTRI NUR HASANAH',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub22->id,
            'nama' => 'ABDUL AZIZ NURAHMAT',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub22->id,
            'nama' => 'MUHAMMAD HAFIZHA RAMADHANI',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub22->id,
            'nama' => 'MIFTAH RIZKIA ALDIRA',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub22->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub23 = Publikasi::firstOrCreate(['judul' => 'KKN Sebagai Upaya Pengembangan Potensi Desa: Optimalisasi UMKM, Pendidikan, dan Lingkungan di Desa Cimenyan'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Pengabdian Masyarakat dan Riset Pendidikan',
            'tautan_jurnal' => 'https://jerkin.org/index.php/jerkin/article/view/2310',
            'tanggal_terbit' => '2025-08-18',
            'penerbit' => null,
            'issn' => '2961-9890',
            'tautan_eksternal' => 'https://jerkin.org/index.php/jerkin/article/view/2310',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'SANDI',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'IDAM NURCAHYA MUHARAM',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'ADHINDA ADELIANA PUTRI',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'HAQIKY HAKIM',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'ANANDA PRAMUDITHA',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'SITI SALWA AZ\'ZAHRA',
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'ALYA MUSTIKA',
        ], [
            'urutan' => 7,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'ALDI TAMANGUNDE',
        ], [
            'urutan' => 8,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'MUHAMMAD ARDHO SURYA LEKSANA',
        ], [
            'urutan' => 9,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'IHSAN ALFARIZI',
        ], [
            'urutan' => 10,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'SINDI MEILANI SINDUANO',
        ], [
            'urutan' => 11,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'nama' => 'MUHAMMAD GHOZALI NUR HIDAYATULLAH',
        ], [
            'urutan' => 12,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub23->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 13,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub24 = Publikasi::firstOrCreate(['judul' => 'PEMANFAATAN SISTEM CERDAS IRIGASI SEBAGAI SOLUSI MANAJEMEN IRIGASI DI DESA SUKAJAYA KECAMATAN MALANGBONG KABUPATEN GARUT'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional',
            'jenis' => 'Jurnal Nasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Jurnal Pengabdian kepada Masyarakat Kebangsaan',
            'tautan_jurnal' => 'https://e-journal.ukri.ac.id/jpkm-kebangsaan/article/view/4352',
            'tanggal_terbit' => '2025-10-22',
            'penerbit' => null,
            'issn' => '3090-6881',
            'tautan_eksternal' => 'https://e-journal.ukri.ac.id/jpkm-kebangsaan/article/view/4352',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub24->id,
            'dosen_id' => Dosen::where('nidn', '0418036802')->first()->id,
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub24->id,
            'dosen_id' => Dosen::where('nidn', '0413097602')->first()->id,
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub24->id,
            'dosen_id' => Dosen::where('nidn', '0303116708')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub24->id,
            'dosen_id' => Dosen::where('nidn', '0407048401')->first()->id,
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub24->id,
            'dosen_id' => Dosen::where('nidn', '0413107002')->first()->id,
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub24->id,
            'dosen_id' => Dosen::where('nidn', '0411117602')->first()->id,
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub24->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 7,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub24->id,
            'nama' => 'SUHARYANTO',
        ], [
            'urutan' => 8,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub25 = Publikasi::firstOrCreate(['judul' => 'A Systematic Literature Review on Intelligent Optimization for Virtual Machine Allocation in Cloud Data Centers'], [
            'kategori_kegiatan' => 'Hasil penelitian atau hasil pemikiran yang Dipresentasikan secara oral dan dimuat dalam prosiding yang dipublikasikan (ber ISSN/ISBN): Internasional',
            'jenis' => 'Prosiding Internasional',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'The 18th International Conference on Computer Science and Information Technology (ICCSIT) 2025',
            'tautan_jurnal' => 'https://dl.acm.org/doi/10.1145/3783862.3783871',
            'tanggal_terbit' => '2025-10-27',
            'penerbit' => null,
            'issn' => '979-8-4007-1858-8',
            'tautan_eksternal' => 'https://dl.acm.org/doi/10.1145/3783862.3783871',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub25->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub25->id,
            'nama' => 'ANDI WAHJU RAHARDJO EMANUEL',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub25->id,
            'nama' => 'SUYOTO',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub26 = Publikasi::firstOrCreate(['judul' => 'Advancements in Machine Learning Algorithms for Big Data Analytics'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal internasional bereputasi',
            'jenis' => 'Jurnal Internasional Bereputasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Journal of Hunan University Natural Sciences',
            'tautan_jurnal' => 'https://www.jonuns.com/index.php/journal/article/view/1875',
            'tanggal_terbit' => '2026-02-27',
            'penerbit' => null,
            'issn' => '1674-2974',
            'tautan_eksternal' => 'https://www.jonuns.com/index.php/journal/article/view/1875',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub26->id,
            'nama' => 'JAROT BUDIASTO',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub26->id,
            'nama' => 'FARIDA ARINIE SOELISTIANTO',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub26->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub26->id,
            'nama' => 'ABDURROHMAN',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub26->id,
            'nama' => 'Loso Judijanto',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        $pub27 = Publikasi::firstOrCreate(['judul' => 'AI-Driven Marketing Strategies: Assessing the Impact of Artificial Intelligence in Transforming Sustainable Marketing Practices for MSMEs a Qualitative Study'], [
            'kategori_kegiatan' => 'Hasil penelitian/pemikiran yang dipublikasikan dalam bentuk jurnal nasional terakreditasi Kemenristekdikti',
            'jenis' => 'Jurnal Nasional Terakreditasi',
            'kategori_capaian' => 'Publikasi',
            'nama_jurnal' => 'Fundamental and Applied Management Journal',
            'tautan_jurnal' => 'https://journal.globresco.com/index.php/FAMJ/article/view/943',
            'tanggal_terbit' => '2026-07-02',
            'penerbit' => null,
            'issn' => '2988-6341',
            'tautan_eksternal' => 'https://journal.globresco.com/index.php/FAMJ/article/view/943',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Jurnal)',
        ]);

        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub27->id,
            'dosen_id' => Dosen::where('nama', 'CANDRA AENI')->first()->id,
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub27->id,
            'nama' => 'Loso Judijanto',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub27->id,
            'dosen_id' => Dosen::where('nama', 'ENCEP SOPANDI')->first()->id,
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub27->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub27->id,
            'dosen_id' => Dosen::where('nama', 'DEWI MAHARANI')->first()->id,
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

        // ==================== SHEET: Book ====================
        $pub28 = Publikasi::firstOrCreate(['judul' => 'Cybersecurity'], [
            'kategori_kegiatan' => 'Hasil penelitian/hasil pemikiran dalam buku yang dipublikasikan dan berisi berbagai tulisan dari berbagai penulis (book chapter) nasional',
            'jenis' => 'Book Chapter Nasional',
            'kategori_capaian' => 'Buku',
            'nama_jurnal' => null,
            'tautan_jurnal' => 'https://www.nulishemat.com/product/cybersecurity/',
            'tanggal_terbit' => '2025-05-26',
            'penerbit' => 'CV Nulis Hemat Indonesia',
            'issn' => '978-623-10-9695-1',
            'tautan_eksternal' => 'https://www.nulishemat.com/product/cybersecurity/',
            'keterangan' => 'Diimpor dari Data_Penelitian.xlsx (Book)',
        ]);

        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'Mohammad Bayu Anggara',
        ], [
            'urutan' => 1,
            'peran' => 'Penulis',
            'is_corresponding' => true,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'DADAN RAHMAT',
        ], [
            'urutan' => 2,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'Amril Mutoi Siregar',
        ], [
            'urutan' => 3,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'ACHMAD SYAFAAT',
        ], [
            'urutan' => 4,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'Muhammad Rafi Juliansyah',
        ], [
            'urutan' => 5,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'Asep Widiana',
        ], [
            'urutan' => 6,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'dosen_id' => Dosen::where('nidn', '0418036802')->first()->id,
        ], [
            'urutan' => 7,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'TARMIN ABDULGHANI',
        ], [
            'urutan' => 8,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'Yaya Suharya',
        ], [
            'urutan' => 9,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisLain::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'nama' => 'Risnandar',
        ], [
            'urutan' => 10,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);
        PublikasiPenulisDosen::firstOrCreate([
            'publikasi_id' => $pub28->id,
            'dosen_id' => Dosen::where('nama', 'SUBHANJAYA ANGGA ATMAJA')->first()->id,
        ], [
            'urutan' => 11,
            'peran' => 'Penulis',
            'is_corresponding' => false,
        ]);

    }

    /**
     * Lengkapi data Dosen (dengan NIDN sesuai Master Data API UKRI - dosen:read)
     * untuk dosen yang namanya cocok persis dengan penulis yang sebelumnya
     * salah masuk sebagai Kolaborator Eksternal (publikasi_penulis_lain) di
     * import Data_Penelitian.xlsx. Dijalankan sebelum seluruh publikasi
     * diimpor supaya lookup `Dosen::where('nama', ...)` di bawah berhasil.
     */
    private function seedDosenFromMasterData(): void
    {
        $ptUkri = MasterPerguruanTinggi::firstOrCreate(
            ['kode_pt' => '041065'],
            ['nama_pt' => 'Universitas Kebangsaan Republik Indonesia']
        );

        // NIDN persis dari Master Data API UKRI (dosen:read). Kalau dosen
        // dengan NIDN ini SUDAH ADA (mis. hasil `ukri:sync` sebelumnya),
        // firstOrCreate tidak akan menimpa nama aslinya - 'nama' di bawah
        // hanya dipakai kalau baris dosen ini belum ada sama sekali.
        $dosenMasterTambahan = [
            ['nidn' => '0413107002', 'nama' => 'IIM ABDURROHIM'],
            ['nidn' => '0413097602', 'nama' => 'IRMAN HARIMAN'],
            ['nidn' => '0418036802', 'nama' => 'OSCAR HADIKARYANA'],
            ['nidn' => '0411117602', 'nama' => 'DENI SUPRIHADI'],
            ['nidn' => '0303116708', 'nama' => 'YASRI'],
            ['nidn' => '0407048401', 'nama' => 'ALKAUTSAR RAHMAN'],
            ['nidn' => '0421057401', 'nama' => 'ERWIN TEGUH ARUJISAPUTRA'],
        ];

        foreach ($dosenMasterTambahan as $d) {
            Dosen::firstOrCreate(['nidn' => $d['nidn']], [
                'master_perguruan_tinggi_id' => $ptUkri->id,
                'nama' => $d['nama'],
            ]);
        }
    }
}
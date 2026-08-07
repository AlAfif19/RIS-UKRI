<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MasterPerguruanTinggi;
use App\Models\Publikasi;
use App\Models\PublikasiPenulisDosen;
use App\Models\PublikasiPenulisLain;
use App\Models\PublikasiPenulisMahasiswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Rapikan data penulis publikasi setelah integrasi Master Data API UKRI:
 *
 *  1. "Kolaborator Eksternal" (publikasi_penulis_lain) yang namanya ternyata
 *     persis sama dengan dosen/mahasiswa UKRI hasil sinkronisasi API
 *     dipindahkan jadi Penulis Dosen / Penulis Mahasiswa yang sesungguhnya.
 *  2. Dosen yang sebelumnya diinput manual (nidn kosong / placeholder TEMP_,
 *     ukri_id null) tapi namanya cocok persis dengan dosen UKRI hasil sync
 *     digabung ke record UKRI-nya: semua kepenulisan dipindah, lalu record
 *     duplikat dihapus.
 *  3. (opsional, --delete-demo) Menghapus 5 publikasi contoh + dosen/
 *     mahasiswa/perguruan tinggi dummy bawaan `PublikasiMasterSeeder` yang
 *     dipakai saat development awal, kalau memang belum "diadopsi" jadi data
 *     asli oleh sinkronisasi (ukri_id masih null & sudah tidak dipakai di
 *     publikasi/dosen/mahasiswa manapun).
 *
 * HANYA mencocokkan nama secara PERSIS (setelah dirapikan spasi & huruf
 * besar/kecil) - sengaja tidak fuzzy, supaya tidak salah menggabungkan dua
 * orang berbeda. Yang tidak cocok akan dilaporkan supaya dicek manual.
 *
 * Defaultnya dry-run (tidak mengubah apa pun). Jalankan dengan --apply untuk
 * benar-benar menyimpan perubahan.
 */
class ReconcileAuthorsWithMasterData extends Command
{
    protected $signature = 'ukri:reconcile-authors
                            {--apply : Simpan perubahan. Tanpa opsi ini perintah hanya menampilkan laporan (dry-run).}
                            {--delete-demo : Hapus juga publikasi contoh & dosen/mahasiswa/perguruan tinggi dummy bawaan PublikasiMasterSeeder.}';

    protected $description = 'Cocokkan ulang penulis publikasi (kolaborator eksternal & dosen manual) dengan data hasil sinkronisasi Master Data API UKRI.';

    /** Judul 5 publikasi contoh dari database/seeders/PublikasiMasterSeeder.php */
    private const DEMO_PUBLIKASI_TITLES = [
        'Sistem Informasi Geografis untuk Pemetaan Lokasi Bencana Alam',
        'Deep Learning for Automated Diagnosis of Skin Lesions',
        'Buku Ajar: Pemrograman Web Dinamis Menggunakan Laravel 11',
        'Pemberdayaan UMKM Melalui Digital Marketing di Jawa Barat',
        'Penerjemahan Buku: Artificial Intelligence Modern Approach (4th Edition)',
    ];

    /** NIDN dosen dummy dari PublikasiMasterSeeder (hanya dihapus kalau ukri_id masih null & tidak dipakai lagi). */
    private const DEMO_DOSEN_NIDN = [
        '0454768669130262', // SUBHANJAYA ANGGA ATMAJA (nama sama juga dipakai di data asli - lihat catatan di handle())
        '6955745646230092', // CANDRA AENI
        '6338746647130093', // ENCEP SOPANDI
        '8736763665300082', // DEWI MAHARANI
    ];

    /** NIM mahasiswa dummy dari PublikasiMasterSeeder. */
    private const DEMO_MAHASISWA_NIM = [
        '20221310001', // Ahmad Rizky Pratama
        '20221310002', // Siti Nurhaliza
        '20221310003', // Budi Santoso
    ];

    /**
     * kode_pt Perguruan Tinggi dummy dari PublikasiMasterSeeder (hanya dihapus
     * kalau sudah tidak dipakai dosen/mahasiswa manapun) - lihat
     * deleteOrphanDemoMasterData(). PT pertama seeder (kode 041065, UKRI
     * sendiri) SENGAJA tidak masuk daftar ini karena itu institusi asli.
     */
    private const DEMO_PT_KODE = [
        '071020', // Universitas PGRI Ronggolawe
        '041012', // Universitas Nurtanio Bandung
        '111005', // Universitas Muhammadiyah Banjarmasin
    ];

    private bool $apply = false;

    public function handle(): int
    {
        $this->apply = (bool) $this->option('apply');

        if (! $this->apply) {
            $this->components->warn('Mode dry-run - tidak ada perubahan yang disimpan. Tambahkan --apply untuk benar-benar mengeksekusi.');
        }

        DB::beginTransaction();

        try {
            $this->reclassifyPenulisLain();
            $this->mergeDuplicateDosen();

            if ($this->option('delete-demo')) {
                $this->deleteDemoPublikasi();
                $this->deleteOrphanDemoMasterData();
            }

            if ($this->apply) {
                DB::commit();
                $this->components->info('Perubahan disimpan.');
            } else {
                DB::rollBack();
                $this->components->info('Dry-run selesai, tidak ada yang disimpan. Jalankan ulang dengan --apply kalau hasilnya sudah sesuai.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /* ------------------------------------------------------------------ */

    private function normalize(string $name): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/', ' ', $name)));
    }

    /**
     * Langkah 1: pindahkan publikasi_penulis_lain yang namanya persis sama
     * dengan dosen/mahasiswa UKRI (ukri_id terisi, hasil `ukri:sync`) menjadi
     * baris penulis_dosen / penulis_mahasiswa yang sesungguhnya.
     */
    private function reclassifyPenulisLain(): void
    {
        $dosenByName = Dosen::whereNotNull('ukri_id')->get(['id', 'nama'])
            ->keyBy(fn ($d) => $this->normalize($d->nama));

        $mahasiswaByName = Mahasiswa::whereNotNull('ukri_id')->get(['id', 'nama'])
            ->keyBy(fn ($m) => $this->normalize($m->nama));

        $moved = [];
        $skipped = 0;

        PublikasiPenulisLain::chunkById(200, function ($rows) use (&$moved, &$skipped, $dosenByName, $mahasiswaByName) {
            foreach ($rows as $lain) {
                $key = $this->normalize($lain->nama);

                if ($dosen = $dosenByName->get($key)) {
                    $moved[] = "[dosen]      {$lain->nama}  ->  publikasi_id={$lain->publikasi_id}";

                    if ($this->apply) {
                        PublikasiPenulisDosen::create([
                            'publikasi_id' => $lain->publikasi_id,
                            'dosen_id' => $dosen->id,
                            'urutan' => $lain->urutan,
                            'afiliasi' => $lain->afiliasi,
                            'peran' => $lain->peran,
                            'is_corresponding' => $lain->is_corresponding,
                        ]);
                        $lain->delete();
                    }

                    continue;
                }

                if ($mhs = $mahasiswaByName->get($key)) {
                    $moved[] = "[mahasiswa]  {$lain->nama}  ->  publikasi_id={$lain->publikasi_id}";

                    if ($this->apply) {
                        PublikasiPenulisMahasiswa::create([
                            'publikasi_id' => $lain->publikasi_id,
                            'mahasiswa_id' => $mhs->id,
                            'urutan' => $lain->urutan,
                            'afiliasi' => $lain->afiliasi,
                            'peran' => $lain->peran,
                            'is_corresponding' => $lain->is_corresponding,
                        ]);
                        $lain->delete();
                    }

                    continue;
                }

                $skipped++;
            }
        });

        $this->components->twoColumnDetail('Kolaborator eksternal dipindahkan ke Dosen/Mahasiswa', (string) count($moved));
        foreach ($moved as $line) {
            $this->line("  - {$line}");
        }
        $this->components->twoColumnDetail('Kolaborator eksternal tetap (tidak cocok data UKRI)', (string) $skipped);
    }

    /**
     * Langkah 2: gabungkan Dosen yang diinput manual (ukri_id null - baik
     * lewat NIDN kosong/TEMP_ maupun seed lama) dengan Dosen hasil sync yang
     * namanya persis sama. Semua kepenulisan dipindah ke record UKRI-nya,
     * lalu duplikatnya dihapus.
     */
    private function mergeDuplicateDosen(): void
    {
        $dosenByName = Dosen::whereNotNull('ukri_id')->get(['id', 'nama'])
            ->keyBy(fn ($d) => $this->normalize($d->nama));

        $manualDosen = Dosen::whereNull('ukri_id')->get(['id', 'nama', 'nidn']);

        $merged = [];
        $kept = 0;

        foreach ($manualDosen as $dosen) {
            $target = $dosenByName->get($this->normalize($dosen->nama));

            if (! $target || $target->id === $dosen->id) {
                $kept++;

                continue;
            }

            $merged[] = "{$dosen->nama} (nidn manual: {$dosen->nidn})  ->  dosen UKRI id={$target->id}";

            if ($this->apply) {
                PublikasiPenulisDosen::where('dosen_id', $dosen->id)->update(['dosen_id' => $target->id]);
                $dosen->delete();
            }
        }

        $this->components->twoColumnDetail('Dosen manual digabung ke data UKRI', (string) count($merged));
        foreach ($merged as $line) {
            $this->line("  - {$line}");
        }
        $this->components->twoColumnDetail('Dosen manual tetap terpisah (tidak cocok data UKRI, dianggap co-author eksternal)', (string) $kept);
    }

    /**
     * Langkah 3 (opsional): hapus 5 publikasi contoh dari
     * PublikasiMasterSeeder beserta dokumen/penulisnya (cascade lewat FK).
     */
    private function deleteDemoPublikasi(): void
    {
        $publikasi = Publikasi::whereIn('judul', self::DEMO_PUBLIKASI_TITLES)->with('dokumen')->get();

        foreach ($publikasi as $pub) {
            $this->line("  - Hapus publikasi contoh: \"{$pub->judul}\"");

            if ($this->apply) {
                foreach ($pub->dokumen as $dok) {
                    if ($dok->path_file) {
                        Storage::disk('public')->delete($dok->path_file);
                    }
                }
                $pub->delete(); // dokumen/penulis_dosen/penulis_mahasiswa/penulis_lain ikut terhapus via FK cascade
            }
        }

        $this->components->twoColumnDetail('Publikasi contoh dihapus', (string) $publikasi->count());
    }

    /**
     * Langkah 4 (opsional): hapus Dosen/Mahasiswa dummy bawaan seeder yang
     * masih ukri_id null DAN sudah tidak dipakai di publikasi manapun
     * (termasuk setelah langkah 3 menghapus publikasi contoh), lalu hapus
     * Perguruan Tinggi dummy yang jadi orphan setelahnya.
     *
     * PENTING: FK dosen/mahasiswa -> master_perguruan_tinggi pakai
     * onDelete('cascade'), jadi PT dummy HANYA dihapus setelah dipastikan
     * tidak ada dosen/mahasiswa (dummy maupun asli) yang masih menempel -
     * supaya tidak ada data asli yang ikut terhapus gara-gara cascade.
     */
    private function deleteOrphanDemoMasterData(): void
    {
        $deletedDosenIds = [];
        $deletedDosen = 0;
        foreach (Dosen::whereNull('ukri_id')->whereIn('nidn', self::DEMO_DOSEN_NIDN)->get() as $dosen) {
            $stillUsed = PublikasiPenulisDosen::where('dosen_id', $dosen->id)->exists();

            if ($stillUsed) {
                $this->line("  - Lewati dosen dummy \"{$dosen->nama}\" (nidn {$dosen->nidn}): masih dipakai di publikasi asli, tidak dihapus.");

                continue;
            }

            $this->line("  - Hapus dosen dummy: {$dosen->nama} (nidn {$dosen->nidn})");
            if ($this->apply) {
                $dosen->delete();
            }
            $deletedDosenIds[] = $dosen->id;
            $deletedDosen++;
        }

        $deletedMahasiswaIds = [];
        $deletedMahasiswa = 0;
        foreach (Mahasiswa::whereNull('ukri_id')->whereIn('nim', self::DEMO_MAHASISWA_NIM)->get() as $mhs) {
            $stillUsed = PublikasiPenulisMahasiswa::where('mahasiswa_id', $mhs->id)->exists();

            if ($stillUsed) {
                $this->line("  - Lewati mahasiswa dummy \"{$mhs->nama}\" (nim {$mhs->nim}): masih dipakai di publikasi asli, tidak dihapus.");

                continue;
            }

            $this->line("  - Hapus mahasiswa dummy: {$mhs->nama} (nim {$mhs->nim})");
            if ($this->apply) {
                $mhs->delete();
            }
            $deletedMahasiswaIds[] = $mhs->id;
            $deletedMahasiswa++;
        }

        // Langkah 5: Perguruan Tinggi dummy yang sekarang sudah orphan.
        // $deletedDosenIds/$deletedMahasiswaIds dikecualikan dari pengecekan
        // "masih dipakai" supaya dry-run tetap melaporkan hasil yang akurat
        // (di dry-run row dosen/mahasiswa-nya belum benar-benar terhapus).
        $deletedPT = 0;
        foreach (MasterPerguruanTinggi::whereIn('kode_pt', self::DEMO_PT_KODE)->get() as $pt) {
            $stillUsedDosen = Dosen::where('master_perguruan_tinggi_id', $pt->id)
                ->whereNotIn('id', $deletedDosenIds ?: [0])
                ->exists();
            $stillUsedMahasiswa = Mahasiswa::where('master_perguruan_tinggi_id', $pt->id)
                ->whereNotIn('id', $deletedMahasiswaIds ?: [0])
                ->exists();

            if ($stillUsedDosen || $stillUsedMahasiswa) {
                $this->line("  - Lewati Perguruan Tinggi dummy \"{$pt->nama_pt}\": masih ada dosen/mahasiswa yang menempel, tidak dihapus.");

                continue;
            }

            $this->line("  - Hapus Perguruan Tinggi dummy: {$pt->nama_pt}");
            if ($this->apply) {
                $pt->delete();
            }
            $deletedPT++;
        }

        $this->components->twoColumnDetail('Dosen dummy dihapus', (string) $deletedDosen);
        $this->components->twoColumnDetail('Mahasiswa dummy dihapus', (string) $deletedMahasiswa);
        $this->components->twoColumnDetail('Perguruan Tinggi dummy dihapus', (string) $deletedPT);
    }
}

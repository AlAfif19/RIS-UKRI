<?php

namespace App\Console\Commands;

use App\Models\Angkatan;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Peminatan;
use App\Models\Prodi;
use App\Services\UkriMasterDataService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Tarik data master dari Master Data API UKRI dan simpan sebagai mirror di
 * tabel lokal (ukri_fakultas, ukri_prodi, ukri_angkatan, ukri_peminatan,
 * dosen, mahasiswa).
 *
 * API sumbernya hanya mengizinkan GET dan tiap halaman dibatasi 20 baris,
 * jadi pencarian nama/typeahead di aplikasi ini jauh lebih cepat lewat tabel
 * mirror lokal daripada memanggil API langsung di setiap request. Jalankan
 * perintah ini terjadwal (lihat routes/console.php) agar data tetap segar.
 *
 * Urutan sinkronisasi penting: fakultas -> prodi -> angkatan & peminatan ->
 * dosen & mahasiswa, supaya relasi fakultas_id/prodi_id/angkatan_id lokal
 * sudah tersedia saat entitas turunannya disinkron.
 */
class SyncUkriMasterData extends Command
{
    protected $signature = 'ukri:sync {entity=all : all|fakultas|prodi|angkatan|peminatan|dosen|mahasiswa}';

    protected $description = 'Sinkronkan data master (fakultas, prodi, angkatan, peminatan, dosen, mahasiswa) dari Master Data API UKRI ke tabel lokal.';

    public function handle(UkriMasterDataService $api): int
    {
        $entity = $this->argument('entity');

        $steps = [
            'fakultas' => fn () => $this->syncFakultas($api),
            'prodi' => fn () => $this->syncProdi($api),
            'angkatan' => fn () => $this->syncAngkatan($api),
            'peminatan' => fn () => $this->syncPeminatan($api),
            'dosen' => fn () => $this->syncDosen($api),
            'mahasiswa' => fn () => $this->syncMahasiswa($api),
        ];

        if ($entity !== 'all' && ! isset($steps[$entity])) {
            $this->error("Entity '{$entity}' tidak dikenal. Pilihan: all, ".implode(', ', array_keys($steps)));

            return self::FAILURE;
        }

        foreach ($steps as $name => $step) {
            if ($entity !== 'all' && $entity !== $name) {
                continue;
            }

            try {
                $this->components->task("Sinkronisasi {$name}", $step);
            } catch (Throwable $e) {
                $this->components->error($e->getMessage());

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    private function syncFakultas(UkriMasterDataService $api): void
    {
        $now = now();

        foreach ($api->fakultas() as $row) {
            Fakultas::updateOrCreate(
                ['ukri_id' => $row['id']],
                [
                    'nama_fakultas' => $row['nama_fakultas'],
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'dekan' => $row['dekan'] ?? null,
                    'synced_at' => $now,
                ]
            );
        }
    }

    private function syncProdi(UkriMasterDataService $api): void
    {
        $now = now();
        $fakultasByName = Fakultas::pluck('id', 'nama_fakultas');

        foreach ($api->prodi() as $row) {
            Prodi::updateOrCreate(
                ['ukri_id' => $row['id']],
                [
                    'nama_prodi' => $row['nama_prodi'],
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'fakultas_id' => $fakultasByName[$row['fakultas'] ?? null] ?? null,
                    'kaprodi' => $row['kaprodi'] ?? null,
                    'synced_at' => $now,
                ]
            );
        }
    }

    private function syncAngkatan(UkriMasterDataService $api): void
    {
        $now = now();
        $prodiByName = Prodi::pluck('id', 'nama_prodi');
        $fakultasByName = Fakultas::pluck('id', 'nama_fakultas');

        foreach ($api->angkatan() as $row) {
            Angkatan::updateOrCreate(
                ['ukri_id' => $row['id']],
                [
                    'angkatan' => $row['angkatan'],
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'status' => $row['status'] ?? null,
                    'prodi_id' => $prodiByName[$row['prodi'] ?? null] ?? null,
                    'fakultas_id' => $fakultasByName[$row['fakultas'] ?? null] ?? null,
                    'synced_at' => $now,
                ]
            );
        }
    }

    private function syncPeminatan(UkriMasterDataService $api): void
    {
        $now = now();
        $prodiByName = Prodi::pluck('id', 'nama_prodi');
        $fakultasByName = Fakultas::pluck('id', 'nama_fakultas');

        foreach ($api->peminatan() as $row) {
            Peminatan::updateOrCreate(
                ['ukri_id' => $row['id']],
                [
                    'nama_peminatan' => $row['nama_peminatan'],
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'fakultas_id' => $fakultasByName[$row['fakultas'] ?? null] ?? null,
                    'prodi_id' => $prodiByName[$row['prodi'] ?? null] ?? null,
                    'synced_at' => $now,
                ]
            );
        }
    }

    private function syncDosen(UkriMasterDataService $api): void
    {
        $now = now();
        $fakultasByName = Fakultas::pluck('id', 'nama_fakultas');
        $prodiByName = Prodi::pluck('id', 'nama_prodi');

        foreach ($api->dosen() as $row) {
            if (empty($row['nidn'])) {
                continue;
            }

            Dosen::updateOrCreate(
                ['nidn' => $row['nidn']],
                [
                    'ukri_id' => $row['id'],
                    'nama' => $row['nama'],
                    'fakultas_id' => $fakultasByName[$row['fakultas'] ?? null] ?? null,
                    'prodi_id' => $prodiByName[$row['prodi'] ?? null] ?? null,
                    'synced_at' => $now,
                ]
            );
        }
    }

    private function syncMahasiswa(UkriMasterDataService $api): void
    {
        $now = now();
        $prodiByName = Prodi::pluck('id', 'nama_prodi');

        // Angkatan API tidak mengirim id mahasiswa, hanya nama. Karena tahun
        // angkatan bisa berulang di tiap prodi, kuncikan lookup-nya dengan
        // "prodi_id|angkatan" supaya tidak salah menghubungkan.
        $angkatanByProdiAndTahun = Angkatan::query()
            ->get(['id', 'prodi_id', 'angkatan'])
            ->mapWithKeys(fn ($a) => [$a->prodi_id.'|'.$a->angkatan => $a->id]);

        foreach ($api->mahasiswa() as $row) {
            if (empty($row['npm'])) {
                continue;
            }

            $prodiId = $prodiByName[$row['prodi'] ?? null] ?? null;
            $angkatanId = $angkatanByProdiAndTahun[$prodiId.'|'.($row['angkatan'] ?? null)] ?? null;

            Mahasiswa::updateOrCreate(
                // Kolom lokal masih bernama `nim` (skema lama); nilainya
                // sekarang diisi dari field `npm` milik Master Data API.
                ['nim' => $row['npm']],
                [
                    'ukri_id' => $row['id'],
                    'nama' => $row['nama'],
                    'prodi' => $row['prodi'] ?? null,
                    'prodi_id' => $prodiId,
                    'angkatan_id' => $angkatanId,
                    'synced_at' => $now,
                ]
            );
        }
    }
}

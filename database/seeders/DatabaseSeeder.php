<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // PublikasiMasterSeeder berisi data contoh/dummy (dosen, mahasiswa,
        // publikasi demo) yang dipakai saat awal development, SEBELUM
        // integrasi Master Data API UKRI. Sekarang data dosen & mahasiswa
        // asli didapat dari `php artisan ukri:sync`, jadi seeder ini sengaja
        // tidak dijalankan lagi di instalasi baru. Untuk data lama yang
        // sudah kadung dibuat sebelum integrasi ini, bersihkan lewat:
        //   php artisan ukri:reconcile-authors --delete-demo --apply
        // $this->call(PublikasiMasterSeeder::class);
    }
}

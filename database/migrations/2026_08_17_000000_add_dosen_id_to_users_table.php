<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            /**
             * Menautkan akun login (role "dosen") ke baris dosen miliknya di
             * tabel `dosen`, supaya Publikasi Karya yang tampil bisa
             * dibatasi hanya punya dosen bersangkutan (lihat
             * PublikasiController::ownedAuthorIds()).
             *
             * Diisi otomatis saat login SSO oleh
             * SsoController::carikanAtauBuatPengguna() — dicocokkan lewat
             * ukri_id, lalu NIDN, lalu email (lihat method
             * cariDosenUntukUser() di controller yang sama).
             */
            $table->foreignId('dosen_id')
                ->nullable()
                ->after('sso_id')
                ->constrained('dosen')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropConstrainedForeignId('dosen_id');

        });
    }
};

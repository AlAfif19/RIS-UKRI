<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            // ukri_id terisi kalau dosen ini disinkron dari Master Data API UKRI
            // (dosen:read). Tetap null untuk co-author eksternal yang diinput
            // manual dari perguruan tinggi lain - itu masih didukung penuh.
            $table->unsignedBigInteger('ukri_id')->nullable()->unique()->after('id');
            $table->foreignId('fakultas_id')->nullable()->after('master_perguruan_tinggi_id')->constrained('ukri_fakultas')->nullOnDelete();
            $table->foreignId('prodi_id')->nullable()->after('fakultas_id')->constrained('ukri_prodi')->nullOnDelete();
            $table->timestamp('synced_at')->nullable()->after('email');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            // Mahasiswa selalu berasal dari UKRI sendiri, jadi tabel ini
            // sepenuhnya menjadi mirror dari endpoint mahasiswa:read.
            $table->unsignedBigInteger('ukri_id')->nullable()->unique()->after('id');
            $table->foreignId('prodi_id')->nullable()->after('master_perguruan_tinggi_id')->constrained('ukri_prodi')->nullOnDelete();
            $table->foreignId('angkatan_id')->nullable()->after('prodi_id')->constrained('ukri_angkatan')->nullOnDelete();
            $table->timestamp('synced_at')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('angkatan_id');
            $table->dropConstrainedForeignId('prodi_id');
            $table->dropColumn(['ukri_id', 'synced_at']);
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prodi_id');
            $table->dropConstrainedForeignId('fakultas_id');
            $table->dropColumn(['ukri_id', 'synced_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel mirror lokal untuk Master Data API UKRI (https://api.ukri.web.id/api/v1).
 *
 * API sumbernya read-only (GET saja), jadi tabel-tabel ini juga hanya boleh
 * ditulis lewat App\Console\Commands\SyncUkriMasterData (perintah `ukri:sync`)
 * - jangan diedit manual. Kolom `ukri_id` menyimpan `id` asli dari API.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ukri_fakultas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ukri_id')->unique();
            $table->string('nama_fakultas');
            $table->boolean('is_active')->default(true);
            $table->string('dekan')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ukri_prodi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ukri_id')->unique();
            $table->string('nama_prodi');
            $table->boolean('is_active')->default(true);
            $table->foreignId('fakultas_id')->nullable()->constrained('ukri_fakultas')->nullOnDelete();
            $table->string('kaprodi')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ukri_angkatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ukri_id')->unique();
            $table->string('angkatan');
            $table->boolean('is_active')->default(true);
            $table->string('status')->nullable();
            $table->foreignId('prodi_id')->nullable()->constrained('ukri_prodi')->nullOnDelete();
            $table->foreignId('fakultas_id')->nullable()->constrained('ukri_fakultas')->nullOnDelete();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ukri_peminatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ukri_id')->unique();
            $table->string('nama_peminatan');
            $table->boolean('is_active')->default(true);
            $table->foreignId('fakultas_id')->nullable()->constrained('ukri_fakultas')->nullOnDelete();
            $table->foreignId('prodi_id')->nullable()->constrained('ukri_prodi')->nullOnDelete();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ukri_peminatan');
        Schema::dropIfExists('ukri_angkatan');
        Schema::dropIfExists('ukri_prodi');
        Schema::dropIfExists('ukri_fakultas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_aktivitas_litabmas', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->nullable();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('master_perguruan_tinggi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pt')->nullable();
            $table->string('nama_pt');
            $table->timestamps();
        });

        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_perguruan_tinggi_id')->nullable()->constrained('master_perguruan_tinggi')->onDelete('cascade');
            $table->string('nidn')->unique();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_perguruan_tinggi_id')->nullable()->constrained('master_perguruan_tinggi')->onDelete('cascade');
            $table->string('nim')->unique();
            $table->string('nama');
            $table->string('prodi')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
        Schema::dropIfExists('dosen');
        Schema::dropIfExists('master_perguruan_tinggi');
        Schema::dropIfExists('master_aktivitas_litabmas');
    }
};

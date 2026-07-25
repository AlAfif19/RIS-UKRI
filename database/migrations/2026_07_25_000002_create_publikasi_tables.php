<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publikasi', function (Blueprint $table) {
            $table->id();
            $table->text('kategori_kegiatan');
            $table->string('jenis')->nullable();
            $table->string('kategori_capaian')->nullable();
            $table->foreignId('aktivitas_litabmas_id')->nullable()->constrained('master_aktivitas_litabmas')->nullOnDelete();
            $table->string('judul');
            $table->string('nama_jurnal')->nullable();
            $table->string('tautan_jurnal')->nullable();
            $table->date('tanggal_terbit');
            $table->integer('volume')->nullable();
            $table->integer('nomor')->nullable();
            $table->string('halaman')->nullable();
            $table->string('penerbit')->nullable();
            $table->string('doi')->nullable();
            $table->string('issn')->nullable();
            $table->string('tautan_eksternal')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('publikasi_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publikasi_id')->constrained('publikasi')->onDelete('cascade');
            $table->string('nama_dokumen');
            $table->string('nama_file')->nullable();
            $table->string('path_file')->nullable();
            $table->string('jenis_file')->nullable();
            $table->date('tanggal_upload')->nullable();
            $table->string('jenis_dokumen')->nullable();
            $table->string('tautan_dokumen')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('publikasi_penulis_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publikasi_id')->constrained('publikasi')->onDelete('cascade');
            $table->foreignId('master_perguruan_tinggi_id')->nullable()->constrained('master_perguruan_tinggi')->nullOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->integer('urutan')->default(1);
            $table->string('afiliasi')->nullable();
            $table->string('peran')->default('Penulis');
            $table->boolean('is_corresponding')->default(false);
            $table->timestamps();
        });

        Schema::create('publikasi_penulis_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publikasi_id')->constrained('publikasi')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->integer('urutan')->default(1);
            $table->string('afiliasi')->nullable();
            $table->string('peran')->default('Penulis');
            $table->boolean('is_corresponding')->default(false);
            $table->timestamps();
        });

        Schema::create('publikasi_penulis_lain', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publikasi_id')->constrained('publikasi')->onDelete('cascade');
            $table->string('nama');
            $table->integer('urutan')->default(1);
            $table->string('afiliasi')->nullable();
            $table->string('peran')->default('Penulis');
            $table->boolean('is_corresponding')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publikasi_penulis_lain');
        Schema::dropIfExists('publikasi_penulis_mahasiswa');
        Schema::dropIfExists('publikasi_penulis_dosen');
        Schema::dropIfExists('publikasi_dokumen');
        Schema::dropIfExists('publikasi');
    }
};

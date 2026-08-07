<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mirror lokal dari GET /api/v1/prodi (Master Data API UKRI).
 * Diisi oleh App\Console\Commands\SyncUkriMasterData - jangan diedit manual.
 */
class Prodi extends Model
{
    protected $table = 'ukri_prodi';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id');
    }

    public function angkatan()
    {
        return $this->hasMany(Angkatan::class, 'prodi_id');
    }

    public function peminatan()
    {
        return $this->hasMany(Peminatan::class, 'prodi_id');
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'prodi_id');
    }

    public function dosen()
    {
        return $this->hasMany(Dosen::class, 'prodi_id');
    }
}

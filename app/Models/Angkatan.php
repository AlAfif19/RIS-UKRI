<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mirror lokal dari GET /api/v1/angkatan (Master Data API UKRI).
 * Diisi oleh App\Console\Commands\SyncUkriMasterData - jangan diedit manual.
 */
class Angkatan extends Model
{
    protected $table = 'ukri_angkatan';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id');
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'angkatan_id');
    }
}

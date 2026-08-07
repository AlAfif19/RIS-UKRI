<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mirror lokal dari GET /api/v1/fakultas (Master Data API UKRI).
 * Diisi oleh App\Console\Commands\SyncUkriMasterData - jangan diedit manual,
 * data master hanya bisa diubah lewat SIMANTAP.
 */
class Fakultas extends Model
{
    protected $table = 'ukri_fakultas';

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
        return $this->hasMany(Prodi::class, 'fakultas_id');
    }

    public function dosen()
    {
        return $this->hasMany(Dosen::class, 'fakultas_id');
    }
}

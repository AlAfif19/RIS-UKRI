<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mirror lokal dari GET /api/v1/peminatan (Master Data API UKRI).
 * Diisi oleh App\Console\Commands\SyncUkriMasterData - jangan diedit manual.
 */
class Peminatan extends Model
{
    protected $table = 'ukri_peminatan';

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

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}

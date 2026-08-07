<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }

    public function perguruanTinggi()
    {
        return $this->belongsTo(MasterPerguruanTinggi::class, 'master_perguruan_tinggi_id');
    }

    /**
     * fakultas_id/prodi_id hanya terisi untuk dosen UKRI sendiri yang
     * disinkron dari Master Data API (lihat App\Console\Commands\SyncUkriMasterData).
     * Co-author eksternal dari perguruan tinggi lain tetap null di sini.
     */
    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('nama', 'like', "%{$term}%")
                ->orWhere('nidn', 'like', "%{$term}%");
        });
    }
}

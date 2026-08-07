<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

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
     * Data mahasiswa sepenuhnya berasal dari UKRI sendiri, jadi tabel ini
     * di-mirror penuh dari Master Data API lewat
     * App\Console\Commands\SyncUkriMasterData (kolom `nim` menyimpan nilai
     * `npm` dari API).
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function angkatan()
    {
        return $this->belongsTo(Angkatan::class, 'angkatan_id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('nama', 'like', "%{$term}%")
                ->orWhere('nim', 'like', "%{$term}%");
        });
    }
}

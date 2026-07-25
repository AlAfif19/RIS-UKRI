<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publikasi extends Model
{
    use HasFactory;

    protected $table = 'publikasi';
    protected $guarded = [];

    public function aktivitasLitabmas()
    {
        return $this->belongsTo(MasterAktivitasLitabmas::class, 'aktivitas_litabmas_id');
    }

    public function dokumen()
    {
        return $this->hasMany(PublikasiDokumen::class, 'publikasi_id');
    }

    public function penulisDosen()
    {
        return $this->hasMany(PublikasiPenulisDosen::class, 'publikasi_id')->orderBy('urutan');
    }

    public function penulisMahasiswa()
    {
        return $this->hasMany(PublikasiPenulisMahasiswa::class, 'publikasi_id')->orderBy('urutan');
    }

    public function penulisLain()
    {
        return $this->hasMany(PublikasiPenulisLain::class, 'publikasi_id')->orderBy('urutan');
    }
}

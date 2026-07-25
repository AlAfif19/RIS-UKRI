<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublikasiPenulisDosen extends Model
{
    use HasFactory;

    protected $table = 'publikasi_penulis_dosen';
    protected $guarded = [];

    public function publikasi()
    {
        return $this->belongsTo(Publikasi::class, 'publikasi_id');
    }

    public function perguruanTinggi()
    {
        return $this->belongsTo(MasterPerguruanTinggi::class, 'master_perguruan_tinggi_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}

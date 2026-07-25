<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublikasiPenulisMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'publikasi_penulis_mahasiswa';
    protected $guarded = [];

    public function publikasi()
    {
        return $this->belongsTo(Publikasi::class, 'publikasi_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}

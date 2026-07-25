<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublikasiDokumen extends Model
{
    use HasFactory;

    protected $table = 'publikasi_dokumen';
    protected $guarded = [];

    public function publikasi()
    {
        return $this->belongsTo(Publikasi::class, 'publikasi_id');
    }
}

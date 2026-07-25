<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublikasiPenulisLain extends Model
{
    use HasFactory;

    protected $table = 'publikasi_penulis_lain';
    protected $guarded = [];

    public function publikasi()
    {
        return $this->belongsTo(Publikasi::class, 'publikasi_id');
    }
}

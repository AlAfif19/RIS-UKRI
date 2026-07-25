<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';
    protected $guarded = [];

    public function perguruanTinggi()
    {
        return $this->belongsTo(MasterPerguruanTinggi::class, 'master_perguruan_tinggi_id');
    }
}

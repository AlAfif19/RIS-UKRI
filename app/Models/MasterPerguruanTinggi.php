<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPerguruanTinggi extends Model
{
    use HasFactory;

    protected $table = 'master_perguruan_tinggi';
    protected $guarded = [];

    public function dosen()
    {
        return $this->hasMany(Dosen::class);
    }
}

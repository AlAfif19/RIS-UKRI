<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Table users dari SIMANTAP
     */
    protected $table = 'users';

    /**
     * Mass assignable
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'sso_id',
        'dosen_id',
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Data dosen (tabel `dosen`) milik akun ini — hanya terisi untuk akun
     * dengan role "dosen" yang berhasil ditautkan saat login SSO (lihat
     * SsoController::cariDosenUntukUser()).
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}
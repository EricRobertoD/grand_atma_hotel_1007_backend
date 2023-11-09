<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'pegawai';
    protected $primaryKey = 'id_pegawai';
    protected $fillable = [
        'id_pegawai',
        'id_role',
        'email',
        'username_pegawai',
        'password',
        'nama_pegawai'

    ];
    public function role()
    {
        return $this->belongsto(Role::class, 'id_role');
    }

    public function NotaPelunasan()
    {
        return $this->hasMany(NotaPelunasan::class, 'id_pegawai');
    }

    public function Reservasi()
    {
        return $this->hasMany(Reservasi::class, 'id_reservasi');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

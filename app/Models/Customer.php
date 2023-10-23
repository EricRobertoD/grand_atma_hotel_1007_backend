<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'customer';
    protected $primaryKey = 'id_customer';
    protected $fillable = [
        'id_customer',
        'username',
        'password',
        'nama',
        'email',
        'no_telp',
        'no_identitas',
        'alamat',
        'nama_institusi',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function Reservasi()
    {
        return $this->hasMany(Reservasi::class, 'id_customer');
    }
}

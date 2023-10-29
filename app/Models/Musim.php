<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Musim extends Model
{
    use HasFactory;
    protected $table = 'musim';
    protected $primaryKey = 'id_musim';
    protected $fillable = [
        'id_musim',
        'nama_musim',
        'jenis_musim',
        'tanggal_mulai_musim',
        'tanggal_selesai_musim'

    ];
    public function TarifMusim()
    {
        return $this->hasMany(TarifMusim::class, 'id_musim');
    }
}

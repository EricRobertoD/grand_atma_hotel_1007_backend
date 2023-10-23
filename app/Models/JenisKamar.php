<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKamar extends Model
{
    use HasFactory;
    protected $table = 'jenis_kamar';
    protected $primaryKey = 'id_jeniskamar';
    protected $fillable = [
        'id_jeniskamar',
        'jenis_kamar',
        'harga_default',
        'ukuran_kamar',
        'fasilitas_kamar',
        'jenis_bed',
        'kapasitas'

    ];
    public function Kamar()
    {
        return $this->hasMany(Kamar::class, 'id_jenis_kamar');
    }

    public function TarifMusim()
    {
        return $this->hasMany(TarifMusim::class, 'id_jenis_kamar');
    }

    public function TransaksiKamar()
    {
        return $this->hasMany(TransaksiKamar::class, 'id_jenis_kamar');
    }
}

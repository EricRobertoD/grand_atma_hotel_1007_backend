<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaPelunasan extends Model
{
    use HasFactory;
    protected $table = 'nota_pelunasan';
    protected $primaryKey = 'id_nota';
    protected $fillable = [
        'id_nota',
        'id_pegawai',
        'id_reservasi',
        'no_nota',
        'tanggal_lunas_nota',
        'total_harga',
        'total_pajak',
        'total_semua'
    ];

    public function Pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

    public function Reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi');
    }
}

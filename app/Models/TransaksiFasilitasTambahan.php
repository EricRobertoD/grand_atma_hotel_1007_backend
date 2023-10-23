<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiFasilitasTambahan extends Model
{
    use HasFactory;
    protected $table = 'transaksi_fasilitas_tambahan';
    protected $primaryKey = 'id_transaksi_fasilitas_tambahan';
    protected $fillable = [
        'id_transaksi_fasilitas_tambahan',
        'id_fasilitas',
        'id_reservasi',
        'tanggal_lunas_fasilitas',
        'jumlah',
        'total_harga_fasilitas'
    ];

    public function FasilitasTambahan()
    {
        return $this->belongsTo(FasilitasTambahan::class, 'id_fasilitas');
    }

    public function Reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi');
    }
}

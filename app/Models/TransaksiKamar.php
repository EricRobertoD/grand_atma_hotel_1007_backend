<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKamar extends Model
{
    use HasFactory;
    protected $table = 'transaksi_kamar';
    protected $primaryKey = 'id_transaksi_kamar';
    protected $fillable = [
        'id_transaksi_kamar',
        'id_reservasi',
        'id_jeniskamar',
        'id_kamar',
        'harga_total',
        'jumlah'
    ];

    public function Kamar()
    {
        return $this->belongsTo(Kamar::class, 'id_kamar');
    }

    public function JenisKamar()
    {
        return $this->belongsTo(JenisKamar::class, 'id_jeniskamar');
    }

    public function Reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi');
    }
}

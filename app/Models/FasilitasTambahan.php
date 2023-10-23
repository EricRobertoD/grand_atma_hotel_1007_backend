<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FasilitasTambahan extends Model
{
    use HasFactory;
    protected $table = 'fasilitas_tambahan';
    protected $primaryKey = 'id_fasilitas';
    protected $fillable = [
        'id_fasilitas',
        'fasilitas_tambahan',
        'tarif',
        'satuan'
    ];

    public function TransaksiFasilitasTambahan()
    {
        return $this->belongsTo(TransaksiFasilitasTambahan::class, 'id_fasilitas');
    }

    public function Reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi');
    }
}

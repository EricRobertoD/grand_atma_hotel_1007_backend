<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use HasFactory;
    protected $table = 'kamar';
    protected $primaryKey = 'id_kamar';
    protected $fillable = [
        'id_kamar',
        'id_jeniskamar',
        'no_kamar',
        'pilih_bed'
    ];
    public function JenisKamar()
    {
        return $this->belongsTo(JenisKamar::class, 'id_jeniskamar');
    }

    public function TransaksiKamar()
    {
        return $this->hasMany(TransaksiKamar::class, 'id_kamar');
    }
}

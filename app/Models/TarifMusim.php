<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifMusim extends Model
{
    use HasFactory;
    protected $table = 'tarif_musim';
    protected $primaryKey = 'id_tarifmusim';
    protected $fillable = [
        'id_tarifmusim',
        'id_jeniskamar',
        'id_musim',
        'tarif_musim'

    ];
    public function Musim()
    {
        return $this->belongsTo(Musim::class, 'id_musim');
    }
    
    public function jenisKamar()
    {
        return $this->belongsTo(JenisKamar::class, 'id_jeniskamar');
    }
}

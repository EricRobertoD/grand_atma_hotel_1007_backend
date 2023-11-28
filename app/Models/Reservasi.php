<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasFactory;
    protected $table = 'reservasi';
    protected $primaryKey = 'id_reservasi';
    protected $fillable = [
        'id_reservasi',
        'id_customer',
        'id_booking',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_reservasi',
        'tanggal_checkin',
        'tanggal_checkout',
        'status',
        'dewasa',
        'anak',
        'total_jaminan',
        'total_deposit',
        'tanggal_pembayaran',
        'tanggal_kirim_ttr',
        "upload_gambar",
        'permintaan_khusus'

    ];

    public function Customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }
    public function TransaksiFasilitasTambahan()
    {
        return $this->hasMany(TransaksiFasilitasTambahan::class, 'id_reservasi');
    }
    public function NotaPelunasan()
    {
        return $this->hasOne(NotaPelunasan::class, 'id_reservasi');
    }
    public function TransaksiKamar()
    {
        return $this->hasMany(TransaksiKamar::class, 'id_reservasi');
    }
    public function Pegawai()
    {
        return $this->hasMany(Pegawai::class, 'id_reservasi');
    }
}

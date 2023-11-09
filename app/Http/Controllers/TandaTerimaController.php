<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservasi;
use Barryvdh\DomPDF\Facade\Pdf;

class TandaTerimaController extends Controller
{
    public function createTandaTerima($id)
    {
        //find
        $reservasi = Reservasi::with(
            'Customer',
            'Pegawai',
            'TransaksiKamar.Kamar.JenisKamar',
            'TransaksiFasilitasTambahan.FasilitasTambahan'
        )
            ->find($id);

        //if data reservasi null
        if (!$reservasi) {
            // return api
            return response()->json([
                'success' => false,
                'message' => 'Data reservasi tidak ditemukan',
            ], 404);
        }

        date_default_timezone_set('Asia/Jakarta');
        $tanggal_sekarang = date('Y-m-d');
        

        $data = [
            'reservasi' => $reservasi,
            'tanggal_sekarang' => $tanggal_sekarang,
        ];

        $pdf = Pdf::loadview('tanda_terima_reservasi', $data);

        return $pdf->output();
        // return $pdf->stream();
    }
}

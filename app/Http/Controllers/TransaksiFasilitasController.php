<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiFasilitasTambahan;
use App\Models\Reservasi;
use App\Models\FasilitasTambahan;

class TransaksiFasilitasController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'id_reservasi' => 'required|exists:reservasi,id_reservasi',
            'id_fasilitas' => 'required|exists:fasilitas_tambahan,id_fasilitas',
            'jumlah' => 'required|integer|min:1',
        ]);

        $reservasi = Reservasi::find($request->id_reservasi);
        $fasilitasTambahan = FasilitasTambahan::find($request->id_fasilitas);

        $totalHargaFasilitas = $request->jumlah * $fasilitasTambahan->tarif;

        $transaksiFasilitas = new TransaksiFasilitasTambahan([
            'id_reservasi' => $request->id_reservasi,
            'id_fasilitas' => $request->id_fasilitas,
            'jumlah' => $request->jumlah,
            'total_harga_fasilitas' => $totalHargaFasilitas,
            'tanggal_lunas_fasilitas' => now(), // You can adjust the date as needed
        ]);

        $reservasi->TransaksiFasilitasTambahan()->save($transaksiFasilitas);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi Fasilitas Tambahan created successfully'
        ]);
    }

    public function storeCheckIn(Request $request)
    {
        // Validate the request data
        $request->validate([
            'id_reservasi' => 'required|exists:reservasi,id_reservasi',
            'id_fasilitas' => 'required|exists:fasilitas_tambahan,id_fasilitas',
            'jumlah' => 'required|integer|min:1',
        ]);

        $reservasi = Reservasi::find($request->id_reservasi);
        $fasilitasTambahan = FasilitasTambahan::find($request->id_fasilitas);

        $totalHargaFasilitas = $request->jumlah * $fasilitasTambahan->tarif;

        $transaksiFasilitas = new TransaksiFasilitasTambahan([
            'id_reservasi' => $request->id_reservasi,
            'id_fasilitas' => $request->id_fasilitas,
            'jumlah' => $request->jumlah,
            'total_harga_fasilitas' => $totalHargaFasilitas,
            'tanggal_lunas_fasilitas' => now(), // You can adjust the date as needed
        ]);

        // Update the deposit attribute of the related Reservasi model
        $reservasi->deposit -= $totalHargaFasilitas;
        $reservasi->save();

        $reservasi->TransaksiFasilitasTambahan()->save($transaksiFasilitas);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi Fasilitas Tambahan created successfully'
        ]);
    }
}

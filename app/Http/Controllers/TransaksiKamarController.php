<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TransaksiKamar;

class TransaksiKamarController extends Controller
{
    public function index()
    {
        $transaksiKamar = TransaksiKamar::all();

        return response([
            'message' => 'Transaksi Kamar retrieved successfully',
            'data' => $transaksiKamar,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'id_reservasi' => 'required|exists:reservasi,id_reservasi',
            'id_jeniskamar' => 'required|exists:jenis_kamar,id_jeniskamar',
            'id_kamar' => 'required|exists:kamar,id_kamar',
            'jenis_bed' => 'required|string',
            'harga_total' => 'required|numeric',
            'jumlah' => 1,
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $transaksiKamar = TransaksiKamar::create($data);

        return response([
            'message' => 'Transaksi Kamar created successfully',
            'data' => $transaksiKamar,
        ], 201);
    }

    
    public function destroy($id)
    {
        $transaksiKamar = TransaksiKamar::find($id);

        if (!$transaksiKamar) {
            return response([
                'message' => 'Transaksi Kamar not found',
            ], 404);
        }

        $transaksiKamar->delete();

        return response([
            'message' => 'Transaksi Kamar deleted successfully',
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $transaksiKamar = TransaksiKamar::find($id);

        if (!$transaksiKamar) {
            return response([
                'message' => 'Transaksi Kamar not found',
            ], 404);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'id_reservasi' => 'exists:reservasi,id_reservasi',
            'id_jeniskamar' => 'exists:jenis_kamar,id_jeniskamar',
            'id_kamar' => 'exists:kamar,id_kamar',
            'jenis_bed' => 'string',
            'harga_total' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $transaksiKamar->update($data);

        return response([
            'message' => 'Transaksi Kamar updated successfully',
            'data' => $transaksiKamar,
        ], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        
        $transaksiKamar = TransaksiKamar::where('jenis_bed', 'like', '%' . $keyword . '%')->get();
    
        if ($transaksiKamar->isEmpty()) {
            return response([
                'message' => 'No matching Transaksi Kamar found',
                'data' => null,
            ], 404);
        }
    
        return response([
            'message' => 'Search results',
            'data' => $transaksiKamar,
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kamar;
use App\Models\JenisKamar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KamarController extends Controller
{
    public function index()
    {
        $kamar = Kamar::with('JenisKamar')->get();

        if ($kamar->count() > 0) {
            return response([
                'message' => 'Retrieve all Kamar Success',
                'data' => $kamar
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_jeniskamar' => 'required|exists:jenis_kamar,id_jeniskamar',
            'no_kamar' => 'required|unique:kamar,no_kamar',
            'pilih_bed' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $kamar = Kamar::create([
            'id_jeniskamar' => $request->input('id_jeniskamar'),
            'no_kamar' => $request->input('no_kamar'),
            'pilih_bed' => $request->input('pilih_bed'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Kamar created successfully',
            'data' => $kamar,
        ], 201);
    }

    public function update(Request $request, Kamar $kamar)
    {
        $id = $kamar->id_kamar;
    
        $validator = Validator::make($request->all(), [
            'id_jeniskamar' => 'required|exists:jenis_kamar,id_jeniskamar',
            'no_kamar' => 'required|unique:kamar,no_kamar,' . $id . ',id_kamar',
            'pilih_bed' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $kamar->update([
            'id_jeniskamar' => $request->input('id_jeniskamar'),
            'no_kamar' => $request->input('no_kamar'),
            'pilih_bed' => $request->input('pilih_bed'),
        ]);
    
        return response([
            'status' => 'success',
            'message' => 'Kamar updated successfully',
            'data' => $kamar,
        ], 200);
    }

    public function destroy(Kamar $kamar)
    {
        $kamar->delete();

        return response([
            'status' => 'success',
            'message' => 'Kamar deleted successfully',
            'data' => $kamar,
        ], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('no_kamar');
        $kamar = Kamar::where('no_kamar', 'like', '%' . $keyword . '%')
            ->with('JenisKamar')
            ->get();

        if ($kamar->isEmpty()) {
            return response([
                'message' => 'No matching Kamar found',
                'data' => null,
            ], 404);
        }

        return response([
            'message' => 'Search results',
            'data' => $kamar,
        ], 200);
    }

    public function show(Kamar $kamar)
    {
        $kamarWithJenisKamar = Kamar::with('JenisKamar')
            ->where('id_kamar', $kamar->id_kamar)
            ->first();

        if ($kamarWithJenisKamar) {
            return response([
                'status' => 'success',
                'message' => 'Retrieve Kamar details successfully',
                'data' => $kamarWithJenisKamar,
            ], 200);
        }

        return response([
            'status' => 'error',
            'message' => 'Kamar not found',
        ], 404);
    }

    public function kamarAvailable()
{
    // 1. Dapatkan jumlah kamar berdasarkan jenis kamar
    $totalKamarPerJenis = Kamar::select('id_jeniskamar', DB::raw('count(*) as total'))
        ->groupBy('id_jeniskamar')
        ->get();

    // 2. Dapatkan jumlah kamar yang sudah dibooking untuk rentang tanggal
    $tanggalMulai = 'tanggal_mulai'; // Gantilah dengan nama kolom yang sesuai
    $tanggalSelesai = 'tanggal_selesai'; // Gantilah dengan nama kolom yang sesuai
    $kamarDibooking = DB::table('reservasi_kamar')
        ->join('reservasi', 'reservasi_kamar.id_reservasi', '=', 'reservasi.id_reservasi')
        ->select('kamar.id_jeniskamar', DB::raw('count(*) as total'))
        ->whereDate($tanggalMulai, '<=', now()) // Gantilah dengan tanggal yang sesuai
        ->whereDate($tanggalSelesai, '>=', now()) // Gantilah dengan tanggal yang sesuai
        ->groupBy('kamar.id_jeniskamar')
        ->get();

    // 3. Hitung kamar yang masih tersedia
    $kamarTersedia = [];

    foreach ($totalKamarPerJenis as $jenis) {
        $jenisId = $jenis->id_jeniskamar;
        $totalKamar = $jenis->total;

        $kamarDibooked = $kamarDibooking->first(function ($item) use ($jenisId) {
            return $item->id_jeniskamar == $jenisId;
        });

        $kamarTersedia[$jenisId] = $totalKamar - ($kamarDibooked ? $kamarDibooked->total : 0);
    }

    return response([
        'message' => 'Ketersediaan Kamar',
        'data' => $kamarTersedia,
    ], 200);
}
}

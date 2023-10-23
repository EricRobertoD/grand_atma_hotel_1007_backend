<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisKamar;
use App\Models\Kamar;
use Illuminate\Support\Facades\Validator;

class JenisKamarController extends Controller
{

    public function index()
    {
        $jenisKamar = JenisKamar::all();

        if ($jenisKamar->isEmpty()) {
            return response([
                'message' => 'No jenis kamar found',
                'data' => null,
            ], 404);
        }

        return response([
            'message' => 'Retrieve jenis kamar success',
            'data' => $jenisKamar,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_kamar' => 'required|string',
            'harga_default' => 'required|numeric',
            'ukuran_kamar' => 'required|string',
            'fasilitas_kamar' => 'required|string',
            'jenis_bed' => 'required|string',
            'kapasitas' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $jenisKamar = JenisKamar::create([
            'jenis_kamar' => $request->input('jenis_kamar'),
            'harga_default' => $request->input('harga_default'),
            'ukuran_kamar' => $request->input('ukuran_kamar'),
            'fasilitas_kamar' => $request->input('fasilitas_kamar'),
            'jenis_bed' => $request->input('jenis_bed'),
            'kapasitas' => $request->input('kapasitas'),
        ]);

        return response([
            'message' => 'Jenis Kamar created successfully',
            'data' => $jenisKamar,
        ], 201);
    }

    public function update(Request $request, JenisKamar $jenisKamar)
    {
        $validator = Validator::make($request->all(), [
            'jenis_kamar' => 'required|string',
            'harga_default' => 'required|numeric',
            'ukuran_kamar' => 'string|nullable',
            'fasilitas_kamar' => 'string|nullable',
            'jenis_bed' => 'string|nullable',
            'kapasitas' => 'integer|nullable',
        ]);
    
        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $jenisKamar->update($request->all());
    
        return response([
            'message' => 'Jenis Kamar updated successfully',
            'data' => $jenisKamar,
        ], 200);
    }

    public function destroy(JenisKamar $jenisKamar)
    {
        $jenisKamar->delete();
    
        return response([
            'message' => 'Jenis Kamar deleted successfully',
            'data' => $jenisKamar,
        ], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('jenis_kamar');
        $jenisKamar = JenisKamar::where('jenis_kamar', 'like', '%' . $keyword . '%')->get();
        
        if ($jenisKamar->isNotEmpty()) {
            return response([
                'message' => 'Search results',
                'data' => $jenisKamar,
            ], 200);
        }
    
        return response([
            'message' => 'No matching Jenis Kamar found',
            'data' => null,
        ], 404);
    }
}

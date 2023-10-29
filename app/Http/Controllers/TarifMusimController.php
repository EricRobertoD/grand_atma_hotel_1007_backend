<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TarifMusim;
use App\Models\JenisKamar;
use App\Models\Musim;
use Illuminate\Support\Facades\Validator;


class TarifMusimController extends Controller
{
    public function index(){
        $tarifMusim = TarifMusim::with('JenisKamar')->with('Musim')->get();

        if(count($tarifMusim) > 0){
            return response([
                'message' => 'Get all Tarif Musim Success',
                'data' => $tarifMusim
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
            'id_musim' => 'required|exists:musim,id_musim',
            'tarif_musim' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $tarifmusim = TarifMusim::create([
            'id_jeniskamar' => $request->input('id_jeniskamar'),
            'id_musim' => $request->input('id_musim'),
            'tarif_musim' => $request->input('tarif_musim'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Musim created successfully',
            'data' => $tarifmusim,
        ], 201);
    }

    public function update(Request $request, TarifMusim $tarifmusim)
    {
        $request->validate([
            'id_jeniskamar' => 'required|exists:jenis_kamar,id_jeniskamar',
            'id_musim' => 'required|exists:musim,id_musim',
            'tarif_musim' => 'required',
        ]);
    
        $tarifmusim->update([
            'id_jeniskamar' => $request->input('id_jeniskamar'),
            'id_musim' => $request->input('id_musim'),
            'tarif_musim' => $request->input('tarif_musim'),
        ]);
    
        return response([
            'status' => 'success',
            'message' => 'Tarif Musim updated successfully',
            'data' => $tarifmusim,
        ], 200);
    }

    public function destroy(TarifMusim $tarifmusim)
    {
        $tarifmusim->delete();

        return response([
            'status' => 'success',
            'message' => 'Musim deleted successfully',
            'data' => $tarifmusim,
        ], 200);
    }

}

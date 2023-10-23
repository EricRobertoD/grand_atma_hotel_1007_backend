<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Musim;
use Illuminate\Support\Facades\Validator;


class MusimController extends Controller
{
    public function index(){
        $musim = Musim::all();

        if(count($musim) > 0){
            return response([
                'message' => 'Get all Musim Success',
                'data' => $musim
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
            'jenis_musim' => 'required|string',
            'tanggal_mulai_musim' => 'required|date',
            'tanggal_selesai_musim' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $musim = Musim::create([
            'jenis_musim' => $request->input('jenis_musim'),
            'tanggal_mulai_musim' => $request->input('tanggal_mulai_musim'),
            'tanggal_selesai_musim' => $request->input('tanggal_selesai_musim'),
        ]);

        return response([
            'message' => 'Musim created successfully',
            'data' => $musim,
        ], 201); 
    }

    public function update(Request $request, Musim $musim)
    {
        $request->validate([
            'jenis_musim' => 'required|string',
            'tanggal_mulai_musim' => 'required|date',
            'tanggal_selesai_musim' => 'required|date',
        ]);

        $musim->update([
            'jenis_musim' => $request->input('jenis_musim'),
            'tanggal_mulai_musim' => $request->input('tanggal_mulai_musim'),
            'tanggal_selesai_musim' => $request->input('tanggal_selesai_musim'),
        ]);

        return response([
            'message' => 'Musim updated successfully',
            'data' => $musim,
        ], 200);
    }
    public function destroy($id)
    {
        $musim = Musim::find($id);

        if (!$musim) {
            return response([
            'message' => 'Musim not found',
        ], 404); 
        }

        $musim->delete();
            return response([
                'message' => 'Musim deleted successfully',
                'data' => $musim
            ], 200);
    }
    
    public function search(Request $request)
    {
        $jenisMusim = $request->input('jenis_musim');
        $musim = Musim::where('jenis_musim', 'like', '%' . $jenisMusim . '%')->get();
    
        if ($musim->isNotEmpty()) {
            return response([
                'message' => 'Search results',
                'data' => $musim
            ], 200);
        }
    
        return response([
            'message' => 'No matching Musim found',
            'data' => null,
        ], 404);
    }
}

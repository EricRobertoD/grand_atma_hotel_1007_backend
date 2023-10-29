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
            'nama_musim' => 'required|string',
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
            'nama_musim' => $request->input('nama_musim'),
            'jenis_musim' => $request->input('jenis_musim'),
            'tanggal_mulai_musim' => $request->input('tanggal_mulai_musim'),
            'tanggal_selesai_musim' => $request->input('tanggal_selesai_musim'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Musim created successfully',
            'data' => $musim,
        ], 201); 
    }

    public function update(Request $request, Musim $musim)
    {
        $id = $musim->id_musim;

        $now = date('Y-m-d');
        $date = date('Y-m-d', strtotime($now. ' + 2 months'));
        if ($musim->tanggal_mulai_musim < $date) {
            return response([
                'message' => 'Musim can\'t be updated Minimum 2 Months Before',
                "errors" => [
                    "musim" => ["Musim can't be updated Minimum 2 Months Before"]
                ]
            ], 400);
        }
        $request->validate([
            'nama_musim' => 'required|string',
            'jenis_musim' => 'required|string',
            'tanggal_mulai_musim' => 'required|date',
            'tanggal_selesai_musim' => 'required|date',
        ]);

        $musim->update([
            'nama_musim' => $request->input('nama_musim'),
            'jenis_musim' => $request->input('jenis_musim'),
            'tanggal_mulai_musim' => $request->input('tanggal_mulai_musim'),
            'tanggal_selesai_musim' => $request->input('tanggal_selesai_musim'),
        ]);

        return response([
            'status' => 'success',
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
                'status' => 'success',
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

    public function show(Musim $musim)
    {
        // $musim = Musim::where('id_fasilitas', $musim->id_fasilitas)->first();

        if ($musim) {
            return response([
                'status' => 'success',
                'message' => 'Retrieve Musim details successfully',
                'data' => $musim,
            ], 200);
        }

        return response([
            'status' => 'error',
            'message' => 'Musim not found',
        ], 404);
    }
}

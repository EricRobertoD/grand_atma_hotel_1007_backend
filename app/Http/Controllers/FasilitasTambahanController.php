<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FasilitasTambahan;
use Illuminate\Support\Facades\Validator;

class FasilitasTambahanController extends Controller
{
    public function index(){
        $fasilitasTambahan = FasilitasTambahan::all();

        if(count($fasilitasTambahan) > 0){
            return response([
                'data' => $fasilitasTambahan
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400); 
    }
    
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'fasilitas_tambahan' => 'required|string',
            'tarif' => 'required|numeric',
            'satuan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $fasilitasTambahan = FasilitasTambahan::create([
            'fasilitas_tambahan' => $request->input('fasilitas_tambahan'),
            'tarif' => $request->input('tarif'),
            'satuan' => $request->input('satuan'),
        ]);

        return response([
            'status' => 'success',
            'message' => 'Fasilitas Tambahan created successfully',
            'data' => $fasilitasTambahan
        ], 201);
    }

    public function update(Request $request, FasilitasTambahan $fasilitasTambahan){
        $validator = Validator::make($request->all(), [
            'fasilitas_tambahan' => 'string',
            'tarif' => 'numeric',
            'satuan' => 'string',
        ]);
    
        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $fasilitasTambahan->update([
            'fasilitas_tambahan' => $request->input('fasilitas_tambahan'),
            'tarif' => $request->input('tarif'),
            'satuan' => $request->input('satuan'),
        ]);
    
        return response([
            'status' => 'success',
            'message' => 'Fasilitas Tambahan updated successfully',
            'data' => $fasilitasTambahan
        ], 200);
    }

    public function destroy(FasilitasTambahan $fasilitasTambahan){
        $fasilitasTambahan->delete();
    
        return response([
            'status' => 'success',
            'message' => 'Fasilitas Tambahan deleted successfully',
            'data' => $fasilitasTambahan
        ], 200);
    }

    public function search(Request $request){
        $keyword = $request->input('fasilitas_tambahan');
    
        $fasilitasTambahan = FasilitasTambahan::where('fasilitas_tambahan', 'like', '%' . $keyword . '%')->get();
    
        if ($fasilitasTambahan->isNotEmpty()) {
            return response([
                'message' => 'Search results',
                'data' => $fasilitasTambahan,
            ], 200);
        }
    
        return response([
            'message' => 'No matching Fasilitas Tambahan found',
            'data' => null,
        ], 404);
    }

    public function show(FasilitasTambahan $fasilitasTambahan)
    {
        // $fasilitasTambahan = FasilitasTambahan::where('id_fasilitas', $fasilitasTambahan->id_fasilitas)->first();

        if ($fasilitasTambahan) {
            return response([
                'status' => 'success',
                'message' => 'Retrieve Fasilitas Tambahan details successfully',
                'data' => $fasilitasTambahan,
            ], 200);
        }

        return response([
            'status' => 'error',
            'message' => 'Fasilitas Tambahan not found',
        ], 404);
    }
    
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservasi;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class ReservasiController extends Controller
{
    public function index(Request $request){
        $id = auth()->user()->id_customer;
        $reservasi = Reservasi::where('id_customer', $id)->with('Customer')->with('TransaksiFasilitasTambahan.FasilitasTambahan')->with('TransaksiKamar.Kamar.JenisKamar')->with('NotaPelunasan')->get();

            return response([
                'message' => 'Retrieve all Reservasi Success',
                'data' => $reservasi
            ], 200);
    }

    public function show($id_customer)
    {
        $reservations = Reservasi::where('id_customer', $id_customer)->get();
    
        if ($reservations->isEmpty()) {
            return response([
                'status' => 'error',
                'message' => 'Reservations not found for the customer',
            ], 404);
        }
    
        return response([
            'status' => 'success',
            'message' => 'Retrieve Reservasi details successfully',
            'data' => $reservations,
        ], 200);
    }
}

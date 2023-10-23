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
        $reservasi = Reservasi::where('id_customer', $id)->with('Customer')->get();

            return response([
                'message' => 'Retrieve all Reservasi Success',
                'data' => $reservasi
            ], 200);
    }
}

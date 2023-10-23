<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->user()->id_customer;
        $customer = Customer::where('id_customer', $id)->get();

        return response([
            'message' => 'Retrieve all customer Success',
            'data' => $customer
        ], 200);
    }

    public function update(Request $request)
    {
        $id = $request->user()->id_customer; // Get the currently authenticated user's ID
    
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customer,email,' . $id . ',id_customer',
            'no_telp' => 'required',
            'no_identitas' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $customer = Customer::find($id);
    
        $customer->update($request->all());
    
        return response([
            'message' => 'Customer information updated successfully',
            'data' => $customer,
        ], 200);
    }
    
    
}

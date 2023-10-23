<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $registerData = $request->all();
        $validate = Validator::make($registerData, [
            'username' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customer',
            'password' => 'required|min:6|max:255',
            'no_telp' => 'required',
            'no_identitas' => 'required',

        ]);
        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = [
                'message' => 'Registrasi gagal. Silakan periksa semua bagian yang ditandai.',
                'errors' => $errors->toArray()
            ];
            
            return response()->json($response, 400);
        }
        $registerData['password'] = bcrypt($registerData['password']);
        $customer = Customer::create($registerData);
        return response()->json([
            'status' => 'success',
            'message' => 'Register Berhasil!',
            'data' => $customer
        ], 200);
    }

    
    public function registerPegawai(Request $request)
    {
        $registerData = $request->all();
        $validate = Validator::make($registerData, [
            'username_pegawai' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customer',
            'password' => 'required|min:6|max:255',
            'nama_pegawai' => 'required',
            'id_role' => 'required',

        ]);
        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = [
                'message' => 'Registrasi gagal. Silakan periksa semua bagian yang ditandai.',
                'errors' => $errors->toArray()
            ];
            
            return response()->json($response, 400);
        }
        $registerData['password'] = bcrypt($registerData['password']);
        $pegawai = Pegawai::create($registerData);
        return response()->json([
            'status' => 'success',
            'message' => 'Register Berhasil!',
            'data' => $pegawai
        ], 200);
    }

    public function login (Request $request){
        $loginData = $request->all();

        $validate = Validator::make($loginData, [
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validate->fails())
            return response(['message'=> $validate->errors()->first(),'errors' => $validate->errors()], 400);

        if(Auth::guard('customer')->attempt($loginData)){
            $customer = Auth::user();
            $token = $customer->createToken('Authentication Token',['customer'])->plainTextToken;

            return response([
                'message' => 'Authenticated',
                'data' => [
                    'customer' => $customer,
                    'token_type' => 'Bearer',
                    'access_token' => $token,
                ]
            ]);
        }else{
            return response(['message' => 'Invalid Credentials user'], 401);
        }
        return response(['message' => 'Berhasil Login'], 200);
    }

    public function loginPegawai(Request $request) {
        $loginData = $request->all();
    
        $validate = Validator::make($loginData, [
            'email' => 'required',
            'password' => 'required'
        ]);
    
        if ($validate->fails()) {
            return response([
                'message' => $validate->errors()->first(),
                'errors' => $validate->errors()
            ], 400);
        }
    
        if (Auth::guard('pegawai')->attempt($loginData)) {
            $pegawai = Auth::guard('pegawai')->user();
            $pegawai->role;
            $token = $pegawai->createToken('Authentication Token',['pegawai'])->plainTextToken;
    
            return response([
                'message' => 'Authenticated',
                'data' => [
                    'pegawai' => $pegawai,
                    'token_type' => 'Bearer',
                    'access_token' => $token,
                ]
            ]);
        } else {
            return response(['message' => 'Invalid Credentials user'], 401);
        }
    }

    public function logout(Request $request)
    {
        $customer = $request->user();
        $customer->token()->revoke();
        return response([
            'message' => 'Logout Success',
            'user' => $customer
        ]);
    }
    
    public function logoutPegawai(Request $request)
    {
        $pegawai = $request->user();
        $pegawai->token()->revoke();
        return response([
            'message' => 'Logout Success',
            'user' => $pegawai
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed', // Ensure 'password' matches 'password_confirmation'
        ]);
    
        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $customer = $request->user();
        $currentPassword = $request->input('current_password');
        $newPassword = $request->input('password');
    
        // Verify the current password
        if (!Hash::check($currentPassword, $customer->password)) {
            return response([
                "errors" => [
                    "old_password" => [
                      "password lama salah."
                    ]
                  ]
            ], 400);
        }
    
        // Set the new password
        $customer->password = Hash::make($newPassword);
    
        // Save the customer model
        $customer->save();
    
        return response([
            'status' => 'success',
            'message' => 'Password changed successfully',
        ], 200);
    }
}

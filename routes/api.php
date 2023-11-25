<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Group;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Auth::routes(['verify' => true]);

Route::post('register', 'App\Http\Controllers\AuthController@register');
Route::post('registerGrup', 'App\Http\Controllers\AuthController@registerGrup');
Route::post('registerPegawai', 'App\Http\Controllers\AuthController@registerPegawai');
Route::post('login', 'App\Http\Controllers\AuthController@login');
Route::post('loginPegawai', 'App\Http\Controllers\AuthController@loginPegawai');
Route::get('jenisKamarPublic', 'App\Http\Controllers\JenisKamarController@index');
Route::post('forgetPassword', 'App\Http\Controllers\AuthController@forgetPassword');
Route::get('customer/{customer}', 'App\Http\Controllers\CustomerController@show');
Route::get('reservasi/{reservasi}', 'App\Http\Controllers\ReservasiController@show');
Route::put('customer/{customer}', 'App\Http\Controllers\CustomerController@updateGrup');
Route::get('reservasiGrup', 'App\Http\Controllers\ReservasiController@indexGrup');
Route::post('logout', 'App\Http\Controllers\AuthController@logout');
Route::get('kamar/{kamar}', 'App\Http\Controllers\KamarController@show');
Route::post('reservasiAvailable', 'App\Http\Controllers\ReservasiController@kamarAvailable');
Route::get('tandaTerimaReservasi/{tandaTerimaReservasi}', 'App\Http\Controllers\TandaTerimaController@createTandaTerima');
Route::post('transaksiFasilitas', 'App\Http\Controllers\TransaksiFasilitasController@store');
Route::get('fasilitasTambahan', 'App\Http\Controllers\FasilitasTambahanController@index');
Route::post('updateBayar/{reservasi}', 'App\Http\Controllers\ReservasiController@updateBayar');
Route::delete('reservasi/{reservasi}', 'App\Http\Controllers\ReservasiController@destroy');


Route::middleware(['auth:sanctum', 'ability:pegawai'])->group(function(){

    Route::get('customerGrup', 'App\Http\Controllers\CustomerController@indexGrup');
    Route::get('checkIn', 'App\Http\Controllers\ReservasiController@indexCheckIn');
    Route::get('showNota', 'App\Http\Controllers\ReservasiController@indexNota');
    Route::put('updateStatus/{reservasi}', 'App\Http\Controllers\ReservasiController@updateStatus');
    Route::put('checkIn/{reservasi}', 'App\Http\Controllers\ReservasiController@checkIn');
    Route::put('checkOut/{reservasi}', 'App\Http\Controllers\ReservasiController@checkOut');

    Route::get('musim', 'App\Http\Controllers\MusimController@index');
    Route::post('musim', 'App\Http\Controllers\MusimController@store');
    Route::put('musim/{musim}', 'App\Http\Controllers\MusimController@update');
    Route::delete('musim/{musim}', 'App\Http\Controllers\MusimController@destroy');
    Route::get('/musim/search', 'App\Http\Controllers\MusimController@search');
    Route::get('musim/{musim}', 'App\Http\Controllers\MusimController@show');

    Route::get('tarifMusim', 'App\Http\Controllers\TarifMusimController@index');
    Route::post('tarifMusim', 'App\Http\Controllers\TarifMusimController@store');
    Route::put('tarifMusim/{tarifmusim}', 'App\Http\Controllers\TarifMusimController@update');
    Route::delete('tarifMusim/{tarifmusim}', 'App\Http\Controllers\TarifMusimController@destroy');

    Route::post('fasilitasTambahan', 'App\Http\Controllers\FasilitasTambahanController@store');
    Route::put('fasilitasTambahan/{fasilitasTambahan}', 'App\Http\Controllers\FasilitasTambahanController@update');
    Route::delete('fasilitasTambahan/{fasilitasTambahan}', 'App\Http\Controllers\FasilitasTambahanController@destroy');
    Route::get('/fasilitasTambahan/search', 'App\Http\Controllers\FasilitasTambahanController@search');
    Route::get('/fasilitasTambahan/{fasilitasTambahan}', 'App\Http\Controllers\FasilitasTambahanController@show');
    Route::post('transaksiFasilitasCheckIn', 'App\Http\Controllers\TransaksiFasilitasController@storeCheckIn');

    Route::get('kamar', 'App\Http\Controllers\KamarController@index');
    Route::post('kamar', 'App\Http\Controllers\KamarController@store');
    Route::put('kamar/{kamar}', 'App\Http\Controllers\KamarController@update');
    Route::delete('kamar/{kamar}', 'App\Http\Controllers\KamarController@destroy');
    Route::get('/kamar/search', 'App\Http\Controllers\KamarController@search');
   
    
    Route::get('jenisKamar', 'App\Http\Controllers\JenisKamarController@index');
    Route::post('jenisKamar', 'App\Http\Controllers\JenisKamarController@store');
    Route::put('jenisKamar/{jenisKamar}', 'App\Http\Controllers\JenisKamarController@update');
    Route::delete('jenisKamar/{jenisKamar}', 'App\Http\Controllers\JenisKamarController@destroy');
    Route::get('/jenisKamar/search', 'App\Http\Controllers\JenisKamarController@search');

    Route::get('transaksiKamar', 'App\Http\Controllers\transaksiKamarController@index');
    Route::post('transaksiKamar', 'App\Http\Controllers\transaksiKamarController@store');
    Route::put('transaksiKamar/{transaksiKamar}', 'App\Http\Controllers\transaksiKamarController@update');
    Route::delete('transaksiKamar/{transaksiKamar}', 'App\Http\Controllers\transaksiKamarController@destroy');
    Route::get('/transaksiKamar/search', 'App\Http\Controllers\transaksiKamarController@search');

    Route::get('getPembatalanGrup', 'App\Http\Controllers\ReservasiController@getPembatalanGrup');
    
    Route::get('getNewCustomer', 'App\Http\Controllers\LaporanController@getNewCustomer');
    Route::get('getPendapatanPerJenisTamuPerBulan', 'App\Http\Controllers\LaporanController@getPendapatanPerJenisTamuPerBulan');
    Route::get('getCustomerCountPerRoomType', 'App\Http\Controllers\LaporanController@getCustomerCountPerRoomType');
    Route::get('getTopCustomersWithMostBookings', 'App\Http\Controllers\LaporanController@getTopCustomersWithMostBookings');

    Route::post('reservasiGrup', 'App\Http\Controllers\ReservasiController@storeGrup');
});



Route::middleware(['auth:sanctum', 'ability:customer'])->group(function(){

    Route::post('changePassword', 'App\Http\Controllers\AuthController@changePassword');
    Route::get('customer', 'App\Http\Controllers\CustomerController@index');
    Route::put('customer', 'App\Http\Controllers\CustomerController@update');
    Route::post('reservasi', 'App\Http\Controllers\ReservasiController@store');
    Route::post('reservasiAdd', 'App\Http\Controllers\ReservasiController@storeAdd');

    Route::get('reservasi', 'App\Http\Controllers\ReservasiController@index');
    Route::get('getPembatalan', 'App\Http\Controllers\ReservasiController@getPembatalan');

});
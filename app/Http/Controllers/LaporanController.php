<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Reservasi;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function getNewCustomer()
    {
        $nowJakarta = now('Asia/Jakarta');

        $newCustomersByMonth = Customer::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(id_customer) as jumlah_customer')
        )
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->orderBy(DB::raw('MONTH(created_at)'))
        ->get();

        $result = [];
        $bulanArray = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        foreach ($bulanArray as $monthName) {
            $result[] = [
                'bulan' => $monthName,
                'jumlah_customer' => 0,
            ];
        }

        foreach ($newCustomersByMonth as $item) {
            $monthName = date("F", mktime(0, 0, 0, $item->month, 1));
            $key = array_search($monthName, array_column($result, 'bulan'));
            if ($key !== false) {
                $result[$key]['jumlah_customer'] = $item->jumlah_customer;
            }
        }

        // Add total count
        $totalCustomers = array_sum(array_column($result, 'jumlah_customer'));

        return response()->json([
            'status' => 'success',
            'message' => 'data laporan customer baru berhasil didapatkan',
            'data' => [
                'dataLaporan' => $result,
                'tanggal_cetak' => $nowJakarta->format('F d, Y'),
                'tahun' => $nowJakarta->year,
                'total_customer_baru' => $totalCustomers,
            ],
        ]);
    }

    public function getPendapatanPerJenisTamuPerBulan()
{
    $result = Reservasi::select(
        DB::raw('MONTH(reservasi.created_at) as month'),
        DB::raw('SUM(CASE WHEN SUBSTRING(reservasi.id_booking, 1, 1) = "P" THEN COALESCE(tk.total_harga_kamar, 0) + COALESCE(tf.total_harga_fasilitas, 0) ELSE 0 END) as pendapatan_personal'),
        DB::raw('SUM(CASE WHEN SUBSTRING(reservasi.id_booking, 1, 1) = "G" THEN COALESCE(tk.total_harga_kamar, 0) + COALESCE(tf.total_harga_fasilitas, 0) ELSE 0 END) as pendapatan_grup'),
        DB::raw('SUM(COALESCE(tk.total_harga_kamar, 0) + COALESCE(tf.total_harga_fasilitas, 0)) as pendapatan_per_bulan')
    )
        ->leftJoin(DB::raw('(SELECT id_reservasi, SUM(harga_total) as total_harga_kamar FROM transaksi_kamar GROUP BY id_reservasi) as tk'), 'reservasi.id_reservasi', '=', 'tk.id_reservasi')
        ->leftJoin(DB::raw('(SELECT id_reservasi, SUM(total_harga_fasilitas) as total_harga_fasilitas FROM transaksi_fasilitas_tambahan GROUP BY id_reservasi) as tf'), 'reservasi.id_reservasi', '=', 'tf.id_reservasi')
        ->groupBy('reservasi.id_reservasi', DB::raw('MONTH(reservasi.created_at)')) // Corrected alias to 'reservasi'
        ->orderBy('reservasi.id_reservasi', 'asc') // Corrected alias to 'reservasi'
        ->orderBy(DB::raw('MONTH(reservasi.created_at)'), 'asc')
        ->get();


    $resultArray = [];
    $bulanArray = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    foreach ($bulanArray as $monthName) {
        $resultArray[] = [
            'bulan' => $monthName,
            'pendapatan_personal' => 0,
            'pendapatan_grup' => 0,
            'pendapatan_per_bulan' => 0,
        ];
    }

    foreach ($result as $item) {
        $monthName = date("F", mktime(0, 0, 0, $item->month, 1));
        $key = array_search($monthName, array_column($resultArray, 'bulan'));
        if ($key !== false) {
            $resultArray[$key]['pendapatan_personal'] += $item->pendapatan_personal;
            $resultArray[$key]['pendapatan_grup'] += $item->pendapatan_grup;
            $resultArray[$key]['pendapatan_per_bulan'] += $item->pendapatan_per_bulan;
        }
    }

    $totalPendapatanGrup = array_sum(array_column($resultArray, 'pendapatan_grup'));
    $totalPendapatanPersonal = array_sum(array_column($resultArray, 'pendapatan_personal'));
    $totalPendapatan = array_sum(array_column($resultArray, 'pendapatan_per_bulan'));

    return response()->json([
        'status' => 'success',
        'message' => 'data laporan pendapatan per jenis tamu per bulan berhasil didapatkan',
        'data' => [
            'dataLaporan' => $resultArray,
            'tanggal_cetak' => now('Asia/Jakarta')->format('F d, Y'),
            'tahun' => now('Asia/Jakarta')->year,
            'total_pendapatan_grup' => $totalPendapatanGrup,
            'total_pendapatan_personal' => $totalPendapatanPersonal,
            'total_pendapatan' => $totalPendapatan,
        ],
    ]);
}

        
    public function getLaporanPendapatan()
    {
        $nowJakarta = now('Asia/Jakarta');

        $laporanPendapatanByMonth = Reservasi::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(CASE WHEN reservasi.id_booking LIKE "P%" THEN transaksi_kamar.harga_total ELSE 0 END) as pendapatan_personal'),
            DB::raw('SUM(CASE WHEN reservasi.id_booking LIKE "G%" THEN transaksi_kamar.harga_total ELSE 0 END) as pendapatan_grup'),
            DB::raw('SUM(transaksi_fasilitas_tambahan.total_harga_fasilitas) as pendapatan_fasilitas'),
        )
            ->leftJoin('transaksi_kamar', 'reservasi.id_reservasi', '=', 'transaksi_kamar.id_reservasi')
            ->leftJoin('transaksi_fasilitas_tambahan', 'reservasi.id_reservasi', '=', 'transaksi_fasilitas_tambahan.id_reservasi')
            ->groupBy(DB::raw('MONTH(reservasi.created_at)'))
            ->orderBy(DB::raw('MONTH(reservasi.created_at)'))
            ->get();

        $result = [];
        $bulanArray = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        foreach ($bulanArray as $monthName) {
            $result[] = [
                'bulan' => $monthName,
                'pendapatan_grup' => 0,
                'pendapatan_personal' => 0,
                'pendapatan_per_bulan' => 0,
            ];
        }

        foreach ($laporanPendapatanByMonth as $item) {
            $monthName = date("F", mktime(0, 0, 0, $item->month, 1));
            $key = array_search($monthName, array_column($result, 'bulan'));
            if ($key !== false) {
                $result[$key]['pendapatan_grup'] = $item->pendapatan_grup;
                $result[$key]['pendapatan_personal'] = $item->pendapatan_personal;
                $result[$key]['pendapatan_per_bulan'] = $item->pendapatan_grup + $item->pendapatan_personal + $item->pendapatan_fasilitas;
            }
        }

        $totalPendapatanGrup = array_sum(array_column($result, 'pendapatan_grup'));
        $totalPendapatanPersonal = array_sum(array_column($result, 'pendapatan_personal'));
        $totalPendapatan = array_sum(array_column($result, 'pendapatan_per_bulan'));

        return response()->json([
            'status' => 'success',
            'message' => 'data laporan pendapatan per jenis tamu per bulan berhasil didapatkan',
            'data' => [
                'dataLaporan' => $result,
                'tanggal_cetak' => $nowJakarta->format('F d, Y'),
                'tahun' => $nowJakarta->year,
                'total_pendapatan_grup' => $totalPendapatanGrup,
                'total_pendapatan_personal' => $totalPendapatanPersonal,
                'total_pendapatan' => $totalPendapatan,
            ],
        ]);
    }

    public function getCustomerCountPerRoomType()
    {
        $result = Reservasi::select(
            'jenis_kamar.jenis_kamar',
            DB::raw('SUM(CASE WHEN SUBSTRING(reservasi.id_booking, 1, 1) = "P" THEN 1 ELSE 0 END) as Personal'),
            DB::raw('SUM(CASE WHEN SUBSTRING(reservasi.id_booking, 1, 1) = "G" THEN 1 ELSE 0 END) as `Group`'),
            DB::raw('SUM(1) as Total')
        )
            ->join('transaksi_kamar', 'reservasi.id_reservasi', '=', 'transaksi_kamar.id_reservasi')
            ->join('jenis_kamar', 'transaksi_kamar.id_jeniskamar', '=', 'jenis_kamar.id_jeniskamar')
            ->groupBy('jenis_kamar.jenis_kamar')
            ->get();
    
        $resultArray = [];
        $totalTamu = 0;
    
        foreach ($result as $item) {
            $totalTamu += $item->Total;
    
            $resultArray[] = [
                'jenis_kamar' => $item->jenis_kamar,
                'Group' => $item->Group,
                'Personal' => $item->Personal,
                'Total' => $item->Total,
            ];
        }
    
        $nowJakarta = now('Asia/Jakarta');
    
        return response()->json([
            'status' => 'success',
            'message' => 'Data laporan jumlah tamu per bulan berhasil didapatkan',
            'data' => [
                'dataLaporan' => $resultArray,
                'tanggal_cetak' => $nowJakarta->format('F d, Y'),
                'total_tamu' => $totalTamu,
                'bulan' => $nowJakarta->format('F'),
                'tahun' => $nowJakarta->year,
            ],
        ]);
    }
    

}

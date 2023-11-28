<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Reservasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LaporanController extends Controller
{
    public function getNewCustomer()
    {
        // Get the input year (tahun) from the request
        $tahun = Request::input('tahun');
    
        // Validate that the 'tahun' parameter is present
        if (!$tahun) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter \'tahun\' is required.',
            ], 400);
        }
    
        $nowJakarta = now('Asia/Jakarta');
    
        $newCustomersByMonth = Customer::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(id_customer) as jumlah_customer')
        )
        ->whereYear('created_at', '=', $tahun) // Filter by the input year
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->orderBy(DB::raw('MONTH(created_at)'))
        ->get();
    
        $result = [];
        $bulanArray = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
    
        foreach ($bulanArray as $monthName) {
            $result[] = [
                'bulan' => $monthName,
                'jumlah_customer' => 0,
            ];
        }
    
        foreach ($newCustomersByMonth as $newCustomer) {
            $monthIndex = $newCustomer->month - 1;
            $result[$monthIndex]['jumlah_customer'] = $newCustomer->jumlah_customer;
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'New customers by month retrieved successfully for the year ' . $tahun,
            'data' => [
                'tahun' => $tahun,
                'dataLaporan' => $result,
                'total_customer_baru' => array_sum(array_column($result, 'jumlah_customer')),
            ],
            'tanggal_cetak' => $nowJakarta->format('F d, Y'),
        ]);
    }
    
public function getPendapatanPerJenisTamuPerBulan()
{
    // Get the input year ('tahun') from the request
    $tahun = Request::input('tahun');

    // Validate that the 'tahun' parameter is present
    if (!$tahun) {
        return response()->json([
            'status' => 'error',
            'message' => 'Parameter \'tahun\' is required.',
        ], 400);
    }

    $result = Reservasi::select(
        DB::raw('MONTH(reservasi.created_at) as month'),
        DB::raw('SUM(CASE WHEN SUBSTRING(reservasi.id_booking, 1, 1) = "P" THEN COALESCE(tk.total_harga_kamar, 0) + COALESCE(tf.total_harga_fasilitas, 0) ELSE 0 END) as pendapatan_personal'),
        DB::raw('SUM(CASE WHEN SUBSTRING(reservasi.id_booking, 1, 1) = "G" THEN COALESCE(tk.total_harga_kamar, 0) + COALESCE(tf.total_harga_fasilitas, 0) ELSE 0 END) as pendapatan_grup'),
        DB::raw('SUM(COALESCE(tk.total_harga_kamar, 0) + COALESCE(tf.total_harga_fasilitas, 0)) as pendapatan_per_bulan')
    )
    ->leftJoin(DB::raw('(SELECT id_reservasi, SUM(harga_total) as total_harga_kamar FROM transaksi_kamar GROUP BY id_reservasi) as tk'), 'reservasi.id_reservasi', '=', 'tk.id_reservasi')
    ->leftJoin(DB::raw('(SELECT id_reservasi, SUM(total_harga_fasilitas) as total_harga_fasilitas FROM transaksi_fasilitas_tambahan GROUP BY id_reservasi) as tf'), 'reservasi.id_reservasi', '=', 'tf.id_reservasi')
    ->whereYear('reservasi.created_at', '=', $tahun) // Filter by the input year
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
        'message' => 'Data laporan pendapatan per jenis tamu per bulan berhasil didapatkan for the year ' . $tahun,
        'data' => [
            'dataLaporan' => $resultArray,
            'tanggal_cetak' => now('Asia/Jakarta')->format('F d, Y'),
            'tahun' => $tahun,
            'total_pendapatan_grup' => $totalPendapatanGrup,
            'total_pendapatan_personal' => $totalPendapatanPersonal,
            'total_pendapatan' => $totalPendapatan,
        ],
    ]);
}

public function getCustomerCountPerRoomType()
{
    // Get the input 'month' and 'year' from the request
    $month = Request::input('month');
    $year = Request::input('year');

    // Validate that the 'month' and 'year' parameters are present
    if (!$month || !$year) {
        return response()->json([
            'status' => 'error',
            'message' => 'Parameters \'month\' and \'year\' are required.',
        ], 400);
    }

    $result = Reservasi::select(
        'jenis_kamar.jenis_kamar',
        DB::raw('SUM(CASE WHEN SUBSTRING(reservasi.id_booking, 1, 1) = "P" THEN 1 ELSE 0 END) as Personal'),
        DB::raw('SUM(CASE WHEN SUBSTRING(reservasi.id_booking, 1, 1) = "G" THEN 1 ELSE 0 END) as `Group`'),
        DB::raw('SUM(1) as Total')
    )
    ->join('transaksi_kamar', 'reservasi.id_reservasi', '=', 'transaksi_kamar.id_reservasi')
    ->join('jenis_kamar', 'transaksi_kamar.id_jeniskamar', '=', 'jenis_kamar.id_jeniskamar')
    ->whereYear('reservasi.tanggal_checkin', '=', $year)
    ->whereMonth('reservasi.tanggal_checkin', '=', $month)
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

    public function getTopCustomersWithMostBookings()
    {
        // Get the input year (tahun) from the request
        $tahun = Request::input('tahun');
    
        // Validate that the 'tahun' parameter is present
        if (!$tahun) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter \'tahun\' is required.',
            ], 400);
        }
    
        $result = Customer::select(
                'customer.id_customer',
                'customer.nama',
                DB::raw('COUNT(reservasi.id_reservasi) as jumlah_reservasi'),
                DB::raw('SUM(COALESCE(transaksi_kamar.harga_total, 0) + COALESCE(transaksi_fasilitas_tambahan.total_harga_fasilitas, 0)) as total_pembayaran')
            )
            ->leftJoin('reservasi', 'customer.id_customer', '=', 'reservasi.id_customer')
            ->leftJoin(DB::raw('(SELECT id_reservasi, SUM(harga_total) as harga_total FROM transaksi_kamar GROUP BY id_reservasi) as transaksi_kamar'), 'reservasi.id_reservasi', '=', 'transaksi_kamar.id_reservasi')
            ->leftJoin(DB::raw('(SELECT id_reservasi, SUM(total_harga_fasilitas) as total_harga_fasilitas FROM transaksi_fasilitas_tambahan GROUP BY id_reservasi) as transaksi_fasilitas_tambahan'), 'reservasi.id_reservasi', '=', 'transaksi_fasilitas_tambahan.id_reservasi')
            ->whereYear('reservasi.created_at', '=', $tahun) // Filter by the input year
            ->groupBy('customer.id_customer', 'customer.nama')
            ->orderByDesc('jumlah_reservasi')
            ->limit(5)
            ->get();
    
        $resultArray = [];
        $nowJakarta = now('Asia/Jakarta');
    
        foreach ($result as $item) {
            $resultArray[] = [
                'id_customer' => $item->id_customer,
                'nama' => $item->nama,
                'jumlah_reservasi' => $item->jumlah_reservasi,
                'total_pembayaran' => number_format($item->total_pembayaran, 0, ',', '.'),
            ];
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'Top 5 customers with most bookings retrieved successfully for the year ' . $tahun,
            'data' => [
                'dataLaporan' => $resultArray,
                'tanggal_cetak' => $nowJakarta->format('F d, Y'),
            ],
        ]);
    }
    
    
    

}

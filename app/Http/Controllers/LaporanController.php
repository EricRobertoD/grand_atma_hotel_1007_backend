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
}

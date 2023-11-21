<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservasi;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use App\Models\Kamar;
use App\Models\JenisKamar;
use App\Models\TarifMusim;
use App\Models\TransaksiKamar;
use Carbon\Carbon;

class ReservasiController extends Controller
{
    public function index(Request $request)
    {
        $id = auth()->user()->id_customer;
        $reservasi = Reservasi::where('id_customer', $id)->with('Customer')->with('TransaksiFasilitasTambahan.FasilitasTambahan')->with('TransaksiKamar.Kamar.JenisKamar')->with('NotaPelunasan.Pegawai')->get();

        return response([
            'message' => 'Retrieve all Reservasi Success',
            'data' => $reservasi
        ], 200);
    }

    public function indexGrup()
    {
        $reservasi = Reservasi::with('Customer')->with('TransaksiFasilitasTambahan.FasilitasTambahan')->with('TransaksiKamar.Kamar.JenisKamar')->with('NotaPelunasan')->where('id_booking', 'LIKE', 'G%')->get();

        return response([
            'message' => 'Retrieve all "grup" reservasis successfully',
            'data' => $reservasi,
        ], 200);
    }

    public function indexCheckIn()
    {
        $reservasi = Reservasi::with('Customer')
            ->with('TransaksiFasilitasTambahan.FasilitasTambahan')
            ->with('TransaksiKamar.Kamar.JenisKamar')
            ->with('NotaPelunasan')
            ->where('status', '!=', 'Reservasi')
            ->get();

        return response([
            'message' => 'Retrieve all "grup" reservasis with "Lunas" status successfully',
            'data' => $reservasi,
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

    public function store(Request $request)
    {
        $id_customer = auth()->user()->id_customer;
        $tanggal_mulai = $request->input('tanggal_mulai');
        $tanggal_selesai = $request->input('tanggal_selesai');

        // Extract the input for each room type
        $roomTypes = [
            'Superior' => $request->input('superior'),
            'Double Deluxe' => $request->input('double_deluxe'),
            'Executive Deluxe' => $request->input('executive_deluxe'),
            'Junior Suite' => $request->input('junior_suite'),
        ];

        $validator = Validator::make($request->all(), [
            'dewasa' => 'required|integer',
            'anak' => 'required|integer',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            // Add validation rules for each room type
            'superior' => 'integer',
            'double_deluxe' => 'integer',
            'executive_deluxe' => 'integer',
            'junior_suite' => 'integer',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $data = $this->kamarAvailable($request);

        // Check if any room type is selected
        $selectedRoomTypes = array_filter($roomTypes, function ($quantity) {
            return $quantity > 0;
        });

        if (empty($selectedRoomTypes)) {
            return response([
                'message' => 'No room types selected or insufficient quantity',
            ], 400);
        }

        // Initialize an array to store the reserved rooms
        $reservedRooms = [];

        // Create a single Reservasi record for all selected room types
        $reservasi = new Reservasi([
            'id_customer' => $id_customer,
            'id_booking' => 'P' . now('Asia/Jakarta')->format('mdy') . '-' . $this->generateIncrementedNumber(),
            'status' => 'Reservasi',
            'dewasa' => $request->input('dewasa'),
            'anak' => $request->input('anak'),
            'total_deposit' => '300000',
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
        ]);

        $reservasi->save();

        $availabilityFailed = false; // Initialize availability check
        $totalPrice = 0; // Initialize total price

        foreach ($selectedRoomTypes as $jenis => $quantity) {
            $availability = $data['data'][$jenis];
            $availableRooms = $availability['rooms'];
            $tersedia = $availability['tersedia'];

            if ($quantity > $tersedia) {
                // If requested quantity is more than available, set availabilityFailed to true
                $availabilityFailed = true;
                break; // Stop processing other room types
            }

            for ($i = 0; $i < $quantity; $i++) {
                if (count($availableRooms) > 0) {
                    // Get the first available room
                    $firstAvailableRoom = reset($availableRooms);

                    // Remove the first available room from the list
                    array_shift($availableRooms);

                    // Calculate the number of days between 'tanggal_mulai' and 'tanggal_selesai'
                    $daysDiff = now('Asia/Jakarta')->parse($tanggal_mulai)->diffInDays(now('Asia/Jakarta')->parse($tanggal_selesai));

                    // Determine the room tariff (tarif_default or tarif_season)
                    $roomTariff = isset($availability['tarif_season']) ? $availability['tarif_season'] : $availability['tarif_default'];

                    // Calculate the total price
                    $hargaTotal = $daysDiff * $roomTariff;

                    // Create and save the TransaksiKamar record with the reserved room
                    $transaksiKamar = new TransaksiKamar([
                        'id_reservasi' => $reservasi->id_reservasi,
                        'id_jeniskamar' => $firstAvailableRoom['id_jeniskamar'],
                        'id_kamar' => $firstAvailableRoom['id_kamar'],
                        'harga_total' => $hargaTotal,
                        'jumlah' => 1, // You may adjust the quantity as needed
                    ]);

                    $transaksiKamar->save();

                    // Add the reserved room to the list
                    $reservedRooms[] = $firstAvailableRoom;

                    // Add to the total price
                    $totalPrice += $hargaTotal;
                }
            }
        }

        if ($availabilityFailed) {
            // Rollback the transaction and return an error response
            $reservasi->delete();

            return response([
                'message' => 'Not enough available rooms for the specified room types',
            ], 400);
        }

        if (empty($reservedRooms)) {
            return response([
                'message' => 'No available rooms for the specified room types or insufficient quantity',
            ], 400);
        }

        // Update the total deposit with the calculated total price
        $reservasi->total_deposit = $totalPrice;
        $reservasi->save();

        return response([
            'status' => 'success',
            'message' => 'Reservasi created successfully',
            'reserved_rooms' => $reservedRooms, // Provide the list of reserved rooms
        ], 201);
    }

    public function storeGrup(Request $request)
    {
        $tanggal_mulai = $request->input('tanggal_mulai');
        $tanggal_selesai = $request->input('tanggal_selesai');

        // Extract the input for each room type
        $roomTypes = [
            'Superior' => $request->input('superior'),
            'Double Deluxe' => $request->input('double_deluxe'),
            'Executive Deluxe' => $request->input('executive_deluxe'),
            'Junior Suite' => $request->input('junior_suite'),
        ];

        $validator = Validator::make($request->all(), [
            'id_customer' => 'required',
            'dewasa' => 'required|integer',
            'anak' => 'required|integer',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            // Add validation rules for each room type
            'superior' => 'integer',
            'double_deluxe' => 'integer',
            'executive_deluxe' => 'integer',
            'junior_suite' => 'integer',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $data = $this->kamarAvailable($request);

        // Check if any room type is selected
        $selectedRoomTypes = array_filter($roomTypes, function ($quantity) {
            return $quantity > 0;
        });

        if (empty($selectedRoomTypes)) {
            return response([
                'message' => 'No room types selected or insufficient quantity',
            ], 400);
        }

        // Initialize an array to store the reserved rooms
        $reservedRooms = [];

        // Create a single Reservasi record for all selected room types
        $reservasi = new Reservasi([
            'id_customer' => $request->input('id_customer'),
            'id_booking' => 'G' . now('Asia/Jakarta')->format('mdy') . '-' . $this->generateIncrementedNumber(),
            'status' => 'Reservasi',
            'dewasa' => $request->input('dewasa'),
            'anak' => $request->input('anak'),
            'total_deposit' => '300000',
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
        ]);

        $reservasi->save();

        $availabilityFailed = false; // Initialize availability check
        $totalPrice = 0; // Initialize total price

        foreach ($selectedRoomTypes as $jenis => $quantity) {
            $availability = $data['data'][$jenis];
            $availableRooms = $availability['rooms'];
            $tersedia = $availability['tersedia'];

            if ($quantity > $tersedia) {
                // If requested quantity is more than available, set availabilityFailed to true
                $availabilityFailed = true;
                break; // Stop processing other room types
            }

            for ($i = 0; $i < $quantity; $i++) {
                if (count($availableRooms) > 0) {
                    // Get the first available room
                    $firstAvailableRoom = reset($availableRooms);

                    // Remove the first available room from the list
                    array_shift($availableRooms);

                    // Calculate the number of days between 'tanggal_mulai' and 'tanggal_selesai'
                    $daysDiff = now('Asia/Jakarta')->parse($tanggal_mulai)->diffInDays(now('Asia/Jakarta')->parse($tanggal_selesai));

                    // Determine the room tariff (tarif_default or tarif_season)
                    $roomTariff = isset($availability['tarif_season']) ? $availability['tarif_season'] : $availability['tarif_default'];

                    // Calculate the total price
                    $hargaTotal = $daysDiff * $roomTariff;

                    // Create and save the TransaksiKamar record with the reserved room
                    $transaksiKamar = new TransaksiKamar([
                        'id_reservasi' => $reservasi->id_reservasi,
                        'id_jeniskamar' => $firstAvailableRoom['id_jeniskamar'],
                        'id_kamar' => $firstAvailableRoom['id_kamar'],
                        'harga_total' => $hargaTotal,
                        'jumlah' => 1, // You may adjust the quantity as needed
                    ]);

                    $transaksiKamar->save();

                    // Add the reserved room to the list
                    $reservedRooms[] = $firstAvailableRoom;

                    // Add to the total price
                    $totalPrice += $hargaTotal;
                }
            }
        }

        if ($availabilityFailed) {
            // Rollback the transaction and return an error response
            $reservasi->delete();

            return response([
                'message' => 'Not enough available rooms for the specified room types',
            ], 400);
        }

        if (empty($reservedRooms)) {
            return response([
                'message' => 'No available rooms for the specified room types or insufficient quantity',
            ], 400);
        }

        // Update the total deposit with the calculated total price
        $reservasi->total_deposit = $totalPrice;
        $reservasi->save();

        return response([
            'status' => 'success',
            'message' => 'Reservasi created successfully',
            'reserved_rooms' => $reservedRooms, // Provide the list of reserved rooms
        ], 201);
    }




    public function storeAdd(Request $request)
    {
        $id_customer = auth()->user()->id_customer;
        $jenis_kamar = $request->input('jenis_kamar');
        $id_booking = $request->input('id_booking'); // Assuming user inputs id_booking

        $validator = Validator::make($request->all(), [
            'dewasa' => 'required|integer',
            'anak' => 'required|integer',
            'jenis_kamar' => 'required',
            'id_booking' => 'required', // Ensure id_booking is required
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Check if the id_booking exists and retrieve the corresponding Reservasi record
        $existingReservasi = Reservasi::where('id_booking', $id_booking)->first();

        if (!$existingReservasi) {
            return response([
                'message' => 'The provided id_booking does not exist.',
            ], 400);
        }

        // Retrieve the 'tanggal_mulai' and 'tanggal_selesai' from the existing Reservasi
        $tanggal_mulai = $existingReservasi->tanggal_mulai;
        $tanggal_selesai = $existingReservasi->tanggal_selesai;
        $request->merge([
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
        ]);

        $data = $this->kamarAvailable($request);

        if (isset($data['data'][$jenis_kamar])) {
            $availability = $data['data'][$jenis_kamar];

            // Check if rooms are available
            if ($availability['tersedia'] > 0) {
                $availableRooms = $availability['rooms'];

                // Get the first available room
                if (!empty($availableRooms)) {
                    $firstAvailableRoom = reset($availableRooms);

                    // Create and save the transaksi_kamar record linked to the existing id_booking
                    $transaksiKamar = new TransaksiKamar([
                        'id_reservasi' => $existingReservasi->id_reservasi,
                        'id_jeniskamar' => $firstAvailableRoom['id_jeniskamar'],
                        'id_kamar' => $firstAvailableRoom['id_kamar'],
                        'harga_total' => 123123, // Calculate the total price
                        'jumlah' => 1, // You may adjust the quantity as needed
                    ]);

                    $transaksiKamar->save();

                    return response([
                        'status' => 'success',
                        'message' => 'Transaksi kamar created successfully and linked to existing id_booking',
                        'data' => $existingReservasi,
                    ], 201);
                }
            }
        }

        return response([
            'message' => 'No available rooms for the specified jenis_kamar',
        ], 400);
    }




    private function generateIncrementedNumber()
    {
        $lastReservasi = Reservasi::orderBy('id_reservasi', 'desc')->first();

        if ($lastReservasi) {
            $lastIdBooking = $lastReservasi->id_booking;
            $lastNumber = (int) substr($lastIdBooking, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%03d', $nextNumber);
    }

    public function updateBayar(Request $request, Reservasi $reservasi)
    {
        // gambar
        $validator = Validator::make($request->all(), [
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // validate
        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $request->validate([
            'status' => 'required'
        ]);

        // handle file upload
        if ($request->hasFile('gambar')) {
            // get filename with extension
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            // get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // get just extension
            $extension = $request->file('gambar')->getClientOriginalExtension();
            // filename to store
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            // upload image
            $path = $request->file('gambar')->storeAs('gambar', $fileNameToStore, 'images');
        } else {
            $fileNameToStore = 'noimage.jpg';
        }

        $reservasi->update([
            'status' => $request->input('status'),
            'upload_gambar' => $fileNameToStore,
        ]);

        return response([
            'status' => 'success',
            'message' => 'Status updated successfully',
            'data' => $reservasi,
        ], 200);
    }

    
public function updateStatus(Request $request, Reservasi $reservasi)
{
    $request->validate([
        'status' => 'required',
    ]);

    $status = $request->input('status');

    if ($status == 'Check In' || $status == 'Check Out') {
        $dateField = ($status == 'Check In') ? 'tanggal_checkin' : 'tanggal_checkout';
        $reservasi->update([
            'status' => $status,
            $dateField => Carbon::now('Asia/Jakarta')->toDateTimeString(),
        ]);
    } else {
        $reservasi->update([
            'status' => $status,
        ]);
    }

    return response([
        'status' => 'success',
        'message' => 'Status updated successfully',
        'data' => $reservasi,
    ], 200);
}


    public function kamarAvailable(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
        ]);


        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $tanggal_mulai = $request->input('tanggal_mulai');
        $tanggal_selesai = $request->input('tanggal_selesai');

        $tarifMusim = TarifMusim::with('JenisKamar', 'Musim')
            ->whereHas('Musim', function ($query) use ($tanggal_mulai, $tanggal_selesai) {
                $query->where('tanggal_mulai_musim', '<=', $tanggal_mulai)
                    ->where('tanggal_selesai_musim', '>=', $tanggal_selesai);
            })
            ->get();

        $roomTypes = JenisKamar::with('kamar')
            ->get();

        $reservations = Reservasi::select('reservasi.*')
            ->with('TransaksiKamar')
            ->where(function ($query) use ($tanggal_mulai, $tanggal_selesai) {
                $query->where('tanggal_mulai', '>=', $tanggal_mulai)
                    ->where('tanggal_selesai', '<=', $tanggal_selesai);
            })
            ->orWhere(function ($query) use ($tanggal_mulai, $tanggal_selesai) {
                $query->where('tanggal_selesai', '>=', $tanggal_selesai)
                    ->where('tanggal_selesai', '<=', $tanggal_selesai);
            })
            ->orWhere(function ($query) use ($tanggal_mulai, $tanggal_selesai) {
                $query->where('tanggal_mulai', '<=', $tanggal_mulai)
                    ->where('tanggal_selesai', '>=', $tanggal_selesai);
            })
            ->orWhere(function ($query) use ($tanggal_mulai, $tanggal_selesai) {
                $query->where('tanggal_selesai', '>', $tanggal_selesai)
                    ->where('tanggal_mulai', '<', $tanggal_selesai);
            })
            ->orWhere(function ($query) use ($tanggal_mulai, $tanggal_selesai) {
                $query->where('tanggal_mulai', '<', $tanggal_mulai)
                    ->where('tanggal_selesai', '>', $tanggal_mulai);
            })
            ->get();

        $kamarTersedia = [];

        // Loop through room types
        foreach ($roomTypes as $roomType) {
            $totalRooms = count($roomType->kamar);
            $bookedRooms = 0;
            $rooms = $roomType->kamar->toArray();

            // Loop through reservations
            foreach ($reservations as $reservation) {
                foreach ($reservation->TransaksiKamar as $transaksiKamar) {
                    if ($transaksiKamar->id_jeniskamar === $roomType->id_jeniskamar) {
                        // Calculate booked rooms
                        $bookedRooms += 1;
                        $rooms = array_filter($rooms, function ($room) use ($transaksiKamar) {
                            return $room['id_kamar'] !== $transaksiKamar->id_kamar;
                        });
                    }
                }
            }

            // Calculate available rooms for the room type
            $availableRooms = $totalRooms - $bookedRooms;

            // get tarif musim by id_jeniskamar
            $tarifMusimForRoomType = $tarifMusim->first(function ($item) use ($roomType) {
                return $item->id_jeniskamar == $roomType->id_jeniskamar;
            });

            $kamarTersedia[$roomType->jenis_kamar] = [
                'tersedia' => $availableRooms,
                'rooms' => $rooms,
            ];

            if ($tarifMusimForRoomType) {
                $kamarTersedia[$roomType->jenis_kamar]['tarif_season'] = $tarifMusimForRoomType->tarif_musim ?? 0;
            } else {
                $kamarTersedia[$roomType->jenis_kamar]['tarif_default'] = $roomType->harga_default;
            }
        }

        return [
            'message' => 'Kamar availability for the specified period',
            'data' => $kamarTersedia,
        ];
    }

    public function getPembatalan()
    {
        $id = auth()->user()->id_customer;

        $reservasiPembatalan = Reservasi::where('id_customer', $id)
            ->whereDate('tanggal_mulai', '>', now('Asia/Jakarta')->toDateString()) // Check if 'tanggal_mulai' is before today
            ->with('Customer')
            ->with('TransaksiFasilitasTambahan.FasilitasTambahan')
            ->with('TransaksiKamar.Kamar.JenisKamar')
            ->with('NotaPelunasan.Pegawai')
            ->get();

        return response([
            'message' => 'Retrieve all Reservasi Pembatalan Success',
            'data' => $reservasiPembatalan,
        ], 200);
    }

    public function getPembatalanGrup()
    {
        $reservasiPembatalan = Reservasi::whereHas('Customer', function ($query) {
            $query->where('tipe', 'Grup');
        })
            ->whereDate('tanggal_mulai', '>', now('Asia/Jakarta')->toDateString())
            ->with('Customer')
            ->with('TransaksiFasilitasTambahan.FasilitasTambahan')
            ->with('TransaksiKamar.Kamar.JenisKamar')
            ->with('NotaPelunasan.Pegawai')
            ->get();

        return response([
            'message' => 'Retrieve all Reservasi Pembatalan Success',
            'data' => $reservasiPembatalan,
        ], 200);
    }



    public function destroy(Reservasi $reservasi)
    {
        if ($reservasi->status === 'Lunas') {
            if (now('Asia/Jakarta') < now('Asia/Jakarta')->parse($reservasi->tanggal_mulai)) {
                $daysDifference = now('Asia/Jakarta')->parse($reservasi->tanggal_mulai)->diffInDays(now('Asia/Jakarta'));

                if ($daysDifference > 7) {
                    $message = 'Karena batal sebelum h-7 uang akan dikembalikan';
                } else {
                    $message = 'Karena batal h-7 gada uang yang dikembalikan';
                }

                $reservasi->delete();

                return response([
                    'status' => 'success',
                    'message' => $message,
                ], 200);
            }

            return response([
                'status' => 'error',
                'message' => 'Cannot delete past reservations',
            ], 400);
        } elseif ($reservasi->status === 'Reservasi') {
            $reservasi->delete();

            return response([
                'status' => 'success',
                'message' => 'Berhasil Batal, belum membayar',
            ], 200);
        }

        return response([
            'status' => 'error',
            'message' => 'Invalid reservation status',
        ], 400);
    }
}

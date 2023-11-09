<!DOCTYPE html>
<html>

<head>
    <title>Tanda Terima Reservasi untuk {{ $reservasi->customer->nama }}</title>
    <style>
        /* Gaya CSS Anda dapat disesuaikan di sini */
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
        }

        .content {
            margin-top: 10px;
        }

        .item {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            text-align: center;
            font-weight: bold;
        }

        .total {
            font-weight: bold;
            border-top: 1px solid #000;
            padding: 5px 0;
        }

        .logo {
            text-align: center;
        }

        .logo img {
            max-width: 400px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="https://firebasestorage.googleapis.com/v0/b/capstone-cdb77.appspot.com/o/logo.png?alt=media&token=c134b6af-1e0d-434e-b381-dcd077196515">
            </div>
            <p>Jl. P. Mangkubumi No.18, Yogyakarta 55233</p>
            <p>Telp. (0274) 487711</p>
        </div>
        <div class="content">
            <div class="item">
                <span>TANDA TERIMA PEMESANAN</span>
            </div>
        </div>
        <div class="footer">
            <div>
                <table>
                    <tr>
                        <td>ID Booking </td>
                        <td>: {{ $reservasi->id_booking }} </td>
                    </tr>
                    <tr>
                        <td>Tanggal </td>
                        <td>: {{ $tanggal_sekarang }} </td>
                    </tr>
                    @if ($reservasi->id_pegawai != null)
                    <tr>
                        <td>PIC</td>
                        <td>: {{ $reservasi->pegawai->nama_pegawai }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <table style="margin-top: 8px;">
                <tr>
                    <td>Nama</td>
                    <td>: {{ $reservasi->customer->nama }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>: {{ $reservasi->customer->alamat }}</td>
                </tr>
            </table>
        </div>
        <div class="content">
            <div class="item">
                <span>DETAIL PEMESANAN</span>
            </div>
        </div>
        <div class="footer">
            <div>
                <table>
                    <tr>
                        <td>Check In</td>
                        <td>: {{ $reservasi->tanggal_mulai }} </td>
                    </tr>
                    <tr>
                        <td>Check Out</td>
                        <td>: {{ $reservasi->tanggal_selesai }} </td>
                    </tr>
                    <tr>
                        <td>Dewasa</td>
                        <td>: {{ $reservasi->dewasa }}</td>
                    </tr>
                    <tr>
                        <td>Anak-anak</td>
                        <td>: {{ $reservasi->anak }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Pembayaran</td>
                        <td>: {{ $reservasi->tanggal_pembayaran }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="content">
            <div class="item">
                <span><br /></span>
            </div>
        </div>
        <div style="margin-top: 8px">
        
        <table style="border-collapse: collapse; width: 100%;">
    <tr>
        <th style="border: 1px solid black; padding: 5px;">Jenis Kamar</th>
        <th style="border: 1px solid black; padding: 5px;">Bed</th>
        <th style="border: 1px solid black; padding: 5px;">Jumlah</th>
        <th style="border: 1px solid black; padding: 5px;">Harga</th>
        <th style="border: 1px solid black; padding: 5px;">Total</th>
    </tr>

    @php
    $jenis_kamar = null;
    $jumlah = 0;
    $harga = 0;
    @endphp

    @foreach ($reservasi->TransaksiKamar as $kamar)
        @if ($kamar->Kamar->JenisKamar['jenis_kamar'] !== $jenis_kamar)
            @if ($jenis_kamar !== null)
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">{{$jenis_kamar}}</td>
                    <td style="border: 1px solid black; padding: 5px;">{{$bed}}</td>
                    <td style="border: 1px solid black; padding: 5px;">{{$jumlah}}</td>
                    <td style="border: 1px solid black; padding: 5px;">Rp. {{ number_format($harga, 0, ',') }}</td>
                    <td style="border: 1px solid black; padding: 5px;">Rp. {{ number_format($harga, 0, ',') }}</td>
                </tr>
            @endif
            @php
            $jenis_kamar = $kamar->Kamar->JenisKamar['jenis_kamar'];
            $bed = $kamar->kamar['pilih_bed'];
            $jumlah = 1;
            $harga = $kamar->harga_total;
            @endphp
        @else
            @php
            $jumlah++;
            $harga += $kamar->harga_total;
            @endphp
        @endif
    @endforeach

    @if ($jenis_kamar !== null)
        <tr>
            <td style="border: 1px solid black; padding: 5px;">{{$jenis_kamar}}</td>
            <td style="border: 1px solid black; padding: 5px;">{{$bed}}</td>
            <td style="border: 1px solid black; padding: 5px;">{{$jumlah}}</td>
            <td style="border: 1px solid black; padding: 5px;">Rp. {{ number_format($harga, 0, ',') }}</td>
            <td style="border: 1px solid black; padding: 5px;">Rp. {{ number_format($harga, 0, ',') }}</td>
        </tr>
    @endif

    @php
    $total = 0;
    foreach ($reservasi->TransaksiKamar as $kamar) {
        $total += $kamar->harga_total;
    }
    @endphp

    <tr>
        <td colspan="4" style="border: 1px solid black; text-align: right; padding: 5px;">Total</td>
        <td style="border: 1px solid black; padding: 5px;">Rp. {{ number_format($total, 0, ',') }}</td>
    </tr>
</table>




        </div>
        <div class="footer" style="margin-top: 32px">
            <div>
                <table>
                    <tr>
                        <td>Permintaan Khusus :</td>
                    </tr>
                    <tr>
                        <td>{{ $reservasi->permintaan_khusus }} </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
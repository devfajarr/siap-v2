@php
    $alamatInstansi = $permohonan->alamat_instansi;

    function formatAlamat($alamat) {
        $pos_koma_terakhir = strrpos($alamat, ',');

        if ($pos_koma_terakhir !== false) {
            $pos_koma_sebelum_terakhir = strrpos(substr($alamat, 0, $pos_koma_terakhir), ',');

            if ($pos_koma_sebelum_terakhir !== false) {
                $baris1 = substr($alamat, 0, $pos_koma_sebelum_terakhir);
                $baris2 = substr($alamat, $pos_koma_sebelum_terakhir + 1);
            } else {
                $baris1 = substr($alamat, 0, $pos_koma_terakhir);
                $baris2 = substr($alamat, $pos_koma_terakhir + 1);
            }
        } else {
            $baris1 = $alamat;
            $baris2 = "";
        }

        return [trim($baris1), trim($baris2)];
    }

    [$barisInstansi1, $barisInstansi2] = formatAlamat($alamatInstansi);
@endphp


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Ijin PKL</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
            line-height: 1.6;
        }
        .footer {
            text-align: left;
            margin-bottom: 20px;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table td {
            vertical-align: top;
            padding: 10px;
        }
        .signature-right {
            text-align: left;
        }
        .info-table {
            width: 100%;
            max-width: 400px;
        }
        .info-table td:first-child {
            width: 120px;
        }
        @page {
            size: A4;
            margin: 1.9cm;
        }
        @media screen {
            body {
                display: none;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>
<div class="header" style="margin-top: 2cm">
    <p>No: {{ $permohonan->no_surat }}</p>
    <p style="margin-top: -18px">Lamp: -</p>
</div>


    <div class="content">
        <p style="line-height: 1.2; margin-bottom: 5px;">
            Kepada Yth.<br>
            Pimpinan {{ $permohonan->nama_instansi }}<br>
            {{ $barisInstansi1 }}<br>
            {{ $barisInstansi2 }}
        </p>


        <p>Dengan Hormat,</p>

        <p style="font-weight: bold;text-align:center">PERMOHONAN IJIN PRAKTIK KERJA LAPANGAN (PKL)</p>

        <p style="text-align: justify;margin-top:-18px">Guna mendapatkan pengalaman praktik bagi mahasiswa dan dalam rangka melengkapi syarat kelulusan Program {{ $permohonan->mahasiswa->kelas->prodi->jenjang == 'D3' ? 'Diploma III' : 'Diploma IV' }} Politeknik Sawunggalih Aji, bersama ini kami mohon Bapak/Ibu berkenan memberikan ijin kepada mahasiswa kami untuk melakukan PKL di {{  $permohonan->nama_instansi }} mulai tanggal {{ \Carbon\Carbon::parse($permohonan->tanggal_mulai)->locale('id')->translatedFormat('d F Y') }}
            sampai dengan {{ \Carbon\Carbon::parse($permohonan->tanggal_selesai)->locale('id')->translatedFormat('d F Y') }}
            .</p>

        <p style="margin-top:-20px">Adapun Mahasiswa yang kami maksud adalah:</p>
        <table class="info-table" style="margin-left: 50px">
            <tr>
                <td>Nama</td>
                <td>: {{ $permohonan->mahasiswa->nama_lengkap }}</td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>: {{ $permohonan->mahasiswa->nim }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>: {{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</td>
            </tr>
        </table>

        <p style="text-align: justify">Demikian permohonan ini kami sampaikan, untuk selanjutnya kami menunggu konfirmasi dari Bapak/Ibu. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
    </div>

    <table style="width: 100%; text-align: left; border-collapse: collapse;">
        <tr>
            <td style="width: 20%;"></td>
            <td style="width: 35%;"></td>
            <td style="width: 45%;">
                <p>Purworejo, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin-top:-22px">POLITEKNIK SAWUNGGALIH AJI</p>
                <br><br>
                <p style="margin-bottom: 2px">{{$direktur->nama}}</p>
                <p style="margin-top:-10px">Direktur</p>
            </td>
        </tr>
    </table>
<p style="position: absolute; bottom: 0cm; font-size: 9pt;">
    {{ Request::fullUrl() }}
</p>

</body>
</html>

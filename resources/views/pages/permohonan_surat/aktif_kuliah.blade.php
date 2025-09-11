@php
    $alamat = $permohonan->mahasiswa->alamat;
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

    [$baris1, $baris2] = formatAlamat($alamat);
    [$barisInstansi1, $barisInstansi2] = formatAlamat($alamatInstansi);
@endphp



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
        }

        @page {
            size: A4;
            margin: 2.54cm;
        }

        p, td {
            line-height: 1;
        }

        .tanda-tangan {
            text-align: right;
            margin-top: 50px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 5px;
        }
        

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
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

<h2 style="text-align: center; font-size:12pt">S U R A T &nbsp; K E T E R A N G A N</h2>
<p style="text-align: center; font-size:12pt; margin-top:-15px">No. {{$permohonan->no_surat}}</p>

<p>Yang bertanda tangan di bawah ini:</p>

<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="width: 35%; padding-right: 3px;">Nama</td>
        <td style="width: 0.5%; text-align: left;">:</td>
        <td style="width: 54.5%; padding-left: 2px;">{{ $direktur->nama }}</td>
    </tr>
    <tr>
        <td style="padding-right: 3px;">Jabatan</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">Direktur</td>
    </tr>
</table>


<p>Dengan ini menerangkan, bahwa:</p>

<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="width: 35%; padding-right: 3px;">Nama Mahasiswa</td>
        <td style="width: 0.5%; text-align: left;">:</td>
        <td style="width: 54.5%; padding-left: 2px;">{{ $permohonan->mahasiswa->nama_lengkap }}</td>
    </tr>
    <tr>
        <td style="padding-right: 3px;">Tempat/Tgl. Lahir</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $permohonan->mahasiswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->translatedFormat('d F Y') }}
</td>
    </tr>
    <tr>
        <td style="padding-right: 3px;">Alamat</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $baris1 }}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td style="padding-left: 2px;">{{ $baris2 }}</td>
    </tr>
    <tr>
        <td style="padding-right: 3px;">Anak dari</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $permohonan->nama_orang_tua }}</td>
    </tr>
    <tr>
        <td style="padding-right: 3px;">Instansi</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $permohonan->nama_instansi }}</td>
    </tr>
    <tr>
        <td style="padding-right: 3px;">NIP/Nopen/NRP</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $permohonan->nip }}</td>
    </tr>
    <tr>
        <td style="padding-right: 3px;">Pangkat/Golongan</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $permohonan->pangkat_atau_golongan ?? '-' }}</td>
    </tr>
    <tr>
        <td style="padding-right: 3px;">Alamat Instansi</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{$barisInstansi1}}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td style="padding-left: 2px;">{{$barisInstansi2}}</td>
    </tr>
</table>


<p>Adalah benar-benar mahasiswa pada:</p>

<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="width: 35%; padding-right: 3px;">Program Studi</td>
        <td style="width: 0.5%; text-align: left;">:</td>
        <td style="width: 54.5%; padding-left: 2px;">{{ $permohonan->mahasiswa->kelas->prodi->jenjang }} {{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</td>
    </tr>
    <tr>
        <td style="white-space: nowrap; padding-right: 3px;">NIM</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $permohonan->mahasiswa->nim}}</td>
    </tr>
    <tr>
        <td style="white-space: nowrap; padding-right: 3px;">Tahun Masuk Perguruan Tinggi</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $permohonan->mahasiswa->tahun_masuk }}</td>
    </tr>
    <tr>
        <td style="white-space: nowrap; padding-right: 3px;">Semester</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $permohonan->mahasiswa->kelas->semester->semester }}</td>
    </tr>
    <tr>
        <td style="white-space: nowrap; padding-right: 3px;">Tahun Akademik</td>
        <td style="text-align: left;">:</td>
        <td style="padding-left: 2px;">{{ $tahunAkademik->tahun_akademik }}</td>
    </tr>
</table>




<p>Demikian surat keterangan ini kami sampaikan agar dapat digunakan sebagaimana mestinya.</p>

<table style="width: 100%; text-align: left; border-collapse: collapse;">
    <tr>
        <td style="width: 20%;"></td>
        <td style="width: 35%;"></td>
        <td style="width: 45%;">
            <p>Purworejo, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin-top:-10px">POLITEKNIK SAWUNGGALIH AJI</p>
            <br><br><br>
            <p style="margin-bottom: 2px">{{$direktur->nama}}</p>
            <p style="margin-top:-2px">Direktur</p>
        </td>
    </tr>
</table>


</body>
</html>
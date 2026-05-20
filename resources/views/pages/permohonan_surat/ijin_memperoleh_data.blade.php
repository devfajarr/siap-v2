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
    <title>Permohonan Ijin Observasi</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
            line-height: 1.5;
        }
        .header, .footer {
            text-align: center;
        }
        .content {
            margin-top: 20px;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
        }
        .content td {
            vertical-align: top;
            padding: 2px;
        }
        .signature {
            margin-top: 40px;
            text-align: right;
        }
        @page {
            size: A4;
            margin-top:1cm;
            margin-bottom:1cm;
            margin-left:2cm;
            margin-right:1.5cm;
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
    <div class="container">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%;">No. : 355/AP.08/DIR/XI/2024</td>
                <td style="width: 50%; text-align: right;">Purworejo, 01 Nopember 2024</td>
            </tr>
            <tr>
                <td>Lamp : satu lembar</td>
                <td></td>
            </tr>
        </table>
        
        
        <br>
        
        <p>Kepada
            <br>
        Yth. Pimpinan {{ $permohonan->nama_instansi }}
        <br>
        {{ $barisInstansi1 }} <br> {{ $barisInstansi2 }}</p>

        <p>Dengan hormat,</p>
        <br>
        <p style="text-align: center;">PERMOHONAN IJIN OBSERVASI DAN PERMINTAAN DATA</p>

        <p style="text-indent: 40px;text-align:justify">Dengan ini kami mohon kepada Bapak/Ibu, kiranya berkenan memberikan ijin kepada mahasiswa kami untuk melakukan observasi serta berkenan memberikan data pendukung untuk kelengkapan penyusunan Laporan {{ $permohonan->jenis_permohonan == 'Ijin Memperoleh Data PKL' ? 'Praktek Kerja Lapangan (PKL)' : 'Tugas Akhir (TA)' }}. Laporan tersebut merupakan salah satu syarat kelulusan jenjang {{ $permohonan->mahasiswa->kelas->prodi->jenjang == 'D3' ? 'Diploma III' : 'Diploma IV' }} guna mendapatkan sebutan gelar {{ $permohonan->mahasiswa->kelas->prodi->jenjang == 'D3' ? 'Ahli Madya (A.Md)' : 'Sarjana Terapan (S.Tr.)' }}. Adapun mahasiswa yang kami maksud adalah:</p>
        
        @if(!empty($anggotaTim) && count($anggotaTim) > 0)
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px;" border="1">
                <thead>
                    <tr>
                        <th style="padding: 5px 10px; text-align: center; width: 5%;">No</th>
                        <th style="padding: 5px 10px; text-align: left; width: 35%;">Nama Lengkap</th>
                        <th style="padding: 5px 10px; text-align: center; width: 25%;">NIM</th>
                        <th style="padding: 5px 10px; text-align: left; width: 35%;">Program Studi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 5px 10px; text-align: center;">1</td>
                        <td style="padding: 5px 10px;">{{ $permohonan->mahasiswa->nama_lengkap }}</td>
                        <td style="padding: 5px 10px; text-align: center;">{{ $permohonan->mahasiswa->nim }}</td>
                        <td style="padding: 5px 10px;">{{ $permohonan->mahasiswa->kelas->prodi->jenjang == 'D3' ? 'D-III' : 'D-IV' }} {{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</td>
                    </tr>
                    @foreach($anggotaTim as $idx => $anggota)
                    <tr>
                        <td style="padding: 5px 10px; text-align: center;">{{ $idx + 2 }}</td>
                        <td style="padding: 5px 10px;">{{ $anggota->nama_lengkap }}</td>
                        <td style="padding: 5px 10px; text-align: center;">{{ $anggota->nim }}</td>
                        <td style="padding: 5px 10px;">{{ $anggota->kelas ? ($anggota->kelas->prodi ? ($anggota->kelas->prodi->jenjang == 'D3' ? 'D-III' : 'D-IV') . ' ' . $anggota->kelas->prodi->nama_prodi : '-') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table>
                <tr><td style="width: 150px;">Nama</td><td>: {{ $permohonan->mahasiswa->nama_lengkap }}</td></tr>
                <tr><td>NIM</td><td>: {{ $permohonan->mahasiswa->nim }}</td></tr>
                <tr><td>Program Studi</td><td>: {{ $permohonan->mahasiswa->kelas->prodi->jenjang == 'D3' ? 'D-III' : 'D-IV' }} {{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</td></tr>
            </table>
        @endif

        <p style="text-align: justify;text-indent:40px">Demikian permohonan ini kami sampaikan, atas perhatian Bapak/Ibu kami ucapkan terima kasih.</p>
        
        <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 50px;">
            <tr>
                <td style="width: 20%;"></td>
                <td style="width: 35%;"></td>
                <td style="width: 45%;">
                    <p style="margin-top:-10px">POLITEKNIK SAWUNGGALIH AJI</p>
                    <br><br><br>
                    <p style="margin-bottom: 2px">{{ $direktur->nama }}</p>
                    <p style="margin-top:-2px">Direktur</p>
                </td>
            </tr>
        </table>
        
        <div style="text-align:center;font-size:14pt;page-break-before: always;margin-top:3cm;">Data yang Diminta</div>

        <table border="1" style="width: 80%; border-collapse: collapse;margin:auto;margin-top:40px;">
            <tr>
                <td style="width: 50px; text-align: center;">No.</td>
                <td style="text-align: center">Data yang Diminta</td>
            </tr>
            @foreach($permohonan->data_diminta as $index => $data)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-indent: 5px">{{ $data }}</td>
            </tr>
            @endforeach
        </table>
        
        
        
        <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 100px;">
            <tr>
                <td style="width: 20%;"></td>
                <td style="width: 35%;"></td>
                <td style="width: 45%;">
                    <p style="margin-top:-10px">POLITEKNIK SAWUNGGALIH AJI</p>
                    <br><br><br>
                    <p style="margin-bottom: 2px">{{ $direktur->nama }}</p>
                    <p style="margin-top:-2px">Direktur</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

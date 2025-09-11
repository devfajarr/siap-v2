@php
    function toRoman($number) {
    $map = [
        10 => 'X', 9 => 'IX', 8 => 'VIII', 7 => 'VII', 6 => 'VI', 
        5 => 'V', 4 => 'IV', 3 => 'III', 2 => 'II', 1 => 'I'
    ];
    
    $result = '';
    foreach ($map as $value => $roman) {
        while ($number >= $value) {
            $result .= $roman;
            $number -= $value;
        }
    }
    
    return $result;
}

@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Pindah Kelas</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .content {
            margin-top: 20px;
            line-height: 1.5;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
        }

        .content td {
            vertical-align: top;
            padding: 2px;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
        }

        @page {
            size: A4;
            margin: 2cm;
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
        <div class="header">
            <h2 style="text-align: center; font-size:12pt">S U R A T &nbsp; K E T E R A N G A N</h2>
            <p style="text-align: center; font-size:12pt; margin-top:-15px">No. {{ $permohonan->no_surat }}</p>
        </div>

        <div class="content">
            <table style="line-height: 1.5;">
                <tr>
                    <td style="width: 80px;">Lampiran</td>
                    <td>: -</td>
                </tr>
                <tr>
                    <td>Hal</td>
                    <td>: Permohonan Pindah Kelas</td>
                </tr>
            </table>
            
            <br>
            
            <table style="border-collapse: collapse; line-height: 1;">
                <tr>
                    <td style="vertical-align: top; width: 80px;">Kepada</td>
                    <td style="vertical-align: top;">: Sdr. {{ $permohonan->mahasiswa->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-indent: 8px;">Prodi {{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-indent: 8px;">{{ $permohonan->mahasiswa->nim }}</td>
                </tr>
            </table>
            
            
            <p style="text-align: justify; text-indent: 40px;">
                Menunjuk surat permohonan Saudara untuk pindah kelas dari Reguler
                {{ $permohonan->kelas_asal}} ke kelas Reguler
                {{ $permohonan->kelas_tujuan}} 
                (tertanggal {{ \Carbon\Carbon::parse($permohonan->created_at)->locale('id')->translatedFormat('d F Y') }}), maka dengan ini Saudara dapat pindah ke kelas 
                Reguler {{ $permohonan->kelas_tujuan}}  terhitung mulai {{ \Carbon\Carbon::parse(now())->locale('id')->translatedFormat('d F Y') }}.
                Di bawah ini kami sampaikan perubahan data Saudara sebagai berikut:
            </p>
            
            <table>
                <tr>
                    <td style="width: 150px;">Program Studi</td>
                    <td style="width: 5px; text-align: left;">:</td>
                    <td>{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }} Reguler
                        {{ $permohonan->kelas_tujuan}} </td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td style="width: 5px; text-align: left;">:</td>
                    <td>{{ $kelas_baru }}</td>
                </tr>
                <tr>
                    <td>Semester</td>
                    <td style="width: 5px; text-align: left;">:</td>
                    <td>{{ toRoman($permohonan->mahasiswa->kelas->semester->semester) }} ({{ $permohonan->mahasiswa->kelas->semester->semester % 2 == 0 ? 'Genap' :'Gasal' }})</td>
                </tr>
                <tr>
                    <td>Tahun Akademik</td>
                    <td style="width: 5px; text-align: left;">:</td>
                    <td>{{ $tahunAkademik->tahun_akademik }}</td>
                </tr>
            </table>
            
            

            <p style=" text-indent: 40px; text-align:justify">Selanjutnya saudara harap menghubungi Bagian Administrasi Keuangan, demikian kami sampaikan atas perhatian yang diberikan kami ucapkan terima kasih.</p>
        </div>

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

        <div class="tembusan">
            <p style="margin-bottom: 0;">Tembusan:</p>
            <ol style="line-height: 1; margin-top: 0; margin-left: 30px; padding-left: 0;">
                <li>Kajur {{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</li>
                <li>Kabag Administrasi Keuangan</li>
            </ol>
        </div>
        
        
        

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Pindah Kuliah</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
        }

        .header {
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

        .content td:first-child {
            width: 30%;
        }

        .content td:nth-child(2) {
            width: 5%;
            text-align: left;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
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
    <div class="container">
        <div class="header">
            <h3>SURAT KETERANGAN PINDAH KULIAH</h3>
            <p style="margin-top:-18px">Nomor: {{ $permohonan->no_surat }}</p>
        </div>

        <div class="content">
            <p style="margin-top:40px">Saya yang bertanda tangan di bawah ini:</p>
            <table style="line-height: 1.5;">

                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>{{ $direktur->nama }}</td>
                </tr>
                <tr>
                    <td>NUPTK</td>
                    <td>:</td>
                    <td>{{ $direktur->dosen->nidn }}</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>Direktur</td>
                </tr>
            </table>

            <p>dengan ini menerangkan bahwa:</p>
            <table style="line-height: 1.5;">

                <tr>
                    <td>Nama Mahasiswa</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->nim }}</td>
                </tr>
                <tr>
                    <td>Prodi</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</td>
                </tr>
                <tr>
                    <td>Jenjang Pendidikan</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->kelas->prodi->jenjang == 'D3' ? 'D III' : 'D IV' }}</td>
                </tr>
                <tr>
                    <td>Tahun Masuk</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->tahun_masuk }}</td>
                </tr>
            </table>

            <p style="text-align: justify;line-height:1.5">Adalah mahasiswa aktif pada Program Studi
                {{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }} angkatan
                {{ $permohonan->mahasiswa->tahun_masuk }} Sehubungan dengan permohonan yang bersangkutan untuk pindah
                kuliah ke <strong>{{ $permohonan->pt_tujuan }}</strong> (Nama Perguruan Tinggi yang dituju), pada
                dasarnya kami tidak keberatan.</p>

            <p style="text-align: justify;line-height:1.5">Bersama ini kami lampirkan Transkrip Nilai/Kartu Hasil Studi
                mahasiswa yang bersangkutan selama kuliah di Politeknik Sawungggalih Aji.</p>

            <p style="text-align: justify;line-height:1.5">Demikian surat ini dibuat dengan sebenarnya dan untuk dapat
                dipergunakan sebagaimana mestinya.</p>
        </div>

        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <tr>
                <td style="width: 20%;"></td>
                <td style="width: 35%;"></td>
                <td style="width: 45%;">
                    <p>Purworejo, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
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

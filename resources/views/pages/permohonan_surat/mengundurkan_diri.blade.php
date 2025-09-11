<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Mengundurkan Diri</title>
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

        .tembusan {
            margin-top: 20px;
        }

        @page {
            size: A4;
            margin: 1.8cm;
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
            <p style="font-size:18pt;margin-bottom:-18px">SURAT KETERANGAN MENGUNDURKAN DIRI</p>
            <p>No {{ $permohonan->no_surat }}</p>
        </div>

        <div class="content">
            <p>Direktur Politeknik Sawunggalih Aji, dengan ini menerangkan:</p>
            <table style="margin-top:-15px">
                <tr>
                    <td style="width: 150px;">Nama</td>
                    <td style="width: 5px;">:</td>
                    <td>{{ $permohonan->mahasiswa->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td>Tempat, Tgl. Lahir</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->tempat_lahir }},
                        {{ \Carbon\Carbon::parse($permohonan->mahasiswa->tanggal_lahir)->locale('id')->translatedFormat('d F Y') }}
                    </td>
                </tr>
                <tr>
                    <td>Prodi</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</td>
                </tr>
                <tr>
                    <td>Jenjang Program</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->kelas->prodi->jenjang == 'D3' ? 'D III' : 'D IV' }}</td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->nim }}</td>
                </tr>
                <tr>
                    <td>Tahun Masuk</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->tahun_masuk }}</td>
                </tr>
                <tr>
                    <td>Alamat asal</td>
                    <td>:</td>
                    <td>{{ $permohonan->mahasiswa->alamat }}</td>
                </tr>
            </table>

            <p style="text-align: justify; line-height: 1.5;">
                Yang bersangkutan benar-benar mahasiswa Politeknik Sawunggalih Aji dan terdaftar terakhir pada semester
                {{ $permohonan->mahasiswa->kelas->semester->semester % 2 == 0 ? 'Genap' : 'Ganjil' }} TA
                {{ $tahunAkademik->tahun_akademik }}.
                <br>
                Surat Keterangan ini diberikan atas permintaan yang bersangkutan untuk mengundurkan diri sebagai
                mahasiswa Politeknik Sawunggalih Aji.
                <br>
                Bahwa yang bersangkutan:
            <ol type="a">
                <li style="text-align:justify">Telah mengajukan surat permohonan mengundurkan diri pada tanggal
                    {{ \Carbon\Carbon::parse($permohonan->created_at)->locale('id')->translatedFormat('d F Y') }}</li>
                <li style="text-align: justify">Dengan dikeluarkannya Surat Keterangan mengundurkan diri ini, maka yang bersangkutan kehilangan
                    haknya sebagai mahasiswa Politeknik Sawunggalih Aji</li>
            </ol>
            </p>
            <p style="line-height: 1.5;text-align:justify">Demikian surat keterangan ini kami sampaikan agar dapat digunakan sebagaimana
                mestinya.</p>

        </div>
        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <tr>
                <td style="width: 20%;"></td>
                <td style="width: 35%;"></td>
                <td style="width: 45%;">
                    <p>Purworejo, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <br><br><br>
                    <p style="margin-bottom: 2px">{{ $wadir->nama }}</p>
                    <p style="margin-top:-2px">Wakil Direktur I</p>
                </td>
            </tr>
        </table>


        <div class="tembusan">
            <p>Tembusan Yth.:</p>
            <ol style="margin-top: -5px">
                <li>Kaprodi {{ $permohonan->mahasiswa->kelas->prodi->nama_prodi }}</li>
                <li>Bagian Keuangan</li>
            </ol>
        </div>
    </div>
</body>

</html>

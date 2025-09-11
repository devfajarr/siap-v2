<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Cuti Kuliah</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 40px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
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
            <div style="text-align: center; font-size:12pt">SURAT KETERANGAN</div>
            <p style="text-align: center; font-size:12pt; margin-top:-8px">No. {{ $permohonan->no_surat }}</p>
        </div>

        <div class="content">
            <table>
                <tr>
                    <td style="width: 80px;">Lampiran</td>
                    <td>: -</td>
                </tr>
                <tr>
                    <td>Hal</td>
                    <td>: Cuti Kuliah</td>
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

            <p>Merujuk surat permohonan cuti kuliah Saudara, tertanggal {{ \Carbon\Carbon::parse($permohonan->created_at)->locale('id')->translatedFormat('d F Y') }} maka melalui surat keterangan ini DIIJINKAN cuti kuliah pada:
            <br>
            <table>
                <tr>
                    <td style="width: 150px;">Semester</td>
                    <td>: {{ $permohonan->masa_cuti }}</td>
                </tr>
                <tr>
                    <td>Tahun Akademik</td>
                    <td>: {{$tahunAkademik->tahun_akademik}}</td>
                </tr>
            </table>
            <br>
            Selanjutnya untuk mengikuti kegiatan akademik kembali, Saudara harus melakukan ketentuan sebagai berikut:
            <ol>
                <li>Saudara mengajukan surat permohonan Aktif Kuliah (blangko disediakan di bagian Akademik dilampiri fotocopy surat ijin cuti kuliah).</li>
                <li>Melakukan Her Registrasi pada bagian administrasi Akademik.</li>
            </ol>
            Atas perhatiannya, diucapkan terima kasih.</p>
        </div>

        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <tr>
                <td style="width: 20%;"></td>
                <td style="width: 35%;"></td>
                <td style="width: 45%;">
                    <p>Purworejo, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin-top:-20px">POLITEKNIK SAWUNGGALIH AJI</p>
                    <br><br><br>
                    <p style="margin-bottom: 2px">{{ $wadir->nama }}</p>
                    <p style="margin-top:-2px">Wakil Direktur I</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

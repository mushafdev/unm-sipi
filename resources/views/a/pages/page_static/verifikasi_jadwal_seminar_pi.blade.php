<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    <style>
        @page {
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.3;
        }
        header {
            display: flex;
            text-align: center;
            margin-bottom: 10px;
        }
        .bold{
            font-weight: bold;
        }
        .hr-1{
            border-top: 1px solid;
            border-bottom: 3px solid;
            border-right: none;
            border-left: none;
            height: 2px;
        }
        header h1 {
            font-size: 12pt;
            margin: 0;
            line-height: 20px;
            font-weight: normal;
        }
        header h2 {
            font-size: 14pt;
            margin: 0;
        }
        .contact-info {
            text-align: center;
            font-size: 9.5pt;
            margin-top: 0px;
            margin-bottom: 0px;
        }
        .content {
            margin-top: 0px;
            margin-left: 15mm;
            margin-right: 15mm;
        }
        .content p {
            text-align: justify;
        }
        table{
            margin: 0px;    
            border-collapse: collapse;
        }
        .table {
            width: 70%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-top: 15px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 5px;
            padding-left: 5px;
            padding-right: 5px;
            text-align: left;
        }
        footer {
            margin-top: -20px;
        }
        .signature {
            text-align: center !important;
            margin-top: 0px;
        }

        .text-center{
            text-align: center !important;
        }
        .text-left{
            text-align: left;
        }
        .text-right{
            text-align: right;
        }
        .align-top{
            vertical-align: top;
        }
        .w-100{
            width: 100%;
        }
        .no-margin{
            margin: 0px;
        }
    </style>
</head>
<body>
    <header>
        <table class="w-100">
            <tr>
                <td>
                    <img width="100px" src="{{public_path('app/assets/static/images/logo/unm_logo.png')}}" alt="Logo Universitas">
                </td>
                <td class="text-center">
                    <h1>KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN<br>
                        {{Str::upper(identity()['university'])}}<br>
                        <b>JURUSAN {{Str::upper($detail->jurusan)}}</b><br></h1>
                    
                    <p class="contact-info">
                        Alamat: {{$detail->alamat}}<br>
                        Telp. {{$detail->telp}}, Fax. {{$detail->fax}}, Hp. {{$detail->hp}}, Email: {{$detail->email}} | Laman: {{$detail->website}}
                    </p>
                </td>
            </tr>
        </table>
        <hr class="hr-1">
    </header>
    <main>
        <h4 class="text-center" style="margin: 5px;font-size: 14pt;">LEMBAR VERIFIKASI<br> JADWAL SEMINAR PRAKTIK INDUSTRI</h4>
        <div class="content">
            <table class="table w-100" style="margin-left:auto;margin-right:auto;">
                <tbody>
                    <tr>
                        <td class="text-center align-top bold" width="5%" rowspan="{{$group_details->count()}}">1.</td>
                        <td width="30%" class="align-top text-right bold" rowspan="{{$group_details->count()}}">Nama Mahasiswa</td>
                        <td width="5%" class="align-top text-center bold" rowspan="{{$group_details->count()}}">:</td>
                        <td class="align-top">{{$loop->parent->iteration ?? 1}}. {{Str::upper($group_details[0]->nama)}} ({{$group_details[0]->nim}})</td>
                    </tr>
                    @for ($i = 1; $i < $group_details->count(); $i++)
                    <tr>
                        <td class="align-top">{{$i+1}}. {{Str::upper($group_details[$i]->nama)}} ({{$group_details[$i]->nim}})</td>
                    </tr>
                    @endfor
                    <tr>
                        <td class="align-top text-center bold">2.</td>
                        <td class="align-top bold">Lokasi Praktik Industri</td>
                        <td class="align-top text-center bold">:</td>
                        <td class="align-top">{{$detail->lokasi_pi}}</td>
                    </tr>
                    <tr>
                        <td class="align-top text-center bold">3.</td>
                        <td class="align-top bold">Waktu PI</td>
                        <td class="align-top text-center bold">:</td>
                        <td class="align-top">Bulan {{getMonthFromNumber($detail->start_month)}} s.d {{getMonthFromNumber($detail->end_month)}} {{$detail->year}}</td>
                    </tr>
                    <tr>
                        <td class="align-top text-center bold">4.</td>
                        <td class="align-top bold">Tanggal Mulai PI</td>
                        <td class="align-top text-center bold">:</td>
                        <td class="align-top"></td>
                    </tr>
                    <tr>
                        <td class="align-top text-center bold">5.</td>
                        <td class="align-top bold">Tanggal Selesai PI</td>
                        <td class="align-top text-center bold">:</td>
                        <td class="align-top"></td>
                    </tr>
                    <tr>
                        <td class="align-top text-center bold">6.</td>
                        <td class="align-top">
                            <b>Jadwal Seminar PI</b><br>
                            <!-- <small style="font-size: 9pt;">(Diisi Oleh Staf Jurusan setelah
                                mendapat jadwal dari dosen Pembimbing)
                                </small> -->

                        </td>
                        <td class="align-top text-center bold">:</td>
                        <td class="align-top"></td>
                    </tr>
                    <tr>
                        <td class="align-top text-center bold">6.</td>
                        <td class="align-top">
                            <b>Paraf Admin JTIK</b><br>
                            <!-- <small style="font-size: 9pt;">(Diisi Oleh Staf Jurusan setelah
                                mendapat jadwal dari dosen Pembimbing)
                                </small> -->

                        </td>
                        <td class="align-top text-center bold">:</td>
                        <td class="align-top">
                            <br><br><br>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="w-100" style="margin-top:30px;">
                <tr>
                    <td class="bold" width="50%">
                        Lampirkan: 
                        <ul>
                            <li>Surat Izin PI (Yang dari MySams)</li>
                            <li>Lembar Persetujuan Hari</li>
                            <li>Lembar Persetujuan Seminar PI</li>
                        </ul>
                    </td>
                    <td class="text-center" width="50%">
                    </td>
                </tr>
            </table>
        </div>
    </main>
</body>
</html>

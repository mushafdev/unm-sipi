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
            margin-bottom: 0px;
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
            padding: 2px;
            padding-left: 5px;
            padding-right: 5px;
            text-align: left;
        }
        .table td {
            border: 1px solid #000;
            padding: 12px;
            padding-left: 5px;
            padding-right: 5px;
            text-align: left;
        }
        footer {
            margin-top: -20px;
        }
        .signature {
            text-align: left;
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
                <td class="text-center">
                    <img width="100px" src="{{public_path('app/assets/static/images/logo/unm_logo.png')}}" alt="Logo Universitas">
                </td>
            </tr>
            <tr>
                <td class="text-center">
                    DAFTAR PESERTA PEMBEKALAN PI<br>
                    PERIODE {{Str::upper(tgl_indo($detail->tanggal))}}
                </td>
            </tr>
        </table>
        {{-- <hr class="hr-1"> --}}
    </header>
    <main>
        {{-- <h4 class="text-center" style="margin: 5px;font-size: 11pt;">FORM NILAI PRAKTIK INDUSTRI (PI)<br> --}}
       
            <table class="table" style="margin-left:auto;margin-right:auto;width:100%;">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">NIM</th>
                        <th class="text-left">Nama</th>
                        <th class="text-center">Kelas</th>
                        <th class="text-center">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group_details as $dt )
                    <tr>
                        <td class="text-center">{{$loop->iteration}}</td>
                        <td class="text-center">{{$dt->nim}}</td>
                        <td class="text-left">{{$dt->nama}}</td>
                        <td class="text-center">{{$dt->kelas}}</td>
                        <td class="text-center"></td>
                    </tr>    
                    @endforeach
                </tbody>
            </table>
        
    </main>
    <footer style="margin-top:10px;">
        <table class="w-100" style="font-size: 11pt">
            <tr>
                <td width="60%"></td>
                <td width="40%">
                    <div class="signature">
                        <p>{{$detail->kota}}, {{tgl_indo(date('Y-m-d'))}} <br>
                        Kepala Laboratorium,<br>
                        {{$detail->jurusan}}</p>
                        <p style="margin-top: 80px;"><b>{{$detail->kepala_lab_nama}}</b><br>
                        NIP. {{$detail->kepala_lab_nip}}</p>
                    </div>
                </td>
            </tr>
        </table>
    </footer>
</body>
</html>

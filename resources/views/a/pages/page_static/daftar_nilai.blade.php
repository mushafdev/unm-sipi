<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    <style>
        @page {
            margin: 10px 50px 0px 50px;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.0;
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

        .box {
        width: 60px;
        height: 35px;
        border: 1px solid black;
        /* margin-top: 5px; */
        margin-left: 5px;
        }
        .dot-line {
        border-bottom: 1px dotted black;
        margin: 10px 0;
        height: 1em; /* Memberi ruang tinggi baris */
        }
        .range-table td {
        padding: 0px 0px;
        }
        table th{
            text-align: left;
        }
        .table {
            width: 70%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-top: 15px;
        }
        .table {
            width: 70%;
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
    <main style="margin-left: 50px;margin-right: 50px;">
        <h4 class="text-center" style="margin: 5px;font-size: 11pt;">FORM NILAI PRAKTIK INDUSTRI (PI)<br>
            OLEH PEMBIMBING LAPANGAN</h4>
            <table style="width: 100%;
            line-height: 1.5;">
                <tr>
                    <th width="25%">Nama Mahasiswa</th>
                    <th width="3%">:</th>
                    <th>{{$detail->nama}}</th>
                </tr>
                <tr>
                    <th>NIM</th>
                    <th>:</th>
                    <th>
                        {{$detail->nim}}
                    </th>
                </tr>
                <tr>
                    <th>Nama Pembimbing</th>
                    <th>:</th>
                    <th>{{$detail->nama_pembimbing_lapangan}}</th>
                </tr>
                <tr>
                    <th>Jabatan Pembimbing</th>
                    <th>:</th>
                    <th>{{$detail->jabatan_pembimbing_lapangan}}</th>
                </tr>
                <tr>
                    <th>Nama Instansi/Industri</th>
                    <th>:</th>
                    <th>{{$detail->lokasi_pi}}</th>
                </tr>
            </table>

            @php
                $nilaiHuruf=array('A','B','C','D','E');
                $komponenNilai=array('Keterampilan Teknis','Pemecahan Masalah & Inovasi','Kedisiplinan & Sikap Kerja','Kerjasama Tim & Komunikasi');
            @endphp
            <table class="table" style="margin-left:auto;margin-right:auto;width:100%;">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Komponen Penilaian</th>
                        <th class="text-center" >Nilai</th>
                    </tr>
                </thead>
                <tbody>
                        @foreach ($komponenNilai as $k =>$v )
                            <tr>
                                <td class="text-center">{{$loop->iteration}}.</td>
                                <td class="">{{$v}}</td>
                                <td class=""></td>
                            </tr>    
                        @endforeach
                        <tr><td colspan="2" class="text-center"><b>Rata-Rata Nilai Akhir</b</td><td></td></tr>
                </tbody>
            </table>

            <table style="margin-top:20px; ">
                <tr>
                    <td><strong>Kesimpulan:</strong></td>
                </tr>
                <tr>
                  <td>Nilai Angka</td>
                  <td width="">:</td>
                  <td>
                    <div class="box"></div>
                  </td>
                </tr>
                <tr>
                  <td>Nilai Huruf</td>
                  <td>:</td>
                  <td>
                    <div class="box"></div>
                  </td>
                </tr>
              </table>
            
              <br>
              <strong>Catatan:</strong><br>
              Range Nilai
            
              <table class="range-table" width="80%">
                <tr>
                  <td width="11%">91 – 100</td><td width="10%">= A</td>
                  <td width="30%"></td>
                  <td width="11%">61 – 65</td><td width="10%">= C</td>
                </tr>
                <tr>
                  <td>86 – 90</td><td>= A-</td>
                  <td></td>
                  <td>56 – 60</td><td>= C-</td>
                </tr>
                <tr>
                  <td>81 – 85</td><td>= B+</td>
                  <td></td>
                  <td>51 – 55</td><td>= D+</td>
                </tr>
                <tr>
                  <td>76 – 80</td><td>= B</td>
                  <td></td>
                  <td>46 – 50</td><td>= D</td>
                </tr>
                <tr>
                  <td>71 – 75</td><td>= B-</td>
                  <td></td>
                  <td>41 – 45</td><td>= D-</td>
                </tr>
                <tr>
                  <td>66 – 70</td><td>= C+</td>
                  <td></td>
                  <td>0 – 41</td><td>= E</td>
                </tr>
              </table>
              <p>Saran untuk Mahasiswa dan Universitas:</p>
                <div class="dot-line"></div>
                <div class="dot-line"></div>
                <div class="dot-line"></div>
        
    </main>
    <footer style="margin-top:10px;">
        <table class="w-100" style="font-size: 11pt">
            <tr>
                <td width="60%"></td>
                <td width="40%">
                    <div class="signature">
                        <p>{{$detail->kota}}, {{tgl_indo(date('Y-m-d'))}} <br>
                        Pembimbing Lapangan</p>
                        <p style="margin-top: 70px;margin-bottom:0px;"><b><u>{{$detail->nama_pembimbing_lapangan}}</u></b><br>
                            {{$detail->jabatan_pembimbing_lapangan}}</p>
                    </div>
                </td>
            </tr>
        </table>
    </footer>
</body>
</html>

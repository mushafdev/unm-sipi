@extends('u.layouts.master')
@section('content')

<link rel="stylesheet" href="{{asset('app/assets/extensions/summernote/summernote-lite.css')}}">
<script src="{{asset('app/assets/extensions/summernote/summernote-lite.js')}}"></script>

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>{{$title}}</h3>
            <p class="text-subtitle text-muted"></p>
        </div>
    </div>
</div>
<div class="page-content"> 
    <section class="section">
        <form name="form" id="form" data-store="{{route('selesai-pi.store')}}" data-index="{{route('selesai-pi.index')}}" data-check="{{route('selesai-pi.check-data')}}" data-send="{{route('selesai-pi.send')}}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="group_id" value="{{encrypt0($lokasi->id)}}">
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nama_lokasi" class="form-label">Nama Perusahaan/Instansi</label>
                                                <h6>{{isset($lokasi->lokasi_pi)?$lokasi->lokasi_pi:''}}</h6>
                                            </div>
                                            <div class="form-group">
                                                <label for="kota" class="form-label">Kota</label>
                                                <h6>{{isset($lokasi->kota)?$lokasi->kota:''}}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            
                                            <div class="form-group">
                                                <label for="alamat" class="form-label">Alamat</label>
                                                <h6>{{isset($lokasi->alamat)?$lokasi->alamat:''}}</h6>
                                            </div>
                                            <div class="form-group">
                                                <label for="alamat" class="form-label">Status Selesai PI</label>
                                                <h6>
                                                    @if ($lokasi->done=='Y')
                                                        <span class="badge bg-success">Telah Dikirim</span>
                                                    @else
                                                        <span class="badge bg-danger">Belum Dikirim</span>
                                                    @endif
                                                    @if ($lokasi->done_verify=='Y')
                                                        <span class="badge bg-success">Telah Diverifikasi admin</span>
                                                    @elseif ($lokasi->done_verify=='X')
                                                        <span class="badge bg-danger">Ditolak admin</span>
                                                    @else
                                                        <span class="badge bg-light">Belum Diverifikasi admin</span>
                                                    @endif
                                                </h6>
                                                @if(!empty($lokasi->done_catatan))
                                                <div class="alert alert-light-danger color-danger"><b>Catatan : </b> {{$lokasi->done_catatan}}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($lokasi->done=='N' || $lokasi->done_verify!='Y')
                                <div class="col-md-12 mt-4">
                                    <h5>Input Bukti Selesai PI</h5>
                                    <div class="alert alert-info"><i class="bi bi-info-circle"></i> Silahkan upload di google drive filenya lalu masukkan linknya dibawah sesuai jenis bukti</div>
                                    <div class="mb-3">
                                        <label for="formFile" class="form-label">Aggota Kelompok</label>
                                        <select class="form-select" id="mahasiswa_id" name="mahasiswa_id" required>
                                            <option value="">Pilih Anggota</option>
                                            @foreach ($mahasiswas as $m )
                                                <option value="{{encrypt0($m->mahasiswa_id)}}">{{$m->nama}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="formFile" class="form-label">Sertifikat dari Industri/Lembar Penilaian Pembimbing Lapangan</label>
                                        <input class="form-control" type="text" id="sertifikat_industri" name="sertifikat_industri" placeholder="Masukkan link google drive untuk sertifikat" required>
                                    </div>
                                    <div class="mb-3 d-none">
                                        <label for="formFile" class="form-label">Laporan Praktik Industri</label>
                                        <input class="form-control" type="text" id="laporan_pi" name="laporan_pi" placeholder="Masukkan link google drive untuk laporan PI">
                                    </div>
                                </div>


                                @if ($lokasi->done=='N' && $lokasi->done_verify!='Y')
                                <div class="col-md-12 text-end">
                                    <button type="button" id="add" class="btn icon icon-left btn-success"><i class="bi bi-plus"></i> Tambahkan/Update</button>
                                </div>
                                @endif

                                @endif
                                <div class="col-md-12 mt-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5>Daftar Bukti Selesai PI kelompok</h5>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Nama</th>
                                                        <th>NIM</th>
                                                        <th class="text-center">Sertifikat Industri/Lembar Penilaian</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($buktis->count()==0)
                                                        <tr>
                                                            <td class="text-center text-danger" colspan="4">Belum ada bukti</td>
                                                        </tr>
                                                    @else
                                                        @foreach($buktis as $dt)
                                                        <tr>
                                                            <td>{{$loop->iteration}}</td>
                                                            <td>{{$dt->nama}}</td>
                                                            <td>{{$dt->nim}}</td>
                                                            <td class="text-center"><a href="{{$dt->sertifikat_industri}}" target="_blank">Lihat File</a></td>
                                                            {{-- <td class="text-center"><a href="{{$dt->laporan_pi}}" target="_blank">Lihat File</a></td> --}}
                                                        </tr>
                                                        @endforeach   
                                                    @endif
                                                    
                                                </tbody>
                                            </table>
                                            <input type="hidden" id="cek-list" value="{{$buktis->count()}}">
                                        </div>
                                    </div>
                                    
                                   
                                </div>

                                @if ($lokasi->done=='N' && $lokasi->done_verify!='Y')
                                <div class="col-md-12 text-end">
                                    <button type="button" id="send" class="btn icon icon-left btn-primary"><i class="bi bi-send"></i> Kirim</button>
                                </div>
                                @endif
                            </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </section>
</div>

<script src="{{asset('app/assets/pages/selesai_pi/selesai_pi.js')}}"></script>
@endsection
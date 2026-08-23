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
        <input type="hidden" name="group_id" id="group_id" value="{{encrypt0($lokasi->id)}}">
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
                                            <div class="form-group">
                                                <label for="alamat" class="form-label">Alamat</label>
                                                <h6>{{isset($lokasi->alamat)?$lokasi->alamat:''}}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="alamat" class="form-label">Status Laporan & Nilai PI</label>
                                                <h6>
                                                    @if ($lokasi->laporan=='Y')
                                                        <span class="badge bg-success">Telah Dikirim</span>
                                                    @else
                                                        <span class="badge bg-danger">Belum Dikirim</span>
                                                    @endif
                                                    @if ($lokasi->laporan_verify=='Y')
                                                        <span class="badge bg-success">Telah Diverifikasi admin</span>
                                                    @elseif ($lokasi->laporan_verify=='X')
                                                        <span class="badge bg-danger">Ditolak admin</span>
                                                    @else
                                                        <span class="badge bg-light">Belum Diverifikasi admin</span>
                                                    @endif
                                                </h6>
                                                @if(!empty($lokasi->laporan_catatan))
                                                <div class="alert alert-light-danger color-danger"><b>Catatan : </b> {{$lokasi->laporan_catatan}}</div>
                                                @endif
                                            </div>

                                            <h5>Anggota Kelompok</h5>
                                            
                                            <table class="table">
                                                <tbody>
                                                    @foreach ($mahasiswas as $dt)
                                                        <tr>
                                                            <td>{{$loop->iteration}}.</td>
                                                            <td>{{$dt->nama}}</td>
                                                            <td>({{$dt->nim}})</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5>Dokumen yang harus diupload</h5>
                                            <p class="text-info ">Maaf, Upload Satu Satu</p>
                                            <div id="upload-forms" data-store="{{ route('laporan-upload-nilai.upload') }}" data-send="{{ route('laporan-upload-nilai.send') }}">
                                                @foreach($dokumen_laporans as $dt)
                                                @php 
                                                $dokumenId = Str::slug($dt->dokumen, '_'); 
                                                $filePath = $uploadedFiles[$dt->dokumen] ?? null;
                                                @endphp

                                                <form id="form-{{ $dokumenId }}" class="upload-form mb-4 border p-3" data-dokumen="{{ $dt->dokumen }}">
                                                    @csrf
                                                    <input type="hidden" name="dokumen" value="{{ $dt->dokumen }}">
                                                    <label class="w-100">{{ $dt->dokumen }} <small class="text-info">*Harus file PDF</small><span class="text-danger ms-0">*</span></label>

                                                    @if ($lokasi->laporan=='N' && $lokasi->laporan_verify!='Y')
                                                    <input type="file" name="file" id="file_{{ $dokumenId }}"  class="form-control mb-2" accept="application/pdf" required>
                                                    @endif
                                                    <div class="progress mb-2 d-none">
                                                        <div class="progress-bar" role="progressbar" 
                                                        style="width: 0%" 
                                                        aria-valuenow="0" 
                                                        aria-valuemin="0" 
                                                        aria-valuemax="100">0%</div>
                                                    </div>
                                                    @if($filePath)
                                                        <a href="{{ asset('files/dokumen_laporan/' . $filePath) }}" target="_blank" class="btn btn-success btn-sm">
                                                            <i class="bi bi-file-earmark-pdf"></i> Lihat file
                                                        </a>
                                                    @endif

                                                    @if ($lokasi->laporan=='N' && $lokasi->laporan_verify!='Y')
                                                        <button type="submit" class="btn btn-primary btn-sm upload-btn icon icon-left" data-form="#form-{{ $dokumenId }}"><i class="bi bi-cloud-upload"></i> Upload</button>
                                                    @endif
                                                    <div class="result text-success small mt-2"></div>
                                                </form>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($lokasi->laporan=='N' && $lokasi->laporan_verify!='Y')
                                <div class="col-md-12 text-end">
                                    <button type="button" id="send" class="btn icon icon-left btn-primary"><i class="bi bi-send"></i> Kirim</button>
                                </div>
                                @endif
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="{{asset('app/assets/pages/laporan_upload_nilai/laporan_upload_nilai.js')}}"></script>
@endsection
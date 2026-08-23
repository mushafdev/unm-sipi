@extends('a.layouts.master')
@section('content')
<link rel="stylesheet" href="{{asset('app/assets/extensions/datatables.net-bs5/css/select.dataTables.css')}}">
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
        <form name="form" id="form" data-verifikasi="{{route('pembekalan.verifikasi',encrypt0($data->id))}}" data-index="{{route('pembekalan.index')}}" enctype="multipart/form-data" method="POST">
        <input type="hidden" id="param" value="verifikasi">
        @method('PUT')
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <h5>Waktu Pembekalan</h5>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group">
                                                <label for="tanggal" class="form-label">Tanggal<span class="text-danger ms-0">*</span></label>
                                                <input type="date" name="tanggal" id="tanggal" class="form-control" placeholder="" value="{{$data->tanggal}}" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="jam" class="form-label">Jam<span class="text-danger ms-0">*</span></label>
                                                <input type="time" name="jam" id="jam" class="form-control" placeholder="" value="{{$data->jam}}" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="catatan" class="form-label">Catatan</label>
                                                <textarea name="catatan" id="catatan" class="form-control" placeholder="" value="" disabled>{{$data->catatan}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <h5>Pilih Mahasiswa</h5>
                                    <table class="table" id="table-mhs" data-list="{{route('pembekalan.get_mahasiswa')}}" data-token="{{csrf_token()}}" data-id="{{encrypt0($data->id)}}">
                                        <thead>
                                            <tr>
                                                {{-- <th><input type="checkbox" class="form-check-input" id="select-all" value=""></th> --}}
                                                <th></th>
                                                <th>Nama</th>
                                                <th>NIM</th>
                                            </tr>
                                        </thead>
                                        <tbody> 
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12 text-end mt-5">
                                    <a href="{{route('pembekalan.index')}}" class="btn icon icon-left btn-light"><i class="bi bi-arrow-left-square"></i> Kembali</a>
                                    @can('pembekalan-edit')
                                    <a href="{{route('pembekalan.edit',encrypt0($data->id))}}" class="btn icon icon-left btn-info">
                                        <i class="bi bi-pencil"></i> Edit</a>
                                    @endcan
                                    <a href="{{route('pembekalan.print_absensi',encrypt0($data->id))}}" target="_blank" class="btn icon icon-left btn-warning">
                                        <i class="bi bi-printer"></i> Print</a>
                                    @can('pembekalan-verifikasi')
                                        <button type="button" id="verifikasi" class="btn icon icon-left btn-success"><i class="bi bi-check"></i> Verifikasi Pembekalan</button>
                                    @endcan
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </section>
</div>
<script src="{{asset('app/assets/extensions/datatables.net-bs5/js/dataTables.select.js')}}"></script>
<script src="{{asset('app/assets/extensions/datatables.net-bs5/js/select.dataTables.js')}}"></script>
<script src="{{asset('app/assets/pages/pembekalan/pembekalan.js')}}?v=1.2"></script>
@endsection
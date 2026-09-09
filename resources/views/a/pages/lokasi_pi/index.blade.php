@extends('a.layouts.master')
@section('content')
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">
                        Data {{$title}}
                    </h5>
                </div>
                <div>
                    @can('lokasi pi-create')
                    <a href="{{route('lokasi-pi.create')}}" class="btn icon icon-left btn-success">
                        <i class="bi bi-plus-circle"></i> Create</a>
                    @endcan
                </div>

            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter Ketersediaan</label>
                        <select id="filter_kosong" class="form-select">
                            <option value="">Semua Lokasi</option>
                            <option value="1">Masih Kosong (Belum ada mahasiswa PI)</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table" id="table" data-list="{{route('lokasi-pi.get_lokasi_pi')}}" data-url="{{route('lokasi-pi.index')}}" data-kelompok="{{route('lokasi-pi.get_kelompok')}}">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Kota</th>
                                <th>Alamat</th>
                                <th>Telp.</th>
                                <th>Kompetensi</th>
                                <th>Kelompok Sementara PI</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody> 
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
</div>

<div class="modal fade text-left" id="modal-kelompok" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Daftar Kelompok Sementara PI <span id="modal-kelompok-nama" class="text-primary"></span></h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted"><i class="bi bi-info-circle"></i> Klik salah satu kelompok untuk membuka detailnya di tab baru.</p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Anggota</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Tahun</th>
                            </tr>
                        </thead>
                        <tbody id="modal-kelompok-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{asset('app/assets/pages/lokasi_pi/lokasi_pi_list.js')}}?v=13"></script>
@endsection
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
                        Data Jadwal Pembekalan
                    </h5>
                </div>
                <div>
                    @can('pembekalan-create')
                    <a href="{{route('pembekalan.create')}}" class="btn icon icon-left btn-success">
                        <i class="bi bi-plus-circle"></i> Create</a>
                    @endcan
                </div>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="table" data-list="{{route('pembekalan.get_pembekalan')}}" data-url="{{route('pembekalan.index')}}">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Total Mahasiswa</th>
                                <th>Telah Diverifikasi</th>
                                <th>Belum Diverifikasi</th>
                                <th>Status</th>
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

<script src="{{asset('app/assets/pages/pembekalan/pembekalan_list.js')}}"></script>
@endsection

@extends('a.layouts.master')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<div class="page-content">
    <div class="row mb-3">
        <div class="col-md-3">
            <img src="{{ asset('app/assets/compiled/svg/undraw_hello_ccwj.svg') }}" alt="Gambar Selamat Datang" class="img-fluid">
        </div>
        <div class="col-md-9">
            <div class="welcome-text">
                <div class="welcome-badge">
                    <i class="bi bi-emoji-smile"></i> Selamat Datang
                </div>
                <h2>Halo, <b>{{ Session::get('detailUser')->nama }}</b>!</h2>
                <p>
                    Senang melihatmu di <b>Sistem Informasi Praktek Industri</b>
                </p>
            </div>
        </div>
    </div>
    <section class="row">
        <div class="col-12 col-lg-12">
            <div class="row">
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                    <div class="stats-icon purple mb-2">
                                        <i class="iconly-boldShow"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Total Kelompok</h6>
                                    <h6 class="font-extrabold mb-0">{{ number_format($total_kelompok, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                    <div class="stats-icon blue mb-2">
                                        <i class="iconly-boldProfile"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Jumlah Mahasiswa</h6>
                                    <h6 class="font-extrabold mb-0">{{ number_format($total_mahasiswa, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                    <div class="stats-icon green mb-2">
                                        <i class="iconly-boldAdd-User"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Jumlah Dosen</h6>
                                    <h6 class="font-extrabold mb-0">{{ number_format($total_dosen, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                    <div class="stats-icon red mb-2">
                                        <i class="iconly-boldBookmark"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Jumlah Lokasi PI</h6>
                                    <h6 class="font-extrabold mb-0">{{ number_format($total_lokasi, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row">
        {{-- Kelompok per Tahun --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4>Jumlah Kelompok PI per Tahun</h4>
                </div>
                <div class="card-body">
                    <div id="chart-per-tahun"></div>
                </div>
            </div>
        </div>

        {{-- Status Kelompok PI --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4>Status Kelompok PI</h4>
                </div>
                <div class="card-body">
                    <div id="chart-status"></div>
                    <div class="d-flex justify-content-around mt-3">
                        <div class="text-center">
                            <h6 class="text-muted mb-0">Sedang PI</h6>
                            <h4 class="font-extrabold">{{ $total_sedang }}</h4>
                        </div>
                        <div class="text-center">
                            <h6 class="text-muted mb-0">Selesai</h6>
                            <h4 class="font-extrabold">{{ $total_selesai }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row">
        {{-- Top Lokasi PI --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>10 Lokasi PI Teratas (Kelompok Selesai PI)</h4>
                </div>
                <div class="card-body">
                    <div id="chart-top-lokasi"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var accent = '#435ebe';

        // ==== Kelompok per Tahun (bar) ====
        var perTahunLabels = @json($kelompok_per_tahun['labels']);
        var perTahunSeries = @json($kelompok_per_tahun['series']);
        new ApexCharts(document.querySelector('#chart-per-tahun'), {
            chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'Poppins, sans-serif' },
            series: [{ name: 'Kelompok', data: perTahunSeries }],
            xaxis: { categories: perTahunLabels },
            colors: [accent],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
            dataLabels: { enabled: true },
            noData: { text: 'Belum ada data' }
        }).render();

        // ==== Status Kelompok (donut) ====
        var statusSeries = @json($status_kelompok['series']);
        var statusLabels = @json($status_kelompok['labels']);
        new ApexCharts(document.querySelector('#chart-status'), {
            chart: { type: 'donut', height: 300, fontFamily: 'Poppins, sans-serif' },
            series: statusSeries,
            labels: statusLabels,
            colors: ['#f9a825', '#435ebe', '#28a745', '#dc3545'],
            legend: { position: 'bottom' },
            noData: { text: 'Belum ada data' }
        }).render();

        // ==== Top Lokasi PI (horizontal bar) ====
        var lokasiLabels = @json($top_lokasi['labels']);
        var lokasiSeries = @json($top_lokasi['series']);
        new ApexCharts(document.querySelector('#chart-top-lokasi'), {
            chart: { type: 'bar', height: 420, toolbar: { show: false }, fontFamily: 'Poppins, sans-serif' },
            series: [{ name: 'Kelompok Selesai PI', data: lokasiSeries }],
            xaxis: { categories: lokasiLabels },
            colors: ['#28a745'],
            plotOptions: { bar: { borderRadius: 6, horizontal: true } },
            dataLabels: { enabled: true },
            noData: { text: 'Belum ada data' }
        }).render();
    });
</script>
@endsection

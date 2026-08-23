@extends('u.layouts.master')

@section('content')
@php
    $group = groupInfo(Session::get('detailUser')->id);
    $verified = $group && $group->pembekalan_verify == 'Y';
    $bg = $verified ? '' : 'bg-light';
    $info = $verified ? '' : 'Anda belum mengikuti pembekalan';

    $userId = encrypt0(Session::get('detailUser')->id);

    $documents = [
        [
            'title' => 'Sertifikat Pembekalan',
            'route' => route('page.sertifikat_pembekalan', $userId),
        ],
        [
            'title' => 'Permohonan Izin Observasi',
            'route' => route('page.permohonan_izin_observasi', $userId),
        ],
        [
            'title' => 'Permohonan Izin PI',
            'route' => route('page.permohonan_izin_pi', $userId),
        ],
        [
            'title' => 'Permohonan Pembimbing',
            'route' => route('page.permohonan_pembimbing', $userId),
        ],
    ];
@endphp

<style>
    .document-icon {
        font-size: 3rem;
        color: #007bff;
    }
    .card-document {
        border: none;
        transition: transform 0.3s;
    }
    .card-document:hover {
        transform: scale(1.05);
    }
    .card-document.bg-light {
        pointer-events: none;
        opacity: 0.9;
    }
</style>

<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3>{{ $title }}</h3>
            <p class="text-subtitle text-muted">Kelompok praktek industri telah dibentuk, silahkan cetak file permohonan di bawah</p>
        </div>
    </div>
</div>

<div class="page-content"> 
    <section class="section">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach ($documents as $doc)
                <div class="col-6 col-lg-3">
                    <a 
                        href="{{ $verified ? $doc['route'] : '#' }}" 
                        class="card text-center shadow-sm card-document {{ $bg }}" 
                        @if ($verified) target="_blank" @endif
                    >
                        <div class="card-body">
                            <div class="document-icon">
                                <i class="bi bi-printer"></i>
                            </div>
                            <h6 class="card-title mt-3">{{ $doc['title'] }}</h6>
                            <small class="text-danger">{{ $info }}</small>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
</div>

<script src="{{ asset('app/assets/pages/group_lokasi/group_lokasi.js') }}"></script>
@endsection


@extends('u.layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <img src="{{ asset('app/assets/compiled/svg/undraw_hello_ccwj.svg') }}" alt="Gambar Selamat Datang" class="img-fluid">
        </div>
        <div class="col-md-8">
            <div class="welcome-text">
                <div class="welcome-badge">
                    <i class="bi bi-emoji-smile"></i> Selamat Datang
                </div>
                <h2>Halo, <b>{{ Session::get('detailUser')->nama }}</b>!</h2>
                <p>
                    Senang melihatmu di <b>Sistem Informasi Praktek Industri</b>
                </p>
                @php
                    $userId = Session::get('detailUser')->id;
                    $groupInfo = groupInfo($userId);
                    $verified = isPembekalanVerified($userId);
                @endphp

                @if (!$verified)
                    <div class="alert alert-warning">
                        Kamu belum mengikuti pembekalan atau Pembekalanmu belum diverifikasi.  
                        Silakan tunggu verifikasi pembekalan sebelum membuat pengelompokan.
                    </div>

                @elseif ($verified && $groupInfo == '')
                    <div class="alert alert-warning">
                        Kamu belum mengisi data pengelompokan.<br>
                        <a href="{{ route('group.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Buat Pengelompokan
                        </a>
                    </div>

                @else
                    <div class="alert alert-success">
                        Kamu sudah mengisi data pengelompokan.<br>
                        <a href="{{ route('group.show', encrypt0($groupInfo->id)) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Lihat Pengelompokan
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
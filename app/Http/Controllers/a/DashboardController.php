<?php

namespace App\Http\Controllers\a;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\LokasiPi;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $d['title'] = 'Dashboard';

        // ==== Statistik ringkas ====
        $d['total_kelompok']  = Group::count();
        $d['total_mahasiswa'] = Mahasiswa::count();
        $d['total_dosen']     = Dosen::count();
        $d['total_lokasi']    = LokasiPi::count();

        // ==== Status kelompok PI ====
        // Menunggu verifikasi : sudah dikirim, belum diverifikasi admin
        // Sedang PI           : disetujui admin, belum diverifikasi selesai
        // Selesai             : disetujui admin & sudah diverifikasi selesai
        // Ditolak             : admin_verify = X
        $menunggu = Group::where('send', 'Y')->where('admin_verify', 'N')->count();
        $sedang   = Group::where('admin_verify', 'Y')->where('done_verify', '!=', 'Y')->count();
        $selesai  = Group::where('admin_verify', 'Y')->where('done_verify', 'Y')->count();
        $ditolak  = Group::where('admin_verify', 'X')->count();

        $d['status_kelompok'] = [
            'labels' => ['Menunggu Verifikasi', 'Sedang PI', 'Selesai', 'Ditolak'],
            'series' => [$menunggu, $sedang, $selesai, $ditolak],
        ];
        $d['total_sedang']  = $sedang;
        $d['total_selesai'] = $selesai;

        // ==== Kelompok per tahun ====
        $perTahun = Group::select('year', DB::raw('COUNT(*) as total'))
            ->groupBy('year')
            ->orderBy('year')
            ->get();
        $d['kelompok_per_tahun'] = [
            'labels' => $perTahun->pluck('year'),
            'series' => $perTahun->pluck('total'),
        ];

        // ==== Top lokasi PI berdasarkan jumlah kelompok yang sudah selesai PI ====
        $topLokasi = LokasiPi::leftJoin('groups', function ($join) {
                $join->on('groups.lokasi_pi_id', '=', 'lokasi_pis.id')
                    ->where('groups.admin_verify', '=', 'Y')
                    ->where('groups.done_verify', '=', 'Y');
            })
            ->select('lokasi_pis.lokasi_pi', DB::raw('COUNT(groups.id) as total'))
            ->groupBy('lokasi_pis.id', 'lokasi_pis.lokasi_pi')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        $d['top_lokasi'] = [
            'labels' => $topLokasi->pluck('lokasi_pi'),
            'series' => $topLokasi->pluck('total'),
        ];

        return view('a.pages.dashboard', $d);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DokumenLaporansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('dokumen_laporans')->insert([
            [
                'kode' => 'laporan_fix',
                'dokumen' => 'Laporan FIX',
                'inserted_by' => 1, // Ganti dengan user ID default jika perlu
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kode' => 'nilai_pi',
                'dokumen' => 'Nilai PI',
                'inserted_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kode' => 'spk_ia',
                'dokumen' => 'SPK dan IA',
                'inserted_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'kode' => 'bukti_setoran',
                'dokumen' => 'Lembar Bukti Setoran Laporan PI',
                'inserted_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

    }
}

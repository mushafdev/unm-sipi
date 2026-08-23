<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class JenisDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('jenis_dokumens')->insert([
             ['jenis_dokumen'=>'izin observersi','kode'=>'IOB','starting_number'=>2122,'created_at' => now(), 'updated_at' => now()],
             ['jenis_dokumen'=>'izin pi','kode'=>'IPI','starting_number'=>2122,'created_at' => now(), 'updated_at' => now()],
             ['jenis_dokumen'=>'permohonan pembimbing','kode'=>'PPB','starting_number'=>2122,'created_at' => now(), 'updated_at' => now()],
         ]);

    }
}

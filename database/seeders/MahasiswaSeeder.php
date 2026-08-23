<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users=User::create(
            ['type'=> 'mahasiswas','role'=>'mahasiswa','email'=>'alimuddin@gmail.com','username'=>'mhs001','password'=>bcrypt('mhs001'),'aktif'=>'Y','created_at' => now(), 'updated_at' => now()],
            
         );
         DB::table('mahasiswas')->insert([
             ['user_id'=> $users->id,'nama'=>'Alimuddin','prodi_id'=>2,'nim'=>'20240201','kelas'=>'TEKOM A','telp'=>'085212555432','created_at' => now(), 'updated_at' => now()],
        ]);
        $users=User::create(
            ['type'=> 'mahasiswas','role'=>'mahasiswa','email'=>'herlina@gmail.com','username'=>'mhs002','password'=>bcrypt('mhs002'),'aktif'=>'Y','created_at' => now(), 'updated_at' => now()],
          
         );
         DB::table('mahasiswas')->insert([
             ['user_id'=> $users->id,'nama'=>'Harlina','prodi_id'=>2,'nim'=>'20240202','kelas'=>'TEKOM A','telp'=>'08544235432','created_at' => now(), 'updated_at' => now()],
        ]);
        $users=User::create(
            ['type'=> 'mahasiswas','role'=>'mahasiswa','email'=>'basuki@gmail.com','username'=>'mhs003','password'=>bcrypt('mhs003'),'aktif'=>'Y','created_at' => now(), 'updated_at' => now()],
          
         );
         DB::table('mahasiswas')->insert([
             ['user_id'=> $users->id,'nama'=>'Basuki','prodi_id'=>2,'nim'=>'20240203','kelas'=>'TEKOM A','telp'=>'08534325432','created_at' => now(), 'updated_at' => now()],
          ]);
        $users=User::create(
            ['type'=> 'mahasiswas','role'=>'mahasiswa','email'=>'jajang@gmail.com','username'=>'mhs004','password'=>bcrypt('mhs004'),'aktif'=>'Y','created_at' => now(), 'updated_at' => now()],
      
         );
         DB::table('mahasiswas')->insert([
             ['user_id'=> $users->id,'nama'=>'Jajang','prodi_id'=>2,'nim'=>'20240204','kelas'=>'TEKOM A','telp'=>'08521253232','created_at' => now(), 'updated_at' => now()],
         ]);

    }
}

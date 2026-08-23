<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superadmin = Role::create(['name' => 'superadmin']);
        $admin = Role::create(['name' => 'admin']);
        $mahasiswa = Role::create(['name' => 'mahasiswa']);
        $dosen = Role::create(['name' => 'dosen']);

        $users=User::create(
        ['type'=>'admins','email'=>'mushafdev@gmail.com','role'=>'superadmin', 'username'=>'superadmin','password' => Hash::make('#Super123'),'created_at' => now(), 'updated_at' => now(),'aktif'=>'Y'],
        );
        DB::table('admins')->insert([
            ['user_id'=> $users->id ,'nama'=>'Mushaf','alamat'=>'Jl. Alamat','telp'=>'085423444567','photo'=>NULL,'created_at' => now(), 'updated_at' => now()],
        ]);
        $users->assignRole($superadmin);

        $users=User::create(
        ['type'=>'admins','email'=>'leonardo@gmail.com','role'=>'admin', 'username'=>'admin123','password' => Hash::make('#Admin123'),'created_at' => now(), 'updated_at' => now(),'aktif'=>'Y'],
        );
        DB::table('admins')->insert([
            ['user_id'=> $users->id ,'nama'=>'Leonardo','alamat'=>'Jl. Jalan','telp'=>'0854331212','photo'=>NULL,'created_at' => now(), 'updated_at' => now()],
        ]);

        $users->assignRole($admin);

    }
}

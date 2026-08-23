<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Dosen hanya boleh MELIHAT Pengelompokan, Verifikasi Selesai PI,
 * dan Laporan & Upload Nilai untuk kelompok yang dia bimbing.
 */
class DosenAksesTest extends TestCase
{
    private ?int $dosenId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Ambil dosen yang punya kelompok lengkap (sudah selesai + upload laporan)
        $this->dosenId = DB::table('groups')
            ->where('send','Y')
            ->where('done','Y')
            ->where('admin_verify','Y')
            ->where('laporan','Y')
            ->whereNotNull('pembimbing_id')
            ->value('pembimbing_id');

        if (! $this->dosenId) {
            $this->markTestSkipped('Belum ada data kelompok dengan pembimbing untuk diuji.');
        }
    }

    private function loginAsDosen()
    {
        $dosen = DB::table('dosens')->where('id', $this->dosenId)->first();
        $user  = User::find($dosen->user_id);

        return $this->actingAs($user)->withSession([
            'id' => $user->id,
            'role' => $user->role,
            'detailUser' => $dosen,
        ]);
    }

    public function test_daftar_data_dibatasi_pada_kelompok_bimbingannya(): void
    {
        $t = $this->loginAsDosen();
        $ajax = ['X-Requested-With' => 'XMLHttpRequest'];

        $kasus = [
            ['/group', 'group.get_group',
                DB::table('groups')->where('send','Y')],
            ['/verifikasi-pi', 'verifikasi-pi.get_verifikasi_pi',
                DB::table('groups')->where('done','Y')->where('admin_verify','Y')],
            ['/laporan-nilai', 'laporan-nilai.get_laporan_nilai',
                DB::table('groups')->where('done','Y')->where('admin_verify','Y')->where('laporan','Y')],
        ];

        foreach ($kasus as [$halaman, $routeData, $query]) {
            $t->get($halaman)->assertStatus(200);

            $r = $t->get(route($routeData), $ajax);
            $r->assertStatus(200);

            $this->assertEqualsCanonicalizing(
                $query->where('pembimbing_id', $this->dosenId)->pluck('id')->all(),
                collect($r->json('data'))->pluck('id')->all(),
                $routeData.' tidak dibatasi ke kelompok bimbingan dosen'
            );
        }
    }

    public function test_detail_kelompok_lain_ditolak(): void
    {
        $t = $this->loginAsDosen();

        $milik = DB::table('groups')->where('pembimbing_id', $this->dosenId)
            ->where('done','Y')->where('admin_verify','Y')->where('laporan','Y')->first();
        $lain  = DB::table('groups')->whereNotNull('pembimbing_id')
            ->where('pembimbing_id','<>', $this->dosenId)
            ->where('done','Y')->where('admin_verify','Y')->where('laporan','Y')->first();

        foreach (['group.show','verifikasi-pi.show','laporan-nilai.show'] as $route) {
            $t->get(route($route, encrypt0($milik->id)))->assertStatus(200);
            $t->get(route($route, encrypt0($lain->id)))->assertStatus(403);
        }
    }

    public function test_detail_bersifat_lihat_saja(): void
    {
        $t = $this->loginAsDosen();

        $milik = DB::table('groups')->where('pembimbing_id', $this->dosenId)
            ->where('done','Y')->where('admin_verify','Y')->where('laporan','Y')->first();

        $t->get(route('group.show', encrypt0($milik->id)))
            ->assertStatus(200)
            ->assertDontSee('id="admin_verify"', false)
            ->assertDontSee('id="verifikasi"', false)
            ->assertDontSee('search-pembimbing', false);

        $t->get(route('verifikasi-pi.show', encrypt0($milik->id)))
            ->assertStatus(200)
            ->assertDontSee('id="done_verify"', false)
            ->assertDontSee('id="verifikasi"', false);

        $t->get(route('laporan-nilai.show', encrypt0($milik->id)))
            ->assertStatus(200)
            ->assertDontSee('id="laporan_verify"', false)
            ->assertDontSee('id="verifikasi"', false);
    }

    public function test_aksi_tulis_ditolak_dan_data_tidak_berubah(): void
    {
        $t = $this->loginAsDosen();

        $milik = DB::table('groups')->where('pembimbing_id', $this->dosenId)->first();
        $id = encrypt0($milik->id);

        $t->post(route('group.verifikasi'), ['id'=>$id,'pembimbing_id'=>$id,'admin_verify'=>'Y'])->assertStatus(403);
        $t->post(route('group.update_pl'), ['id'=>$id,'nama_pembimbing_lapangan'=>'x'])->assertStatus(403);
        $t->post(route('group.send'), ['id'=>$id])->assertStatus(403);
        $t->post(route('group.destroy_item'), ['id'=>$id])->assertStatus(403);
        $t->delete(route('group.destroy', $id))->assertStatus(403);
        $t->get(route('group.edit', $id))->assertStatus(403);
        $t->get(route('group.create'))->assertStatus(403);
        $t->post(route('verifikasi-pi.verifikasi'), ['id'=>$id,'done_verify'=>'Y'])->assertStatus(403);
        $t->post(route('laporan-nilai.verifikasi'), ['id'=>$id,'laporan_verify'=>'Y'])->assertStatus(403);

        $after = DB::table('groups')->where('id', $milik->id)->first();
        $this->assertEquals($milik->admin_verify, $after->admin_verify);
        $this->assertEquals($milik->done_verify, $after->done_verify);
        $this->assertEquals($milik->laporan_verify, $after->laporan_verify);
        $this->assertEquals($milik->nama_pembimbing_lapangan, $after->nama_pembimbing_lapangan);
    }

    public function test_menu_dosen_sesuai_hak_akses(): void
    {
        $r = $this->loginAsDosen()->get('/admin/dashboard');

        $r->assertStatus(200)
            ->assertSee('Pengelompokan')
            ->assertSee('Verifikasi Selesai Pi')
            ->assertSee('Laporan & Upload Nilai', false)
            ->assertDontSee('Permission')
            ->assertDontSee(route('users.index'))
            ->assertDontSee(route('mahasiswa.index'));
    }

    public function test_superadmin_masih_bisa_verifikasi(): void
    {
        $user = User::where('role','superadmin')->first();
        if (! $user) {
            $this->markTestSkipped('Tidak ada user superadmin.');
        }
        $detail = DB::table($user->type)->where('user_id',$user->id)->first();

        $milik = DB::table('groups')->whereNotNull('pembimbing_id')
            ->where('done','Y')->where('admin_verify','Y')->where('laporan','Y')->first();

        $this->actingAs($user)->withSession([
            'id' => $user->id,
            'role' => $user->role,
            'detailUser' => $detail,
        ])->get(route('laporan-nilai.show', encrypt0($milik->id)))
            ->assertStatus(200)
            ->assertSee('id="laporan_verify"', false)
            ->assertSee('id="verifikasi"', false);
    }
}

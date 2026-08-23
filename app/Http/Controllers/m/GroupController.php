<?php

namespace App\Http\Controllers\m;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupDetail;
use App\Models\GroupDokumen;
use App\Models\JenisDokumen;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Rules\CheckGroupMahasiswa;
use App\Rules\CheckGroup;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class GroupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:group-verifikasi', only : ['verifikasi']),
            new Middleware('deny-dosen', only : [
                'create','store','edit','update','destroy','destroy_item',
                'send','update_pl','verifikasi','verifikasi_mhs'
            ]),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $d['title']='Kelompok';
        $role=$request->session()->get('role');
        if($role=='mahasiswa'){
            return redirect()->route('group.create');
        }elseif($role=='superadmin' || $role=='admin' || $role=='dosen'){
            return view('a.pages.group.index',$d);
        }
    }

    public function get_group(Request $request)
    {
        if ($request->ajax()) {
            $data = Group::leftJoin('lokasi_pis','lokasi_pis.id','=','groups.lokasi_pi_id')
            ->leftJoin('dosens','groups.pembimbing_id','=','dosens.id')
            ->select('groups.*','lokasi_pis.lokasi_pi','dosens.nama as pembimbing','dosens.nip as pembimbing_nip')->where('groups.send','Y')
            ->orderByRaw('FIELD(admin_verify, "N", "Y", "X")')
            ->orderBy('groups.created_at','desc');
            if(isDosen()){
                $data=$data->where('groups.pembimbing_id',dosenId());
            }
            $data=$data->get();
            $data=$data->map(function($row){
                $mahasiswas=DB::table('group_details')->join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
                ->select('group_details.*','mahasiswas.nama','mahasiswas.nim')->where('group_id',$row->id)->get();
                $row->mahasiswas='';
                foreach($mahasiswas as $dt){
                    $row->mahasiswas.='<span class="badge bg-info me-1">'.$dt->nama.'-'.$dt->nim.'</span>';
                }
                
                
                return $row;
            });
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        
                        $btn='<div class="btn-group">
                        <button class="btn" type="button"  data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu">';
                        $btn.='<li><h6 class="dropdown-header">Action </h6></li>';
                        $btn.= '<li><a href="'.route('group.show',encrypt0($row->id)).'" title="Edit" class="dropdown-item detail"><i class="icon-mid bi bi-eye me-2" aria-hidden="true"></i> Detail</a></li>';
                        $btn.='</ul>';
                        $btn.='</div>';
                        return $btn;
                    })
                    ->addColumn('pembimbing', function($row){
                        
                        return $row->pembimbing?$row->pembimbing:'-';
                    })
                    ->addColumn('mahasiswas', function($row){
                        
                        return $row->mahasiswas;
                    })
                    ->addColumn('admin_verify', function($row){
                        
                        return '<span class="badge '.adminVerifyStatus($row->admin_verify,'badge').'">'.Str::remove('admin', adminVerifyStatus($row->admin_verify,'text')).'</span>';
                    })

                    ->rawColumns(['action','aktif','mahasiswas','admin_verify'])
                    ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $role = $request->session()->get('role');

        if ($role == 'mahasiswa') {

            $mahasiswa_id = $request->session()->get('detailUser')->id;

            // ❗CEK: Hanya boleh jika pembekalan_verify = 'Y'
            if (!isPembekalanVerified($mahasiswa_id)) {
                abort(403, 'Anda belum mengikuti pembekalan atau Pembekalanmu belum diverifikasi. Tidak dapat membuat kelompok.');
            }

            // ❗CEK: Sudah punya kelompok → redirect
            $existing = GroupDetail::join('groups','groups.id','=','group_details.group_id')->where('group_details.mahasiswa_id', $mahasiswa_id)->first();
            if ($existing) {
                return redirect()->route('group.show', encrypt0($existing->group_id));
            }
        }

        return view('u.pages.group.create', [
            'title' => 'Buat Kelompok'
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'lokasi_pi'=>'nullable',
            'lokasi_pi_id'=>[
                'required',
                new CheckGroup('')
            ],
            'start_month'=>'required',
            'end_month'=>'required',
            'year'=>'required',
            'mahasiswa_id.*'=>[
                'required',
                new CheckGroupMahasiswa
            ],
        ];

        $messages = [
            'mahasiswa_id.*.required' => 'The nim field is required',
        ];

        $request->validate($rules,$messages);

        try {
            $error='';
            // BARU
            DB::transaction(function() use ($request,&$error) {

                $creatorId = $request->session()->get('detailUser')->id;

                // Cek pembuat kelompok di dalam transaction (mencegah double-submit)
                $existingCreator = GroupDetail::where('mahasiswa_id', $creatorId)
                    ->whereNotNull('group_id')
                    ->lockForUpdate()
                    ->exists();
                if ($existingCreator) {
                    throw new \Exception('Anda sudah terdaftar dalam kelompok lain.');
                }

                $field = new Group;

                $field->lokasi_pi_id = decrypt0($request->lokasi_pi_id);
                $field->start_month = $request->start_month;
                $field->end_month = $request->end_month;
                $field->year = $request->year;
                $field->mahasiswa_id = $creatorId;
                $field->save();

                if (!empty($request->mahasiswa_id)) {
                    foreach ($request->mahasiswa_id as $mhsIdEnc) {
                        $mhsId = decrypt0($mhsIdEnc);

                        // Cek di dalam transaction — mencegah race condition antar request
                        $alreadyInGroup = GroupDetail::where('mahasiswa_id', $mhsId)
                            ->whereNotNull('group_id')
                            ->lockForUpdate()
                            ->exists();
                            
                        if ($alreadyInGroup) {
                            throw new \Exception('Salah satu mahasiswa sudah terdaftar dalam kelompok lain.');
                        }

                        GroupDetail::updateOrCreate(
                            ['mahasiswa_id' => $mhsId],
                            [
                                'group_id' => $field->id,
                                'verify'   => ($mhsId == $creatorId) ? 'Y' : 'N'
                            ]
                        );
                    }
                }
            });
            return response()->json([
                'text' => 'Data sukses disimpan',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' =>$e->getMessage()
            ],500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
    {
        $d['title']='Detail Kelompok';
        $groups=Group::join('lokasi_pis','groups.lokasi_pi_id','=','lokasi_pis.id')
        ->leftJoin('dosens','groups.pembimbing_id','=','dosens.id')
        ->where('groups.id',decrypt0($id))
        ->select('groups.*','lokasi_pis.lokasi_pi','lokasi_pis.alamat','lokasi_pis.kota','dosens.nama as pembimbing','dosens.nip as pembimbing_nip','groups.nama_pembimbing_lapangan as pembimbing_lapangan','groups.jabatan_pembimbing_lapangan as pembimbing_lapangan_jabatan')->first();

        if(isDosen() && (empty($groups) || $groups->pembimbing_id != dosenId())){
            abort(403,'Anda hanya dapat melihat kelompok bimbingan anda.');
        }

        $group_details=GroupDetail::join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
        ->join('prodis','prodis.id','=','mahasiswas.prodi_id')
        ->where('group_details.group_id',$groups->id)
        ->select('group_details.*','mahasiswas.nama','mahasiswas.nim','mahasiswas.kelas','prodis.prodi')->get();
        $d['data']=$groups;
        $d['detail']=$group_details;

        $role=$request->session()->get('role');
        if($role=='mahasiswa'){
            return view('u.pages.group.show',$d);
        }elseif($role=='superadmin' || $role=='admin' || $role=='dosen'){
            $d['admin_verifys']=array('N'=>'Belum Diverifikasi','Y'=>'Disetujui','X'=>'Ditolak');
            return view('a.pages.group.show',$d);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request,string $id)
    {

        $role=$request->session()->get('role');

        $d['title']='Edit Kelompok';
        $groups=Group::join('lokasi_pis','groups.lokasi_pi_id','=','lokasi_pis.id')
        ->leftJoin('dosens','groups.pembimbing_id','=','dosens.id')
        ->where('groups.id',decrypt0($id))
        ->select('groups.*','lokasi_pis.lokasi_pi','lokasi_pis.alamat','lokasi_pis.kota','dosens.nama as pembimbing','dosens.nip as pembimbing_nip')->first();
       $group_details=GroupDetail::join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')->where('group_details.group_id',$groups->id)
        ->select('group_details.*','mahasiswas.nama','mahasiswas.nim','mahasiswas.kelas')->get();
        $d['data']=$groups;
        $d['detail']=$group_details;

        if($role=='mahasiswa'){
            return view('u.pages.group.edit',$d);
        }elseif($role=='superadmin' || $role=='admin'){
            return view('a.pages.group.edit',$d);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        $rules = [
            'lokasi_pi'=>'nullable',
            'lokasi_pi_id'=>[
                'required',
                new CheckGroup($id)
            ],
            'pembimbing_id'=>'nullable',
            'start_month'=>'required',
            'end_month'=>'required',
            'year'=>'required',
            'mahasiswa_id.*'=>[
                'required',
                new CheckGroupMahasiswa
            ],
        ];

        $messages = [
            'mahasiswa_id.*.required' => 'The nim field is required',
        ];

        $request->validate($rules,$messages);

        try {
            $error='';
            DB::transaction(function() use ($request,&$error,&$id) {
        
                $field = Group::find(decrypt0($id));

                $field->lokasi_pi_id = decrypt0($request->lokasi_pi_id);
                if(!empty($request->pembimbing_id)){
                    $field->pembimbing_id = decrypt0($request->pembimbing_id);
                }
                $field->start_month = $request->start_month;
                $field->end_month = $request->end_month;
                $field->year = $request->year;
                if($request->session()->get('role')=='mahasiswa'){
                    $field->mahasiswa_id =  $request->session()->get('detailUser')->id;
                }
                $field->save();
                if(!empty($request->mahasiswa_id)){
                    $count=count($request->mahasiswa_id);
                    if ($count > 0) {
                        for ($i = 0; $i < $count; $i++) {

                            $mhsId = decrypt0($request->mahasiswa_id[$i]);

                            // Cek apakah mahasiswa sudah di kelompok LAIN (bukan kelompok ini)
                            $alreadyInOtherGroup = GroupDetail::where('mahasiswa_id', $mhsId)
                                ->whereNotNull('group_id')
                                ->where('group_id', '<>', $field->id)
                                ->lockForUpdate()
                                ->exists();
                            if ($alreadyInOtherGroup) {
                                throw new \Exception('Mahasiswa sudah terdaftar dalam kelompok lain.');
                            }

                            GroupDetail::updateOrCreate(
                                ['mahasiswa_id' => $mhsId],
                                [
                                    'group_id' => $field->id,
                                    'verify'   => ($mhsId == $field->mahasiswa_id) ? 'Y' : 'N'
                                ]
                            );

                        }
                    }
                }

                
                
            });
            return response()->json([
                'text' => 'Data sukses diupdate',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' =>$e->getMessage()
            ],500);
        }
    }
    
    public function send(Request $request)
    {
        
        $rules = [
            'id'=>'required',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {
            $error='';
            DB::transaction(function() use ($request) {
        
                $field = Group::find(decrypt0($request->id));
                $field->send = 'Y';
                $field->admin_verify = 'N';
                $field->save();
            });
            return response()->json([
                'text' => 'Data sukses dikirim',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' =>$e->getMessage()
            ],500);
        }
    }
    
    // public function create_no_dokumen($jenis_dokumen,$kode,$start)
    // {
    //     $year=date('Y');
    //     $max_id=DB::table('group_dokumens')->selectRaw("MAX(SUBSTRING_INDEX(nomor, '/', 1)) AS max_id")->where('jenis_dokumen',$jenis_dokumen)->first();
    //     if(isset($max_id->max_id)){
    //         $nomor=((int)$max_id->max_id+1).'/PI/'.$kode.'/UN36.2/JTIK/P/'.monthToRomawi(date('n')).'/'.$year;
    //     }else {
    //         $nomor=$start.'/PI/'.$kode.'/UN36.2/JTIK/P/'.monthToRomawi(date('n')).'/'.$year;
    //     }
    //     return $nomor;
    // }
    public function create_no_dokumen($jenis_dokumen,$kode,$start)
    {
        // /UN36.2/JTIK/V/2025
        $year=date('Y');
        $max_id=DB::table('group_dokumens')->selectRaw("MAX(SUBSTRING_INDEX(nomor, '/', 1)) AS max_id")->where('jenis_dokumen',$jenis_dokumen)->first();
        if(isset($max_id->max_id)){
            $nomor=((int)$max_id->max_id+1).'/UN36.2/JTIK/V/'.$year;
        }else {
            $nomor=$start.'/UN36.2/JTIK/V/'.$year;
        }
        return $nomor;
    }


    public function update_pl(Request $request)
    {
        
        $rules = [
            'id'=>'required',
            'nama_pembimbing_lapangan'=>'nullable',
            'jabatan_pembimbing_lapangan'=>'nullable',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {
            $error='';
            DB::transaction(function() use ($request) {
        
                $field = Group::find(decrypt0($request->id));
                $field->nama_pembimbing_lapangan = $request->nama_pembimbing_lapangan;
                $field->jabatan_pembimbing_lapangan = $request->jabatan_pembimbing_lapangan;
                $field->save();
                


            });
            return response()->json([
                'text' => 'Data sukses diupdate',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' =>$e->getMessage()
            ],500);
        }
    }

    public function verifikasi(Request $request)
    {
        
        $rules = [
            'id'=>'required',
            'pembimbing_id'=>'required',
            'admin_verify'=>'required',
            'catatan'=>'nullable',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {
            $error='';
            DB::transaction(function() use ($request) {
        
                $field = Group::find(decrypt0($request->id));

                $field->pembimbing_id = decrypt0($request->pembimbing_id);
                $field->catatan = $request->catatan;
                $field->admin_verify = $request->admin_verify;
                if($request->admin_verify=='X'){
                    $field->send='N';   
                }
                $field->save();
                if($field->admin_verify=='Y'){
                    $cekDokumen=GroupDokumen::where('group_id',$field->id)->count();
                    if($cekDokumen==0){
                        $jenisDokumens=JenisDokumen::get();
                        foreach($jenisDokumens as $dt){
                            $newDokumen= new GroupDokumen;
                            $newDokumen->group_id=$field->id;
                            $newDokumen->jenis_dokumen=$dt->jenis_dokumen;
                            $newDokumen->nomor=$this->create_no_dokumen($dt->jenis_dokumen,$dt->kode,$dt->starting_number);
                            $newDokumen->save();
                        }
                    }
                   
                }
                
                


            });
            return response()->json([
                'text' => 'Data sukses diverifikasi',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' =>$e->getMessage()
            ],500);
        }
    }
    
    public function verifikasi_mhs(Request $request)
    {
        
        $rules = [
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {
            $error='';
            DB::transaction(function() use ($request) {
                $mahasiswa_id=$request->session()->get('detailUser')->id;
                $field = GroupDetail::where('mahasiswa_id',$mahasiswa_id);
                $field->update([
                    'verify'=>'Y'
                ]) ;
            });
            return response()->json([
                'text' => 'Data sukses diverifikasi',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' =>$e->getMessage()
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy_item(Request $request)
    {
        $id=decrypt0($request->id);
        try {
            $fieldDetail = GroupDetail::find($id);
            $fieldDetail->update([
                'group_id' => null
            ]);
            return response()->json([
                'text' => 'Data sukses dihapus',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' =>$e->getMessage()
            ],500);
        }
    }
    
    public function destroy(string $id)
    {
        try {
            $id = decrypt0($id);
            $group = Group::findOrFail($id);

            // Pembatasan: Tidak boleh hapus jika status done = 'Y'
            if ($group->done == 'Y') {
                return response()->json([
                    'message' => 'Kelompok yang sudah selesai (Done) tidak dapat dihapus.'
                ], 403);
            }

            DB::transaction(function() use ($id, $group) {
                // Unlink members instead of deleting them
                GroupDetail::where('group_id', $id)->update(['group_id' => null]);
                
                // Delete related documents
                GroupDokumen::where('group_id', $id)->delete();

                // Delete other related data to prevent foreign key constraints
                DB::table('group_buktis')->where('group_id', $id)->delete();
                DB::table('group_laporans')->where('group_id', $id)->delete();
                DB::table('logbooks')->where('group_id', $id)->delete();
                
                // Delete the group
                $group->delete();
            });
            return response()->json([
                'text' => 'Data sukses dihapus',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\a;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dosen;
use App\Models\Group;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\User;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class DosenController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:dosen-list', only : ['index','get_dosen','get_data']),
            new Middleware('permission:dosen-create', only : ['store']),
            new Middleware('permission:dosen-edit', only : ['store']),
            new Middleware('permission:dosen-delete', only : ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $d['title']='Dosen';
        $d['jurusans']=Jurusan::get();
        $d['prodis']=Prodi::get();
        return view('a.pages.dosen.index',$d);
    }

    public function get_dosen(Request $request)
    {
        if ($request->ajax()) {
            $data = Dosen::
            leftJoin('prodis','prodis.id','=','dosens.prodi_id')
            ->leftJoin('jurusans','jurusans.id','=','prodis.jurusan_id')
            ->select('dosens.*','prodis.prodi','jurusans.jurusan')
            ->selectRaw('(SELECT COUNT(*) FROM `groups` WHERE `groups`.pembimbing_id = dosens.id AND `groups`.admin_verify = "Y" AND `groups`.done_verify != "Y") as jumlah_kelompok')
            ->orderBy('dosens.created_at','desc')->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        
                        $btn='<div class="btn-group">
                        <button class="btn" type="button"  data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu">';
                        $btn.='<li><h6 class="dropdown-header">Action </h6></li>';

                        if (Gate::allows('dosen-edit')) {
                            $btn.= '<li><a href="#" data-id="' . encrypt0($row->id) . '" title="Edit" class="dropdown-item edit" data-bs-toggle="modal" data-bs-target="#modal"><i class="icon-mid bi bi-pencil-square me-2" aria-hidden="true"></i> Edit</a></li>';
                        }

                        if (Gate::allows('dosen-delete')) {
                            $btn.= '<li><a href="#" class="dropdown-item delete text-danger" title="Hapus" title="Hapus" data-id="'.encrypt0($row->id).'"><i class="icon-mid bi bi-trash me-2" aria-hidden="true"></i> Delete</a></li>';
                        }
                        $btn.='</ul>';
                        $btn.='</div>';
                        return $btn;
                    })
                    ->addColumn('aktif', function($row){
                        if($row->aktif=='Y'){
                            $aktif='<span class="badge bg-success">Aktif</span>';
                        }else{
                            $aktif='<span class="badge bg-danger">Tidak Aktif</span>';
                        }
                        return $aktif;
                    })
                    ->addColumn('id_enc', function($row){

                        return encrypt0($row->id);
                    })
                    ->addColumn('jumlah_kelompok', function($row){
                        if($row->jumlah_kelompok > 0){
                            return '<a href="#" class="badge bg-primary lihat-kelompok" style="cursor:pointer" data-id="'.encrypt0($row->id).'" data-nama="'.e($row->nama).'">'.$row->jumlah_kelompok.' Kelompok</a>';
                        }
                        return '<span class="badge bg-secondary">0 Kelompok</span>';
                    })

                    ->rawColumns(['action','aktif','user_photo','role','jumlah_kelompok'])
                    ->make(true);
        }
    }

    public function get_data(Request $request)
    {
        $id = decrypt0($request->id);
        $q = Dosen::where('id', $id)->first();

        return response()->json([
            'id' => encrypt0($q->id),
            'result' => $q,
        ]);
    }

    public function get_kelompok(Request $request)
    {
        $id = decrypt0($request->id);
        $groups = Group::leftJoin('lokasi_pis', 'lokasi_pis.id', '=', 'groups.lokasi_pi_id')
            ->where('groups.pembimbing_id', $id)
            ->where('groups.admin_verify', 'Y')
            ->where('groups.done_verify', '!=', 'Y')
            ->select('groups.*', 'lokasi_pis.lokasi_pi')
            ->orderBy('groups.year', 'desc')
            ->get();

        $data = $groups->map(function ($g) {
            $members = DB::table('group_details')
                ->join('mahasiswas', 'mahasiswas.id', '=', 'group_details.mahasiswa_id')
                ->where('group_details.group_id', $g->id)
                ->select('mahasiswas.nama', 'mahasiswas.nim')
                ->get();
            $anggota = $members->map(function ($m) {
                return $m->nama . ' (' . $m->nim . ')';
            })->implode(', ');

            return [
                'url'     => route('group.show', encrypt0($g->id)),
                'anggota' => $anggota != '' ? $anggota : '-',
                'jumlah'  => $members->count(),
                'lokasi'  => $g->lokasi_pi ?? '-',
                'tahun'   => $g->year,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $d['title']='Tambah Dosen';
        return view('a.pages.dosen.create',$d);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id='';
        if(!empty($request->id)){
            $id=decrypt0($request->id);
        }
        $rules = [
            'nama'=>'required',
            'email'=>'nullable|email:rfc,dns',
            'nip'=>'required|unique:dosens,nip,'.$id,
            'pangkat'=>'required',
            'golongan'=>'required',
            'jabatan'=>'required',
            'prodi_id'=>'required',
            'username'=>'nullable|unique:users|min:5|max:50',
            'password'=>'nullable|min:5|max:50',
            'aktif'=>'nullable',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {

            $error='';
            $text='';
            DB::transaction(function() use ($request,$id, &$text ) {
                if ($request->param == 'add') {

                    $user= new User;
                    $field = new Dosen;
                    $field->inserted_by =  $request->session()->get('id');
                    $text = 'Data sukses disimpan';
                } else {
                    $field = Dosen::find($id);
                    $field->updated_by =  $request->session()->get('id');
                    $text = 'Data sukses diupdate';
                    $user= User::find($field->user_id);
                }
                
                $user->type='dosens';
                $user->email=$request->email;
                $user->aktif = 'Y';
                $user->role = 'dosen';
                if(!empty($request->username)){
                    $user->username = $request->username;
                }
                if(!empty($request->password)){
                    $user->password = bcrypt($request->password);
                }
                $user->save();

                $field->user_id = $user->id;
                $field->nama = $request->nama;
                $field->nip = $request->nip;
                $field->pangkat = $request->pangkat;
                $field->golongan = $request->golongan;
                $field->jabatan = $request->jabatan;
                $field->prodi_id = $request->prodi_id;
                $field->save();

            });
            return response()->json([
                'text' => $text,
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
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $field = Dosen::find(decrypt0($id));
            $field->delete();
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
}

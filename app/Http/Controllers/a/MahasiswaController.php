<?php

namespace App\Http\Controllers\a;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\User;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class MahasiswaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:mahasiswa-list', only : ['index','get_mahasiswa','get_data']),
            new Middleware('permission:mahasiswa-create', only : ['store']),
            new Middleware('permission:mahasiswa-edit', only : ['store']),
            new Middleware('permission:mahasiswa-delete', only : ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $d['title']='Mahasiswa';
        $d['jurusans']=Jurusan::get();
        $d['prodis']=Prodi::get();
        return view('a.pages.mahasiswa.index',$d);
    }

    public function get_mahasiswa(Request $request)
    {
        if ($request->ajax()) {
            $data = Mahasiswa::
            leftJoin('users','users.id','=','mahasiswas.user_id')
            ->leftJoin('prodis','prodis.id','=','mahasiswas.prodi_id')
            ->leftJoin('jurusans','jurusans.id','=','prodis.jurusan_id')
            ->select('mahasiswas.*','users.email','users.aktif','prodis.prodi','jurusans.jurusan')
            ->orderBy('mahasiswas.created_at','desc')->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        
                        $btn='<div class="btn-group">
                        <button class="btn" type="button"  data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu">';
                        $btn.='<li><h6 class="dropdown-header">Action </h6></li>';
                        if (Gate::allows('mahasiswa-edit')) {
                            $btn.= '<li><a href="#" data-id="' . encrypt0($row->id) . '" title="Edit" class="dropdown-item edit" data-bs-toggle="modal" data-bs-target="#modal"><i class="icon-mid bi bi-pencil-square me-2" aria-hidden="true"></i> Edit</a></li>';
                        }
                        if (Gate::allows('mahasiswa-delete')) {
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

                    ->rawColumns(['action','aktif','user_photo','role'])
                    ->make(true);
        }
    }

    public function get_data(Request $request)
    {
        $id = decrypt0($request->id);
        $q = Mahasiswa::
        leftJoin('users','users.id','=','mahasiswas.user_id')
        ->where('mahasiswas.id', $id)
        ->select('mahasiswas.*','users.email','users.aktif')
        ->first();

        return response()->json([
            'id' => encrypt0($q->id),
            'result' => $q,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $d['title']='Tambah Mahasiswa';
        return view('a.pages.mahasiswa.create',$d);
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
            'kelas'=>'required',
            'prodi_id'=>'required',
            'username'=>'nullable|min:5|max:50|unique:users,username,'.$id,
            'password'=>'nullable|min:5|max:50',
            'aktif'=>'required',
        ];

        if ($request->param == 'add') {
            $rules['nim'] = 'required|unique:mahasiswas,nim';
        } else {
            $rules['nim'] = [
                'required',
                // Rule::unique('mahasiswas', 'nim')->ignore($id)
            ];
        }

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {

            $error='';
            $text='';
            DB::transaction(function() use ($request,$id, &$text ) {
                if ($request->param == 'add') {

                    $user= new User;
                    $field = new Mahasiswa;
                    $field->inserted_by =  $request->session()->get('id');
                    $text = 'Data sukses disimpan';
                } else {

                    $field = Mahasiswa::find($id);
                    $field->updated_by =  $request->session()->get('id');
                    $text = 'Data sukses diupdate';
                    $user= User::find($field->user_id);
                }
                
                $user->type='mahasiswas';
                $user->email=$request->email;
                $user->aktif = $request->aktif;
                $user->role = 'mahasiswa';
                if(!empty($request->username)){
                    $user->username = $request->username;
                }
                if(!empty($request->password)){
                    $user->password = bcrypt($request->password);
                }
                $user->save();

                $field->user_id = $user->id;
                $field->nama = $request->nama;
                $field->nim = $request->nim;
                $field->kelas = $request->kelas;
                $field->prodi_id = $request->prodi_id;
                $field->save();

                $user->assignRole($user->role);

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
            $field = Mahasiswa::find(decrypt0($id));
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

<?php

namespace App\Http\Controllers\a;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LokasiPi;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Fakultas;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class LokasiPiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // new Middleware('permission:lokasi pi-list', only : ['index','get_lokasi_pi','get_data']),
            new Middleware('permission:lokasi pi-create', only : ['create','store']),
            new Middleware('permission:lokasi pi-edit', only : ['edit','update']),
            new Middleware('permission:lokasi pi-delete', only : ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $d['title']='Lokasi Praktik Industri';
        $d['jurusans']=Jurusan::get();
        $d['prodis']=Prodi::get();
        return view('a.pages.lokasi_pi.index',$d);
    }

    public function get_lokasi_pi(Request $request)
    {
        if ($request->ajax()) {
            $data = LokasiPi::select('lokasi_pis.*')
                    ->selectRaw('(SELECT COUNT(*) FROM `groups` WHERE `groups`.lokasi_pi_id = lokasi_pis.id AND `groups`.admin_verify = "Y" AND `groups`.done_verify != "Y") as jumlah_kelompok');

            // Filter lokasi yang masih kosong (belum ada mahasiswa yang PI di sana)
            if ($request->filter_kosong == '1') {
                $data->whereRaw('(SELECT COUNT(*) FROM `groups` WHERE `groups`.lokasi_pi_id = lokasi_pis.id AND `groups`.admin_verify = "Y") = 0');
            }

            $data = $data->orderBy('lokasi_pis.created_at','desc')->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        
                        $btn='<div class="btn-group">
                        <button class="btn" type="button"  data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu">';
                        $btn.='<li><h6 class="dropdown-header">Action </h6></li>';
                        if (Gate::allows('lokasi pi-edit')) {
                            $btn.= '<li><a href="'.route('lokasi-pi.edit',encrypt0($row->id)).'" title="Edit" class="dropdown-item edit"><i class="icon-mid bi bi-pencil-square me-2" aria-hidden="true"></i> Edit</a></li>';
                        }
                        if (Gate::allows('lokasi pi-delete')) {
                            $btn.= '<li><a href="#" class="dropdown-item delete text-danger" title="Hapus" title="Hapus" data-id="'.encrypt0($row->id).'"><i class="icon-mid bi bi-trash me-2" aria-hidden="true"></i> Delete</a></li>';
                        }
                        $btn.='</ul>';
                        $btn.='</div>';
                        return $btn;
                    })
                    ->addColumn('id_enc', function($row){
                        
                        return encrypt0($row->id);
                    })
                    ->addColumn('kompetensi', function($row){
                        $explodedArray = explode(',', $row->kompetensi);

                        // Hasil looping
                        $result = '';
                        foreach ($explodedArray as $index => $value) {
                            $result.= '<span class="badge bg-info me-1">'.trim($value).'</span>'; 
                        }
                        return $result;
                    })
                    ->addColumn('jumlah_kelompok', function($row){
                        return '<span class="badge bg-primary">'.$row->jumlah_kelompok.' Kelompok</span>';
                    })

                    ->rawColumns(['action','aktif','kompetensi','role','jumlah_kelompok'])
                    ->make(true);
        }
    }

    public function get_data(Request $request)
    {
        $id = decrypt0($request->id);
        $q = LokasiPi::where('id', $id)->first();

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

        $d['title']='Tambah Lokasi Praktik Industri';
        return view('a.pages.lokasi_pi.create',$d);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'lokasi_pi'=>'required',
            'kota'=>'required',
            'alamat'=>'required',
            'telp'=>'required',
            'kompetensi'=>'nullable',
            'keterangan'=>'nullable',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {
            
            $field = new LokasiPi;
            $field->inserted_by =  $request->session()->get('id');
            $field->lokasi_pi = $request->lokasi_pi;
            $field->alamat = $request->alamat;
            $field->kota = $request->kota;
            $field->telp = $request->telp;
            $field->keterangan = $request->keterangan;
            $field->kompetensi = $request->kompetensi;

            $field->save();
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
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $d['title']='Edit Lokasi Praktik Industri';
        $d['data'] = LokasiPi::find(decrypt0($id));
        return view('a.pages.lokasi_pi.edit',$d);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'lokasi_pi'=>'required',
            'kota'=>'required',
            'alamat'=>'required',
            'telp'=>'required',
            'kompetensi'=>'nullable',
            'keterangan'=>'nullable',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {
            $field = LokasiPi::find(decrypt0($id));
            

            $field->inserted_by =  $request->session()->get('id');
            $field->lokasi_pi = $request->lokasi_pi;
            $field->alamat = $request->alamat;
            $field->kota = $request->kota;
            $field->telp = $request->telp;
            $field->keterangan = $request->keterangan;
            $field->kompetensi = $request->kompetensi;

            $field->updated_by =  $request->session()->get('id');

            $field->save();
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $field = LokasiPi::find(decrypt0($id));
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

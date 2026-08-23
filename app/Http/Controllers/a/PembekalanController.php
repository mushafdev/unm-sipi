<?php

namespace App\Http\Controllers\a;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GroupDetail;
use App\Models\Pembekalan;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PembekalanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:pembekalan-list', only : ['index','get_pembekalan','get_mahasiswa']),
            new Middleware('permission:pembekalan-create', only : ['create','store']),
            new Middleware('permission:pembekalan-edit', only : ['edit','update']),
            new Middleware('permission:pembekalan-verifikasi', only : ['verifikasi','verifikasi_update','print_absensi']),
            new Middleware('permission:pembekalan-delete', only : ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $d['title']='Pembekalan';
        return view('a.pages.pembekalan.index',$d);
    }

    public function get_pembekalan(Request $request)
    {
        if ($request->ajax()) {
            $data = Pembekalan::latest()->get();
            $data=$data->map(function($row){
                $total_mahasiswa=DB::table('group_details')->where('pembekalan_id',$row->id)->count();
                $telah_verifikasi=DB::table('group_details')->where('pembekalan_id',$row->id)->where('pembekalan_verify','Y')->count();
                $belum_verifikasi=DB::table('group_details')->where('pembekalan_id',$row->id)->where('pembekalan_verify','N')->count();
                $row->total_mahasiswa=$total_mahasiswa;
                $row->telah_verifikasi=$telah_verifikasi;
                $row->belum_verifikasi=$belum_verifikasi;
                $status='N';
                if($total_mahasiswa==$telah_verifikasi){
                    $status='Y';
                }
                $row->status=$status;
                
                return $row;
            });
            return Datatables::of($data->sortBy('status'))
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $btn='';
                        $btn='<div class="btn-group">
                        <button class="btn" type="button"  data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu">';
                        $btn.='<li><h6 class="dropdown-header">Action </h6></li>';
                        $btn.= '<li><a href="'.route('pembekalan.verifikasi',encrypt0($row->id)).'" title="Verifikasi" class="dropdown-item edit text-primary"><i class="icon-mid bi bi-check-square me-2" aria-hidden="true"></i> Verifikasi</a></li>';
                        $btn.= '<li><a href="'.route('pembekalan.edit',encrypt0($row->id)).'" title="Edit" class="dropdown-item edit"><i class="icon-mid bi bi-pencil-square me-2" aria-hidden="true"></i> Edit</a></li>';
                        $btn.= '<li><span class="dropdown-item  delete text-danger" title="Hapus" title="Hapus" data-id="'.encrypt0($row->id).'"><i class="icon-mid bi bi-trash me-2" aria-hidden="true"></i> Delete</span></li>';
                        $btn.='</ul>';
                        $btn.='</div>';
                        return $btn;
                    })
                   
                    ->addColumn('status', function($row){
                        if($row->status=='Y'){
                            $status='<span class="badge bg-success">Selesai</span>';
                        }else{
                            $status='<span class="badge bg-danger">Belum</span>';
                        }
                        return $status;
                    })
                    ->addColumn('tanggal', function($row){
                        return convertYmdToMdy2($row->tanggal);
                    })

                    ->rawColumns(['action','status','user_photo','role'])
                    ->make(true);
        }
    }

    public function get_mahasiswa(Request $request)
    {
        if ($request->ajax()) {
            if($request->param=='add'){
                $data = DB::table('mahasiswas')->leftJoin('group_details','mahasiswas.id','=','group_details.mahasiswa_id')
                ->whereNUll('group_id')
                ->where('group_details.pembekalan_id',NULL)
                ->select('mahasiswas.id as mahasiswa_id','mahasiswas.nama','mahasiswas.nim','mahasiswas.kelas')->get();
            }
            if($request->param=='edit'){
                $idPembekalan=decrypt0($request->id);
                // $data = GroupDetail::join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
                // ->whereRaw("pembekalan_id='$idPembekalan' OR (pembekalan_id is NULL )")
                // ->select('group_details.*','mahasiswas.nama','mahasiswas.nim','mahasiswas.kelas')->orderBy('pembekalan_id','desc')->get();
                $data = DB::table('mahasiswas')->leftJoin('group_details','mahasiswas.id','=','group_details.mahasiswa_id')
                ->whereNUll('group_id')
                ->where(function($query) use ($idPembekalan){
                    $query->where('group_details.pembekalan_id',NULL)
                          ->orWhere('group_details.pembekalan_id',$idPembekalan);
                })
                ->select('group_details.pembekalan_id','mahasiswas.id as mahasiswa_id','mahasiswas.nama','mahasiswas.nim','mahasiswas.kelas')->get();
             
            }
            if($request->param=='verifikasi'){
                $idPembekalan=decrypt0($request->id);
                $data = GroupDetail::join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
                ->where('pembekalan_id',$idPembekalan)
                ->select('group_details.*','mahasiswas.nama','mahasiswas.nim','mahasiswas.kelas')->orderBy('pembekalan_verify','desc')->get();
             
            }
         
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('selectbox', function($row){
                        return '<input type="checkbox" name="mahasiswa_id[]"  class="form-check-input mahasiswa_id" value="'.$row->mahasiswa_id.'">';
                    })

                    ->rawColumns(['action','aktif','selectbox'])
                    ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $d['title']='Tambah Jadwal Pembekalan';
        return view('a.pages.pembekalan.create',$d);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'tanggal'=>'required',
            'jam'=>'required',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {

            $error='';
            DB::transaction(function() use ($request) {


                $field = new Pembekalan;

                $field->tanggal = $request->tanggal;
                $field->jam = $request->jam;
                $field->catatan = $request->catatan;
                $field->save();

                // if($request->mahasiswa_id){
                //     $count=count($request->mahasiswa_id);
                //     for($i=0;$i<$count;$i++){
                //         GroupDetail::where('mahasiswa_id',$request->mahasiswa_id[$i])->update([
                //             'pembekalan_id'=>$field->id,
                //         ]);
                //     }
                // }

                if ($request->mahasiswa_id) {
                    $rows = [];

                    foreach ($request->mahasiswa_id as $mhsId) {
                        $rows[] = [
                            'mahasiswa_id' => $mhsId,
                            'pembekalan_id' => $field->id,
                        ];
                    }

                    GroupDetail::upsert(
                        $rows,
                        ['mahasiswa_id'],      // unique key untuk cek data existing
                        ['pembekalan_id']      // kolom yg di-update jika ada
                    );
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
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $d['title']='Edit Jadwal Pembekalan';
        $d['data'] = Pembekalan::find(decrypt0($id));
        return view('a.pages.pembekalan.edit',$d);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tanggal' => 'required',
            'jam' => 'required',
        ]);

        try {

            DB::transaction(function() use ($request, $id) {

                $field = Pembekalan::find(decrypt0($id));

                // Update pembekalan
                $field->tanggal = $request->tanggal;
                $field->jam = $request->jam;
                $field->catatan = $request->catatan;
                $field->save();

                // 1. Reset mahasiswa lama
                GroupDetail::where('pembekalan_id', $field->id)
                    ->update(['pembekalan_id' => NULL]);


                if ($request->mahasiswa_id) {

                    foreach ($request->mahasiswa_id as $mhsId) {

                        // cek sudah ada row group_detail atau belum
                        $existing = GroupDetail::where('mahasiswa_id', $mhsId)->first();

                        if ($existing) {
                            // 2A. mahasiswa lama → update pembekalan
                            $existing->update([
                                'pembekalan_id' => $field->id
                            ]);

                        } else {
                            // 2B. mahasiswa baru → INSERT BARU
                            
                            GroupDetail::insert([ // pastikan tidak null
                                'mahasiswa_id' => $mhsId,
                                'pembekalan_id' => $field->id,
                            ]);
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
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function verifikasi(string $id)
    {
        $d['title']='Verifikasi Pembekalan';
        $d['data'] = Pembekalan::find(decrypt0($id));
        return view('a.pages.pembekalan.verifikasi',$d);
    }

    public function create_no_sertifikat()
    {
        $year=date('Y');
        $max_id=DB::table('group_details')->selectRaw("MAX(SUBSTRING_INDEX(pembekalan_sertifikat, '/', 1)) AS max_id")->first();
        if(isset($max_id->max_id)){
            $nomor=((int)$max_id->max_id+1).'/UN36.2/JTIK/V/'.$year;
        }else {
            $nomor='2011/UN36.2/JTIK/V/'.$year;
        }
        return $nomor;
    }

    public function verifikasi_update(Request $request, string $id)
    {
        
        try {

            $error='';
            DB::transaction(function() use ($request,$id) {
                $field = Pembekalan::find(decrypt0($id));
                $groupDetail=GroupDetail::where('pembekalan_id',$field->id);
                $groupDetail->update([
                    'pembekalan_verify'=>'N',
                    'pembekalan_verify_at'=>NULL,
                    'pembekalan_verify_by'=>NULL,
                    'pembekalan_sertifikat'=>NULL,
                ]);

                if($request->mahasiswa_id){
                    $count=count($request->mahasiswa_id);
                    for($i=0;$i<$count;$i++){
                        GroupDetail::where('mahasiswa_id',$request->mahasiswa_id[$i])->update([
                            'pembekalan_verify'=>'Y',
                            'pembekalan_verify_at'=>date('Y-m-d H:i:s'),
                            'pembekalan_verify_by'=>$request->session()->get('id'),
                            'pembekalan_sertifikat'=>$this->create_no_sertifikat(),
                        ]);
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

    public function print_absensi($id)
     {
        $id = decrypt0($id);
        $detail=DB::table('group_details')
        ->join('groups','groups.id','=','group_details.group_id')
        ->leftJoin('dosens','dosens.id','=','groups.pembimbing_id')
        ->join('pembekalans','pembekalans.id','=','group_details.pembekalan_id')
        ->join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
        ->join('prodis','prodis.id','=','mahasiswas.prodi_id')
        ->join('jurusans','jurusans.id','=','prodis.jurusan_id')
        ->join('fakultas','fakultas.id','=','jurusans.fakultas_id')
        ->where('group_details.pembekalan_id',$id)
        ->select(
          'group_details.*','pembekalans.tanggal',
          'jurusans.jurusan','jurusans.kepala_lab_nama','jurusans.kepala_lab_nip','jurusans.kota')
        ->first();
        $group_detail=DB::table('group_details')
          ->join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
          ->join('prodis','prodis.id','=','mahasiswas.prodi_id')
          ->where('group_details.pembekalan_id',$id)
          ->select('group_details.id','mahasiswas.nama','mahasiswas.nim','mahasiswas.kelas','prodis.prodi')->get();
          $d['title']='Daftar Peserta';
          $d['perihal']='Daftar Peserta';
          $d['detail']=$detail;
          $d['group_details']=$group_detail;
          $pdf = Pdf::setPaper('A4', 'portrait')
          ->loadview('a.pages.pembekalan.print_absensi',$d);
          return $pdf->stream();

     }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $error='';
            DB::transaction(function() use ($id) {
                $field = Pembekalan::find(decrypt0($id));
                GroupDetail::where('pembekalan_id',$field->id)->update([
                    'pembekalan_verify'=>'N',
                    'pembekalan_verify_at'=>NULL,
                    'pembekalan_verify_by'=>NULL,
                    'pembekalan_id'=>NULL,
                ]);
                $field->delete();
            });
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

<?php

namespace App\Http\Controllers\a;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupDetail;
use App\Models\GroupBukti;
use App\Models\GroupLaporan;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Rules\CheckGroupMahasiswa;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class LaporanNilaiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:laporan nilai-list', only : ['index','get_laporan_nilai','show']),
            new Middleware('permission:laporan nilai-verifikasi', only : ['verifikasi']),
        ];
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $d['title']='Laporan & Upload Nilai PI';
        return view('a.pages.laporan_nilai.index',$d);
    }

    public function get_laporan_nilai(Request $request)
    {
        if ($request->ajax()) {
            $data = Group::leftJoin('lokasi_pis','lokasi_pis.id','=','groups.lokasi_pi_id')
            ->leftJoin('dosens','groups.pembimbing_id','=','dosens.id')
            ->select('groups.*','lokasi_pis.lokasi_pi','dosens.nama as pembimbing','dosens.nip as pembimbing_nip')
            ->where('groups.done','Y')
            ->where('groups.admin_verify','Y')
            ->where('groups.laporan','Y')
            ->orderByRaw('FIELD(laporan_verify, "N","Y","X")')
            ->orderBy('groups.created_at','desc')->get();
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
                        $btn.= '<li><a href="'.route('laporan-nilai.show',encrypt0($row->id)).'" title="Edit" class="dropdown-item detail"><i class="icon-mid bi bi-eye me-2" aria-hidden="true"></i> Detail</a></li>';
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
                    ->addColumn('laporan_verify', function($row){
                        if ($row->laporan_verify=='Y'){
                            $result= '<span class="badge bg-success">Telah Diverifikasi admin</span>';
                        }elseif ($row->laporan_verify=='X'){
                            $result= '<span class="badge bg-danger">Ditolak admin</span>';
                        }else{
                            $result= '<span class="badge bg-light">Belum Diverifikasi</span>';
                        }
                        return $result;
                    })

                    ->rawColumns(['action','aktif','mahasiswas','laporan_verify'])
                    ->make(true);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
    {
        $d['title']='Detail Laporan dan Upload Nilai';
        $groups=Group::join('lokasi_pis','groups.lokasi_pi_id','=','lokasi_pis.id')
        ->leftJoin('dosens','groups.pembimbing_id','=','dosens.id')
        ->leftJoin('group_lokasis','groups.id','=','group_lokasis.group_id')
        ->where('groups.id',decrypt0($id))
        ->select('groups.*','lokasi_pis.lokasi_pi','lokasi_pis.alamat','lokasi_pis.kota','dosens.nama as pembimbing','dosens.nip as pembimbing_nip','group_lokasis.kompetensi','groups.nama_pembimbing_lapangan as pembimbing_lapangan','groups.jabatan_pembimbing_lapangan as pembimbing_lapangan_jabatan')->first();
        $group_details=GroupDetail::join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
        ->join('prodis','prodis.id','=','mahasiswas.prodi_id')
        ->where('group_details.group_id',$groups->id)
        ->select('group_details.*','mahasiswas.nama','mahasiswas.nim','mahasiswas.kelas','prodis.prodi')->get();
        $d['data']=$groups;
        $d['detail']=$group_details;
        $d['dokumen_laporans']=GroupLaporan::where('group_id',$groups->id)->get();
        $d['admin_verifys']=array('N'=>'Belum Diverifikasi','Y'=>'Disetujui','X'=>'Ditolak');
        return view('a.pages.laporan_nilai.show',$d);
    }

    public function verifikasi(Request $request)
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
                $field->laporan_verify = $request->laporan_verify;
                $field->laporan_catatan = $request->laporan_catatan;
                if($request->laporan_verify=='X'){
                    $field->laporan='N';
                }
                $field->updated_by =  $request->session()->get('id');
                $field->save();
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

}

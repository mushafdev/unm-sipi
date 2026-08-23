<?php

namespace App\Http\Controllers\m;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupLokasi;
use App\Models\GroupDetail;
use App\Models\GroupBukti;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SelesaiPiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $id=$request->session()->get('detailUser')->id;
        $group_id=GroupDetail::where('mahasiswa_id',$id)->first()->group_id;
        $lokasi=Group::join('lokasi_pis','lokasi_pis.id','=','groups.lokasi_pi_id')
            ->select('groups.*','lokasi_pis.lokasi_pi','lokasi_pis.kota','lokasi_pis.alamat')
            ->where('groups.id',$group_id)->first();
        $cek=GroupLokasi::where('group_id',$group_id)->count();
        $detail=[];
        if($cek>0){
            $detail=GroupLokasi::where('group_lokasis.group_id',$group_id)->first();
        }
        $d['title']='Selesai PI';
        $d['lokasi']=$lokasi;
        $d['data']=$detail;
        $d['mahasiswas']=GroupDetail::join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
        ->select('group_details.mahasiswa_id','mahasiswas.nama','mahasiswas.nim')->where('group_id',$group_id)->get();
        $d['buktis']=GroupBukti::join('mahasiswas','mahasiswas.id','=','group_buktis.mahasiswa_id')
        ->select('group_buktis.*','mahasiswas.nama','mahasiswas.nim')->where('group_id',$group_id)->get();
        return view('u.pages.selesai_pi.index',$d);
    }



   

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    public function checkData(Request $request)
    {
        $exists = GroupBukti::where('group_id', decrypt0($request->group_id))->where('mahasiswa_id', decrypt0($request->mahasiswa_id))->exists();
        return response()->json(['exists' => $exists]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'group_id'=>'required',
            'mahasiswa_id'=>'required',
            'sertifikat_industri'=>'required',
            'laporan_pi'=>'nullable',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {
            $error='';
            DB::transaction(function() use ($request,&$error) {
                $group_id = decrypt0($request->group_id);
                $mahasiswa_id = decrypt0($request->mahasiswa_id);
                $exists = GroupBukti::where('group_id', $group_id)->where('mahasiswa_id', $mahasiswa_id)->first();
                if($exists){
                    $field = GroupBukti::find($exists->id);
                }else{
                    $field = new GroupBukti();
                }
                $field->mahasiswa_id = $mahasiswa_id;
                $field->group_id = $group_id;
                $field->sertifikat_industri = $request->sertifikat_industri;
                $field->laporan_pi = $request->laporan_pi;

                $field->save();

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
    
    public function send(Request $request)
    {
        $rules = [
            'group_id'=>'required',
        ];

        $messages = [
        ];

        $request->validate($rules,$messages);

        try {
            $error='';
            DB::transaction(function() use ($request,&$error) {
                $group_id = decrypt0($request->group_id);
                $field = Group::find($group_id);
                $field->done = 'Y';
                $field->done_verify = 'N';
                $field->done_catatan = null;
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

    /**
     * Remove the specified resource from storage.
     */
}

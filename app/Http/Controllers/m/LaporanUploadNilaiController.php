<?php

namespace App\Http\Controllers\m;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupLokasi;
use App\Models\GroupDetail;
use App\Models\GroupLaporan;
use Throwable;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LaporanUploadNIlaiController extends Controller
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
        $d['title']='Laporan & Upload Nilai';
        $d['lokasi']=$lokasi;
        $d['data']=$detail;
        $d['mahasiswas']=GroupDetail::join('mahasiswas','mahasiswas.id','=','group_details.mahasiswa_id')
        ->select('group_details.mahasiswa_id','mahasiswas.nama','mahasiswas.nim')->where('group_id',$group_id)->get();
        $existingFiles = [];
        if ($group_id) {
            $existingFiles = GroupLaporan::where('group_id', $group_id)->get()->keyBy('dokumen');
        }
        $d['existingFiles'] = $existingFiles;
        $d['dokumen_laporans'] = DB::table('dokumen_laporans')
            ->select('kode', 'dokumen')
            ->get();
        $d['uploadedFiles'] = GroupLaporan::where('group_id', $group_id)
                        ->pluck('file', 'dokumen');
        return view('u.pages.laporan_upload_nilai.index',$d);
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
    public function upload(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'dokumen' => 'required|string',
            'file' => 'required|file|mimes:pdf|max:5120',
        ]);
        $group_id = decrypt0($request->group_id);

        $existing = GroupLaporan::where('group_id', $group_id)
            ->where('dokumen', $request->dokumen)
            ->first();

        $file_name = $group_id.'-'.time().'.'.$request->file->extension();
        Storage::disk('dokumen_laporan')->put($file_name, file_get_contents($request->file));
                  

        if ($existing) {
            if ($existing->file) {
                Storage::disk('dokumen_laporan')->delete($existing->file);
            }
            $existing->update([
                'file' => $file_name,
                'updated_by' => $request->user()->id,
                'verify' => 'N',
            ]);
        } else {
            GroupLaporan::create([
                'group_id' => $group_id,
                'dokumen' => $request->dokumen,
                'file' => $file_name,
                'verify' => 'N',
                'inserted_by' => $request->user()->id,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Berhasil upload ' . $request->dokumen]);
    }

    
    public function send(Request $request)
    {
        $rules = [
            'group_id'=>'required',
        ];

        $request->validate($rules);

        try {
            DB::transaction(function() use ($request) {
                $group_id = decrypt0($request->group_id);

                $dokumenWajib = DB::table('dokumen_laporans')->pluck('dokumen')->toArray();

                $dokumenUploaded = GroupLaporan::where('group_id', $group_id)->pluck('dokumen')->toArray();

                $dokumenKurang = array_diff($dokumenWajib, $dokumenUploaded);

                if (!empty($dokumenKurang)) {
                    throw new \Exception('Dokumen belum lengkap. Harap lengkapi semua dokumen sebelum mengirim.');
                }

                // Jika lengkap, simpan status laporan
                $field = Group::find($group_id);
                $field->laporan = 'Y';
                $field->laporan_verify = 'N';
                $field->laporan_catatan = null;
                $field->save();
            });

            return response()->json([
                'text' => 'Data sukses dikirim',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
}

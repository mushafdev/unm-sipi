<?php

use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
/**
 * Write code on Method
 *
 * @return response()
 */

if (! function_exists('encrypt0')) {
    function encrypt0($string)
    {
        return base64_encode(Crypt::encryptString($string));
    }
}

if (! function_exists('decrypt0')) {
    function decrypt0($string)
    {
        try {
            return Crypt::decryptString(base64_decode($string));
        } catch (DecryptException $e) {
            abort(404);
        }
    }
}

if (! function_exists('isDosen')) {
    /**
     * Cek apakah user yang login adalah dosen.
     */
    function isDosen()
    {
        return auth()->check() && auth()->user()->role == 'dosen';
    }
}

if (! function_exists('dosenId')) {
    /**
     * ID dosen (dosens.id) dari user yang sedang login. Null jika bukan dosen.
     */
    function dosenId()
    {
        if (! isDosen()) {
            return null;
        }

        $detail = session('detailUser');

        return $detail->id ?? null;
    }
}

if (! function_exists('convertYmdToMdy')) {
    function convertYmdToMdy($date)
    {
        return Carbon::createFromFormat('Y-m-d', $date)->format('m-d-Y');
    }
}

if (! function_exists('convertYmdToMdy2')) {
    function convertYmdToMdy2($date)
    {
        return Carbon::createFromFormat('Y-m-d', $date)->format('m/d/Y');
    }
}
  
/**
 * Write code on Method
 *
 * @return response()
 */
if (! function_exists('convertMdyToYmd')) {
    function convertMdyToYmd($date)
    {
        return Carbon::createFromFormat('m-d-Y', $date)->format('Y-m-d');
    }
}

if (! function_exists('identity')) {
    function identity()
    {
        $result = array (
            'title'=>   "Sistem Informasi Praktik Industri",
            'singkat'=>   'SiPi',
            'sub_title'=>'Jurusan Teknik Informatika dan Komputer',
            'text'=>'',
            'by'=>'Jurusan Teknik Informatika dan Komputer',
            'support'=>'Jurusan Teknik Informatika dan Komputer',
            'university'=>'Universitas Negeri Makassar',
        );
        return $result;
    }
}

if (! function_exists('groupInfo')) {
    function groupInfo($id)
    {
        return DB::table('group_details')
            ->join('groups','groups.id','=','group_details.group_id')
            ->select('groups.*','group_details.pembekalan_verify')
            ->where('group_details.mahasiswa_id', $id)
            ->where('group_details.pembekalan_verify', 'Y')
            ->first() ?? '';
    }
}

if (! function_exists('isPembekalanVerified')) {
    function isPembekalanVerified($id)
    {
        return DB::table('group_details')
            ->where('mahasiswa_id', $id)
            ->where('pembekalan_verify', 'Y')
            ->exists();
    }
}

if (! function_exists('akunMhs')) {
    function akunMhs($id)
    {
        $result = DB::table('mahasiswas')->where('id',$id)->first();
        return $result;
    }
}

if (! function_exists('getMonthFromNumber')) {
    function getMonthFromNumber($num)
    {
        $bulan = array (
            1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );

        return $bulan[$num];
    }
}

if (! function_exists('adminVerifyStatus')) {
    function adminVerifyStatus($status,$jenis)
    {
        $result = array (
            'N' =>array(
                'badge'=>'bg-light-secondary',
                'text'=>'Belum diverifikasi admin',
            ),
            
            'X' =>array(
                'badge'=>'bg-danger',
                'text'=>'Ditolak admin',
            ),
            'Y' =>array(
                'badge'=>'bg-success',
                'text'=>'Disetujui admin',
            ),
        );

        return $result[$status][$jenis];
    }
}

if (! function_exists('tgl_indo')) {
    function tgl_indo($tanggal){
        if($tanggal=='' || $tanggal=='0000-00-00' ){
            return '-';
        }else{
            $bulan = array (
                1 =>   'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember'
            );
            $pecahkan = explode('-', $tanggal);
            return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
        }

    }
}

if (! function_exists('monthToRomawi')) {
    function monthToRomawi($bulan) {
        // Array mapping bulan ke angka Romawi
        $romawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
    
        if (isset($romawi[$bulan])) {
            return $romawi[$bulan];
        } else {
            return "???";
        }
    }
}

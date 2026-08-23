<?php

namespace App\Http\Controllers\u;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifikasiEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LoginController extends Controller
{

    public function index()
    {
        return view('u.pages.login');
    }
    
    public function registrasi()
    {

        $d['jurusans']=Jurusan::get();
        $d['prodis']=Prodi::get();
        return view('u.pages.registrasi',$d);
    }
    
    public function forgot_password()
    {
        return view('u.pages.forgot_password');
    }
    
    
    public function forgot_password_email(Request $request)
    {
        $request->validate(['email' => 'required|email']);
 
        $status = Password::sendResetLink(
            $request->only('email')
        );
     
        return $status === Password::RESET_LINK_SENT
                    ? back()->with([
                        'status' => 
                        'Link reset password telah dikirim ke email anda. Silahkan periksa INBOX/SPAM email.'
                    ])
                    : back()->withErrors(['email' => __($status)]);
    }


    public function reset_password(string $token)
    {
        return view('u.pages.reset_password', ['token' => $token]);
    }
    
    public function reset_password_update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'username' => [
                'nullable',
                'min:5',
                Rule::unique('users')->ignore($request->email, 'email') // ignore user yang sedang reset
            ],
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(function ($query) {
                    $query->where('aktif', 'Y'); 
                }),
            ],
            'password' => 'required|min:8|confirmed',
        ]);
     
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use ($request) {
                $user->forceFill([
                    'username' => $request->username,
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                
                $user->save();
     
                event(new PasswordReset($user));
            }
        );
     
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('success', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }

    public function cari_mahasiswa(Request $request)
    {
        $nim = $request->get('nim', '');
        $kd_prodi = $request->get('kd_prodi', '');
        $q = $request->get('q', '');

        $response = Http::withHeaders([
            'Token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjMwIiwidXNlcm5hbWUiOiJNdWhhbW1hZCBBbCBNaXNyaSBIaWRheWF0IiwiZW1haWwiOiJhbG1pc3JpQG1haWwuY29tIiwidHlwZSI6IjEiLCJyZWdfZGF0ZSI6IjIwMjUtMDEtMTYiLCJleHBfZGF0ZSI6IjIwMjktMDEtMTYifQ.H6rn5SI9ohZowJ9S5ZLkHaENNQDQrvIihZaJs8cNdek'
        ])->get('https://ids.jtik.ft.unm.ac.id/hub/api', [
            'kd_prodi' => $kd_prodi,
            'nim' => $nim,
            'q' => $q
        ]);

        return $response->json();
    }

    // public function registrasi_store(Request $request){

    //     $rules = [
    //         'nim'=>'required|unique:mahasiswas',
    //         'nama'=>'required',
    //         'kelas'=>'required',
    //         'prodi_id'=>'required',
    //         'email'=>'required|email:rfc,dns|unique:users',
    //         'username'=>'required|min:5|unique:users',
    //         'password'=>'required|min:5|max:50|same:password_confirmation',
    //         'captcha' => 'required|captcha'
    //     ];

    //     $messages = [
    //         'prodi_id.required' => 'Prodi is required',
    //         'captcha.captcha' => 'Please check captcha',
    //     ];

    //     $request->validate($rules,$messages);

    //     $nim = $request->nim;
    //     $nama = $request->nama;
    //     $q = $request->q;

    //     try {
    //         $response = Http::withHeaders([
    //             'Token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjMwIiwidXNlcm5hbWUiOiJNdWhhbW1hZCBBbCBNaXNyaSBIaWRheWF0IiwiZW1haWwiOiJhbG1pc3JpQG1haWwuY29tIiwidHlwZSI6IjEiLCJyZWdfZGF0ZSI6IjIwMjUtMDEtMTYiLCJleHBfZGF0ZSI6IjIwMjktMDEtMTYifQ.H6rn5SI9ohZowJ9S5ZLkHaENNQDQrvIihZaJs8cNdek'
    //         ])->timeout(10)->get('https://ids.jtik.ft.unm.ac.id/hub/api', [
    //             'nim'      => $nim,
    //         ]);

    //         if ($response->failed()) {
    //             return back()->withErrors([
    //                 'error_message' => 'Tidak dapat menghubungi server verifikasi. Coba lagi nanti.'
    //             ])->withInput();
    //         }

    //         $dataApi = $response->json();

    //         // Pastikan data sesuai
    //         if (empty($dataApi['status']) || $dataApi['status'] !== true || empty($dataApi['data'])) {
    //             return back()->withErrors([
    //                 'error_message' => 'Data mahasiswa tidak ditemukan atau tidak valid.'
    //             ])->withInput();
    //         }

    //         // Override nama & kelas dari API agar tidak dimanipulasi user
    //         $request->merge([
    //             'nama'  => $dataApi['data'][0]['nm_mhs'] ?? $request->nama,
    //             'kelas' => strtoupper($dataApi['data'][0]['kelas'] ?? $request->kelas)
    //         ]);

    //     } catch (\Throwable $th) {
    //         return back()->withErrors([
    //             'error_message' => 'Gagal memeriksa data ke server: ' . $th->getMessage()
    //         ])->withInput();
    //     }

    //     try {
    //         $saved = false;
    //         DB::transaction(function() use ($request,&$saved, $nim, $nama) {
    //             $user= new User;
    //             $user->type='mahasiswas';
    //             $user->email=$request->email;
    //             $user->aktif = 'N';
    //             $user->role = 'mahasiswa';
    //             $user->username = $request->username;
    //             $user->password = bcrypt($request->password);
    //             $user->save();

    //             $field = new Mahasiswa;
    //             $field->user_id = $user->id;
    //             $field->inserted_by =  $request->session()->get('id');
    //             $field->nama = $nama;
    //             $field->nim = $nim;
    //             $field->kelas = $request->kelas;
    //             $field->prodi_id = $request->prodi_id;
                

    //             $metaEmail = [
    //                 'subject' => 'Verifikasi Email '.identity()['singkat'],
    //                 'nama' => $request->nama,
    //                 'link_verifikasi' => url('email/verify/'.(encrypt0($request->email)).'/'.base64_encode(Hash::make($request->email))),
    //                 'time' => encrypt0(base64_encode(Carbon::now()))
    //             ];
    //             Mail::to($request->email)->send(new VerifikasiEmail($metaEmail));
    //             $saved=$field->save();
                        
    //         });
    //         if($saved){
    //             return redirect('/')->with('success','Data berhasil disimpan. Silahkan cek email anda di INBOX atau di SPAM untuk verifikasi !');                   
    //         }else{
    //             return back()->withErrors([
    //                 'error_message' => 'Terjadi Kesalahan. Silahkan diulangi',
    //             ]);
    //         }
    //     }catch (Exception $e) {
    //         Log::error('Registrasi Store Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
    //         return back()->withErrors([
    //             'error_message' => 'Terjadi Kesalahan. Silahkan diulangi',
    //         ]);
    //     }

       
    // }

    // public function registrasi_store(Request $request)
    // {
    //     // 1. Validasi input
    //     $rules = [
    //         'nim'       => 'required|unique:mahasiswas',
    //         'nama'      => 'required',
    //         'kelas'     => 'required',
    //         'prodi_id'  => 'required',
    //         'email'     => [
    //             'required',
    //             'email:rfc,dns',
    //             // email hanya dianggap duplicate kalau sudah diverifikasi
    //             Rule::unique('users')->where(function ($q) {
    //                 return $q->where('aktif','Y');
    //             }),
    //         ],
    //         'username'  => 'required|min:5|unique:users',
    //         'password'  => 'required|min:5|max:50|same:password_confirmation',
    //         'captcha'   => 'required|captcha'
    //     ];

    //     $messages = [
    //         'prodi_id.required' => 'Prodi is required',
    //         'captcha.captcha'   => 'Please check captcha',
    //     ];

    //     $request->validate($rules, $messages);

    //     $nim  = $request->nim;
    //     $nama = $request->nama;

    //     // 2. Verifikasi data ke server eksternal
    //     try {
    //         $response = Http::withHeaders([
    //             'Token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjMwIiwidXNlcm5hbWUiOiJNdWhhbW1hZCBBbCBNaXNyaSBIaWRheWF0IiwiZW1haWwiOiJhbG1pc3JpQG1haWwuY29tIiwidHlwZSI6IjEiLCJyZWdfZGF0ZSI6IjIwMjUtMDEtMTYiLCJleHBfZGF0ZSI6IjIwMjktMDEtMTYifQ.H6rn5SI9ohZowJ9S5ZLkHaENNQDQrvIihZaJs8cNdek' // sebaiknya taruh di config/services.php
    //         ])->timeout(10)->get('https://ids.jtik.ft.unm.ac.id/hub/api', [
    //             'nim' => $nim,
    //         ]);

    //         if ($response->failed()) {
    //             return back()->withErrors([
    //                 'error_message' => 'Tidak dapat menghubungi server verifikasi. Coba lagi nanti.'
    //             ])->withInput();
    //         }

    //         $dataApi = $response->json();

    //         if (empty($dataApi['status']) || $dataApi['status'] !== true || empty($dataApi['data'])) {
    //             return back()->withErrors([
    //                 'error_message' => 'Data mahasiswa tidak ditemukan atau tidak valid.'
    //             ])->withInput();
    //         }

    //         // Override data dari API (hindari manipulasi user)
    //         $request->merge([
    //             'nama'  => $dataApi['data'][0]['nm_mhs'] ?? $request->nama,
    //             'kelas' => strtoupper($dataApi['data'][0]['kelas'] ?? $request->kelas)
    //         ]);

    //     } catch (\Throwable $th) {
    //         return back()->withErrors([
    //             'error_message' => 'Gagal memeriksa data ke server: ' . $th->getMessage()
    //         ])->withInput();
    //     }

    //     // 3. Simpan data + kirim email dalam transaksi
    //     try {
    //         $saved = false;

    //         DB::transaction(function () use ($request, &$saved, $nim, $nama) {
    //             // Jika ada user lama dengan email yang sama tapi belum verifikasi → hapus
    //             User::where('email', $request->email)
    //                 ->where('aktif', 'N') // hanya hapus yang belum aktif
    //                 ->delete();

    //             // Buat user baru
    //             $user = new User;
    //             $user->type     = 'mahasiswas';
    //             $user->email    = $request->email;
    //             $user->aktif    = 'N';
    //             $user->role     = 'mahasiswa';
    //             $user->username = $request->username;
    //             $user->password = bcrypt($request->password);
    //             $user->save();

    //             // Buat mahasiswa
    //             $field = new Mahasiswa;
    //             $field->user_id     = $user->id;
    //             $field->inserted_by = $request->session()->get('id');
    //             $field->nama        = $nama;
    //             $field->nim         = $nim;
    //             $field->kelas       = $request->kelas;
    //             $field->prodi_id    = $request->prodi_id;

    //             // Data email
    //             $metaEmail = [
    //                 'subject' => 'Verifikasi Email ' . identity()['singkat'],
    //                 'nama'    => $request->nama,
    //                 'link_verifikasi' => url('email/verify/' . encrypt0($request->email) . '/' . base64_encode(Hash::make($request->email))),
    //                 'time'    => encrypt0(base64_encode(Carbon::now()))
    //             ];

    //             // Kirim email (kalau gagal → rollback DB)
    //             Mail::to($request->email)->send(new VerifikasiEmail($metaEmail));

    //             $saved = $field->save();
    //         });

    //         if ($saved) {
    //             return redirect('/')->with('success', 'Registrasi berhasil! Silahkan cek email anda (INBOX/SPAM) untuk verifikasi.');
    //         } else {
    //             return back()->withErrors([
    //                 'error_message' => 'Terjadi Kesalahan. Silahkan diulangi',
    //             ]);
    //         }

    //     } catch (\Throwable $e) {
    //         Log::error('Registrasi Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    //         return back()->withErrors([
    //             'error_message' => 'Registrasi gagal: ' . $e->getMessage(),
    //         ])->withInput();
    //     }
    // }

    public function registrasi_store(Request $request)
    {
        // 1. Validasi input
        $rules = [
            'nim' => [
                'required',
                Rule::unique('mahasiswas', 'nim')->where(function ($q) {
                    $q->whereIn('user_id', function ($sub) {
                        $sub->select('id')
                            ->from('users')
                            ->where('aktif', 'Y');
                    });
                }),
            ],
            'nama'      => 'required',
            'kelas'     => 'required',
            'prodi_id'  => 'required',
            'email' => [
                'required',
                'email:rfc,dns',
                Rule::unique('users', 'email')->where(function ($q) {
                    return $q->where('aktif', 'Y');
                }),
            ],
            'username' => [
                'required',
                'min:5',
                Rule::unique('users', 'username')->where(function ($q) {
                    return $q->where('aktif', 'Y');
                }),
            ],
            'password'  => 'required|min:5|max:50|same:password_confirmation',
            'captcha'   => 'required|captcha',
        ];

        $messages = [
            'prodi_id.required' => 'Prodi is required',
            'captcha.captcha'   => 'Please check captcha',
        ];

        $request->validate($rules, $messages);

        $nim  = $request->nim;
        $nama = $request->nama;

        // 2. Verifikasi data ke server eksternal
        try {
            $response = Http::withHeaders([
                'Token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjMwIiwidXNlcm5hbWUiOiJNdWhhbW1hZCBBbCBNaXNyaSBIaWRheWF0IiwiZW1haWwiOiJhbG1pc3JpQG1haWwuY29tIiwidHlwZSI6IjEiLCJyZWdfZGF0ZSI6IjIwMjUtMDEtMTYiLCJleHBfZGF0ZSI6IjIwMjktMDEtMTYifQ.H6rn5SI9ohZowJ9S5ZLkHaENNQDQrvIihZaJs8cNdek'
            ])->timeout(10)->get('https://ids.jtik.ft.unm.ac.id/hub/api', [
                'nim' => $nim,
            ]);

            if ($response->failed()) {
                return back()->withErrors([
                    'error_message' => 'Tidak dapat menghubungi server verifikasi. Coba lagi nanti.'
                ])->withInput();
            }

            $dataApi = $response->json();

            if (empty($dataApi['status']) || $dataApi['status'] !== true || empty($dataApi['data'])) {
                return back()->withErrors([
                    'error_message' => 'Data mahasiswa tidak ditemukan atau tidak valid.'
                ])->withInput();
            }

            // Override data dari API
            $request->merge([
                'nama'  => $dataApi['data'][0]['nm_mhs'] ?? $request->nama,
                'kelas' => strtoupper($dataApi['data'][0]['kelas'] ?? $request->kelas)
            ]);

        } catch (\Throwable $th) {
            return back()->withErrors([
                'error_message' => 'Gagal memeriksa data ke server: ' . $th->getMessage()
            ])->withInput();
        }

        // 3. Cek apakah user sudah ada
        $existingUser = User::where('email', $request->email)  
            ->first();

        $existingMhs = Mahasiswa::where('nim', $nim)->first();

        if ($existingUser && $existingUser->aktif === 'Y') {
            return back()->withErrors([
                'error_message' => 'Akun sudah terdaftar & aktif. Silakan login.'
            ]);
        }

        if ($existingMhs && $existingMhs->user && $existingMhs->user->aktif === 'Y') {
            return back()->withErrors([
                'error_message' => 'NIM sudah terdaftar & aktif. Silakan login.'
            ]);
        }

        // 4. Jika sudah ada tapi belum aktif → kirim ulang verifikasi
        // if ($existingUser && $existingUser->aktif === 'N') {
        //     try {
        //         // $metaEmail = [
        //         //     'subject' => 'Verifikasi Email ' . identity()['singkat'],
        //         //     'nama'    => $request->nama,
        //         //     'link_verifikasi' => url('email/verify/' . encrypt0($request->email) . '/' . base64_encode(Hash::make($request->email))),
        //         //     'time'    => encrypt0(base64_encode(Carbon::now()))
        //         // ];

        //         // Mail::to($request->email)->send(new VerifikasiEmail($metaEmail));
        //         User::where('id', $existingUser->id)->update([
        //             'email' => $request->email,
        //             'username' => $request->username,
        //             'password' => bcrypt($request->password),
        //         ]);

        //         // return redirect('/')->with('success', 'Akun sudah terdaftar tapi belum aktif. Link verifikasi telah dikirim ulang ke email Anda. Cek di INBOX/SPAM.');
        //         return redirect('/')->with('success', 'Silahkan login');
        //     } catch (\Exception $e) {
        //         return back()->withErrors([
        //             'error_message' => 'Gagal mengirim ulang email verifikasi: ' . $e->getMessage()
        //         ]);
        //     }
        // }

        // 5. Kalau belum ada sama sekali → buat baru
        try {
            $saved = false;

            DB::transaction(function () use ($request, &$saved, $nim, $nama) {
                // Buat user baru
                $user = new User;
                $user->type     = 'mahasiswas';
                $user->email    = $request->email;
                $user->aktif    = 'Y';
                $user->role     = 'mahasiswa';
                $user->username = $request->username;
                $user->password = bcrypt($request->password);
                $user->save();

                // Buat mahasiswa
                $field = new Mahasiswa;
                $field->user_id     = $user->id;
                $field->inserted_by = $request->session()->get('id');
                $field->nama        = $nama;
                $field->nim         = $nim;
                $field->kelas       = $request->kelas;
                $field->prodi_id    = $request->prodi_id;

                // Data email
                $metaEmail = [
                    'subject' => 'Verifikasi Email ' . identity()['singkat'],
                    'nama'    => $request->nama,
                    'link_verifikasi' => url('email/verify/' . encrypt0($request->email) . '/' . base64_encode(Hash::make($request->email))),
                    'time'    => encrypt0(base64_encode(Carbon::now()))
                ];

                // Mail::to($request->email)->send(new VerifikasiEmail($metaEmail));

                $saved = $field->save();
            });

            if ($saved) {
                // return redirect('/')->with('success', 'Registrasi berhasil! Silahkan cek email anda (INBOX/SPAM) untuk verifikasi.');
                return redirect('/')->with('success', 'Registrasi berhasil! Silahkan login');
            } else {
                return back()->withErrors([
                    'error_message' => 'Terjadi Kesalahan. Silahkan diulangi',
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('Registrasi Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors([
                'error_message' => 'Registrasi gagal: ' . $e->getMessage(),
            ])->withInput();
        }
    }



    public function verify_email(Request $request)
    {
        try {
            $id1=decrypt0($request->segment(3));
            $id2=base64_decode($request->segment(4));
            if (Hash::check($id1, $id2)) {
                $data=User::where('email',$id1);
                if($data->count()>0){

                    $field=$data->first();
                    if(empty($field->email_verified_at)){
                        $data->update([
                            'email_verified_at'=>Carbon::now(),
                            'aktif' => 'Y',
                        ]);
                        return redirect('/')->with('success','Email berhasil diverifikasi. Silahkan login.');   
                    }
                }
            }
                
            
        }catch (Exception $e) {
            return back()->withErrors([
                'error_message' => 'Terjadi Kesalahan. Silahkan diulangi',
            ]);
        }
       


    }

    public function reload_captcha()
    {
        return response()->json(['captcha'=> captcha_src()]);
    }

    public function authenticate(Request $request){

            $rules = [
                'username'=>'required|min:5|max:50',
                'password'=>'required|min:5|max:50',
                'captcha' => 'required|captcha'
            ];

            $messages = [
                // 'username.required' => 'Username kosong',
                // 'username.min' => 'Username tidak boleh lebih kecil dari 5 karakter',
                // 'password.min' => 'Password tidak boleh lebih kecil dari 8 karakter',
                // 'password.required' => 'Password kosong',
                // 'captcha.required' => 'Captcha kosong',
                'captcha.captcha' => 'Please check captcha',
            ];

            $request->validate($rules,$messages);
            $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            if (Auth::attempt([$fieldType => addslashes($request->username), 'password' => $request->password, 'aktif' => 'Y'],false)) {
                $user=Auth::user();
                $detailData=DB::table($user->type)->where('user_id',$user->id)->first();
                $request->session()->put('id',$user->id);
                $request->session()->put('detailUser',$detailData);
                $request->session()->put('role',$user->role);
                if($user->role=='admin' || $user->role=='superadmin' || $user->role=='dosen'){
                    return redirect()->intended('/admin/dashboard');
                }
                return redirect()->intended('home');
                
                
            }

            return back()->withErrors([
                'password' => 'Username and Password is not valid',
            ])->onlyInput('password');
    }

    public function logout(Request $request){
        $request->session()->flush();
        Auth::guard('mahasiswa')->logout();
        return redirect('login');
    }
}

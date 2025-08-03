<?php

namespace App\Http\Controllers;

use App\Models\AnggotaRombel;
use App\Models\Audit;
use App\Models\BidangSdm;
use App\Models\JabatanPtk;
use App\Models\JenisPendaftaran;
use App\Models\JenisPtk;
use App\Models\LayakPip;
use App\Models\Pengguna;
use App\Models\Periodik;
use App\Models\PesertaDidik;
use App\Models\Ptk;
use App\Models\PtkTerdaftar;
use App\Models\RegistrasiPesertaDidik;
use App\Models\RolePengguna;
use App\Models\RombonganBelajar;
use App\Models\RwyKerja;
use App\Models\RwyPendFormal;
use App\Models\Sekolah;
use App\Models\Semester;
use App\Models\User;
use App\Models\Yayasan;
use App\Models\Wilayah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Artisan;

class DapodikController extends Controller
{
    private function get_pengguna(){
        $data = Pengguna::whereHas('role', function($query){
            $query->where('peran_id', 10);
            $query->where('sekolah_id', request()->sekolah_id);
        })->first();
        return $data?->pengguna_id;
    }
    private function get_sekolah()
    {
        return Sekolah::on('dapodik')->withWhereHas('pengguna', function ($query) {
            $query->whereHas('role', function($query){
                $query->where('peran_id', 10);
            });
        })->get();
    }
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        Auth::guard('web')->logout();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
    public function index()
    {
        $sekolah = [];
        $error = null;
        $user = auth()->user();
        try {
            $sekolah = $this->get_sekolah();
            $user->sekolah = Sekolah::on('dapodik')->with(['yayasan'])->find($user->sekolah_id);
        } catch (\Throwable $th) {
            //sdd($th->getMessage());
            $error = Str::of($th->getMessage())->contains('fe_sendauth');
        }
        //Sekolah::find($user->sekolah_id);
        $data = [
            'jam_sinkron' => $this->jam_sinkron(),
            'sekolah' => $sekolah,
            'user' => $user,
            'error' => $error,
        ];
        return response()->json($data);
    }
    public function sekolah(Request $request)
    {
        $user = auth()->user();
        $JenisPendaftaran = [];
        $JenisPtk = [];
        $wilayah = [];
        if ($request->isMethod('post')) {
            if(request()->data == 'yayasan'){
                Yayasan::where('yayasan_id', request()->yayasan_id)->update(['soft_delete' => 0]);
                return response()->json([
                    'request' => request()->yayasan_id, 
                ]);
            }
            request()->validate(
                [
                    'sekolah_id' => 'required',
                ],
                [
                    'sekolah_id.required' => 'Sekolah tidak boleh kosong',
                ]
            );
            $dapodik = Sekolah::on('dapodik')->find(request()->sekolah_id);
            $response = null;
            $sekolah = null;
            $allowed = false;
            try {
                $response = Http::post('https://adidev.web.id/api/npsn', [
                    'npsn' => $dapodik->npsn,
                ]);
            } catch (\Throwable $th) {
                //throw $th;
            }
            if ($response) {
                $result = $response->object();
                $allowed = $result->allowed;
            }
            if ($allowed) {
                $sekolah = Sekolah::updateOrCreate(
                    [
                        'sekolah_id' => $request->sekolah_id,
                    ],
                    [
                        'npsn' => $dapodik->npsn,
                        'nama' => $dapodik->nama,
                    ]
                );
                $audit = Audit::orderBy('event_id', 'DESC')->first();
                $user->sekolah_id = request()->sekolah_id;
                $user->last_id = $audit->event_id;
                $user->pengguna_id = (request()->pengguna_id) ? request()->pengguna_id : $this->get_pengguna();
                $user->save();
            }
            $data = [
                'sekolah' => $sekolah,
                'allowed' => $allowed,
                'npsn' => $dapodik->npsn,
            ];
        } else {
            $semester = NULL;
            try {
                if(request()->ptk){
                    $wilayah = Wilayah::where('id_level_wilayah', 1)->orderBy('kode_wilayah')->get();
                } else {
                    $JenisPendaftaran = JenisPendaftaran::where('daftar_sekolah', 1)->whereNull('expired_date')->orderBy('jenis_pendaftaran_id')->get();
                    $JenisPtk = JenisPtk::whereNull('expired_date')->orderBy('jenis_ptk_id')->get();
                }
                $semester = Semester::where('periode_aktif', 1)->first();
            } catch (\Throwable $th) {
                //throw $th;
            }
            $data = [
                'sekolah' => Sekolah::find($user->sekolah_id),
                'jenis_pendaftaran' => $JenisPendaftaran,
                'jenis_ptk' => $JenisPtk,
                'semester' => $semester,
                'wilayah' => $wilayah,
                'jam_sinkron' => $this->jam_sinkron(),
            ];
        }
        return response()->json($data);
    }
    public function cari_data()
    {
        request()->validate(
            [
                'nik' => 'required',// ['required', 'digits:16'],
            ],
            [
                'nik.required' => 'NIK tidak boleh kosong!',
                'nik.digits' => 'NIK harus berupa angka 16 digit!',
            ]
        );
        $rombongan_belajar = [];
        $response = null;
        $pd = null;
        $ptk = null;
        if (request()->data == 'siswa') {
            $pd = PesertaDidik::on('dapodik')->with(['kelas' => function ($query) {
                $query->whereHas('semester', function ($query) {
                    $query->where('periode_aktif', 1);
                });
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('rombongan_belajar.soft_delete', 0);
                $query->where('anggota_rombel.soft_delete', 0);
                $query->where('jenis_rombel', 1);
            }])->where(function($query){
                if(Str::isUuid(request()->nik)){
                    $query->where('peserta_didik_id', request()->nik);
                } else {
                    $query->where('nik', request()->nik);
                }
            })->first();
        }
        if (request()->data == 'guru') {
            $ptk = Ptk::withWhereHas('ptk_terdaftar', function ($query) {
                $query->where('sekolah_id', request()->sekolah_id);
                //$query->whereNull('jenis_keluar_id');
                $query->where('ptk_terdaftar.soft_delete', 0);
                $query->whereHas('tahun_ajaran', function ($query) {
                    $query->where('periode_aktif', 1);
                });
            })->where('nik', request()->nik)->where('soft_delete', 0)->first();
        }
        try {
            $rombongan_belajar = RombonganBelajar::where(function ($query) {
                $query->whereHas('semester', function ($query) {
                    $query->where('periode_aktif', 1);
                });
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('soft_delete', 0);
                $query->where('jenis_rombel', 1);
            })->orderBy('tingkat_pendidikan_id')->orderBy('nama')->get();
            if(Str::isUuid(request()->nik)){
                $post = [
                    'peserta_didik_id' => request()->nik,
                ];
            } else {
                $post = [
                    'nik' => request()->nik,
                ];
            }
            $response = Http::post('http://sync.erapor-smk.net/api/v7/' . request()->data, $post);
        } catch (\Throwable $th) {
            //throw $th;
        }
        $arr = ($response) ? (array) $response->object() : null;
        $data = [
            'result' => ($arr) ? $response->object() : null,
            'rombongan_belajar' => $rombongan_belajar,
            'pd' => $pd,
            'ptk' => $ptk,
        ];
        return response()->json($data);
    }
    public function cek_pd()
    {
        $data = [
            'pd' => PesertaDidik::on('dapodik')->with([
                'kelas' => function ($query) {
                    $query->whereHas('semester', function ($query) {
                        $query->where('periode_aktif', 1);
                    });
                    $query->where('sekolah_id', request()->sekolah_id);
                    $query->where('rombongan_belajar.soft_delete', 0);
                    $query->where('anggota_rombel.soft_delete', 0);
                    $query->where('jenis_rombel', 1);
                },
                'registrasi' => function($query){
                    $query->where('sekolah_id', request()->sekolah_id);
                    $query->where('soft_delete', 0);
                }
            ])->where('nik', request()->nik)->where('peserta_didik_id', request()->peserta_didik_id)->first(),
        ];
        return response()->json($data);
    }
    public function daftar()
    {
        if (request()->data == 'siswa') {
            $text = 'Peserta Didik';
            request()->validate(
                [
                    'jenis_pendaftaran_id' => 'required',
                    'tanggal_masuk_sekolah' => 'required',
                    'rombongan_belajar_id' => 'required',
                ],
                [
                    'jenis_pendaftaran_id.required' => 'Jenis Pendaftaran tidak boleh kosong!',
                    'tanggal_masuk_sekolah.required' => 'Tanggal Masuk Sekolah tidak boleh kosong!',
                    'rombongan_belajar_id.required' => 'Rombongan Belajar tidak boleh kosong!',
                ]
            );
            $data = request()->peserta_didik;
            $pd = PesertaDidik::on('dapodik')->updateOrCreate(
                [
                    'peserta_didik_id' => $data['peserta_didik_id'],
                ],
                [
                    'nama' => $data['nama'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'nisn' => $data['nisn'],
                    'nik' => $data['nik'],
                    'no_kk' => $data['no_kk'],
                    'tempat_lahir' => $data['tempat_lahir'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'agama_id' => $data['agama_id'],
                    'kebutuhan_khusus_id' => $data['kebutuhan_khusus_id'],
                    'alamat_jalan' => $data['alamat_jalan'],
                    'rt' => $data['rt'],
                    'rw' => $data['rw'],
                    'nama_dusun' => $data['nama_dusun'],
                    'desa_kelurahan' => $data['desa_kelurahan'],
                    'kode_wilayah' => $data['kode_wilayah'],
                    'kode_pos' => $data['kode_pos'],
                    'lintang' => $data['lintang'],
                    'bujur' => $data['bujur'],
                    'jenis_tinggal_id' => $data['jenis_tinggal_id'],
                    'alat_transportasi_id' => $data['alat_transportasi_id'],
                    'nik_ayah' => $data['nik_ayah'],
                    'nik_ibu' => $data['nik_ibu'],
                    'anak_keberapa' => $data['anak_keberapa'],
                    'nik_wali' => $data['nik_wali'],
                    'nomor_telepon_rumah' => $data['nomor_telepon_rumah'],
                    'nomor_telepon_seluler' => $data['nomor_telepon_seluler'],
                    'email' => $data['email'],
                    'penerima_kps' => $data['penerima_KPS'],
                    'no_kps' => $data['no_KPS'],
                    'layak_pip' => $data['layak_PIP'],
                    'penerima_kip' => $data['penerima_KIP'],
                    'no_kip' => $data['no_KIP'],
                    'nm_kip' => $data['nm_KIP'],
                    'no_kks' => $data['no_KKS'],
                    'reg_akta_lahir' => $data['reg_akta_lahir'],
                    'id_layak_pip' => $data['id_layak_pip'],
                    'id_bank' => $data['id_bank'],
                    'rekening_bank' => $data['rekening_bank'],
                    'nama_kcp' => $data['nama_kcp'],
                    'rekening_atas_nama' => $data['rekening_atas_nama'],
                    'status_data' => $data['status_data'],
                    'nama_ayah' => $data['nama_ayah'],
                    'tahun_lahir_ayah' => $data['tahun_lahir_ayah'],
                    'jenjang_pendidikan_ayah' => $data['jenjang_pendidikan_ayah'],
                    'pekerjaan_id_ayah' => $data['pekerjaan_id_ayah'],
                    'penghasilan_id_ayah' => $data['penghasilan_id_ayah'],
                    'kebutuhan_khusus_id_ayah' => $data['kebutuhan_khusus_id_ayah'],
                    'nama_ibu_kandung' => $data['nama_ibu_kandung'],
                    'tahun_lahir_ibu' => $data['tahun_lahir_ibu'],
                    'jenjang_pendidikan_ibu' => $data['jenjang_pendidikan_ibu'],
                    'penghasilan_id_ibu' => $data['penghasilan_id_ibu'],
                    'pekerjaan_id_ibu' => $data['pekerjaan_id_ibu'],
                    'kebutuhan_khusus_id_ibu' => $data['kebutuhan_khusus_id_ibu'],
                    'nama_wali' => $data['nama_wali'],
                    'tahun_lahir_wali' => $data['tahun_lahir_wali'],
                    'jenjang_pendidikan_wali' => $data['jenjang_pendidikan_wali'],
                    'pekerjaan_id_wali' => $data['pekerjaan_id_wali'],
                    'penghasilan_id_wali' => $data['penghasilan_id_wali'],
                    'kewarganegaraan' => $data['kewarganegaraan'],
                    'pekerjaan_id' => $data['pekerjaan_id'],
                    'create_date' => $data['create_date'],
                    'last_update' => Carbon::now()->addMinutes(120),
                    'soft_delete' => 0,
                    'last_sync' => Carbon::now()->addMinutes(90),
                    'updater_id' => auth()->user()->pengguna_id,
                ]
            );
            $reg = RegistrasiPesertaDidik::find(request()->registrasi_id);
            if (!$reg) {
                $reg = new RegistrasiPesertaDidik;
                $reg->registrasi_id = Str::uuid();
                $reg->create_date = Carbon::now();
                $reg->updater_id = auth()->user()->pengguna_id;
                $reg->peserta_didik_id = $data['peserta_didik_id'];
                $reg->sekolah_id = request()->sekolah_id;
                $reg->id_hobby = request()->id_hobby ?? '-1';
                $reg->id_cita = request()->id_cita ?? '-1';
                $reg->a_pernah_paud = request()->a_pernah_paud;
                $reg->a_pernah_tk = request()->a_pernah_tk;
                $reg->sekolah_asal = request()->sekolah_asal;
            }
            $reg->jenis_pendaftaran_id = request()->jenis_pendaftaran_id;
            $reg->tanggal_masuk_sekolah = request()->tanggal_masuk_sekolah;
            $reg->last_update = Carbon::now()->addMinutes(120);
            $reg->nipd = request()->nipd;
            $reg->jenis_keluar_id = null;
            $reg->tanggal_keluar = null;
            $reg->soft_delete = 0;
            $reg->last_sync = Carbon::now()->addMinutes(90);
            $reg->save();
            AnggotaRombel::updateOrCreate(
                [
                    'peserta_didik_id' => $data['peserta_didik_id'],
                ],
                [
                    'rombongan_belajar_id' => request()->rombongan_belajar_id,
                    'jenis_pendaftaran_id' => request()->jenis_pendaftaran_id,
                    'create_date' => Carbon::now(),
                    'last_update' => Carbon::now()->addMinutes(120),
                    'soft_delete' => 0,
                    'last_sync' => Carbon::now()->addMinutes(90),
                    'updater_id' => auth()->user()->pengguna_id,
                ]
            );
        } else {
            $text = 'PTK';
            request()->validate(
                [
                    'jenis_ptk_id' => 'required',
                    'jabatan_ptk_id' => 'required',
                    'nomor_surat_tugas' => 'required',
                    'tanggal_surat_tugas' => 'required',
                    'tmt_tugas' => 'required',
                    'ptk_induk' => 'required',
                ],
                [
                    'jenis_ptk_id.required' => 'Jenis PTK tidak boleh kosong!',
                    'jabatan_ptk_id.required' => 'Jenis PTK tidak boleh kosong!',
                    'nomor_surat_tugas.required' => 'Nomor Surat Tugas tidak boleh kosong!',
                    'tanggal_surat_tugas.required' => 'Tanggal Surat Tugas tidak boleh kosong!',
                    'tmt_tugas.required' => 'TMT Tugas tidak boleh kosong!',
                    'ptk_induk.required' => 'PTK Induk tidak boleh kosong!',
                ]
            );
            $ptk_terdaftar = PtkTerdaftar::find(request()->ptk_terdaftar_id);
            $ptk = collect(request()->ptk);
            if (!$ptk_terdaftar) {
                $ptk_terdaftar = new PtkTerdaftar;
                $ptk_terdaftar->ptk_terdaftar_id = Str::uuid();
                $ptk_terdaftar->create_date = Carbon::now();
                $ptk_terdaftar->updater_id = auth()->user()->pengguna_id;
                $ptk_terdaftar->ptk_id = $ptk['ptk_id'];
                $ptk_terdaftar->sekolah_id = request()->sekolah_id;
            }
            $rws = $ptk['rwy_pend_formal'];
            $rwk = $ptk['rwy_kerja'];
            $rwd = $ptk['bidang_sdm'];
            $pengguna = $ptk['pengguna'];
            unset($ptk['rwy_pend_formal'], $ptk['rwy_kerja'], $ptk['bidang_sdm'], $ptk['pengguna'], $ptk['terdaftar'], $ptk['status_kepegawaian']);
            $ptk = array_change_key_case($ptk->toArray(), CASE_LOWER);
            $ptk['last_update'] = Carbon::now()->addMinutes(120);
            $ptk['soft_delete'] = 0;
            $ptk['last_sync'] = Carbon::now()->addMinutes(90);
            $find = Ptk::find($ptk['ptk_id']);
            if(!$find){
                Ptk::create($ptk);
            }
            $ptk_id = $ptk['ptk_id'];
            foreach ($rws as $rw) {
                $p = array_change_key_case($rw, CASE_LOWER);
                $bidang_studi_id = $p['bidang_studi_id'];
                $jenjang_pendidikan_id = $p['jenjang_pendidikan_id'];
                unset($p['ptk_id'], $p['bidang_studi_id'], $p['jenjang_pendidikan_id']);
                try {
                    RwyPendFormal::updateOrCreate(
                        [
                            'ptk_id' => $ptk_id,
                            'bidang_studi_id' => $bidang_studi_id,
                            'jenjang_pendidikan_id' => $jenjang_pendidikan_id,
                        ],
                        $p,
                    );
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
            foreach ($rwk as $rk) {
                $p = array_change_key_case($rk, CASE_LOWER);
                $jenis_ptk_id = $p['jenis_ptk_id'];
                $jenjang_pendidikan_id = $p['jenjang_pendidikan_id'];
                unset($p['ptk_id'], $p['jenis_ptk_id'], $p['jenjang_pendidikan_id']);
                $find = RwyKerja::where(function($query) use ($ptk_id, $jenis_ptk_id, $jenjang_pendidikan_id){
                    $query->where('ptk_id', $ptk_id);
                    $query->where('jenis_ptk_id', $jenis_ptk_id);
                    $query->where('jenjang_pendidikan_id', $jenjang_pendidikan_id);
                })->first();
                if(!$find){
                    RwyKerja::updateOrCreate(
                        [
                            'ptk_id' => $ptk_id,
                            'jenis_ptk_id' => $jenis_ptk_id,
                            'jenjang_pendidikan_id' => $jenjang_pendidikan_id,
                        ],
                        $p,
                    );
                }
            }
            foreach ($rwd as $rd) {
                $p = array_change_key_case($rd, CASE_LOWER);
                $bidang_studi_id = $p['bidang_studi_id'];
                $urutan = $p['urutan'];
                unset($p['ptk_id'], $p['bidang_studi_id'], $p['urutan']);
                $a = [
                    'ptk_id' => $ptk_id,
                    'bidang_studi_id' => $bidang_studi_id,
                    'urutan' => $urutan,
                ];
                BidangSdm::updateOrCreate(
                    $a,
                    $p,
                );
            }
            $find = Pengguna::where(function ($query) use ($ptk) {
                $query->where('ptk_id', $ptk['ptk_id']);
                $query->where('sekolah_id', request()->sekolah_id);
                $query->orWhere('username', $ptk['email']);
            })->first();
            if (!$find) {
                $pengguna = Pengguna::create([
                    'pengguna_id' => Str::uuid(),
                    'sekolah_id' => request()->sekolah_id,
                    'username' =>( $ptk['email']) ? $ptk['email'] : Str::random(5).'@email.com',
                    'nama' => $ptk['nama'],
                    'tempat_lahir' => $ptk['tempat_lahir'],
                    'tgl_lahir' => $ptk['tanggal_lahir'],
                    'jenis_kelamin' => $ptk['jenis_kelamin'],
                    'alamat' => $ptk['alamat_jalan'],
                    'kode_wilayah' => $ptk['kode_wilayah'],
                    'approval_pengguna' => 1,
                    'password' => bcrypt(12345678),
                    'ptk_id' => $ptk['ptk_id'],
                    'create_date' => now(),
                    'last_update' => Carbon::now()->addMinutes(120),
                    'last_sync' => Carbon::now()->addMinutes(90),
                    'soft_delete' => 0,
                    'updater_id' => auth()->user()->pengguna_id,
                ]);
                RolePengguna::create([
                    'id_role_pengguna' => Str::uuid(),
                    'pengguna_id' => $pengguna->pengguna_id,
                    'peran_id' => 53,
                    'sekolah_id' => request()->sekolah_id,
                    'jenis_lembaga' => 'S',
                    'create_date' => now(),
                    'last_update' => Carbon::now()->addMinutes(120),
                    'last_sync' => Carbon::now()->addMinutes(90),
                ]);
            }
            $ptk_terdaftar->jabatan_ptk_id = request()->jabatan_ptk_id;
            $ptk_terdaftar->tahun_ajaran_id = request()->tahun_ajaran_id;
            $ptk_terdaftar->jenis_ptk_id = request()->jenis_ptk_id;
            $ptk_terdaftar->nomor_surat_tugas = request()->nomor_surat_tugas;
            $ptk_terdaftar->tanggal_surat_tugas = request()->tanggal_surat_tugas;
            $ptk_terdaftar->ptk_induk = request()->ptk_induk;
            $ptk_terdaftar->tmt_tugas = request()->tmt_tugas;
            $ptk_terdaftar->last_update = Carbon::now()->addMinutes(120);
            $ptk_terdaftar->soft_delete = 0;
            $ptk_terdaftar->last_sync = Carbon::now()->addMinutes(90);
            $ptk_terdaftar->save();
        }
        //Audit::where('table_name', 'ptk')->where('client_query', 'ILIKE', '%insert%')->delete();
        $data = [
            'text' => $text.' berhasil didaftarkan',
            'request' => request()->all(),
            'color' => 'success',
            'title' => 'Berhasil!',
        ];
        return response()->json($data);
    }
    private function kelas()
    {
        return function ($query) {
            $query->whereHas('semester', function ($query) {
                $query->where('periode_aktif', 1);
            });
            $query->where('sekolah_id', auth()->user()->sekolah_id);
            $query->where('rombongan_belajar.soft_delete', 0);
            $query->where('anggota_rombel.soft_delete', 0);
            $query->where('jenis_rombel', 1);
        };
    }
    public function pip()
    {
        if (request()->isMethod('post')) {
            $pd = PesertaDidik::on('dapodik')->find(request()->peserta_didik_id);
            $pd->layak_pip = request()->layak_pip;
            $pd->id_layak_pip = request()->id_layak_pip;
            $pd->last_update = now()->format('Y-m-d H:i:s.u');
            $pd->save();
            $data = [
                'request' => request()->all(),
            ];
            return response()->json($data);
        } else {
            $sortby = request()->sortby;
            $data = PesertaDidik::on('dapodik')->whereHas('kelas', $this->kelas())->with([
                'kelas' => $this->kelas(),
                'kelayakan_pip',
            ])
                ->orderBy($sortby, request()->sortbydesc)
                ->when(request()->q, function ($query) {
                    $query->where('nama', 'ILIKE', '%' . request()->q . '%');
                    $query->orWhere('nisn', 'ILIKE', '%' . request()->q . '%');
                })->paginate(request()->per_page);
            $layak_pip = LayakPip::whereNull('expired_date')->where('id_layak_pip', '>', 0)->orderBy('id_layak_pip')->get();
            return response()->json(['status' => 'success', 'data' => $data, 'layak_pip' => $layak_pip, 'q' => request()->q]);
        }
    }
    public function simpan_guru(){
        request()->validate(
            [
                'ptk_induk' => 'required',
                'jabatan_ptk_id' => 'required',
            ],
            [
                'ptk_induk.required' => 'Tinggi Badan tidak boleh kosong!',
                'jabatan_ptk_id.required' => 'Berat Badan tidak boleh kosong!',
            ]
        );
        $find = PtkTerdaftar::find(request()->ptk_terdaftar_id);
        $find->ptk_induk = request()->ptk_induk;
        $find->jenis_ptk_id = request()->jenis_ptk_id;
        $find->jabatan_ptk_id = request()->jabatan_ptk_id;
        $find->save();
        $data = [
            'request' => request()->all(),
            'find' => $find,
        ];
        return response()->json($data);
    }
    public function periodik()
    {
        if (request()->isMethod('post')) {
            request()->validate(
                [
                    'tinggi_badan' => 'required|integer|digits:3',
                    'berat_badan' => 'required|integer',
                    'jarak_rumah_ke_sekolah' => 'required|integer',
                    'waktu_tempuh_ke_sekolah' => 'required|integer',
                    'menit_tempuh_ke_sekolah' => 'required|integer',
                    'jumlah_saudara_kandung' => 'required|integer',
                ],
                [
                    'tinggi_badan.required' => 'Wajib isi',
                    'tinggi_badan.integer' => 'Wajib isi',
                    'tinggi_badan.digits' => 'Wajib 3 digit',
                    'berat_badan.required' => 'Wajib isi',
                    'berat_badan.integer' => 'Wajib isi',
                    'jarak_rumah_ke_sekolah.required' => 'Wajib isi',
                    'jarak_rumah_ke_sekolah.integer' => 'Wajib Angka',
                    'waktu_tempuh_ke_sekolah.required' => 'Wajib isi',
                    'waktu_tempuh_ke_sekolah.integer' => 'Wajib angka!',
                    'menit_tempuh_ke_sekolah.required' => 'Wajib isi',
                    'menit_tempuh_ke_sekolah.integer' => 'Wajib angka!',
                    'jumlah_saudara_kandung.required' => 'Wajib isi',
                    'jumlah_saudara_kandung.integer' => 'Wajib angka!',
                ]
            );
            $find = Periodik::where('peserta_didik_id', request()->peserta_didik_id)->where('semester_id', request()->semester_id)->first();
            if (!$find) {
                $find = new Periodik;
                $find->create_date = Carbon::now();
                $find->updater_id = auth()->user()->pengguna_id;
                $find->peserta_didik_id = request()->peserta_didik_id;
                $find->semester_id = request()->semester_id;
            }
            $find->tinggi_badan = request()->tinggi_badan;
            $find->berat_badan = request()->berat_badan;
            $find->jarak_rumah_ke_sekolah = request()->jarak_rumah_ke_sekolah;
            $find->waktu_tempuh_ke_sekolah = request()->waktu_tempuh_ke_sekolah;
            $find->menit_tempuh_ke_sekolah = request()->menit_tempuh_ke_sekolah;
            $find->jumlah_saudara_kandung = request()->jumlah_saudara_kandung;
            $find->last_update = Carbon::now()->addMinutes(120);
            $find->soft_delete = 0;
            $find->last_sync = Carbon::now()->addMinutes(90);
            $find->save();
            $data = [
                'request' => request()->all(),
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => 'PTK berhasil didaftarkan',
            ];
            return response()->json($data);
        } else {
            $sortby = request()->sortby;
            $data = PesertaDidik::on('dapodik')->whereHas('kelas', $this->kelas())->with([
                'kelas' => $this->kelas(),
                'periodik' => function ($query) {
                    $query->whereHas('semester', function ($query) {
                        $query->where('periode_aktif', 1);
                    });
                },
            ])
                ->orderBy($sortby, request()->sortbydesc)
                ->when(request()->q, function ($query) {
                    $query->where('nama', 'ILIKE', '%' . request()->q . '%');
                    $query->orWhere('nisn', 'ILIKE', '%' . request()->q . '%');
                })->paginate(request()->per_page);
            $semester = Semester::where('periode_aktif', 1)->first();
            return response()->json(['status' => 'success', 'data' => $data, 'semester' => $semester]);
        }
    }
    public function get_jabatan()
    {
        $data = JabatanPtk::where('jenis_ptk_id', request()->jenis_ptk_id)->whereNull('expired_date')->orderBy('jabatan_ptk_id')->get();
        return response()->json($data);
    }
    private function terdaftar(){
        return function($query){
            $query->where('sekolah_id', auth()->user()->sekolah_id);
            $query->whereNull('jenis_keluar_id');
            $query->whereHas('tahun_ajaran', function($query){
                $query->where('periode_aktif', 1);
            });
        };
    }
    public function guru(){
        $sortby = request()->sortby;
        $data = Ptk::on('dapodik')->with([
            'ptk_terdaftar' => $this->terdaftar(),
        ])->whereHas('ptk_terdaftar', $this->terdaftar())
        ->orderBy($sortby, request()->sortbydesc)
        ->when(request()->q, function ($query) {
            $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            $query->orWhere('nik', 'ILIKE', '%' . request()->q . '%');
        })->paginate(request()->per_page);
        $jabatan = JabatanPtk::whereNull('expired_date')->orderBy('jabatan_ptk_id')->get();
        return response()->json([
            'data' => $data, 
            'jabatan' => $jabatan,
            'jenis_ptk' => JenisPtk::whereNull('expired_date')->orderBy('jenis_ptk_id')->get(),
            'q' => request()->q
        ]);
    }
    public function reset(){
        $user = auth()->user();
        $user->sekolah_id = NULL;
        Artisan::call('migrate:refresh --seed');
        $data = [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Aplikasi berhasil direset!',
        ];
        return response()->json($data);
    }
    private function jam_sinkron(){
        $timezone = config('app.timezone');
        $start = Carbon::create(date('Y'), date('m'), date('d'), '00', '00', '01', 'Asia/Jakarta');
        $end = Carbon::create(date('Y'), date('m'), date('d'), '03', '00', '00', 'Asia/Jakarta');
        $now = Carbon::now()->timezone($timezone);
        $jam_sinkron = Carbon::now()->timezone($timezone)->isBetween($start, $end, false);
        return $jam_sinkron;
    }
    public function tambah_ptk(){
        request()->validate(
            [
                'nik' => 'required|unique:dapodik.ptk,nik',
            ],
            [
                'nik.required' => 'Sekolah tidak boleh kosong',
                'nik.unique' => 'NIK telah terdaftar',
            ]
        );
        $data = [
            'color' => 'success',
            'text' => 'PTK baru berhasil disimpan',
            'title' => 'Berhasil'
        ];
        return response()->json($data);
    }
    public function wilayah(){
        $data = Wilayah::where(function($query){
            if(request()->cari == 'kabupaten'){
                $query->where('id_level_wilayah', 2);
            } else {
                $query->where('id_level_wilayah', 3);
            }
            $query->where('mst_kode_wilayah', request()->kode_wilayah);
        })->orderBy('kode_wilayah')->get();
        return response()->json($data);
    }
}

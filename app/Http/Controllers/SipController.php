<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master;
use JWTAuth;
use App\Models\Position;
use DB;
use App\Http\Libraries\Set_koneksi;

class SipController extends Controller
{
    public function CheckAuth($request){
        //fungsi cek security every service
        $token = DB::table('Tabel_Generate')
                    ->where('id_user', $request->user_id)
                    ->select('token')
                    ->first();
        $param_tanggal = $request->date;
        $param_user_id = $request->user_id;
        $first = md5(md5($param_tanggal).md5($param_user_id));

        $now = date_create()->format('d/m/Y');
        $second =md5($token->token);
        $md5_sign = md5($first.$second);
        
        if($md5_sign==$request->parammd5)
            return 'yes';
        else
            return 'no';
    }

    public function login(Request $request)
    {
        $now = date_create()->format('Y-m-d H:i:s');
        // safety method
        if($request->isMethod('Post'))
        {
            // inisiai Json
            $data=[];
            $data ['user_id'] = null;
            $data ['username'] = null;
            $data ['posisi'] = null;
            $data ['token'] = null;
            $data ['status'] = null;
            $data ['jenjang'] = null;

            $ability[]= [];
            
            // get user
            $user = Master::where('user_id','=',$request->user_id)->where('user_password','=',md5($request->password))->first();
            $superuser = Master::where('user_id','=',$request->user_id)->where('pass','=',md5($request->password))->first();
            $user_s = DB::connection("pgsql_swasta")->table('public.master_user')->where('user_id','=',$request->user_id)->where('user_password','=',md5($request->password))->first();
                                                                
            if ($user)
            {
                // make token
                $token = JWTAuth::fromUser($user);

                // insert to table generate
                $check = DB::table('public.Tabel_Generate')->where('id_user',$user->user_id)->count();
                if($check==0)
                {
                    $server_token = DB::table('public.Tabel_Generate')->where('id_user',$user->user_id)->insert(['token'=>$token,'id_user'=>$user->user_id,'generate_time'=>$now]);
                }
                else{
                    $server_token = DB::table('public.Tabel_Generate')->where('id_user',$user->user_id)->update(['token'=>$token,'generate_time'=>$now]);
                }
                // find user position
                $posisi = DB::table('public.schema_akses')->where('user_id','=',$request->user_id)->first();
                $unit_kerja = DB::table('public.unit_kerja')->where('unit_id','=',$request->user_id)->first();

                if($unit_kerja){
                    $kelompok = $unit_kerja->kelompok_id;
                    $jenjang = $unit_kerja->jenjang;
                }else{
                    $kelompok = '3';
                    $jenjang = '';
                }

                $data ['user_id'] = $user->user_id;
                $data ['username'] = $user->user_name;
                $data ['posisi'] = $posisi->level_id;
                $message = "success";
                $data ['token'] = $token;
                $data ['status'] = $kelompok;
                $data ['jenjang'] = $jenjang;

            }else if($superuser)
            {
                // make token
                $token = JWTAuth::fromUser($superuser);

                // insert to table generate
                $check = DB::table('public.Tabel_Generate')->where('id_user',$superuser->user_id)->count();
                if($check==0)
                {
                    $server_token = DB::table('public.Tabel_Generate')->where('id_user',$superuser->user_id)->insert(['token'=>$token,'id_user'=>$superuser->user_id,'generate_time'=>$now]);
                }
                else{
                    $server_token = DB::table('public.Tabel_Generate')->where('id_user',$superuser->user_id)->update(['token'=>$token,'generate_time'=>$now]);
                }
                // find user position
                $posisi = DB::table('public.schema_akses')->where('user_id','=',$request->user_id)->first();
                $unit_kerja = DB::table('public.unit_kerja')->where('unit_id','=',$request->user_id)->first();

                if($posisi->level_id == 2){
                    $ability[0]['action']= 'read';
                    $ability[0]['subject'] = 'ACL';
                }

                if($unit_kerja){
                    $kelompok = $unit_kerja->kelompok_id;
                    $jenjang = $unit_kerja->jenjang;
                }else{
                    $kelompok = '3';
                    $jenjang = '';
                }

                $data ['user_id'] = $superuser->user_id;
                $data ['username'] = $superuser->user_name;
                $data ['posisi'] = $posisi->level_id;
                $message = "success";
                $data ['token'] = $token;
                $data ['status'] = $kelompok;
                $data ['jenjang'] = $jenjang;
                $data['ability'] = $ability;

            }else if($user_s){
                 // make token
                 $token = '315eb115d98fcbad39ffc5edebd669c9';

                //  JWTAuth::fromUser($user);

                 // insert to table generate
                //  $check = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->count();
                //  if($check==0)
                //  {
                //      $server_token = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->insert(['token'=>$token,'id_user'=>$user['user_id'],'generate_time'=>$now]);
                //  }
                //  else{
                //      $server_token = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->update(['token'=>$token,'generate_time'=>$now]);
                //  }
                 // find user position
                 $posisi = DB::connection("pgsql_swasta")->table('public.schema_akses')->where('user_id','=',$request['user_id'])->first();
                 $unit_kerja = DB::connection("pgsql_swasta")->table('public.unit_kerja')->where('unit_id','=',$request['user_id'])->first();
 
                 if($unit_kerja){
                     $kelompok = $unit_kerja->kelompok_id;
                     $jenjang = $unit_kerja->jenjang;
                 }else{
                     $kelompok = '3';
                     $jenjang = '';
                 }
 
                 $data ['user_id'] = $user_s->user_id;
                 $data ['username'] = $user_s->user_name;
                 $data ['posisi'] = $posisi->level_id;
                 $message = "success";
                 $data ['token'] = $token;
                 $data ['status'] = $kelompok;
                 $data ['jenjang'] = $jenjang;
            }else
            {
                // not found
                $message = "Not Found";
            }
            return response()->json(compact('message','data'),200);
        }
        else
        {
            $message = "Method Not Allowed";
            return response()->json(compact('message','data'),405);
        }
    }

    public function jmlSiswaDalamkotaSD(Request $request){
        if($request->isMethod('Post')){
            $jmlSiswaDalamKota = DB::connection("pgsql_sd")->select(DB::raw("SELECT COUNT(DISTINCT s.id_siswa) AS total
            FROM PUBLIC.siswa AS s
            JOIN PUBLIC.wali_murid AS w ON s.npsn = w.npsn
            WHERE w.npsn = '$request->npsn' AND w.kab_id = 'SURABAYA' AND (s.alumni IS NOT TRUE OR s.alumni IS NULL) AND s.id_siswa = w.id_siswa AND s.status_siswa = 'Aktif'"));
        }
        return response()->json(compact('jmlSiswaDalamKota'),200);
    }

    public function jmlSiswaLuarKotaSD(Request $request){
        if($request->isMethod('Post')){
            $jmlSiswaLuarKota = DB::connection("pgsql_sd")->select(DB::raw("SELECT COUNT(DISTINCT s.id_siswa) AS total
            FROM PUBLIC.siswa AS s
            JOIN PUBLIC.wali_murid AS w ON s.npsn = w.npsn
            WHERE w.npsn = '$request->npsn' AND (w.kab_id != 'SURABAYA' OR w.kab_id IS NULL) AND (s.alumni IS NOT TRUE OR s.alumni IS NULL) AND s.id_siswa = w.id_siswa AND (s.status_siswa = 'Aktif' OR (s.status_siswa = 'Mutasi' AND s.sekolah_mutasi='$request->npsn'))"));
        }
        return response()->json(compact('jmlSiswaLuarKota'),200);
    }

    public function jmlSiswaDalamkotaSMP(Request $request){
        if($request->isMethod('Post')){
            $jmlSiswaDalamKota = DB::connection("pgsql_smp")->select(DB::raw("SELECT COUNT(DISTINCT s.id_siswa) AS total
            FROM PUBLIC.siswa AS s
            JOIN PUBLIC.wali_murid AS w ON s.npsn = w.npsn
            WHERE w.npsn = '$request->npsn' AND w.kab_id = 'SURABAYA' AND (s.alumni IS NOT TRUE OR s.alumni IS NULL) AND s.id_siswa = w.id_siswa AND s.status_siswa = 'Aktif'"));
        }
        return response()->json(compact('jmlSiswaDalamKota'),200);
    }

    public function jmlSiswaLuarKotaSMP(Request $request){
        if($request->isMethod('Post')){
            $jmlSiswaLuarKota = DB::connection("pgsql_smp")->select(DB::raw("SELECT COUNT(DISTINCT s.id_siswa) AS total
            FROM PUBLIC.siswa AS s
            JOIN PUBLIC.wali_murid AS w ON s.npsn = w.npsn
            WHERE w.npsn = '$request->npsn' AND (w.kab_id != 'SURABAYA' OR w.kab_id IS NULL) AND (s.alumni IS NOT TRUE OR s.alumni IS NULL) AND s.id_siswa = w.id_siswa AND (s.status_siswa = 'Aktif' OR (s.status_siswa = 'Mutasi' AND s.sekolah_mutasi='$request->npsn'))"));
        }
        return response()->json(compact('jmlSiswaLuarKota'),200);
    }

    public function simpanPegawai(Request $request){
        if($request->isMethod('Post')){

            $sdm_id = $request->npsn . $request->peg_id;
            
            $insert = DB::table('budget2021.sdm')->insert([
                'sdm_id' => $sdm_id,
                'kode_dana' => $request->kode_dana?$request->kode_dana:'3.03',
                'kode_kegiatan' => $request->kode_kegiatan?$request->kode_kegiatan:0,
                'npsn' => $request->npsn?$request->npsn:0,
                'peg_id' => $request->peg_id?$request->peg_id:0,
                'nama_pegawai' => $request->nama_pegawai,
                'nik_pegawai' => $request->nik,
                'jenis_pegawai' => $request->jenis_pegawai,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tgl_lahir' => $request->tgl_lahir,
                'alamat_pegawai' => $request->alamat_pegawai,
                'no_telpon' => $request->no_telpon,
                'npwp' => $request->npwp,
                'kualifikasi_pendidikan' => $request->kualifikasi_pendidikan,
                'nama_jenis' => $request->nama_jenis,
                'no_rekening' => $request->no_rekening,
                'nama_bank' => $request->nama_bank,
                'an_bank' => $request->an_bank,
                'jenis_tendik' => $request->jenis_tendik,
                'status_guru' => $request->status_guru,
                'keterangan' => $request->keterangan,
                'komponen_id' => $request->komponen_id,
                'komponen_name' => $request->komponen_name,
                'komponen_harga' => $request->komponen_harga,
                'bulan' => $request->bulan,
                'hari' => $request->hari,
                'jam' => $request->jam,
                'nominal' => $request->nilai,
                'update_by'=> $request->npsn
            ]);

            if($insert){
                $message = "success";
            }else{$message = "failed";}
        }
        return response()->json(compact('message'),200);
    }

    public function tambahPelatih(request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
            $komponen_id = '2.1.1.01.01.01.004.041.A.S';
            $komponen_name = 'Jasa Pelatih Ekskul';
            $komponen_harga = 150000;     
                   
            if($sumberDana=='bopda'){
                $kode_dana = '3.03';
                $kode_kegiatan = '5.05.3.03.122';
            }else{
                $kode_dana = '3.01';
                $kode_kegiatan = '5.05.3.01.048';
            }

            $data = $request->data;
            // $sdm_id = $request->data->npsn . $request->$data->peg_id;

            $conn = Set_koneksi::set_koneksi($request); //jenjang, status

            if(count($data)!=0){
                for ($i=0; $i < count($data); $i++){
                    $data_insert = [
                        'sdm_id' => $data[$i]['npsn'] . $data[$i]['peg_id'],
                        'kode_dana' => $kode_dana,
                        'kode_kegiatan' => $kode_kegiatan,
                        'npsn' => $data[$i]['npsn'],
                        'peg_id' => $data[$i]['peg_id'],
                        'nama_pegawai' => $data[$i]['nama_pegawai'],
                        'nik_pegawai' => $data[$i]['nik'],
                        'jenis_pegawai' => $data[$i]['jenis_pegawai'],
                        'jenis_kelamin' => $data[$i]['jenis_kelamin'],
                        'tempat_lahir' => $data[$i]['tempat_lahir'],
                        'tgl_lahir' => $data[$i]['tgl_lahir'],
                        'alamat_pegawai' => $data[$i]['alamat_pegawai'],
                        'no_telpon' => $data[$i]['no_telpon'],
                        'npwp' => $data[$i]['npwp'],
                        'kualifikasi_pendidikan' => $data[$i]['kualifikasi_pendidikan'],
                        // 'nama_jenis' => $data[$i]['nama_jenis'],
                        'no_rekening' => $data[$i]['no_rekening'],
                        'nama_bank' => $data[$i]['nama_bank'],
                        'an_bank' => $data[$i]['an_bank'],
                        // 'jenis_tendik' => $data[$i]['jenis_tendik'],
                        'status_guru' => $data[$i]['status_guru'],
                        'keterangan' => $data[$i]['keterangan'],
                        'komponen_id' => $komponen_id,
                        'komponen_name' => $komponen_name,
                        'komponen_harga' => $komponen_harga,
                        'bulan' => $data[$i]['bulan'],
                        'hari' => $data[$i]['hari'],
                        'jam' => $data[$i]['jam'],
                        'nominal' => $data[$i]['nilai'],
                        'update_by'=> $data[$i]['npsn']
                    ];

                    $insert = DB::connection($conn['conn_status'])->table('budget2021.sdm')->insert($data_insert);
                }
            }

            if($insert){
                $message = 'success';
            }else {
                $message = 'failed';
            }
        }
        return response()->json(compact('message'), 200);
    }

    public function tambahPegawai(request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
           
            if($sumberDana=='bopda'){
                $kode_dana = '3.03';
                $kode_kegiatan = '5.05.3.03.122';
            }else{
                $kode_dana = '3.01';
                $kode_kegiatan = '5.05.3.01.048';
            }

            $data = $request->data;
            
            $conn = Set_koneksi::set_koneksi($request); //jenjang, status

            if(count($data)!=0){
                for ($i=0; $i < count($data); $i++){
                    if($request->status == 1){
                        if($data[$i]['status_guru'] == 'GTT'){
                            $komponen_id = '2.1.1.01.01.02.099.004.1.S';
                            $komponen_name = 'Honorarium GTT (Guru Tidak Tetap)';
                            $komponen_harga = 196562.5;
                        }else if($data[$i]['status_guru'] == 'PTT'){
                            if($data[$i]['jenis_tendik']=='Penjaga'||$data[$i]['jenis_tendik']=='Keamanan'||$data[$i]['jenis_tendik']=='Kebersihan'){
                                $komponen_id = '2.1.1.01.01.02.099.005.A.S';
                                $komponen_name = 'Honorarium Tenaga Keamanan / Kebersihan Kategori A';
                                $komponen_harga = 4300480;
                            }else if($data[$i]['jenis_tendik']=='Administrasi'||$data[$i]['jenis_tendik']=='Laboran'||$data[$i]['jenis_tendik']=='Pustakawan'){
                                $komponen_id = '2.1.1.01.01.02.099.004.2.S';
                                $komponen_name = 'Tenaga Kependidikan Kategori A (TU/Laboran/Pustakawan)';
                                $komponen_harga = 4717500;
                            }
                        }
                    }else if($request->status == 2){
                        if($data[$i]['status_guru']=='GTT'||$data[$i]['status_guru']=='GTY'){
                            $komponen_id = '2.1.1.01.01.02.099.005.A.1.S';
                            $komponen_name = 'Honorarium GTT/GTY';
                            $komponen_harga = 44796.75;
                        }else if($data[$i]['status_guru']=='PTT'||$data[$i]['status_guru']=='PTY'){
                            $komponen_id = '2.1.1.01.01.02.099.005.A.2.S';
                            $komponen_name = 'Honorarium PTT/PTY';
                            $komponen_harga = 179187;
                        }
                    }
                    
                    $data_insert = [
                        'sdm_id' => $data[$i]['npsn'] . $data[$i]['peg_id'],
                        'kode_dana' => $kode_dana,
                        'kode_kegiatan' => $kode_kegiatan,
                        'npsn' => $data[$i]['npsn'],
                        'peg_id' => $data[$i]['peg_id'],
                        'nama_pegawai' => $data[$i]['nama_pegawai'],
                        'nik_pegawai' => $data[$i]['nik'],
                        'jenis_pegawai' => $data[$i]['jenis_pegawai'],
                        'jenis_kelamin' => $data[$i]['jenis_kelamin'],
                        'tempat_lahir' => $data[$i]['tempat_lahir'],
                        'tgl_lahir' => $data[$i]['tgl_lahir'],
                        'alamat_pegawai' => $data[$i]['alamat_pegawai'],
                        'no_telpon' => $data[$i]['no_telpon'],
                        'npwp' => $data[$i]['npwp'],
                        'kualifikasi_pendidikan' => $data[$i]['kualifikasi_pendidikan'],
                        'nama_jenis' => $data[$i]['nama_jenis'],
                        'no_rekening' => $data[$i]['no_rekening'],
                        'nama_bank' => $data[$i]['nama_bank'],
                        'an_bank' => $data[$i]['an_bank'],
                        'jenis_tendik' => $data[$i]['jenis_tendik'],
                        'status_guru' => $data[$i]['status_guru'],
                        'keterangan' => $data[$i]['keterangan'],
                        'komponen_id' => $komponen_id,
                        'komponen_name' => $komponen_name,
                        'komponen_harga' => $komponen_harga,
                        'bulan' => $data[$i]['bulan'],
                        'hari' => $data[$i]['hari'],
                        'jam' => $data[$i]['jam'],
                        'nominal' => $data[$i]['nilai'],
                        'update_by'=> $data[$i]['npsn']
                    ];

                    $insert = DB::connection($conn['conn_status'])->table('budget2021.sdm')->insert($data_insert);
                }
            }  
            if($insert){
                $message = 'success';
            }else {
                $message = 'failed';
            }
        }
        return response()->json(compact('message'), 200);
    }

    public function getPelatih(request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
            $npsn = $request->npsn;
            $jenjang = $request->jenjang;

            if($sumberDana=='bopda'){
                $kode = '3.03';
            }else{
                $kode = '3.01';
            }

            if($jenjang=='MI'||$jenjang=='MTS'){
                $status_skpbm = "Verifikasi Pengawas";
            }else{
                $status_skpbm ="Setujui Dinas";
            }

            $conn = Set_koneksi::set_koneksi($request);
            $id_sudah = [];

            $pelatih_sudah = DB::connection($conn['conn_status'])->table('budget2021.sdm')->selectRaw("peg_id")->where('npsn', $npsn)->get();

            if($pelatih_sudah->count()!=0){
                foreach ($pelatih_sudah as $key) {
                    array_push($id_sudah,"'".$key->peg_id."'");
                }
            }

            if(count($id_sudah)!=0){
                $str_id = join(",",$id_sudah);

                $pelatih = DB::connection("pgsql_skpbm")->select(DB::raw("SELECT concat(tran.npsn, peg.peg_id) as sdm_id, concat('') as nilai, concat('') as jam, concat('') as bulan, concat('') AS hari, tran.npsn, tran.nama_sekolah, peg.peg_id, peg.nama_pegawai, peg.status_guru, peg.nik, peg.jenis_pegawai, peg.jenis_kelamin, peg.tempat_lahir, peg.tgl_lahir, peg.alamat_pegawai, peg.no_telpon, peg.npwp, peg.kualifikasi_pendidikan, peg.jurusan_pendidikan, peg.keterangan, peg.no_rekening, peg.nama_bank, peg.an_bank
                FROM PUBLIC.skpbm_jadwal_pegawai AS peg
                JOIN PUBLIC.skpbm_jadwal_transaksi AS tran ON peg.skpbm_jadwal_transaksi_id = tran.id_skpbm_jadwal_transaksi
                WHERE tran.npsn = '$npsn' AND peg.jenis_pegawai = 'Pelatih' AND tran.status = '$status_skpbm' AND tran.setting_skpbm_jadwal_id =3 AND peg.is_aktif = TRUE AND peg.peg_id not in ($str_id)
                "));
            }else{
                $pelatih = DB::connection("pgsql_skpbm")->select(DB::raw("SELECT concat(tran.npsn, peg.peg_id) as sdm_id, concat('') as nilai, concat('') as jam, concat('') as bulan, concat('') AS hari, tran.npsn, tran.nama_sekolah, peg.peg_id, peg.nama_pegawai, peg.status_guru, peg.nik, peg.jenis_pegawai, peg.jenis_kelamin, peg.tempat_lahir, peg.tgl_lahir, peg.alamat_pegawai, peg.no_telpon, peg.npwp, peg.kualifikasi_pendidikan, peg.jurusan_pendidikan, peg.keterangan, peg.no_rekening, peg.nama_bank, peg.an_bank
                FROM PUBLIC.skpbm_jadwal_pegawai AS peg
                JOIN PUBLIC.skpbm_jadwal_transaksi AS tran ON peg.skpbm_jadwal_transaksi_id = tran.id_skpbm_jadwal_transaksi
                WHERE tran.npsn = '$npsn' AND peg.jenis_pegawai = 'Pelatih' AND tran.status = '$status_skpbm' AND tran.setting_skpbm_jadwal_id =3 AND peg.is_aktif = TRUE"));
            }


            if($pelatih){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }
        return response()->json(compact('message', 'pelatih'), 200);
    }

    public function getPegawai(request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
            $npsn = $request->npsn;
            $jenjang = $request->jenjang;
            $is_induk = "";

            if($request->status==1){
                $where = "";
                if($jenjang=='SD'||$jenjang=='SMP'){
                    $is_induk = "AND (peg.is_induk IS TRUE OR (peg.is_induk IS NOT TRUE AND EXISTS(SELECT 1 FROM skpbm_jadwal_pegawai aa, skpbm_jadwal_transaksi bb
                    WHERE aa.skpbm_jadwal_transaksi_id = bb.id_skpbm_jadwal_transaksi
                        AND aa.is_aktif is true and aa.is_induk is true 
                        AND bb.status_sekolah = 'S'
                        AND (aa.bd_sertifikasi is null or aa.bd_sertifikasi = '')
                        AND peg.nik = aa.nik
                        AND tran.setting_skpbm_jadwal_id = bb.setting_skpbm_jadwal_id
                    ) 
                        ) or peg.nik in ('3578151301760004',
                                        '3518114402740005',
                                        '3515084605690003',
                                        '3578151603820003',
                                        '3524261203800004',
                                        '3578025212730004',
                                        '3578035202730004',
                                        '3578042509760001',
                                        '3578086811680001',
                                        '3578105607730003',
                                        '3578042307740012',
                                        '3578260211730002',
                                        '3578044910630004',
                                        '3578076009760002',
                                        '3578021107710004',
                                        '3578090304800001',
                                        '3578091604690001',
                                        '3578015808810002',
                                        '3515141307750004',
                                        '3578020506620002',
                                        '3525150608970003',
                                        '3505154602950003',
                                        '3515166012970001',
                                        '3578121804960001',
                                        '3509120811980003',
                                        '3578106606600001')
                )";
                }
            }else if($request->status==2){
                if($jenjang=='MI'||$jenjang=='MTS'){
                    $is_induk = "";
                    $where = "";
                }else{
                    if($sumberDana=='bopda'){
                        $where = "";
                        $is_induk = "";
                    }else{
                        $where = "AND (peg.bd_sertifikasi IS NULL OR peg.bd_sertifikasi = '' OR peg.bd_sertifikasi = '-')";
                        $is_induk = "AND (peg.is_induk IS TRUE OR (peg.is_induk IS NOT TRUE AND EXISTS(SELECT 1 FROM skpbm_jadwal_pegawai aa, skpbm_jadwal_transaksi bb
                            WHERE aa.skpbm_jadwal_transaksi_id = bb.id_skpbm_jadwal_transaksi
                                AND aa.is_aktif is true and aa.is_induk is true 
                                AND bb.status_sekolah = 'S'
                                AND (aa.bd_sertifikasi is null or aa.bd_sertifikasi = '')
                                AND peg.nik = aa.nik
                                AND tran.setting_skpbm_jadwal_id = bb.setting_skpbm_jadwal_id
                            ) 
                                ) or peg.nik in ('3578151301760004',
                                                '3518114402740005',
                                                '3515084605690003',
                                                '3578151603820003',
                                                '3524261203800004',
                                                '3578025212730004',
                                                '3578035202730004',
                                                '3578042509760001',
                                                '3578086811680001',
                                                '3578105607730003',
                                                '3578042307740012',
                                                '3578260211730002',
                                                '3578044910630004',
                                                '3578076009760002',
                                                '3578021107710004',
                                                '3578090304800001',
                                                '3578091604690001',
                                                '3578015808810002',
                                                '3515141307750004',
                                                '3578020506620002',
                                                '3525150608970003',
                                                '3505154602950003',
                                                '3515166012970001',
                                                '3578121804960001',
                                                '3509120811980003',
                                                '3578106606600001')
                        )";
                    }
                }
               
                if($sumberDana=='bopda'){
                    $where = "";
                }else{
                    $where = "AND (peg.bd_sertifikasi IS NULL OR peg.bd_sertifikasi = '' OR peg.bd_sertifikasi = '-')";
                }
            }

            if($jenjang=='MI'||$jenjang=='MTS'){
                $status_skpbm = "Verifikasi Pengawas";
            }else{
                $status_skpbm ="Setujui Dinas";
            }

            if($sumberDana=='bopda'){
                $kode = '3.03';
            }else{
                $kode = '3.01';
            }

            $conn = Set_koneksi::set_koneksi($request);
            $id_sudah = [];

            $pelatih_sudah = DB::connection($conn['conn_status'])->table('budget2021.sdm')->selectRaw("peg_id")->where('npsn', $npsn)->get();

            if($pelatih_sudah->count()!=0){
                foreach ($pelatih_sudah as $key) {
                    array_push($id_sudah,"'".$key->peg_id."'");
                }
            }

            

            if(count($id_sudah)!=0){
                $str_id = join(",",$id_sudah);
                $pegawai = DB::connection("pgsql_skpbm")->select(DB::raw("SELECT concat(tran.npsn, peg.peg_id) as sdm_id,concat('') AS hari, concat('') as nilai, concat('') as jam, concat('') as bulan, tran.npsn, tran.nama_sekolah, peg.peg_id, peg.nama_pegawai, peg.status_guru, peg.nik, peg.jenis_pegawai, peg.jenis_kelamin, peg.tempat_lahir, peg.tgl_lahir, peg.alamat_pegawai, peg.no_telpon, peg.npwp, peg.kualifikasi_pendidikan, peg.jurusan_pendidikan, jenis.nama_jenis, peg.no_rekening, peg.nama_bank, peg.an_bank, peg.keterangan, peg.jenis_tendik, tran.status, peg.jumlah_jam, peg.jam_kelas, peg.jam_sekolah_lain, peg.bd_sertifikasi, peg.is_induk
                FROM skpbm_jadwal_pegawai AS peg
                JOIN skpbm_jadwal_transaksi AS tran ON peg.skpbm_jadwal_transaksi_id = tran.id_skpbm_jadwal_transaksi 
                LEFT JOIN skpbm_jadwal_jenis AS jenis ON peg.skpbm_jadwal_jenis_id = jenis.id_skpbm_jadwal_jenis
                WHERE tran.status = '$status_skpbm' AND tran.setting_skpbm_jadwal_id = 3 AND peg.is_aktif IS TRUE AND (peg.status_guru in ('GTT','PTT','GTY','PTY') AND peg.jenis_pegawai IN ('Tendik','Guru','KS')) AND tran.npsn = '$npsn'
                $is_induk AND peg.peg_id not in ($str_id) $where"));
            }else{
                
                $pegawai = DB::connection("pgsql_skpbm")->select(DB::raw("SELECT concat(tran.npsn, peg.peg_id) as sdm_id,concat('') AS hari, concat('') as nilai, concat('') as jam, concat('') as bulan, tran.npsn, tran.nama_sekolah, peg.peg_id, peg.nama_pegawai, peg.status_guru, peg.nik, peg.jenis_pegawai, peg.jenis_kelamin, peg.tempat_lahir, peg.tgl_lahir, peg.alamat_pegawai, peg.no_telpon, peg.npwp, peg.kualifikasi_pendidikan, peg.jurusan_pendidikan, jenis.nama_jenis, peg.no_rekening, peg.nama_bank, peg.an_bank, peg.keterangan, peg.jenis_tendik, tran.status, peg.jumlah_jam, peg.jam_kelas, peg.jam_sekolah_lain, peg.bd_sertifikasi, peg.is_induk
                FROM skpbm_jadwal_pegawai AS peg
                JOIN skpbm_jadwal_transaksi AS tran ON peg.skpbm_jadwal_transaksi_id = tran.id_skpbm_jadwal_transaksi 
                LEFT JOIN skpbm_jadwal_jenis AS jenis ON peg.skpbm_jadwal_jenis_id = jenis.id_skpbm_jadwal_jenis
                WHERE tran.status = '$status_skpbm' AND tran.setting_skpbm_jadwal_id = 3 AND peg.is_aktif IS TRUE AND (peg.status_guru in ('GTT','PTT','GTY','PTY') AND peg.jenis_pegawai IN ('Tendik','Guru','KS')) AND tran.npsn = '$npsn'
                $is_induk $where"));
            }
            if($pegawai){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }
        return response()->json(compact('message', 'pegawai'), 200);
    }

    public function hapusPelatih(request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
            $npsn = $request->npsn;
            //$data = $request->data;
            $sdm_id = $request->sdm_id;

            if($sumberDana=='bopda'){
                $kode = '3.03';
            }else{
                $kode = '3.01';
            }

            $conn = Set_koneksi::set_koneksi($request);

            $deletePelatih = DB::connection($conn['conn_status'])->table('budget2021.sdm')
                            ->where('sdm_id', '=', $sdm_id)->delete();
            
            if($deletePelatih){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }
        return response()->json(compact('message'), 200);
    }

    public function updatePelatih(request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
            $npsn = $request->npsn;
            $data = $request->data;

            if($sumberDana=='bopda'){
                $kode = '3.03';
            }else{
                $kode = '3.01';
            }

            $conn = Set_koneksi::set_koneksi($request);

            $updateSdm = DB::connection($conn['conn_status'])->table('budget2021.sdm')
                        ->where('sdm_id', '=', $data['sdm_id'])
                        ->update([
                            'jam' => $data['jam'],
                            'hari' => $data['hari'],
                            'bulan' => $data['bulan'],
                            'nominal' => $data['nominal']
                        ]);
            if($updateSdm){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }
        return response()->json(compact('message'), 200);
    }

    public function listPegawai(request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
            $npsn = $request->npsn;

            if($sumberDana=='bopda'){
                $kode = '3.03';
            }else{
                $kode = '3.01';
            }

            $conn = Set_koneksi::set_koneksi($request);

            $list_pegawai = DB::connection($conn['conn_status'])->select(DB::raw("
                            SELECT *
                            FROM budget2021.sdm AS s
                            WHERE s.jenis_pegawai != 'Pelatih' AND s.kode_dana = '$kode' AND s.npsn = '$npsn' AND komponen_id NOT IN ('2.1.1.01.01.01.004.018','2.1.1.01.01.01.004.016','2.1.1.01.01.01.004.017')
                            ORDER BY s.nama_pegawai ASC
                        "));

            if($list_pegawai){
                $message = "success";
            }else{
                $message = "failed";
            }
        }
        return response()->json(compact('message', 'list_pegawai'), 200);
    }

    public function listPelatih(request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
            $npsn = $request->npsn;

            $penyelia = '';
            if(isset($request->penyelia)){
                $penyelia = $request->penyelia;
            }

            if($sumberDana=='bopda'){
                $kode = '3.03';
            }else{
                $kode = '3.01';
            }

            $conn = Set_koneksi::set_koneksi($request);

            if($request->status=='1'){
                if($penyelia==''){
                    if($kode=='3.03'){
                        $where = "s.jenis_pegawai != 'Pelatih' AND komponen_id NOT IN ('2.1.1.01.01.01.004.018','2.1.1.01.01.01.004.016','2.1.1.01.01.01.004.017')";
                    }else{
                        $where = "s.jenis_pegawai = 'Pelatih'";
                    }
                }else{
                    if($kode=='3.03'){
                        $where = "s.jenis_pegawai != 'Pelatih' AND status_perangkaan IS NOT NULL AND komponen_id NOT IN ('2.1.1.01.01.01.004.018','2.1.1.01.01.01.004.016','2.1.1.01.01.01.004.017')";
                    }else{
                        $where = "s.jenis_pegawai = 'Pelatih' AND  status_perangkaan IS NOT NULL"; 
                    }
                }
                $list_pelatih = DB::connection($conn['conn_status'])->select(DB::raw("SELECT *
                FROM budget2021.sdm AS s
                WHERE s.kode_dana = '$kode' AND s.npsn = '$npsn' AND $where
                ORDER BY s.nama_pegawai ASC"));
            }else {
                if($penyelia==''){
                    $where = "s.jenis_pegawai = 'Pelatih'";
                }else{
                    if(isset($request->jenis_pelatih)){
                        if($request->jenis_pelatih=='Pelatih'){
                            $where = "s.jenis_pegawai = 'Pelatih'";
                        }else{
                            $where = "s.jenis_pegawai != 'Pelatih'";
                        }
                    }else{
                        if($sumberDana=='bos'){
                            $where = "s.jenis_pegawai = 'Pelatih'";
                        }else{
                            $where = "s.jenis_pegawai != 'Pelatih'";
                        }
                    }
                }
                
                $list_pelatih = DB::connection($conn['conn_status'])->select(DB::raw("SELECT *
                        FROM budget2021.sdm AS s
                        WHERE $where AND s.kode_dana = '$kode' AND s.npsn = '$npsn'
                        ORDER BY s.nama_pegawai ASC"));
            }

            
            if($list_pelatih){
                $message = "success";
            }else{
                $message = "failed";
            }
        }
        return response()->json(compact('message', 'list_pelatih'), 200);
        
    }

    public function getSSH(Request $request){
        
            if($request->isMethod('Post')){
                $komponen = DB::select(DB::raw("SELECT k.komponen_name, k.komponen_harga, k.komponen_id
                FROM budget2021.komponen AS K
                WHERE k.komponen_id = '$request->komponen_id'"));
                
                $message = "success";
            }
            return response()->json(compact('message', 'komponen'),200);
    }

    public function detailSDM(Request $request){
        if($request->isMethod('Post')){
            $dataSdm = DB::select(DB::raw("SELECT s.nik_pegawai as nik, s.nama_pegawai, s.status_guru, s.nama_jenis, s.keterangan, s.jenis_tendik, s.status_perangkaan, s.jam, s.hari, s.bulan, s.nominal as nilai, s.komponen_harga
            FROM budget2021.sdm AS s
            WHERE s.nik_pegawai = '$request->nik'"));

            if($dataSdm){
                $message = 'success';
            }else {$message = 'failed';}
        }
        return response()->json(compact('message','dataSdm'),200);
    }

    public function updateSDM(Request $request){
        if($request->isMethod('Post')){
            $updateSdm = DB::table('budget2021.sdm')
                        ->where('nik_pegawai', '=', $request->nik)
                        ->update([
                            'jam' => $request->jam,
                            'bulan' => $request->bulan,
                            'nominal' => $request->nilai
                        ]);
            if($updateSdm){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }
        return response()->json(compact('message'),200);
    }

    public function listEntry(Request $request){
        if($request->isMethod('Post')){
            $list_entry = DB::select(DB::raw("SELECT *
            FROM budget2021.sdm AS s
            WHERE s.npsn = '$request->npsn'"));

            if($list_entry){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }

        return response()->json(compact('message', 'list_entry'), 200);
    }

    public function kirimPenyeliaPelatih(Request $request){
        if($request->isMethod('Post')){
            $sumberDana = $request->sumberDana;
            $npsn = $request->npsn;
            
            if($sumberDana=='bopda'){
                $kode = '3.03';
                $where = "and jenis_pegawai != 'Pelatih'";
            }else{
                $kode = '3.01';
                $where = "and jenis_pegawai = 'Pelatih'";
            }

            if($request->status==2){
                $where="";
            }

            $conn = Set_koneksi::set_koneksi($request);

            $updateStatusPelatih = DB::connection($conn['conn_status'])->table('budget2021.sdm')
                                    ->whereRaw("npsn='$npsn' and kode_dana='$kode' and (status_perangkaan != '1' OR status_perangkaan is null) $where")
                                    ->update(['status_perangkaan' => 0]);

            if($updateStatusPelatih){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }
        return response()->json(compact('message'),200);
    }

    public function kirimPenyelia(Request $request){
        if($request->isMethod('Post')){
            $updateStatusSDM = DB::table('budget2021.sdm')
                            ->where('npsn', '=', $request->npsn)
                            ->update(['status_perangkaan'=> "Kirim Penyelia"]);

            if($updateStatusSDM){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }
        return response()->json(compact('message'),200);
    }

    public function cekStatusPerangkaan(Request $request){
        if($request->isMethod('Post')){
            $statusPerangkaan = DB::select(DB::raw("SELECT distinct(s.status_perangkaan)
            FROM budget2021.sdm AS s
            WHERE s.npsn = '$request->npsn'"));
        }
        return response()->json(compact('statusPerangkaan'),200);
    }

    public function listSSH(Request $request){
        
            if($request->isMethod('Post')){
                $ssh = DB::select(DB::raw("SELECT *
                FROM budget2021.ssh2021
                WHERE komponen_name ILIKE '%$request->nama_komponen%' OR (komponen_id ILIKE '%$request->nama_komponen%' OR satuan ILIKE '%$request->nama_komponen%')"));
            }
            return response()->json(compact('ssh'),200);
    }

    public function listRekening(){
        $list_rekening = DB::select(DB::raw("SELECT concat(r.rekening_code,' | ', r.rekening_name) AS text, rd.rekening_code AS value
        FROM budget2021.rekening AS r
        JOIN budget2021.rekening_detail rd ON r.rekening_code = rd.rekening_code"));
        return response()->json(compact('list_rekening'),200);
    }

    public function getRekInfo(Request $request){
        $rek_info = DB::select(DB::raw("SELECT *
        FROM budget2021.rekening_detail AS rd
        WHERE rd.rekening_code = '$request->rekening'"));

        return response()->json(compact('rek_info'),200);
    }

    public function kirimUsulan(Request $request){
        if($request->isMethod('Post')){
            $kirim_usulan = DB::table('budget2021.komponen_usulan')->insert([
                'komponen_name' => $request->nama_barang,
                'komponen_harga' => $request->harga,
                'non_pajak' => $request->pajak,
                'rekening_code' => $request->rekening,
                'merk' => $request->merk,
                'satuan' => $request->satuan,
                'unit_id' => $request->unit_id
            ]);

            if($kirim_usulan){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }

        return response()->json(compact('message'), 200);

    }

    public function listUsulan(Request $request){
        if($request->isMethod('Post')){
            $list_usulan = DB::select(DB::raw("SELECT *
            FROM budget2021.komponen_usulan AS K
            WHERE K.unit_id = '$request->npsn'
            ORDER BY K.usulan_id ASC"));
        }
        return response()->json(compact('list_usulan'),200);
    }

    public function kirimDinasUsulan(Request $request){
        if($request->isMethod('Post')){
            $updateStatusUsulan = DB::table('budget2021.komponen_usulan')
                                    ->where('unit_id', '=', $request->unit_id)
                                    ->where('usulan_id', '=', $request->usulan_id)
                                    ->update(['status_usulan' => 1]);

            if($updateStatusUsulan){
                $message = 'success';
            }else{
                $message = 'failed';
            }             
        }
        return response()->json(compact('message'),200);
    }

    public function getSettingProfil(Request $request){
        if($request->isMethod('Post')){
            $npsn = $request->npsn;

            $conn = Set_koneksi::set_koneksi($request);

            $dataKS = DB::connection($conn['conn_status'])->select(DB::raw("SELECT ks.*, concat(ks.periode_awal_kepala_sekolah, ' s/d ',ks.periode_akhir_kepala_sekolah) AS periode
                            FROM PUBLIC.detail_kepala_sekolah_unit_kerja AS ks
                            WHERE ks.unit_id = '$npsn'
                            ORDER BY ks.periode_akhir_kepala_sekolah DESC
                        "));
            
            $dataBendaharaBopda = DB::connection($conn['conn_status'])->select(DB::raw("SELECT bb.*, concat(bb.periode_awal_bendahara, ' s/d ',bb.periode_akhir_bendahara) AS periode
                            FROM PUBLIC.detail_bendahara_unit_kerja AS bb
                            WHERE bb.unit_id = '$npsn'
                            ORDER BY bb.periode_akhir_bendahara DESC
                    "));

            $dataBendaharaBos = DB::connection($conn['conn_status'])->select(DB::raw("SELECT bos.*, concat(bos.periode_awal_bendahara_bos, ' s/d ',bos.periode_akhir_bendahara_bos) AS periode
                            FROM PUBLIC.detail_bendahara_bos_unit_kerja AS bos
                            WHERE bos.unit_id = '$npsn'
                            ORDER BY bos.periode_akhir_bendahara_bos DESC
                    "));
            
            $dataKomite = DB::connection($conn['conn_status'])->select(DB::raw("SELECT kom.*, concat(kom.periode_awal_ketua_komite_sekolah, ' s/d ',kom.periode_akhir_ketua_komite_sekolah) AS periode
                            FROM PUBLIC.detail_komite_unit_kerja AS kom
                            WHERE kom.unit_id = '$npsn'
                            ORDER BY kom.periode_akhir_ketua_komite_sekolah DESC
                    "));
            $dataSekolah = DB::connection($conn['conn_status'])->select(DB::raw("SELECT uk.unit_id, uk.unit_name, uk.unit_address, uk.nss, du.nama_desa, du.nama_kecamatan, du.nama_kabupaten, du.nama_provinsi, du.no_rek_bos, du.no_rek_bos_giro, du.no_rek_bopda, du.no_rek_bopda_giro
                            FROM PUBLIC.unit_kerja AS uk
                            JOIN PUBLIC.detail_unit_kerja AS du ON uk.unit_id = du.unit_id
                            WHERE uk.unit_id = '$npsn'
                    "));
        }
        return response()->json(compact('dataKS', 'dataBendaharaBopda', 'dataBendaharaBos', 'dataKomite', 'dataSekolah'),200);
    }

    public function webhook(Request $request){
        if($request->isMethod('Post')){
            $id = $request->id;
            $phone = $request->phone;
            $message = $request->message;
            $url = $request->url;
            $timestamp = $request->timestamp;
        
            $insert = DB::table('public.wa')->insert([
                'message_id' => $id,
                'phone' => $phone,
                'message' => $message,
                'url' => $url,
                'timestamp' => $timestamp
            ]);
            
            if($insert){
                $message = "success";
            }else{$message = "failed";}
        }
        return response()->json(compact('message'), 200);
    }

    public function get_wa(Request $request){
            $user = $request->user;
            if($user=='3578193110890002'){
                $mod = 'IN (3,8)';
            }else if($user=='3512073103870001'){
                $mod = 'IN (1,6)';
            }else if($user=='3578202812930001'){
                $mod = 'IN (2,7)';
            }else if($user=='3515186906900005'){
                $mod = 'IN (4,9)';
            }else if($user=='3512073107780001'){
                $mod = 'IN (0,5)';
            }
            
            $wa_message = DB::select(DB::raw("SELECT wa.id_wa, wa.phone, wa.message, wa.url, TO_TIMESTAMP(wa.timestamp), wa.status, wa.reply, wa.reply_by
            FROM PUBLIC.wa AS wa
            WHERE MOD(CAST(RIGHT(wa.phone,5) AS INTEGER),9) $mod
            ORDER BY TIMESTAMP DESC"));
            
            if($wa_message){
                $message = "success";
            }else{$message = "failed";}
        
        return response()->json(compact('message', 'wa_message'), 200);
    }

    public function updatewa(Request $request){
        $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
        SET status = 0, reply_by = '$request->nama', reply = '$request->pesan'
        WHERE phone = '$request->phone'"));
    }

    public function waselesai(Request $request){
        $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
        SET status = 1
        WHERE phone = '$request->phone'"));

        if($wa_stat){
            $message = "success";
        }else{$message = "failed";}

        return response()->json(compact('message'), 200);
    }

}

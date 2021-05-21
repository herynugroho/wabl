<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use JWTAuth;
use App\Models\Position;
use DB;
use App\Http\Libraries\Set_koneksi;

class PenyeliaController extends Controller
{
    function cek_skpbm_pelatih(request $request){
        if($request->isMethod('Post')){
            $nik_pegawai = $request->nik_pegawai;

            $pelatih_skpbm = DB::connection("pgsql_skpbm")->select(DB::raw("
                SELECT tran.npsn, tran.nama_sekolah, peg.peg_id, peg.nama_pegawai, peg.nik, peg.jenis_pegawai, peg.jenis_kelamin, peg.alamat_pegawai, peg.no_telpon, peg.kualifikasi_pendidikan, peg.jurusan_pendidikan, peg.keterangan, pel.hari_ke, pel.jam_awal, pel.jam_akhir
                FROM PUBLIC.skpbm_jadwal_pegawai AS peg
                JOIN PUBLIC.skpbm_jadwal_transaksi AS tran ON peg.skpbm_jadwal_transaksi_id = tran.id_skpbm_jadwal_transaksi
                JOIN PUBLIC.skpbm_jadwal_transaksi_pelajaran AS pel ON peg.skpbm_jadwal_transaksi_id = pel.skpbm_jadwal_transaksi_id AND peg.nik = pel.nik
                WHERE peg.jenis_pegawai = 'Pelatih' AND tran.status = 'Setujui Dinas' AND tran.setting_skpbm_jadwal_id =3 AND peg.is_aktif = TRUE AND peg.nik = '$nik_pegawai'
                "));

            if($pelatih_skpbm){
                $message = 'success';
            }else{
                $message = 'failed';
            }

        }
        return response()->json(compact('message', 'pelatih_skpbm'), 200);
    }

    function cek_perangkaan_pelatih(request $request){
        if($request->isMethod('Post')){
            $nik_pegawai = $request->nik_pegawai;

            $pelatih_perangkaan = DB::connection("pgsql")->select(DB::raw("
                SELECT concat(s.hari, ' Hari' ,' x ', s.bulan, ' Bulan') AS koefisien, s.nominal, uk.unit_name, s.status_perangkaan
                FROM budget2021.sdm AS s
                JOIN PUBLIC.unit_kerja AS uk ON s.npsn = uk.unit_id
                WHERE s.kode_dana = '3.01' AND s.jenis_pegawai = 'Pelatih' AND s.nik_pegawai = '$nik_pegawai'
                "));
            
            $pelatih_perangkaan_s = DB::connection("pgsql_swasta")->select(DB::raw("
                SELECT concat(s.hari, ' Hari' ,' x ', s.bulan, ' Bulan') AS koefisien, s.nominal, uk.unit_name, s.status_perangkaan
                FROM budget2021.sdm AS s
                JOIN PUBLIC.unit_kerja AS uk ON s.npsn = uk.unit_id
                WHERE s.kode_dana = '3.01' AND s.jenis_pegawai = 'Pelatih' AND s.nik_pegawai = '$nik_pegawai' and s.hari is not null
                "));
            
            $pelatih_perangkaan = array_merge($pelatih_perangkaan,$pelatih_perangkaan_s);

            if($pelatih_perangkaan){
                $message = 'success';
            }else{
                $message = 'failed';
            }

        }
        return response()->json(compact('message', 'pelatih_perangkaan'), 200);
    }


    public function sekolahNegeriPenyelia(request $request){
        if($request->isMethod('Post')){
            $user_id = $request->user_id;

            if(isset($request->status)){
                $sekolah = DB::connection('pgsql_swasta')->select(DB::raw("
                    SELECT uk.unit_id, uk.unit_name, uk.jenjang, uk.kelompok_id
                    FROM PUBLIC.unit_kerja AS uk 
                    WHERE uk.kelompok_id = '2' and uk.unit_id IN (SELECT uh.unit_id FROM PUBLIC.user_handle AS uh WHERE uh.user_id = '$user_id')
                    ORDER BY uk.unit_name ASC
                    "));
            }else{
                $sekolah = DB::connection('pgsql')->select(DB::raw("
                    SELECT uk.unit_id, uk.unit_name, uk.jenjang, uk.kelompok_id
                    FROM PUBLIC.unit_kerja AS uk 
                    WHERE uk.kelompok_id = '1' and uk.unit_id IN (SELECT uh.unit_id FROM PUBLIC.user_handle AS uh WHERE uh.user_id = '$user_id')
                    ORDER BY uk.unit_name ASC
                    "));
            }

            if($sekolah){
                $message = 'success';
            }else{
                $message = 'failed';
            }
        }
        return response()->json(compact('message', 'sekolah'), 200);
    }

    function verifikasi_sekolah(Request $request){
        $jenjang = $request->jenjang; // "SD"
        $kode_kegiatan = $request->kode_kegiatan; // "3.01.3.01.007"
        $komponen_id = $request->komponen_id; // null
        $npsn = $request->npsn; // "20532932"
        $rekening = $request->rekening; // null
        $status = $request->status; // "1"
        $subtitle = $request->subtitle; // "ATK"
        $sumberDana = $request->sumberDana; // "bos"

        $conn = Set_koneksi::Set_koneksi($request);

        $user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

        $dt_update = [
            'catatan'=>null,
            'status'=>'1',
            'update_time'=>date('Y-m-d H:i:s'),
            'update_user'=>str_replace(["'"], ["\'"], $request->user_id),
            'update_ip'=>GettingIP::get_client_ip(),
        ];

        $kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')
        ->where('npsn',$npsn)
        ->where('kode_kegiatan',$kode_kegiatan)
        ->first();

        $nama_kegiatan = '';
        if(!empty($kegiatan)){
            $nama_kegiatan = $kegiatan->nama_kegiatan;
        }

        if($komponen_id=='' && $rekening==''){
            $update1 = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')
            ->where('npsn',$npsn)
            ->where('kode_kegiatan',$kode_kegiatan)
            ->where('subtitle',$subtitle)
            ->update($dt_update);
        }else{
            $update1 = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')
            ->where('npsn',$npsn)
            ->where('kode_kegiatan',$kode_kegiatan)
            ->where('subtitle',$subtitle)
            ->where('komponen_id',$komponen_id)
            ->where('rekening',$rekening)
            ->update($dt_update);
        }

        if($update1){
            $message = 'Berhasil diverifikasi';
            $code = '200';
        }else{
            $message = 'Gagal diverifikasi';
            $code = '250';
        }

        return response()->json(compact('message','code','nama_kegiatan','kode_kegiatan'), 200);
    }

    function revisi_sekolah(Request $request){
        $jenjang = $request->jenjang; // "SD"
        $kode_kegiatan = $request->kode_kegiatan; // "3.01.3.01.007"
        $komponen_id = $request->komponen_id; // null
        $npsn = $request->npsn; // "20532932"
        $rekening = $request->rekening; // null
        $status = $request->status; // "1"
        $subtitle = $request->subtitle; // "ATK"
        $sumberDana = $request->sumberDana; // "bos"
        $catatan = $request->catatan;

        $conn = Set_koneksi::Set_koneksi($request);

        $user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

        $dt_update = [
            'status'=>'12',
            'catatan'=>$catatan,
            'update_time'=>date('Y-m-d H:i:s'),
            'update_user'=>str_replace(["'"], ["\'"], $request->user_id),
            'update_ip'=>GettingIP::get_client_ip(),
        ];

        $kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')
        ->where('npsn',$npsn)
        ->where('kode_kegiatan',$kode_kegiatan)
        ->first();

        $nama_kegiatan = '';
        if(!empty($kegiatan)){
            $nama_kegiatan = $kegiatan->nama_kegiatan;
        }

        if($komponen_id=='' && $rekening==''){
            $update1 = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')
            ->where('npsn',$npsn)
            ->where('kode_kegiatan',$kode_kegiatan)
            ->where('subtitle',$subtitle)
            ->update($dt_update);
        }else{
            $update1 = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')
            ->where('npsn',$npsn)
            ->where('kode_kegiatan',$kode_kegiatan)
            ->where('subtitle',$subtitle)
            ->where('komponen_id',$komponen_id)
            ->where('rekening',$rekening)
            ->update($dt_update);
        }

        if($update1){
            $message = 'Berhasil direvisi';
            $code = '200';
        }else{
            $message = 'Gagal direvisi';
            $code = '250';
        }

        return response()->json(compact('message','code','nama_kegiatan','kode_kegiatan'), 200);
    }

    function verifikasi_pelatih(Request $request){
        $jenjang = $request->jenjang; // "SD"
        $kode_kegiatan = $request->kode_kegiatan; // "3.01.3.01.007"
        $komponen_id = $request->komponen_id; // null
        $npsn = $request->npsn; // "20532932"
        $sdm_id = $request->sdm_id; // "20532932"
        $user_id = $request->user_id;
        $sumber = $request->sumberDana;

        $conn = Set_koneksi::Set_koneksi($request);

        $update = [
            'catatan'=>null,
            'status_perangkaan'=>'1',
            'update_by'=>$user_id
        ];


        $pelatih = DB::connection($conn['conn_status'])->table('budget2021.sdm')->where('sdm_id',$sdm_id)->update($update);

        if($pelatih){
            if($request->status=='1'){
                if($sumber=='bopda'){
                    $get_pelatih = DB::connection($conn['conn_status'])->table('budget2021.sdm')->where('sdm_id','=',$sdm_id)->first();
                    $data_master = [
                        'peg_id' => $get_pelatih->peg_id,
                        'npsn' => $get_pelatih->npsn,
                        'nama_pegawai' => $get_pelatih->nama_pegawai,
                        'status_guru' => $get_pelatih->status_guru,
                        'nik_pegawai' => $get_pelatih->nik_pegawai,
                        'jenis_pegawai' => $get_pelatih->jenis_pegawai,
                        'jenis_kelamin' => $get_pelatih->jenis_kelamin,
                        'tempat_lahir' => $get_pelatih->tempat_lahir,
                        'tgl_lahir' => $get_pelatih->tgl_lahir,
                        'alamat_pegawai' => $get_pelatih->alamat_pegawai,
                        'no_telpon' => $get_pelatih->no_telpon,
                        'npwp' => $get_pelatih->npwp,
                        'kualifikasi_pendidikan' => $get_pelatih->kualifikasi_pendidikan,
                        'nama_jenis' => $get_pelatih->nama_jenis,
                        'no_rekening' => $get_pelatih->no_rekening,
                        'nama_bank' => $get_pelatih->nama_bank,
                        'an_bank' => $get_pelatih->an_bank,
                        'jenis_tendik' => $get_pelatih->jenis_tendik,
                        'keterangan' => $get_pelatih->keterangan,
                        'bulan' => $get_pelatih->bulan,
                        'hari' => $get_pelatih->hari,
                        'jam' => $get_pelatih->jam,
                        'kode_dana' => $get_pelatih->kode_dana,
                        'kode_kegiatan' => $get_pelatih->kode_kegiatan,
                        'status_perangkaan' => $get_pelatih->status_perangkaan,
                        'created_at' => $get_pelatih->created_at,
                        'update_by' => $get_pelatih->update_by,
                        'catatan' => null,
                        'lock' => null,
                    ];

                    $komponen_id = DB::connection('pgsql')->table('budget2021.ssh2021')->whereRaw("komponen_id IN ('2.1.1.01.01.01.004.018','2.1.1.01.01.01.004.016','2.1.1.01.01.01.004.017')")->get();

                    if($komponen_id->count()!=0){
                        foreach ($komponen_id as $key) {
                            if($key->komponen_id=='2.1.1.01.01.01.004.018'){
                                $nama_plus = 'JK';
                            }else if($key->komponen_id=='2.1.1.01.01.01.004.016'){
                                $nama_plus = 'JKN';
                            }else if($key->komponen_id=='2.1.1.01.01.01.004.017'){
                                $nama_plus = 'JKK';
                            }

                            $new_sdm_id = $get_pelatih->sdm_id.$nama_plus;

                            $data_plus = [
                                'sdm_id' => $new_sdm_id,
                                'komponen_id' => $key->komponen_id,
                                'komponen_name' => $key->komponen_name,
                                'komponen_harga' => $key->komponen_harga_bulat,
                                'nominal' => (ceil($get_pelatih->nominal/$get_pelatih->bulan*$key->komponen_harga_bulat)*$get_pelatih->bulan),
                            ];
                            $insert_jk = array_merge($data_master,$data_plus);
                            
                            // SIMPAN JK
                            $get_jk = DB::connection($conn['conn_status'])->table('budget2021.sdm')->where('sdm_id',$new_sdm_id)->first();
                            if(!empty($get_jk)){
                                DB::connection($conn['conn_status'])->table('budget2021.sdm')->where('sdm_id',$insert_jk['sdm_id'])->update($insert_jk);
                            }else{
                                DB::connection($conn['conn_status'])->table('budget2021.sdm')->insert($insert_jk);
                            }
                        }
                    }
                }
            }
            $message = 'Berhasil diverifikasi';
            $code = '200';
        }else{
            $message = 'Gagal diverifikasi';
            $code = '250';
        }

        return response()->json(compact('message','code'), 200);
    }

    function revisi_pelatih(Request $request){
        $jenjang = $request->jenjang; // "SD"
        $kode_kegiatan = $request->kode_kegiatan; // "3.01.3.01.007"
        $komponen_id = $request->komponen_id; // null
        $npsn = $request->npsn; // "20532932"
        $sdm_id = $request->sdm_id; // "20532932"
        $user_id = $request->user_id;
        $catatan = $request->catatan;

        $conn = Set_koneksi::Set_koneksi($request);

        $update = [
            'status_perangkaan'=>'12',
            'catatan'=>$catatan,
            'update_by'=>$user_id
        ];

        $pelatih = DB::connection($conn['conn_status'])->table('budget2021.sdm')->where('sdm_id',$sdm_id)->update($update);

        if($pelatih){
            $message = 'Berhasil direvisi';
            $code = '200';
        }else{
            $message = 'Gagal direvisi';
            $code = '250';
        }

        return response()->json(compact('message','code'), 200);
    }

    function lock_pelatih(Request $request){
        $sumberDana = $request->sumberDana;
        $npsn = $request->npsn;
        $lock = $request->lock;

        if($sumberDana=='bopda'){
            $kode = '3.03';
        }else{
            $kode = '3.01';
        }

        $conn = Set_koneksi::set_koneksi($request);


        if($lock=='lock'){
            $status_lock = 1;
        }else{
            $status_lock = 'null';

        }

        if($request->status=='1'){
            if($sumberDana=='bopda'){
                $update = DB::connection($conn['conn_status'])->select("
                    UPDATE budget2021.sdm 
                    SET lock=".$status_lock."
                    WHERE jenis_pegawai != 'Pelatih' AND kode_dana = '$kode' AND npsn = '$npsn'");
            }else{
                $update = DB::connection($conn['conn_status'])->select("
                    UPDATE budget2021.sdm 
                    SET lock=".$status_lock."
                    WHERE jenis_pegawai = 'Pelatih' AND kode_dana = '$kode' AND npsn = '$npsn'");
            }

            if($lock!='lock'){
                if($sumberDana=='bopda'){
                    DB::connection($conn['conn_status'])->select("
                        UPDATE budget2021.sdm SET status_perangkaan='0' 
                        WHERE jenis_pegawai != 'Pelatih' AND kode_dana = '$kode' AND npsn = '$npsn'");

                    // DB::connection($conn['conn_status'])->select("DELETE FROM budget2021.sdm WHERE jenis_pegawai != 'Pelatih' AND kode_dana = '$kode' AND npsn = '$npsn' AND komponen_id IN ('2.1.1.01.01.01.004.018','2.1.1.01.01.01.004.016','2.1.1.01.01.01.004.017')");
                }else{
                    DB::connection($conn['conn_status'])->select("
                        UPDATE budget2021.sdm SET status_perangkaan='0' 
                        WHERE jenis_pegawai = 'Pelatih' AND kode_dana = '$kode' AND npsn = '$npsn'");
                }
            }
        }else{
            if($request->jenis_pelatih=='Pelatih'){
                $where = "jenis_pegawai = 'Pelatih'";
            }else{
                $where = "jenis_pegawai != 'Pelatih'";
            }

            if($lock!='lock'){
                DB::connection($conn['conn_status'])->select("
                    UPDATE budget2021.sdm SET status_perangkaan='0' 
                    WHERE $where AND kode_dana = '$kode' AND npsn = '$npsn'");
            }

            $update = DB::connection($conn['conn_status'])->select("
                UPDATE budget2021.sdm 
                SET lock=".$status_lock."
                WHERE $where AND kode_dana = '$kode' AND npsn = '$npsn'");
        }


        if($update){
            $message = 'Berhasil di-'.$lock;
            $code = '200';
        }else{
            $message = 'Gagal di-'.$lock;
            $code = '250';
        }

        return response()->json(compact('message','code'), 200);
    }

    function kirim_ver_awal(Request $request){
        $jenjang = $request->jenjang; // "SD"
        $kode_kegiatan = $request->kode_kegiatan; // "3.01.3.01.007"
        $komponen_id = $request->komponen_id; // null
        $npsn = $request->npsn; // "20532932"
        $rekening = $request->rekening; // null
        $status = $request->status; // "1"
        $subtitle = $request->subtitle; // "ATK"
        $sumberDana = $request->sumberDana; // "bos"
        $status_awal = $request->status_awal; // "bos"

        $conn = Set_koneksi::Set_koneksi($request);

        $user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

        if($status_awal=='1'){
            $message = 'Berhasil diverifikasi';
            $icon = 'CheckIcon';
            $type = 'success';
        }else{
            $message = 'Berhasil di revisi';
            $icon = 'XIcon';
            $type = 'danger';
        }

        $dt_update = [
            'status_kegiatan'=>$status_awal,
            'last_update_time'=>date('Y-m-d H:i:s'),
            'last_update_user'=>str_replace(["'"], ["\'"], $request->user_id),
            'last_update_ip'=>GettingIP::get_client_ip(),
        ];

        $kegiatan = DB::connection('pgsql_swasta')->table('budget2021.kegiatan_awal')
        ->where('npsn',$npsn)
        ->get();

        if($kegiatan->count()!=0){
            $update1 = DB::connection('pgsql_swasta')->table('budget2021.kegiatan_awal')
            ->where('npsn',$npsn)
            ->update($dt_update);

            if($update1){
                $code = '200';
            }else{
                $message = 'Gagal Update';
                $code = '250';
            }
        }else{
            $message = 'Tidak ada kegiatan untuk di verifikasi';
            $code = '250';
        }


        return response()->json(compact('message','code','icon','type'), 200);
    }

    function unlock_alokasi(Request $request){
        $npsn = $request->npsn;
        $sumber = $request->sumberDana;

        if($sumber=='bos'){
            $kode = '.3.01.';
        }else{
            $kode = '.3.03.';
        }

        $conn = Set_koneksi::set_koneksi($request);

        $get_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->where('kode_kegiatan','like','%'.$kode.'%')->where('npsn',$npsn)->get();
        if($get_kegiatan->count()!=0){
            $update = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->where('kode_kegiatan','like','%'.$kode.'%')->where('npsn',$npsn)->update(['kunci_alokasi'=>null]);
            if($update){
                $message = 'Berhasil dibuka';
                $code = '200';
            }else{
                $message = 'Gagal dibuka';
                $code = '250';
            }
        }else{
            $message = 'Kegiatan tidak ditemukan';
            $code = '250';
        }


        return response()->json(compact('message','code'), 200);
    }
}

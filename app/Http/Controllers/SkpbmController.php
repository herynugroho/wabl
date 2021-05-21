<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class SkpbmController extends Controller
{
    public function get_pegawai(Request $request){
        if($request->isMethod('Post')){
            $user_id = $request->user_id;                               
            $pelatih = DB::connection("pgsql_skpbm")->select(DB::raw("SELECT concat('') as nilai, concat('') as jam, concat('') as bulan, concat('') AS hari, tran.npsn, tran.nama_sekolah, peg.peg_id, peg.nama_pegawai, peg.status_guru, peg.nik, peg.jenis_pegawai, peg.jenis_kelamin, peg.tempat_lahir, peg.tgl_lahir, peg.alamat_pegawai, peg.no_telpon, peg.npwp, peg.kualifikasi_pendidikan, peg.jurusan_pendidikan, peg.keterangan, peg.no_rekening, peg.nama_bank, peg.an_bank
            FROM PUBLIC.skpbm_jadwal_pegawai AS peg
            JOIN PUBLIC.skpbm_jadwal_transaksi AS tran ON peg.skpbm_jadwal_transaksi_id = tran.id_skpbm_jadwal_transaksi
            WHERE tran.npsn = '$request->user_id' AND peg.jenis_pegawai = 'Pelatih' AND tran.status = 'Setujui Dinas' AND tran.setting_skpbm_jadwal_id =3 AND peg.is_aktif = TRUE
            "));

            $pegawai = DB::connection("pgsql_skpbm")->select(DB::raw("SELECT concat('') as nilai, concat('') as jam, concat('') as bulan, tran.npsn, tran.nama_sekolah, peg.peg_id, peg.nama_pegawai, peg.status_guru, peg.nik, peg.jenis_pegawai, peg.jenis_kelamin, peg.tempat_lahir, peg.tgl_lahir, peg.alamat_pegawai, peg.no_telpon, peg.npwp, peg.kualifikasi_pendidikan, peg.jurusan_pendidikan, jenis.nama_jenis, peg.no_rekening, peg.nama_bank, peg.an_bank, peg.keterangan, peg.jenis_tendik, tran.status, peg.jumlah_jam, peg.jam_kelas, peg.jam_sekolah_lain, peg.bd_sertifikasi, peg.is_induk
            FROM skpbm_jadwal_pegawai AS peg
            JOIN skpbm_jadwal_transaksi AS tran ON peg.skpbm_jadwal_transaksi_id = tran.id_skpbm_jadwal_transaksi 
            LEFT JOIN skpbm_jadwal_jenis AS jenis ON peg.skpbm_jadwal_jenis_id = jenis.id_skpbm_jadwal_jenis
            WHERE tran.status = 'Setujui Dinas' AND tran.setting_skpbm_jadwal_id = 3 AND peg.is_aktif IS TRUE AND (peg.status_guru in ('GTT','PTT') AND peg.jenis_pegawai IN ('Tendik','Guru')) AND tran.status_sekolah = 'N' AND	tran.npsn = '$request->user_id' 
            AND (peg.is_induk IS TRUE OR (peg.is_induk IS NOT TRUE AND EXISTS(SELECT 1 FROM skpbm_jadwal_pegawai aa, skpbm_jadwal_transaksi bb
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
                                            '3578020506620002')
                    )"));

            $message = "Success";
            $list_pegawai['pelatih'] = $pelatih;
            $list_pegawai['gttptt'] = $pegawai;

            return response()->json(compact('message', 'list_pegawai'), 200);
        }
        else {
            $message = "Failed, check Your Method";
            return response()->json(compact('message', 400));
        }
    
    }

    
}

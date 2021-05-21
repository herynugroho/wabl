<?php

namespace App\Http\Controllers\BerkasBpd;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use DB;
use App\Http\Controllers\Controller;


class PenyeliaController extends Controller
{
	function list_berkas(Request $request){
		$conn = Set_koneksi::set_koneksi($request);
		$npsn = $request->npsn;

		$list_berkas = DB::connection($conn['conn_status'])->table('budget2021.master_berkas_verifikasi')->selectRaw("*,'' as centang")->orderBy('kelompok','ASC')->get();

		$verifikasi = 0;

		if($list_berkas->count()!=0){
			foreach ($list_berkas as $value) {

				$cek_berkas = DB::connection($conn['conn_status'])->table('budget2021.berkas_verifikasi')->whereRaw("npsn='$npsn' AND jenis_berkas='".$value->jenis_berkas."'")->first();

				if($value->kelompok=='1'){
					$nama_berkas = 'Permohonan Bantuan Dana Hibah BPD Tahun 2021';
				}else if($value->kelompok=='2'){
					$nama_berkas = 'Permohonan Pencairan Dana Hibah BPD Tahun 2021';
				}else if($value->kelompok=='3'){
					$nama_berkas = 'Pengajuan Pencairan Semester I';
				}else if($value->kelompok=='4'){
					$nama_berkas = 'Pengajuan Pencairan Semester II';
				}else{
					$nama_berkas = '';
				}

				$centang = null;
				if(!empty($cek_berkas)){
					$centang = ($cek_berkas->status_berkas=='Iya') ? true : null;
					if($centang==true){
						$verifikasi = 'sudah';
					}
				}

				$value->nama_kelompok = $nama_berkas;
				$value->centang = $centang;
			}
		}


		return response()->json(compact('list_berkas','verifikasi'), 200);
	}

	function simpan_berkas(Request $request){
		$data = $request->props;
		$npsn = $request->npsn;

		$conn = Set_koneksi::set_koneksi($request);

		for ($i=0; $i < count($data); $i++) { 
			$baris = $data[$i];
			$cek_berkas = DB::connection($conn['conn_status'])->table('budget2021.berkas_verifikasi')->whereRaw("npsn='$npsn' AND jenis_berkas='".$baris['jenis_berkas']."'")->first();

			$data_update = [
				'npsn'=>$npsn,
				'jenis_berkas'=>$baris['jenis_berkas'],
				'status_berkas'=>($baris['centang']==true) ? 'Iya' : null,
			];

			if(!empty($cek_berkas)){
				$cek_berkas = DB::connection($conn['conn_status'])->table('budget2021.berkas_verifikasi')->whereRaw("npsn='$npsn' AND jenis_berkas='".$baris['jenis_berkas']."'")->update($data_update);
			}else{
				$cek_berkas = DB::connection($conn['conn_status'])->table('budget2021.berkas_verifikasi')->insert($data_update);
			}
		}

		$message = 'Berhasil disimpan';

		return response()->json(compact('message'), 200);
	}
}

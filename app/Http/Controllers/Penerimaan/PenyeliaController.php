<?php

namespace App\Http\Controllers\Penerimaan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use App\Http\Libraries\Konversi;
use DB;
use App\Http\Controllers\Controller;


class PenyeliaController extends Controller
{
	function get_penerimaan(Request $request){
		$unit_id = $request->user_id;

		$npsn_pakai = DB::connection('pgsql')->table('public.user_handle')->selectRaw("unit_id")->whereRaw("user_id = '$unit_id'");
		$npsn_pakai_all = DB::connection('pgsql')->table('public.user_handle')->selectRaw("unit_id")->whereRaw("user_id = '$unit_id'")->union($npsn_pakai);

		// return $npsn_pakai_all;

		$penerimaan = [];

		$penerimaan1 = DB::connection('pgsql')->table('budget2021.penerimaan')->selectRaw("
			npsn,
			sekolah as nama_sekolah,
			nominal,
			tanggal_penerimaan as tgl_penerimaan,
			kode_dana,
			tahap,
			penerimaan,
			'' as jenis,
			file_pembuktian,perbaikan_nominal,perbaikan_tanggal,status_penerimaan,catatan,'NEGERI' as status_sekolah,
			0 as nilai_honor,
			0 as nilai_barjas
			")->whereRaw("kode_dana='3.01' AND status_penerimaan='0'")->whereIn('npsn',$npsn_pakai_all)->get();

		$penerimaan2 = DB::connection('pgsql')->table('budget2021.pencairan')->selectRaw("
			npsn, 
			nama_sekolah, 
			0 as nominal,
			tanggal_sp2d as tgl_penerimaan,
			'3.03' as kode_dana,
			periode as tahap,
			CONCAT('BOPDA ',jenis,' ',periode,' Tahun 2021') as penerimaan,
			jenis,
			file_pembuktian,perbaikan_nominal,perbaikan_tanggal,status_penerimaan,catatan,'NEGERI' as status_sekolah,
			nilai_honor,
			nilai_barjas
			")->whereRaw("(tanggal_sp2d is not null) AND status_penerimaan='0'")->whereIn('npsn',$npsn_pakai_all)->get();

		$penerimaan3 = DB::connection('pgsql_swasta')->table('budget2021.pencairan')->selectRaw("
			npsn, 
			nama_sekolah, 
			0 as nominal,
			tanggal_sp2d as tgl_penerimaan,
			'3.03' as kode_dana,
			periode as tahap,
			CONCAT('BOPDA ',jenis,' ',periode,' Tahun 2021') as penerimaan,
			jenis,
			file_pembuktian,perbaikan_nominal,perbaikan_tanggal,status_penerimaan,catatan,'SWASTA' as status_sekolah,
			nilai_honor,
			nilai_barjas
			")->whereRaw("(tanggal_sp2d is not null) AND status_penerimaan='0'")->whereIn('npsn',$npsn_pakai_all)->get();

		if($penerimaan1->count()!=0){
			foreach ($penerimaan1 as $key) {
				if($key->tahap=='1'){
					// $sisa_saldo = DB::connection('pgsql')->table('budget2021.pagu')->whereRaw("npsn='".$key->npsn."' AND kode_sumber='3.07'")->first();

					// $sisa = (!empty($sisa_saldo)) ? $sisa_saldo->penerimaan : 0;

					// $key->nominal += $sisa;
				}

				$key->nominal = number_format($key->nominal,0,',','.');
				$key->perbaikan_nominal = number_format($key->perbaikan_nominal,0,',','.');

				array_push($penerimaan,$key);
			}
		}

		if($penerimaan2->count()!=0){
			foreach ($penerimaan2 as $key) {
				if($key->jenis!=''){
					if($key->jenis=='Pegawai'){
						$key->nominal = $key->nilai_honor;
					}else if($key->jenis=='Barjas'){
						$key->nominal = $key->nilai_barjas;
					}
				}

				$key->nominal = number_format($key->nominal,0,',','.');
				$key->perbaikan_nominal = number_format($key->perbaikan_nominal,0,',','.');

				array_push($penerimaan,$key);
			}
		}

		if($penerimaan3->count()!=0){
			foreach ($penerimaan3 as $key) {
				if($key->jenis!=''){
					if($key->jenis=='Pegawai'){
						$key->nominal = $key->nilai_honor;
					}else if($key->jenis=='Barjas'){
						$key->nominal = $key->nilai_barjas;
					}
				}

				$key->nominal = number_format($key->nominal,0,',','.');
				$key->perbaikan_nominal = number_format($key->perbaikan_nominal,0,',','.');

				array_push($penerimaan,$key);
			}
		}

		return response()->json(compact('penerimaan'), 200);
	}

	function get_file(Request $request){
		$nama_file = $request->nama_file;
		$ar_nama_file = explode('.', $nama_file);
		$ext = $ar_nama_file[1];

		$storage = Storage::disk('ftp')->url('/bukti_penerimaan/'.$nama_file);
		// return $storage;

		if($ext=='pdf'){
			$filenya = '<iframe src="http://103.76.17.11:8081/dispendik/sipks'.$storage.'" style="width: 100%;height: 200px"></iframe>';
		}else{
			$url = "http://103.76.17.11:8081/dispendik/sipks".$storage;
			$filenya = '<img src="data:image/png; base64,'.base64_encode(file_get_contents($url)).'" style="width: 100%;"/>';
		}

		return response()->json(compact('filenya'));
	}

	function kirim_sesuai(Request $request){
		$jenjang = $request->jenjang;
		$kode_dana = $request->kode_dana;
		$npsn = $request->npsn;
		$status_sekolah = $request->status_sekolah;
		$tahap = $request->tahap;
		$jenis = $request->jenis;

		if($status_sekolah=='NEGERI'){
			$request->status='1';
			if($kode_dana=='3.01'){
				$table='budget2021.penerimaan';
				$where = "npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='$tahap'";
			}else{
				$table='budget2021.pencairan';
				$where = "npsn='$npsn' AND jenis='$jenis' AND periode='$tahap'";
			}
		}else{
			$request->status='2';
			$table='budget2021.pencairan';
			$where = "npsn='$npsn' AND periode='$tahap'";
		}

		$conn = Set_koneksi::set_koneksi($request);

		$penerimaan = DB::connection($conn['conn_status'])->table($table)->whereRaw($where)->first();

		$code = '250';
		if(!empty($penerimaan)){
			$data_update = [
				'status_penerimaan'=>'13',
				'catatan'=>'Sudah kami sesuailan, silahkan diperiksa kembali',
			];

			if($table=='budget2021.penerimaan'){

				// $pagu = DB::connection('pgsql')->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->first();

				// $sisa = (!empty($pagu)) ? $pagu->penerimaan : 0;

				// 'nominal'=>($penerimaan->perbaikan_nominal-$sisa),
				$imbuhan_update = [
					'nominal'=>$penerimaan->perbaikan_nominal,
					'tanggal_penerimaan'=>$penerimaan->perbaikan_tanggal,
				];
			}else{
				if($jenis=='Barjas'){
					$imbuhan_update = [
						'nilai_barjas'=>$penerimaan->perbaikan_nominal,
						'tanggal_sp2d'=>$penerimaan->perbaikan_tanggal,
					];
				}else{
					$imbuhan_update = [
						'nilai_honor'=>$penerimaan->perbaikan_nominal,
						'tanggal_sp2d'=>$penerimaan->perbaikan_tanggal,
					];
				}
			}

			$data_update = array_merge($data_update,$imbuhan_update);

			$update = DB::connection($conn['conn_status'])->table($table)->whereRaw($where)->update($data_update);
			if($update){
				$code = '200';
			}
		}

		return response()->json(compact('code'), 200);
	}

	function kirim_tdk_sesuai(Request $request){
		$catatan = $request->catatan;
		$jenjang = $request->jenjang;
		$kode_dana = $request->kode_dana;
		$npsn = $request->npsn;
		$status_sekolah = $request->status_sekolah;
		$tahap = $request->tahap;
		$jenis = $request->jenis;


		if($status_sekolah=='NEGERI'){
			$request->status='1';
			if($kode_dana=='3.01'){
				$table='budget2021.penerimaan';
				$where = "npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='$tahap'";
			}else{
				$table='budget2021.pencairan';
				$where = "npsn='$npsn' AND jenis='$jenis' AND periode='$tahap'";
			}
		}else{
			$request->status='2';
			$table='budget2021.pencairan';
			$where = "npsn='$npsn' AND periode='$tahap'";
		}

		$conn = Set_koneksi::set_koneksi($request);

		$penerimaan = DB::connection($conn['conn_status'])->table($table)->whereRaw($where)->first();

		$code = '250';
		if(!empty($penerimaan)){
			$data_update = [
				'status_penerimaan'=>'12',
				'catatan'=>$catatan,
			];
			$update = DB::connection($conn['conn_status'])->table($table)->whereRaw($where)->update($data_update);
			if($update){
				$code = '200';
			}
		}

		return response()->json(compact('code'), 200);
	}
}

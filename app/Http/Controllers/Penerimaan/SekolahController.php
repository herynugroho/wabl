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


class SekolahController extends Controller
{
	function get_penerimaan(Request $request){
		$jenjang = $request->jenjang;
		$npsn = $request->npsn;
		$status = $request->status;
		$sumber = $request->sumber;

		$conn = Set_koneksi::set_koneksi($request);

		if($sumber=='bos'){
			$kode_dana = '3.01';
			// BOS
			if($status=='1'){
				// NEGERI
				$pagu = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->first();

				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.penerimaan')->selectRaw("
					id_penerimaan,npsn,sekolah,nominal,tanggal_penerimaan as tgl_penerimaan,kode_dana,tahap,penerimaan,'' as jenis,file_pembuktian,status_penerimaan,catatan
					")->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana'")->get();
				if($penerimaan->count()!=0){
					foreach ($penerimaan as $key) {
						$periode = '';
						if($key->tahap=='1'){
							// $sisa_saldo = (!empty($pagu)) ? $pagu->penerimaan : 0;
							// $key->nominal += $sisa_saldo;

							$periode = 'Tahap 1';
						}else if($key->tahap=='2'){
							$periode = 'Tahap 2';
						}else if($key->tahap=='3'){
							$periode = 'Tahap 3';
						}

						$key->periode = $periode;
						$key->nominal = number_format($key->nominal,0,',','.');
					}
				}
			}else{
				// SWASTA
			}
		}else{
			$kode_dana = '3.03';
			// BOPDA
			if($status=='1'){
				// NEGERI
				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->selectRaw("
					npsn, nama_sekolah, periode, tanggal_sp2d as tgl_penerimaan,jenis,file_pembuktian,status_penerimaan,catatan,periode as tahap,
					CONCAT('BOPDA ',jenis,' ',periode,' Tahun 2021') as penerimaan,nilai_honor,nilai_barjas
					")->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null)")->get();
				if($penerimaan->count()!=0){
					foreach ($penerimaan as $key) {
						$nominal = 0;
						if($key->jenis=='Pegawai'){
							$nominal = $key->nilai_honor;
						}else if($key->jenis=='Barjas'){
							$nominal = $key->nilai_barjas;
						}

						$key->nominal = number_format($nominal,0,',','.');
					}
				}
			}else{
				// SWASTA
				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->selectRaw("
					npsn, nama_sekolah, periode, tanggal_sp2d as tgl_penerimaan,jenis,file_pembuktian,status_penerimaan,catatan,periode as tahap,
					CONCAT('BOPDA ',periode,' Tahun 2021') as penerimaan,nilai_honor,nilai_barjas
					")->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null)")->get();
				if($penerimaan->count()!=0){
					foreach ($penerimaan as $key) {
						$key->nominal = number_format($key->nilai_barjas,0,',','.');
					}
				}
			}
		}

		return response()->json(compact('penerimaan'), 200);
	}

	function kirim_koreksi(Request $request){
		$jenjang = $request->jenjang;
		$npsn = $request->npsn;
		$status = $request->status;
		$sumber = $request->sumber;
		$periode = $request->periode;
		$jenis = $request->jenis;
		$koreksi_nominal = $request->koreksi_nominal;
		$koreksi_tanggal = $request->koreksi_tanggal;

		//get filename with extension
		$filenamewithextension = $request->file('file1')->getClientOriginalName();
		$filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
		$extension = $request->file('file1')->getClientOriginalExtension();
		$nama_file_pembuktian = 'Pembuktian_'.$npsn.'_'.date('YmdHis').'.'.$extension;
		Storage::disk('ftp')->put('bukti_penerimaan/'.$nama_file_pembuktian, fopen($request->file('file1'), 'r+'));

		$conn = Set_koneksi::set_koneksi($request);

		$code = '250';

		if($sumber=='bos'){
			$kode_dana = '3.01';
			// BOS
			if($status=='1'){
				// NEGERI
				$pagu = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->first();

				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.penerimaan')->selectRaw("
					id_penerimaan,npsn,sekolah,nominal,tanggal_penerimaan as tgl_penerimaan,kode_dana,tahap,penerimaan,'' as jenis,file_pembuktian,status_penerimaan,catatan
					")->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='$periode'")->first();

				if(!empty($penerimaan)){
					if($penerimaan->file_pembuktian!='' || $penerimaan->file_pembuktian!=null){
						if(Storage::disk('ftp')->exists('/bukti_penerimaan/'.$penerimaan->file_pembuktian)){
							Storage::disk('ftp')->delete('/bukti_penerimaan/'.$penerimaan->file_pembuktian);
						}
					}

					$data_update = [
						'catatan'=>null,
						'file_pembuktian'=>$nama_file_pembuktian,
						'status_penerimaan'=>'0',
						'perbaikan_nominal'=>$koreksi_nominal,
						'perbaikan_tanggal'=>$koreksi_tanggal,
					];

					$update = DB::connection($conn['conn_status'])->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='$periode'")->update($data_update);
					if($update){
						$code = '200';
					}
				}
			}else{
				// SWASTA
			}
		}else{
			$kode_dana = '3.03';
			// BOPDA
			if($status=='1'){
				// NEGERI
				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null) AND jenis='$jenis' AND periode='$periode'")->first();

				if(!empty($penerimaan)){
					if($penerimaan->file_pembuktian!='' || $penerimaan->file_pembuktian!=null){
						if(Storage::disk('ftp')->exists('/bukti_penerimaan/'.$penerimaan->file_pembuktian)){
							Storage::disk('ftp')->delete('/bukti_penerimaan/'.$penerimaan->file_pembuktian);
						}
					}

					$data_update = [
						'catatan'=>null,
						'file_pembuktian'=>$nama_file_pembuktian,
						'status_penerimaan'=>'0',
						'perbaikan_nominal'=>$koreksi_nominal,
						'perbaikan_tanggal'=>$koreksi_tanggal,
					];

					$update = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null) AND jenis='$jenis' AND periode='$periode'")->update($data_update);
					if($update){
						$code = '200';
					}
				}
			}else{
				// SWASTA
				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null) AND jenis='$jenis' AND periode='$periode'")->first();
				if(!empty($penerimaan)){
					if($penerimaan->file_pembuktian!='' || $penerimaan->file_pembuktian!=null){
						if(Storage::disk('ftp')->exists('/bukti_penerimaan/'.$penerimaan->file_pembuktian)){
							Storage::disk('ftp')->delete('/bukti_penerimaan/'.$penerimaan->file_pembuktian);
						}
					}

					$data_update = [
						'catatan'=>null,
						'file_pembuktian'=>$nama_file_pembuktian,
						'status_penerimaan'=>'0',
						'perbaikan_nominal'=>$koreksi_nominal,
						'perbaikan_tanggal'=>$koreksi_tanggal,
					];

					$update = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null) AND jenis='$jenis' AND periode='$periode'")->update($data_update);
					if($update){
						$code = '200';
					}
				}
			}
		}

		return response()->json(compact('code'), 200);
	}

	function kirim_sesuai(Request $request){
		$jenjang = $request->jenjang;
		$npsn = $request->npsn;
		$status = $request->status;
		$sumber = $request->sumber;
		$periode = $request->periode;
		$jenis = $request->jenis;

		$conn = Set_koneksi::set_koneksi($request);

		$code = '250';

		if($sumber=='bos'){
			$kode_dana = '3.01';
			// BOS
			if($status=='1'){
				// NEGERI
				$pagu = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->first();

				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.penerimaan')->selectRaw("
					id_penerimaan,npsn,sekolah,nominal,tanggal_penerimaan as tgl_penerimaan,kode_dana,tahap,penerimaan,'' as jenis,file_pembuktian,status_penerimaan,catatan
					")->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='$periode'")->first();

				if(!empty($penerimaan)){
					$data_update = [
						'status_penerimaan'=>'1',
						'catatan'=>null,
					];
					$update = DB::connection($conn['conn_status'])->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='$periode'")->update($data_update);
					if($update){
						$code = '200';
					}
				}
			}else{
				// SWASTA
			}
		}else{
			$kode_dana = '3.03';
			// BOPDA
			if($status=='1'){
				// NEGERI
				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null) AND jenis='$jenis' AND periode='$periode'")->first();

				if(!empty($penerimaan)){
					$data_update = [
						'status_penerimaan'=>'1',
						'catatan'=>null,
					];
					$update = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null) AND jenis='$jenis' AND periode='$periode'")->update($data_update);
					if($update){
						$code = '200';
					}
				}
			}else{
				// SWASTA
				$penerimaan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null) AND jenis='$jenis' AND periode='$periode'")->first();
				if(!empty($penerimaan)){
					$data_update = [
						'status_penerimaan'=>'1',
						'catatan'=>null,
					];
					$update = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->whereRaw("npsn='$npsn' AND (tanggal_sp2d is not null) AND jenis='$jenis' AND periode='$periode'")->update($data_update);
					if($update){
						$code = '200';
					}
				}
			}
		}

		return response()->json(compact('code'), 200);
	}
}

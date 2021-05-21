<?php

namespace App\Http\Controllers\Kegiatan;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use DB;
use App\Http\Controllers\Controller;


class PenyeliaController extends Controller
{
	function kirim_penyelia(Request $request){
		$npsn = $request->npsn;
		$sumber = $request->sumberDana;
		
		$conn = Set_koneksi::set_koneksi($request);

		$kode = ($sumber=='bos') ? '.3.01.' : '.3.03.';

		$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

		if(isset($request->kegiatan_awal)){
			$table = 'budget2021.kegiatan_awal';
		}else{
			$table = 'budget2021.kegiatan';
		}

		$kegiatan = DB::connection($conn['conn_status'])->table($table)->where('npsn',$npsn)->where('kode_kegiatan','like','%'.$kode.'%')->get();

		$dt_update = [
			'status_kegiatan'=>'0',
			'last_update_time'=>date('Y-m-d H:i:s'),
			'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
			'last_update_ip'=>GettingIP::get_client_ip(),
		];

		$dt_update1 = [
			'status'=>'0',
			'update_time'=>date('Y-m-d H:i:s'),
			'update_user'=>str_replace(["'"], ["\'"], $user->user_name),
			'update_ip'=>GettingIP::get_client_ip(),
		];

		$update = DB::connection($conn['conn_status'])->table($table)->where('npsn',$npsn)->where('kode_kegiatan','like','%'.$kode.'%')->whereRaw("(status_kegiatan != '1' OR status_kegiatan IS NULL)")->update($dt_update);
		

		if(isset($request->kegiatan_awal)){
		}else{
			$update1 = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->where('npsn',$npsn)->where('kode_kegiatan','like','%'.$kode.'%')->whereRaw("(status != '1' OR status IS NULL)")->update($dt_update1);
		}

		if($update){
			$status = '200';
			$message = 'Berhasil dikirim';
		}else{
			$status = '250';
			$message = 'Gagal dikirim';
		}

		return response()->json(compact('message','status'), 200);
	}
}

<?php
namespace App\Http\Libraries;

use Illuminate\Http\Request;
/**
 * 
 */
class Set_koneksi
{
	public static function set_koneksi(Request $request) {
		$conn_jenjang = '';
		$conn_status = '';
		$jenjang = $request->jenjang;
		$status = (isset($request->status)) ? $request->status : '';

		if($jenjang=='SD' || $jenjang=='MI'){
			$conn_jenjang = 'pgsql_sd';
		}else{
			$conn_jenjang = 'pgsql_smp';
		}

		if($status=='1'){
			$conn_status = 'pgsql';
		}else{
			$conn_status = 'pgsql_swasta';
		}

		$data = [
			'conn_jenjang'=>$conn_jenjang,
			'conn_status'=>$conn_status,
			'jenjang'=>$jenjang,
			'status'=>$status,
		];

		return $data;
	}
}
?>
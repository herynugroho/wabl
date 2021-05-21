<?php
namespace App\Http\Libraries;

use Illuminate\Http\Request;
/**
 * 
 */
use DB;
use App\Http\Libraries\Set_koneksi;
class List_berkas
{
	public static function get_list(Request $request,$where) {
		$conn = Set_koneksi::set_koneksi($request);
		$npsn = $request->npsn;

		$berkas = DB::connection($conn['conn_status'])->table('budget2021.master_berkas')->whereRaw("$where")->orderByRaw("LENGTH(jenis_berkas) ASC,jenis_berkas ASC")->get();

		foreach ($berkas as $key) { 
			$nomor_surat = DB::connection($conn['conn_status'])->table('budget2021.nomor_surat')->whereRaw("npsn='$npsn' AND jenis_berkas='".$key->jenis_berkas."'")->first();

			if(!empty($nomor_surat)){
				$no_surat = $nomor_surat->nomor_surat;
			}else{
				$no_surat = null;
			}

			$key->no_surat = $no_surat;
			$key->nomor_surat = $no_surat;
		}

		return $berkas;
	}
}
?>
<?php
namespace App\Http\Libraries;
/**
 * 
 */
use DB;
class GettingIP
{
	public static function get_client_ip() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}

	public static function bulan_angka_to_text($angka){
		if ($angka==1) {
			$bulan = 'Januari';
		}else if($angka==2){
			$bulan = 'Februari';
		}else if($angka==3){
			$bulan = 'Maret';
		}else if($angka==4){
			$bulan = 'April';
		}else if($angka==5){
			$bulan = 'Mei';
		}else if($angka==6){
			$bulan = 'Juni';
		}else if($angka==7){
			$bulan = 'Juli';
		}else if($angka==8){
			$bulan = 'Agustus';
		}else if($angka==9){
			$bulan = 'September';
		}else if($angka==10){
			$bulan = 'Oktober';
		}else if($angka==11){
			$bulan = 'November';
		}else if($angka==12){
			$bulan = 'Desember';
		}else{
			$bulan = '';
		}

		return $bulan;
	}

	public static function dis_btn_kegiatan($arr_data){
		if(count($arr_data)!=0){
			foreach ($arr_data as $key => $value) {
				if(is_null($value)){
					$arr_data[$key] = 'null';
				}
			}
			$datanya = array_count_values($arr_data);

			$status_kegiatan = 'asem';

			if(isset($datanya['1'])){$status_kegiatan = '1';}
			if(isset($datanya['0'])){$status_kegiatan = '0';}
			if(isset($datanya['null'])){$status_kegiatan = null;}
			if(isset($datanya['12'])){$status_kegiatan = '12';}
		}else{
			$status_kegiatan = 'kosong';			
		}

		return $status_kegiatan;
	}

	public static function show_btn_alokasi($arr_data){
		foreach ($arr_data as $key => $value) {
			if(is_null($value)){
				$arr_data[$key] = 'null';
			}
		}
		$datanya = array_count_values($arr_data);

		if(isset($datanya['null'])){
			return false;
		}else if(isset($datanya['1'])){
			if($datanya['1']==count($arr_data)){
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	}

	public static function str_komponen_id($where){
		$arr_komponen = [];
		$komponen_ada = DB::connection('pgsql')->table('budget2021.ssh2021')->select('komponen_id')->whereRaw("$where")->get();
		if($komponen_ada->count()!=0){
			foreach ($komponen_ada as $key) {
				array_push($arr_komponen, "'".$key->komponen_id."'");
			}
		}

		$str_komponen = "'0'";
		if(count($arr_komponen)!=0){
			$str_komponen = join(',',$arr_komponen);
		}

		return $str_komponen;
	}

	public static function cek_spp($request){
		$npsn = $request['npsn'];
		$cek_spp = DB::connection('pgsql_skpbm')->select("SELECT jt.npsn, jt.jenjang, jt.nama_sekolah, max(jk.nominal) AS spp_max
			FROM skpbm_jadwal_kriteria_spp AS jk
			JOIN skpbm_jadwal_transaksi AS jt ON jk.skpbm_jadwal_transaksi_id = jt.id_skpbm_jadwal_transaksi
			WHERE ((jt.status = 'Setujui Dinas' AND jt.setting_skpbm_jadwal_id =3 AND jt.jenjang IN ('SD', 'SMP')) OR 
			(jt.status = 'Verifikasi Pengawas' AND jt.setting_skpbm_jadwal_id =3 AND jt.jenjang IN ('MI', 'MTS'))) AND npsn='$npsn'
			GROUP BY jt.npsn, jt.nama_sekolah, jt.jenjang");

		if(count($cek_spp)!=0){
			$spp_sekolah = $cek_spp[0]->spp_max;
			if($spp_sekolah<=500000){
				$message = '200';
				$nama_sekolah = $cek_spp[0]->nama_sekolah;
			}else{
				$message = '300';
				$nama_sekolah = $cek_spp[0]->nama_sekolah;
			}
		}else{
			$message = '250';
			$nama_sekolah = '';
		}

		return ['message'=>$message,'nama_sekolah'=>$nama_sekolah];
	}

	public static function penyebut($nilai) {
		$nilai = abs($nilai);
		$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($nilai < 12) {
			$temp = " ". $huruf[$nilai];
		} else if ($nilai <20) {
			$temp = GettingIp::penyebut($nilai - 10). " belas";
		} else if ($nilai < 100) {
			$temp = GettingIp::penyebut($nilai/10)." puluh". GettingIp::penyebut($nilai % 10);
		} else if ($nilai < 200) {
			$temp = " seratus" . GettingIp::penyebut($nilai - 100);
		} else if ($nilai < 1000) {
			$temp = GettingIp::penyebut($nilai/100) . " ratus" . GettingIp::penyebut($nilai % 100);
		} else if ($nilai < 2000) {
			$temp = " seribu" . GettingIp::penyebut($nilai - 1000);
		} else if ($nilai < 1000000) {
			$temp = GettingIp::penyebut($nilai/1000) . " ribu" . GettingIp::penyebut($nilai % 1000);
		} else if ($nilai < 1000000000) {
			$temp = GettingIp::penyebut($nilai/1000000) . " juta" . GettingIp::penyebut($nilai % 1000000);
		} else if ($nilai < 1000000000000) {
			$temp = GettingIp::penyebut($nilai/1000000000) . " milyar" . GettingIp::penyebut(fmod($nilai,1000000000));
		} else if ($nilai < 1000000000000000) {
			$temp = GettingIp::penyebut($nilai/1000000000000) . " trilyun" . GettingIp::penyebut(fmod($nilai,1000000000000));
		}     
		return $temp;
	}

	public static function terbilang($nilai) {
		if($nilai<0) {
			$hasil = "minus ". trim(GettingIp::penyebut($nilai));
		} else {
			if($nilai==0){
				$hasil = "nol";
			}else{
				$hasil = trim(GettingIp::penyebut($nilai));
			}
		}     		
		return $hasil.' rupiah';
	}
}
?>
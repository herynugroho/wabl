<?php

namespace App\Http\Controllers\Kegiatan;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use DB;
use App\Http\Controllers\Controller;


class KegiatanController extends Controller
{
	public function list_kegiatan(Request $request){
		// return $request->all();
		// if($request->isMethod('Post')){
		$sumberdana = $request->sumberDana;
		if($sumberdana=='bopda'){
			$kode = '3.03';
		}else{
			$kode = '3.01';
		}

		$npsn = $request->npsn;
		$id_sudah = [];

		$conn = Set_koneksi::set_koneksi($request);

		if(isset($request->kegiatan_awal)){
			$table = 'budget2021.kegiatan_awal';
		}else{
			$table = 'budget2021.kegiatan';

		}

		$data_kegiatan = DB::connection($conn['conn_status'])->table($table)->selectRaw("kode_kegiatan")->where('npsn',$npsn)->get();
		if($data_kegiatan->count()!=0){
			foreach ($data_kegiatan as $key) {
				array_push($id_sudah,"'".$key->kode_kegiatan."'");
			}
		}

		if(count($id_sudah)!=0){
			$str_id = join(",",$id_sudah);

			$list_kegiatan = DB::connection($conn['conn_status'])->select(DB::raw("SELECT * FROM (SELECT *,CONCAT(kode_urusan,'.',kode_bidang,'.',kode_dana,'.',kode_kegiatan) as kode_concat
				FROM budget2021.template_kegiatan AS s
				WHERE kode_dana ='$kode') as con WHERE kode_concat NOT IN ($str_id)"));
		}else{
			$list_kegiatan = DB::connection($conn['conn_status'])->select(DB::raw("SELECT *,CONCAT(kode_urusan,'.',kode_bidang,'.',kode_dana,'.',kode_kegiatan) as kode_concat
				FROM budget2021.template_kegiatan AS s
				WHERE kode_dana ='$kode'"));
		}

		if($list_kegiatan){
			$message = 'success';
		}else{
			$message = 'failed';
		}
		// }

		return response()->json(compact('message', 'list_kegiatan'), 200);

	}

	function kirim_kegiatan(Request $request){
		$npsn = $request->npsn;
		$sumberDana = $request->sumberDana;
		$data = $request->data;

		$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

		$conn = Set_koneksi::set_koneksi($request);

		if(count($data)!=0){
			for ($i=0; $i < count($data); $i++) { 
				$data_insert = [
					'kode_kegiatan'=>$data[$i]['kode_concat'],
					'nama_kegiatan'=>$data[$i]['nama_kegiatan'],
					'npsn'=>$user->user_id,
					'ip_address'=>GettingIP::get_client_ip(),
					'insert_ip'=>GettingIP::get_client_ip(),
					'last_update_ip'=>GettingIP::get_client_ip(),
					'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
					'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
					'waktu_access'=>date('Y-m-d H:i:s'),
					'insert_time'=>date('Y-m-d H:i:s'),
					'last_update_time'=>date('Y-m-d H:i:s'),
					'status_kegiatan'=>null,
				];

				if(isset($request->kegiatan_awal)){
					$table = 'budget2021.kegiatan_awal';
					$data_insert = array_merge($data_insert,['nominal'=>0]);
				}else{
					$table = 'budget2021.kegiatan';
				}

				$input = DB::connection($conn['conn_status'])->table($table)->where('npsn',$user->user_id)->where('kode_kegiatan',$data[$i]['kode_concat'])->first();
				if(!empty($input)){
					$data_update = [
						'kode_kegiatan'=>$data[$i]['kode_concat'],
						'npsn'=>$user->user_id,
						'last_update_ip'=>GettingIP::get_client_ip(),
						'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'last_update_time'=>date('Y-m-d H:i:s'),
					];
					$simpan = DB::connection($conn['conn_status'])->table($table)->where('npsn',$user->user_id)->where('kode_kegiatan',$data[$i]['kode_concat'])->update($data_update);
				}else{
					$simpan = DB::connection($conn['conn_status'])->table($table)->insert($data_insert);
				}

			}
		}else{

		}
		$message='success';

		return response()->json(compact('message'), 200);
	}

	function get_kegiatan(Request $request){
		$npsn = $request->npsn;
		$sumberDana = $request->sumberDana;

		if($sumberDana=='bopda' || $sumberDana=='bopda_awal'){
			$kode = '.3.03.';
			$kode_swasta = '3.03';
		}else{
			$kode = '.3.01.';
			$kode_swasta = '3.01';
		}

		$conn = Set_koneksi::Set_koneksi($request);

		$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

		$cek_detail_ada = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='$npsn' AND kode_kegiatan LIKE '%$kode%'")->count();
		if($cek_detail_ada==0){
			$btn_tambah_komponen = null;
		}else{
			$cek_status_detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='$npsn' AND kode_kegiatan LIKE '%$kode%' AND status IS NULL")->count();
			if($cek_status_detail_kegiatan!=0){
				$btn_tambah_komponen = null;
			}else{
				$cek_status_detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='$npsn' AND kode_kegiatan LIKE '%$kode%' AND status ='12'")->count();
				if($cek_status_detail_kegiatan!=0){
					$btn_tambah_komponen = '12';
				}else{
					$btn_tambah_komponen = '1';
				}
			}
		}

		// HONORER GTT PTT
		$cek_honor = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->selectRaw("*")->where('budget2021.kegiatan.kode_kegiatan','5.05.3.03.016')->where('npsn',$npsn)->count();

		$str_komponen = GettingIP::str_komponen_id("rekening IN ('5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007','5.1.02.02.01.0013')");

		if($request->status=='1'){
			$cek_data_honor = DB::connection($conn['conn_status'])->select("
				SELECT * 
				FROM budget2021.sdm as sdm  
				WHERE npsn='$npsn' AND sdm.kode_kegiatan = '5.05.3.03.016' AND sdm.komponen_id IN($str_komponen) AND (status_perangkaan='1' AND lock='1')
				");
		}else{
			$cek_data_honor = DB::connection($conn['conn_status'])->select("
				SELECT * 
				FROM budget2021.sdm as sdm  
				WHERE npsn='$npsn' AND sdm.kode_dana = '$kode_swasta' AND sdm.komponen_id IN($str_komponen) AND (status_perangkaan='1' AND lock='1')
				");
		}

		// SIMPAN SDM DETAIL
		if(count($cek_data_honor)!=0){
			for ($i=0; $i < count($cek_data_honor); $i++) { 
				$baris = $cek_data_honor[$i];

				$cek_detail_sdm = DB::connection($conn['conn_status'])->table('budget2021.sdm_detail')->where('sdm_id',$baris->sdm_id)->where('komponen_id',$baris->komponen_id)->where('npsn',$baris->npsn)->first();

				if(!empty($cek_detail_sdm)){
				}else{
					$dt_insert = [
						'sdm_id'=>$baris->sdm_id,
						'npsn'=>$baris->npsn,
						'komponen_id'=>$baris->komponen_id,
						'bulan_1'=>null,
						'bulan_2'=>null,
						'bulan_3'=>null,
						'bulan_4'=>null,
						'bulan_5'=>null,
						'bulan_6'=>null,
						'bulan_7'=>null,
						'bulan_8'=>null,
						'bulan_9'=>null,
						'bulan_10'=>null,
						'bulan_11'=>null,
						'bulan_12'=>null,
						'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'insert_ip'=>GettingIP::get_client_ip(),
						'insert_time'=>date('Y-m-d H:i:s'),
						'update_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'update_ip'=>GettingIP::get_client_ip(),
						'update_time'=>date('Y-m-d H:i:s'),
					];

					DB::connection($conn['conn_status'])->table('budget2021.sdm_detail')->insert($dt_insert);
				}
			}
		}
		// END SIMPAN SDM DETAIL
		if($request->status=='1'){
			// TAMBAH KEGIATAN NEGERI
			if($cek_honor==0){
				if(count($cek_data_honor)!=0){
					$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

					$data_insert = [
						'kode_kegiatan'=>'5.05.3.03.016',
						'nama_kegiatan'=>'Honorarium GTT dan PTT',
						'npsn'=>$user->user_id,
						'ip_address'=>GettingIP::get_client_ip(),
						'insert_ip'=>GettingIP::get_client_ip(),
						'last_update_ip'=>GettingIP::get_client_ip(),
						'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'waktu_access'=>date('Y-m-d H:i:s'),
						'insert_time'=>date('Y-m-d H:i:s'),
						'last_update_time'=>date('Y-m-d H:i:s'),
						'status_kegiatan'=>'1',
					];
					
					DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->insert($data_insert);
				}
			}else{
				if(count($cek_data_honor)!=0){
					$status = '1';
				}else{
					$status = null;
				}
				$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

				$data_insert = [
					'kode_kegiatan'=>'5.05.3.03.016',
					'nama_kegiatan'=>'Honorarium GTT dan PTT',
					'npsn'=>$user->user_id,
					'last_update_ip'=>GettingIP::get_client_ip(),
					'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
					'waktu_access'=>date('Y-m-d H:i:s'),
					'last_update_time'=>date('Y-m-d H:i:s'),
					'status_kegiatan'=>$status,
				];

				DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("kode_kegiatan='5.05.3.03.016' AND npsn='$user->user_id'")->update($data_insert);
			}
		}
		// END HONORER GTT PTT

		// CEK PELATIH
		$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
		$p_komponen_name = 'Jasa Pelatih Ekskul';
		$p_kode_kegiatan = '5.05.3.01.048';

		$cek_pelatih = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->selectRaw("*")->where('budget2021.kegiatan.kode_kegiatan','5.05.3.01.048')->where('npsn',$npsn)->count();
		// $cek_data_pelatih_disetujui = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("
		// 	kode_kegiatan='$p_kode_kegiatan' AND komponen_id='$p_komponen_id' AND komponen_name='$p_komponen_name' AND npsn='$npsn' AND status_perangkaan IS NULL
		// 	")->count();
		if($request->status=='1'){
			$cek_data_pelatih_pengajuan = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("
				kode_kegiatan='$p_kode_kegiatan' AND komponen_id='$p_komponen_id' AND komponen_name='$p_komponen_name' AND npsn='$npsn' AND (status_perangkaan='1' AND lock='1')
				")->get();
		}else{
			$cek_data_pelatih_pengajuan = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("
				kode_dana='$kode_swasta' AND npsn='$npsn' AND (status_perangkaan='1' AND lock='1')
				")->get();
		}

		$arr_cek_lock_pelatih = [];

		// SIMPAN SDM PELATIH DETAIL
		if($cek_data_pelatih_pengajuan->count()!=0){
			foreach ($cek_data_pelatih_pengajuan as $baris) { 
				$cek_detail_sdm = DB::connection($conn['conn_status'])->table('budget2021.sdm_detail')->where('sdm_id',$baris->sdm_id)->where('komponen_id',$baris->komponen_id)->where('npsn',$baris->npsn)->first();

				array_push($arr_cek_lock_pelatih,$baris->lock);

				if(!empty($cek_detail_sdm)){
				}else{
					$dt_insert = [
						'sdm_id'=>$baris->sdm_id,
						'npsn'=>$baris->npsn,
						'komponen_id'=>$baris->komponen_id,
						'bulan_1'=>null,
						'bulan_2'=>null,
						'bulan_3'=>null,
						'bulan_4'=>null,
						'bulan_5'=>null,
						'bulan_6'=>null,
						'bulan_7'=>null,
						'bulan_8'=>null,
						'bulan_9'=>null,
						'bulan_10'=>null,
						'bulan_11'=>null,
						'bulan_12'=>null,
						'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'insert_ip'=>GettingIP::get_client_ip(),
						'insert_time'=>date('Y-m-d H:i:s'),
						'update_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'update_ip'=>GettingIP::get_client_ip(),
						'update_time'=>date('Y-m-d H:i:s'),
					];

					DB::connection($conn['conn_status'])->table('budget2021.sdm_detail')->insert($dt_insert);
				}
			}
		}	
		// END SIMPAN SDM PELATIH DETAIL

		if($request->status=='1'){
			if($cek_pelatih==0){
				if($cek_data_pelatih_pengajuan->count()!=0){
					if(in_array(1,$arr_cek_lock_pelatih)){
						$data_insert = [
							'kode_kegiatan'=>'5.05.3.01.048',
							'nama_kegiatan'=>'Pembayaran Honor',
							'npsn'=>$user->user_id,
							'ip_address'=>GettingIP::get_client_ip(),
							'insert_ip'=>GettingIP::get_client_ip(),
							'last_update_ip'=>GettingIP::get_client_ip(),
							'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
							'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
							'waktu_access'=>date('Y-m-d H:i:s'),
							'insert_time'=>date('Y-m-d H:i:s'),
							'last_update_time'=>date('Y-m-d H:i:s'),
							'status_kegiatan'=>'1',
						];
						DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->insert($data_insert);
					}			
				}
			}else{
				if($cek_data_pelatih_pengajuan->count()!=0){
					if(in_array(1,$arr_cek_lock_pelatih)){
						$data_update = [
							'last_update_ip'=>GettingIP::get_client_ip(),
							'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
							'waktu_access'=>date('Y-m-d H:i:s'),
							'last_update_time'=>date('Y-m-d H:i:s'),
							'status_kegiatan'=>'1',
						];
						DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("kode_kegiatan='5.05.3.01.048' AND npsn='$user->user_id'")->update($data_update);
					}else{
						$data_update = [
							'last_update_ip'=>GettingIP::get_client_ip(),
							'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
							'waktu_access'=>date('Y-m-d H:i:s'),
							'last_update_time'=>date('Y-m-d H:i:s'),
							'status_kegiatan'=>'0',
						];
						DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("kode_kegiatan='5.05.3.01.048' AND npsn='$user->user_id'")->update($data_update);
					}
				}
			}
		}
		// END CEK PELATIH

		// SET PELATIH GTT SWASTA
		if($request->status!='1'){
			if($sumberDana=='bos'){
				$cek_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("npsn='$npsn' AND kode_kegiatan='5.05.3.01.048'")->first();
				$kode_kegiatan = '5.05.3.01.048';
				$nama_kegiatan = 'Pembayaran Honor';
				$kode_swasta = '3.01';
			}else{
				$cek_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("npsn='$npsn' AND kode_kegiatan='5.05.3.03.122'")->first();
				$kode_kegiatan = '5.05.3.03.122';
				$nama_kegiatan = 'Biaya Upah/Honorarium Tenaga Pendidik Dan Tenaga Kependidikan Non Pegawai Negeri Sipil';
				$kode_swasta = '3.03';
			}

			$status_pelatih = '';
			$status_pegawai = '';

			// CEK PELATIH GTT PTT
			$get_gtt_pelatih = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("npsn='$npsn' AND kode_dana='$kode_swasta' AND (status_perangkaan='1' AND lock='1')")->get();

			if($get_gtt_pelatih->count()!=0){
				$status_swasta = '1';
			}else{
				$exist_gtt_ptt = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("npsn='$npsn' AND kode_dana='$kode_swasta'")->get();
				if($exist_gtt_ptt->count()!=0){
					$arr_sp = [];
					foreach ($exist_gtt_ptt as $key) {
						array_push($arr_sp,$key->status_perangkaan);
					}
					if(in_array(null,$arr_sp)){
						$status_swasta = null;
					}else if(in_array('0',$arr_sp)){
						$status_swasta = '0';
					}else if(in_array('12',$arr_sp)){
						$status_swasta = '12';
					}else{
						$status_swasta = null;
					}
				}else{
					$tidak_ada = 'true';
					$status_swasta = null;
				}
			}

			if(isset($tidak_ada)){

			}else{
				if(!empty($cek_kegiatan)){
					$data_update = [
						'last_update_ip'=>GettingIP::get_client_ip(),
						'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'waktu_access'=>date('Y-m-d H:i:s'),
						'last_update_time'=>date('Y-m-d H:i:s'),
						'status_kegiatan'=>$status_swasta,
					];
					DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("kode_kegiatan='$kode_kegiatan' AND npsn='$user->user_id'")->update($data_update);
				}else{
					$data_insert = [
						'kode_kegiatan'=>$kode_kegiatan,
						'nama_kegiatan'=>$nama_kegiatan,
						'npsn'=>$user->user_id,
						'ip_address'=>GettingIP::get_client_ip(),
						'insert_ip'=>GettingIP::get_client_ip(),
						'last_update_ip'=>GettingIP::get_client_ip(),
						'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'last_update_user'=>str_replace(["'"], ["\'"], $user->user_name),
						'waktu_access'=>date('Y-m-d H:i:s'),
						'insert_time'=>date('Y-m-d H:i:s'),
						'last_update_time'=>date('Y-m-d H:i:s'),
						'status_kegiatan'=>$status_swasta,
					];
					DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->insert($data_insert);
				}
			}
		}
		// END SET PELATIH GTT SWASTA

		$message = 'success';

		$penyelia = '';
		if(isset($request->penyelia)){
			$penyelia = $request->penyelia;
		}


		if($penyelia==''){
			if(isset($request->kegiatan_awal)){
				$data_kegiatan = DB::connection('pgsql_swasta')->table('budget2021.kegiatan_awal as ka')->selectRaw("*,ka.nominal as nilai,'' as status,'' as posisi")->where('ka.kode_kegiatan','like','%'.$kode.'%')->where('ka.npsn',$npsn)->get();
			}else{
				$data_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->selectRaw("*,'0' as nilai,'' as status,'' as posisi")->where('budget2021.kegiatan.kode_kegiatan','like','%'.$kode.'%')->where('npsn',$npsn)->get();
			}
		}else{
			if(isset($request->kegiatan_awal)){
				$data_kegiatan = DB::connection('pgsql_swasta')->table('budget2021.kegiatan_awal as ka')->selectRaw("*,ka.nominal as nilai,'' as status,'' as posisi")->where('ka.kode_kegiatan','like','%'.$kode.'%')->where('ka.npsn',$npsn)->where('ka.status_kegiatan','!=',null)->get();
				$message = 'bopda_awal';
			}else{
				$data_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->selectRaw("*,'0' as nilai,'' as status,'' as posisi")->where('budget2021.kegiatan.kode_kegiatan','like','%'.$kode.'%')->where('npsn',$npsn)->where('status_kegiatan','!=',null)->get();
			}
		}

		$status_kegiatan = [];
		if(isset($request->kegiatan_awal)){
			if($data_kegiatan->count()!=0){
				foreach ($data_kegiatan as $key) {
					array_push($status_kegiatan,$key->status_kegiatan);
				}
			}
		}else{
			if($data_kegiatan->count()!=0){
				foreach ($data_kegiatan as $key) {
					if($key->kode_kegiatan=='5.05.3.03.016'){
						// GTT PTT (Belanja Pegawai)
						$str_komponen = GettingIP::str_komponen_id("rekening IN ('5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007','5.1.02.02.01.0013')");

						$nilai = DB::connection($conn['conn_status'])->table('budget2021.sdm')->where('npsn',$key->npsn)->where('budget2021.sdm.kode_kegiatan',$key->kode_kegiatan)->whereRaw("komponen_id IN ($str_komponen) AND (status_perangkaan='1' AND lock='1')")->sum('budget2021.sdm.nominal');

						$key->nilai = $nilai;
					}else if($key->kode_kegiatan=='5.05.3.01.048'){
						// PELATIH (BarJas)
						if($request->status=='1'){
							$nilai = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("
								kode_kegiatan='$p_kode_kegiatan' AND komponen_id='$p_komponen_id' AND komponen_name='$p_komponen_name' AND npsn='$npsn' AND (status_perangkaan='1' AND lock='1')
								")->sum('budget2021.sdm.nominal');
						}else{
							$nilai = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("
								npsn='$npsn' AND kode_dana='$kode_swasta' AND (status_perangkaan='1' AND lock='1')
								")->sum('budget2021.sdm.nominal');
						}

						$key->nilai = $nilai;
					}else if($key->kode_kegiatan=='5.05.3.03.122'){
						// PELATIH (BarJas)
						if($request->status=='1'){
							$nilai = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("
								kode_kegiatan='$p_kode_kegiatan' AND komponen_id='$p_komponen_id' AND komponen_name='$p_komponen_name' AND npsn='$npsn' AND (status_perangkaan='1' AND lock='1')
								")->sum('budget2021.sdm.nominal');
						}else{
							$nilai = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("
								npsn='$npsn' AND kode_dana='$kode_swasta' AND (status_perangkaan='1' AND lock='1')
								")->sum('budget2021.sdm.nominal');
						}

						$key->nilai = $nilai;
					}else{
						$nilai = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->where('npsn',$key->npsn)->where('budget2021.kegiatan_detail.kode_kegiatan',$key->kode_kegiatan)->sum('budget2021.kegiatan_detail.nilai');

						$get_all_kegiatan = DB::connection($conn['conn_status'])->select("
							SELECT * FROM budget2021.kegiatan_detail as kd WHERE npsn='".$key->npsn."' AND kode_kegiatan='".$key->kode_kegiatan."'
							");
						if(count($get_all_kegiatan)!=0){
							$detail_kegiatan = DB::connection($conn['conn_status'])->select("
								SELECT status,COUNT(status) as jumlah FROM budget2021.kegiatan_detail as kd WHERE npsn='".$key->npsn."' AND kode_kegiatan='".$key->kode_kegiatan."' AND (status IN ('0','12') OR status IS NULL) GROUP BY status 
								");

							if(count($detail_kegiatan)!=0){
								$arr_st_kegiatan = [];
								for ($sk=0; $sk < count($detail_kegiatan); $sk++) { 
									array_push($arr_st_kegiatan,$detail_kegiatan[$sk]->status);
								}
								$st_kegiatan = GettingIP::dis_btn_kegiatan($arr_st_kegiatan);

								if($st_kegiatan!='kosong'){
									DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("npsn='".$key->npsn."' AND kode_kegiatan='".$key->kode_kegiatan."'")->update(['status_kegiatan'=>$st_kegiatan]);

									$key->status_kegiatan = $st_kegiatan;
								}

							}else{
								DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("npsn='".$key->npsn."' AND kode_kegiatan='".$key->kode_kegiatan."'")->update(['status_kegiatan'=>'1']);
								$key->status_kegiatan = '1';
							}
						}else{
							DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->whereRaw("npsn='".$key->npsn."' AND kode_kegiatan='".$key->kode_kegiatan."'")->update(['status_kegiatan'=>null]);
							$key->status_kegiatan = null;
						}

						$key->nilai = $nilai;
					}

					array_push($status_kegiatan,$key->status_kegiatan);
				}
			}
		}

		$btn_kirim_penyelia = GettingIP::dis_btn_kegiatan($status_kegiatan);
		$btn_tambah_kegiatan = GettingIP::dis_btn_kegiatan($status_kegiatan);
		$btn_alokasi = GettingIP::show_btn_alokasi($status_kegiatan);

		$get_kunci_alokasi = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='$npsn' AND kode_kegiatan LIKE '%$kode%' AND kunci_alokasi='1'")->get()->count();

		$kunci_alokasi = ($get_kunci_alokasi>0) ? false : true;

		return response()->json(compact('message', 'data_kegiatan','btn_tambah_kegiatan','btn_kirim_penyelia','btn_alokasi','btn_tambah_komponen','kunci_alokasi'), 200);
	}

	function detail_kegiatan(Request $request){
		$kode_kegiatan = $request->kode_kegiatan;
		$npsn = $request->npsn;

		if($request->sumber=='bos'){
			$kode_dana = '3.01';
		}else{
			$kode_dana = '3.03';
		}

		$conn = Set_koneksi::set_koneksi($request);
		$status_kegiatan = '';

		if($kode_kegiatan=='5.05.3.03.016'){
			$detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.sdm')
			->selectRaw("komponen_name,'' as no_rekening,'' as komponen_id,'' as komponen_harga_bulat,'' as pajak,SUM(nominal) as nilai,kode_kegiatan,npsn,'' as koefisien,'1' as status,'' as catatan")
			->where('npsn',$npsn)->where('budget2021.sdm.kode_kegiatan',$kode_kegiatan)->whereRaw("(sdm.status_perangkaan='1' AND sdm.lock='1')")->groupBy('komponen_name','npsn','kode_kegiatan')->get();
			if($detail_kegiatan->count()!=0){
				$cek_status_kirim = 0;
				foreach ($detail_kegiatan as $value) { 
					
					$children = DB::connection($conn['conn_status'])->table('budget2021.sdm')
					->selectRaw("nama_pegawai,komponen_name,'' as no_rekening,komponen_id,komponen_harga as komponen_harga_bulat,'' as pajak,nominal as nilai,kode_kegiatan,npsn,'' as koefisien,status_perangkaan as status,catatan,jam,hari,bulan")
					->where('npsn',$npsn)->where('budget2021.sdm.kode_kegiatan',$kode_kegiatan)->whereRaw("komponen_name='$value->komponen_name' AND (sdm.status_perangkaan='1' AND sdm.lock='1')")->get();
					
					$s_status_kegiatan = [];
					if($children->count()!=0){
						foreach ($children as $key) {

							$sshnya = DB::connection('pgsql')->table('budget2021.ssh2021 as ssh')->whereRaw("ssh.komponen_id='$key->komponen_id'")->first();

							$key->satuan = (!empty($sshnya)) ? $sshnya->satuan : '';
							$key->komponen_name = $key->nama_pegawai;
							$key->komponen_harga_bulat = (!empty($sshnya)) ? $sshnya->komponen_harga_bulat : '';

							$koefisien = ($key->jam!='') ? $key->jam.' Jam' : '';

							if($koefisien==''){
								$koefisien .= ($key->hari!='') ? $key->hari.' Hari' : '';
							}else{
								$koefisien .= ($key->hari!='') ? ' X '.$key->hari.' Hari' : '';
							}

							if($koefisien==''){
								$koefisien .= ($key->bulan!='') ? $key->bulan.' Bulan' : '';
							}else{
								$koefisien .= ($key->bulan!='') ? ' X '.$key->bulan.' Bulan' : '';
							}
							$key->koefisien = $koefisien;

							// array_push($status_all,(is_null($key->status)) ? 'kosong' : $key->status);
							// array_push($s_status_kegiatan,(is_null($key->status)) ? 'kosong' : $key->status);
						}
					}
					// $c_status_header = ($status_header=='kosong') ? null : $status_header;
					$value->children = $children;
				}
			}
		}else if($kode_kegiatan=='5.05.3.01.048'){
			$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
			$p_komponen_name = 'Jasa Pelatih Ekskul';

			if($request->status=='1'){
				$detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.sdm as sdm')->whereRaw("
					sdm.kode_kegiatan='$kode_kegiatan' AND sdm.komponen_id='$p_komponen_id' AND sdm.komponen_name='$p_komponen_name' AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
					")->get();
			}else{
				$detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.sdm as sdm')->whereRaw("
					sdm.kode_dana='3.01' AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
					")->get();
			}
		}else if($kode_kegiatan=='5.05.3.03.122'){
			$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
			$p_komponen_name = 'Jasa Pelatih Ekskul';

			if($request->status=='1'){
				$where = "sdm.kode_kegiatan='$p_kode_kegiatan' AND sdm.komponen_id='$p_komponen_id' AND sdm.komponen_name='$p_komponen_name'";
			}else{
				$where = "sdm.kode_dana='$kode_dana'";
			}

			if($request->status=='1'){
				$detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.sdm as sdm')->whereRaw("
					$where
					AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
					")->get();
			}else{
				$detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.sdm as sdm')->whereRaw("
					$where
					AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
					")->get();
			}
		}else{

			$detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')
			->selectRaw("subtitle,subtitle as komponen_name,'' as no_rekening,'' as komponen_id,'' as komponen_harga_bulat,'' as pajak,SUM(nilai) as nilai,kode_kegiatan,npsn,'' as koefisien,'' as status,'' as catatan,kunci_alokasi")
			->where('kd.npsn',$npsn)->where('kd.kode_kegiatan',$kode_kegiatan)
			// ->whereRaw("komponen_id IS NULL")
			->groupBy('subtitle','kode_kegiatan','npsn','kunci_alokasi')->get();

			$status_all = [];

			if($detail_kegiatan->count()!=0){
				$cek_status_kirim = 0;
				foreach ($detail_kegiatan as $value) { 
					$kunci_alokasi = [];
					
					$children = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')
					->selectRaw("kd.subtitle,kd.komponen_id,kd.rekening as no_rekening,'' as komponen_name,'' as komponen_harga_bulat, 
						CASE WHEN (kd.pajak='Iya') THEN '10' ELSE '0' END as pajak,
						nilai,kode_kegiatan,npsn,'' as koefisien,qty,'' as satuan,koefisien,satuan_koefisien,koefisien2,satuan_koefisien2,koefisien3,satuan_koefisien3,kd.status,kd.catatan,kd.kunci_alokasi")
					->where('subtitle',$value->komponen_name)
					->whereRaw("komponen_id IS NOT NULL")
					->where('kd.npsn',$value->npsn)->where('kd.kode_kegiatan',$value->kode_kegiatan)->get();
					
					$s_status_kegiatan = [];

					array_push($kunci_alokasi,$value->kunci_alokasi);

					if($children->count()!=0){
						foreach ($children as $key) {

							$sshnya = DB::connection('pgsql')->table('budget2021.ssh2021 as ssh')->whereRaw("ssh.komponen_id='$key->komponen_id' AND rekening='$key->no_rekening'")->first();

							$key->satuan = (!empty($sshnya)) ? $sshnya->satuan : '';
							$key->komponen_name = (!empty($sshnya)) ? $sshnya->komponen_name : '';
							$key->komponen_harga_bulat = (!empty($sshnya)) ? $sshnya->komponen_harga_bulat : '';

							$koef = $key->qty.' '.$key->satuan;
							if($key->koefisien>0){
								$koef .= ' X '.$key->koefisien.' '.$key->satuan_koefisien;
							}
							if($key->koefisien2>0){
								$koef .= ' X '.$key->koefisien2.' '.$key->satuan_koefisien2;
							}
							if($key->koefisien3>0){
								$koef .= ' X '.$key->koefisien3.' '.$key->satuan_koefisien3;
							}

							$key->koefisien = $koef;

							// array_push($status_all,(is_null($key->status)) ? 'kosong' : $key->status);
							// array_push($s_status_kegiatan,(is_null($key->status)) ? 'kosong' : $key->status);

							array_push($status_all,$key->status);
							array_push($s_status_kegiatan,$key->status);
							array_push($kunci_alokasi,$key->kunci_alokasi);
						}
					}

					$status_header = GettingIP::dis_btn_kegiatan($s_status_kegiatan);

					// $c_status_header = ($status_header=='kosong') ? null : $status_header;
					// STATUS HEADER ROW

					if($status_header=='kosong'){
						$status_header = null;
					}

					$subtitle = str_replace(["'"],["''"],$value->komponen_name);
					if(in_array('1', $kunci_alokasi)){
						$status_all = ['ada'];
						$status_header = 1;
					}else{
						$update_header_sub = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='".$value->npsn."' AND subtitle='".$subtitle."' AND komponen_id IS NULL")->update(['status'=>$status_header]);
					}
					// END STATUS HEADER ROW
					
					$value->status = $status_header;
					$value->children = $children;
				}
			}

			$status_kegiatan = GettingIP::dis_btn_kegiatan($status_all);
			// $c_status_kegiatan = ($status_kegiatan=='kosong') ? null : $status_kegiatan;

			if(count($status_all)!=0){
				DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->where('npsn',$npsn)->where('kode_kegiatan',$kode_kegiatan)->update(['status_kegiatan'=>$status_kegiatan]);
			}

		}
		$message = 'success';

		return response()->json(compact('message', 'detail_kegiatan','kode_kegiatan','status_kegiatan'), 200);
	}

	function hapus(Request $request){
		$id_hapus = $request->id_hapus;
		$npsn = $request->npsn;

		$conn = Set_koneksi::set_koneksi($request);

		if(isset($request->kegiatan_awal)){
			$table = 'budget2021.kegiatan_awal';
		}else{
			$table = 'budget2021.kegiatan';
		}

		$kegiatan = DB::connection($conn['conn_status'])->table($table)->where('npsn',$npsn)->where('kode_kegiatan',$id_hapus)->first();
		if(!empty($kegiatan)){
			$hapus = DB::connection($conn['conn_status'])->table($table)->where('npsn',$npsn)->where('kode_kegiatan',$id_hapus)->delete();
			if($hapus){
				$message = 'Berhasil dihapus';
			}else{
				$message = 'Gagal dihapus';
			}
		}else{
			$message = 'Data tidak ditemukan';
		}

		return response()->json(compact('message'), 200);
	}

	function simpan_subtitle(Request $request){
		$kode_kegiatan = $request->kode_kegiatan;
		$data = [
			'subtitle'=>$request->subtitle,
			'npsn'=>$request->npsn,
			'kode_kegiatan'=>$kode_kegiatan,
		];

		$conn = Set_koneksi::set_koneksi($request);

		$kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->where('kode_kegiatan',$kode_kegiatan)->where('npsn',$request->npsn)->first();

		$nama_kegiatan = $kegiatan->nama_kegiatan;

		$simpan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->insert($data);

		if($simpan){
			$message = 'Berhasil disimpan';
		}else{
			$message = 'Gagal disimpan';
		}

		return response()->json(compact('message','kode_kegiatan','nama_kegiatan'), 200);
	}

	function simpan_komponen(Request $request){
		$sumberDana = $request->sumberDana;
		$kode_kegiatan = $request->id_kegiatan;
		$npsn = $request->npsn;

		$nilai = str_replace([','], ['.'], $request->nilai);
		$qty = str_replace([','], ['.'], $request->qty);
		$koefisien = str_replace([','], ['.'], $request->qty_lain[0]);
		$koefisien2 = str_replace([','], ['.'], $request->qty_lain[1]);
		$koefisien3 = str_replace([','], ['.'], $request->qty_lain[2]);

		$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

		$conn = Set_koneksi::set_koneksi($request);

		$data = [
			'kode_kegiatan'=>$request->id_kegiatan,
			'komponen_id'=>$request->komponen_id,
			'rekening'=>$request->rekening,
			'nilai'=>$nilai,
			'npsn'=>$npsn,
			'qty'=>$qty,
			'koefisien'=>($koefisien!='') ? $koefisien : 0,
			'koefisien2'=>($koefisien2!='') ? $koefisien2 : 0,
			'koefisien3'=>($koefisien3!='') ? $koefisien3 : 0,
			'satuan_koefisien'=>$request->satuan_lain[0]['value'],
			'satuan_koefisien2'=>$request->satuan_lain[1]['value'],
			'satuan_koefisien3'=>$request->satuan_lain[2]['value'],
			'pajak'=>(isset($request->pajak) && $request->pajak==true) ? 'Iya' : null,
			'subtitle'=>$request->subtitle,
		];

		$where = [
			'kode_kegiatan'=>$request->id_kegiatan,
			'komponen_id'=>$request->komponen_id,
			'npsn'=>$request->npsn,
			'subtitle'=>$request->subtitle,
			'rekening'=>$request->rekening_lama,
		];

		$kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->where('kode_kegiatan',$kode_kegiatan)->where('npsn',$request->npsn)->first();

		$nama_kegiatan = $kegiatan->nama_kegiatan;

		$get_komponen = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->where($where)->first();

		if(!empty($get_komponen)){
			if(is_null($get_komponen->status)){
				$status_edit = null;
				$catatan_edit = null;
			}else{
				$status_edit = '12';
				$catatan_edit = 'Revisi Sekolah';
			}

			$data_user = [
				'update_user'=>str_replace(["'"], ["\'"], $user->user_name),
				'update_time'=>date('Y-m-d H:i:s'),
				'update_ip'=>GettingIP::get_client_ip(),
				'status' => $status_edit,
				'catatan' => $catatan_edit
			];

			$data = array_merge($data,$data_user);

			$simpan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->where($where)->update($data);
		}else{

			$data_user = [
				'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
				'insert_time'=>date('Y-m-d H:i:s'),
				'insert_ip'=>GettingIP::get_client_ip(),
				'update_user'=>str_replace(["'"], ["\'"], $user->user_name),
				'update_time'=>date('Y-m-d H:i:s'),
				'update_ip'=>GettingIP::get_client_ip()
			];

			$data = array_merge($data,$data_user);

			$simpan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->insert($data);
		}


		$message = 'Berhasil disimpan';

		return response()->json(compact('message','kode_kegiatan','nama_kegiatan'), 200);
	}

	function hapus_komponen(Request $request){
		$komponen_id = $request->komponen_id;
		$npsn = $request->npsn;
		$subtitle = $request->subtitle;
		$kode_kegiatan = $request->kode_kegiatan;
		$rekening = $request->rekening;

		$conn = Set_koneksi::set_koneksi($request);

		$get_data = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')->where('kd.npsn',$npsn)->where('kd.subtitle',$subtitle)->where('kd.kode_kegiatan',$kode_kegiatan)->where('rekening',$rekening)->where('kd.komponen_id',$komponen_id)->first();

		$kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->where('kode_kegiatan',$kode_kegiatan)->where('npsn',$npsn)->first();

		$nama_kegiatan = $kegiatan->nama_kegiatan;
		$kode_kegiatan = $kegiatan->kode_kegiatan;

		$hapus = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')->where('kd.npsn',$npsn)->where('kd.subtitle',$subtitle)->where('rekening',$rekening)->where('kd.komponen_id',$komponen_id)->delete();

		if($hapus){
			$message = 'Success menghapus';
		}else{
			$message = 'Gagal menghapus';
		}

		return response()->json(compact('message','kode_kegiatan','nama_kegiatan'), 200);
	}

	function hapus_subtitle(Request $request){
		$npsn = $request->npsn;
		$subtitle = $request->subtitle;
		$kode_kegiatan = $request->kode_kegiatan;

		$conn = Set_koneksi::set_koneksi($request);

		$get_data = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')->where('kd.npsn',$npsn)->where('kd.subtitle',$subtitle)->where('kd.kode_kegiatan',$kode_kegiatan)->first();

		$kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->where('kode_kegiatan',$get_data->kode_kegiatan)->where('npsn',$npsn)->first();

		$nama_kegiatan = $kegiatan->nama_kegiatan;
		$kode_kegiatan = $kegiatan->kode_kegiatan;

		$hapus = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')->where('kd.npsn',$npsn)->where('kd.subtitle',$subtitle)->delete();

		if($hapus){
			$message = 'Success menghapus';
		}else{
			$message = 'Gagal menghapus';
		}

		return response()->json(compact('message','kode_kegiatan','nama_kegiatan'), 200);
	}

	function get_satuan_lain(Request $request){
		$ssh = DB::select(DB::raw("SELECT DISTINCT(satuan) as value
			FROM budget2021.ssh2021 ORDER BY satuan ASC"));
		foreach ($ssh as $key) {
			$key->text = $key->value;
		}

		$tambah = [
			[
				'value'=>'Semester',
				'text'=>'Semester',
			],
			[
				'value'=>'Macam',
				'text'=>'Macam',
			],
			[
				'value'=>'Triwulan',
				'text'=>'Triwulan',
			],
			[
				'value'=>'Materi',
				'text'=>'Materi',
			],
		];

		$ssh = array_merge($ssh,$tambah);

		return response()->json(compact('ssh'), 200);
	}

	function edit_komponen(Request $request){
		$komponen_id = $request->komponen_id;
		$npsn = $request->npsn;
		$subtitle = $request->subtitle;
		$kode_kegiatan = $request->kode_kegiatan;
		$rekening = $request->rekening;

		$conn = Set_koneksi::set_koneksi($request);

		// return $rekening;
		$rek = DB::connection($conn['conn_status'])->table('budget2021.ssh2021 as ssh')->where('komponen_id',$komponen_id)->get();


		$lists_rekening =[];
		if($rek->count()!=0){
			foreach ($rek as $key) {
				array_push($lists_rekening, ['value'=>$key->rekening,'text'=>$key->rekening]);
			}
		}

		$data = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')->selectRaw("kd.*,ssh.komponen_name,ssh.satuan,ssh.komponen_harga_bulat,kd.rekening")->join('budget2021.ssh2021 as ssh','ssh.komponen_id','kd.komponen_id')->where('kd.npsn',$npsn)->where('kd.subtitle',$subtitle)->where('kd.kode_kegiatan',$kode_kegiatan)->where('kd.komponen_id',$komponen_id)->where('kd.rekening',$rekening)->first();

		$list_rekening = [['value'=>$data->rekening],'text'=>$data->rekening];

		$koefisien = ($data->koefisien>0) ? $data->koefisien : '';
		$koefisien2 = ($data->koefisien2>0) ? $data->koefisien2 : '';
		$koefisien3 = ($data->koefisien3>0) ? $data->koefisien3 : '';

		$arr_koefisien = [$koefisien,$koefisien2,$koefisien3];
		$arr_satuan = [
			[
				'value'=>$data->satuan_koefisien,
				'text'=>$data->satuan_koefisien,
			],
			[
				'value'=>$data->satuan_koefisien2,
				'text'=>$data->satuan_koefisien2,
			],
			[
				'value'=>$data->satuan_koefisien3,
				'text'=>$data->satuan_koefisien3,
			],
		];

		$data->arr_koefisien = $arr_koefisien;
		$data->arr_satuan = $arr_satuan;

		// $data->komponen_harga_bulat = (int)$data->komponen_harga_bulat;

		if($data){
			$message = 'Success menghapus';
		}else{
			$message = 'Gagal menghapus';
		}

		return response()->json(compact('message','data','lists_rekening','list_rekening'), 200);
	}

	function update_subtitle(Request $request){
		$kode_kegiatan = $request->kode_kegiatan;
		$npsn = $request->npsn;
		$sub_baru = $request->sub_baru;
		$sub_lama = $request->sub_lama;

		$conn = Set_koneksi::set_koneksi($request);

		$kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->where('kode_kegiatan',$kode_kegiatan)->where('npsn',$npsn)->first();

		$nama_kegiatan = $kegiatan->nama_kegiatan;
		$kode_kegiatan = $kegiatan->kode_kegiatan;

		$update = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')->where('kd.npsn',$npsn)->where('kd.subtitle',$sub_lama)->where('kd.kode_kegiatan',$kode_kegiatan)->update(['subtitle'=>$sub_baru]);

		if($update){
			$message = 'Success mengupdate';
		}else{
			$message = 'Gagal mengupdate';
		}

		return response()->json(compact('message','kode_kegiatan','nama_kegiatan'), 200);
	}

	function simpan_nominal_awal(Request $request){
		$npsn = $request->npsn;
		$data = $request->data;

		$conn = Set_koneksi::set_koneksi($request);
		$simpan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_awal')
		->whereRaw("npsn='$npsn' AND kode_kegiatan='".$data['kode_kegiatan']."'")->update(['nominal'=>$data['nilai']]);

		if($simpan){
			$message = 'Berhasil disimpan';
			$code = '200';
		}else{
			$message = 'Gagal disimpan';
			$code = '250';
		}

		return response()->json(compact('message','code'),200);
	}
}

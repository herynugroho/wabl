<?php

namespace App\Http\Controllers\Pencairan;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use App\Http\Libraries\Konversi;
use DB;
use App\Http\Controllers\Controller;


class NegeriController extends Controller
{
	function generate_data(Request $request){
		$jenis = $request->jenis;  // "Barjas"
		$jenjang = $request->jenjang;  // "SD"
		$status = $request->status;  // "NEGERI"
		$sumber_dana = $request->sumber_dana;  // "BOS"
		$periode = $request->periode;  // "BOS"
		$tahap = $request->tahap;  // "BOS"
		$aksi_button = $request->aksi_button;
		$id_delivery = $request->id_delivery;
		$tgl_sp2d = $request->s_tgl_sp2d;  //: this.tgl_sp2d,
		$nomor_sp2d = $request->s_nomor_sp2d;  //: this.nomor_sp2d,

		$request->jenjang = ($jenjang=='SD') ? 'SD' : 'SMP';
		$request->status = ($status=='NEGERI') ? '1' : '2';
		$request->sumberDana = ($sumber_dana=='BOS') ? 'bos' : 'bopda';

		$jenjangnya = ($jenjang=='SD') ? "'SD','MI'" : "'SMP','MTS'";

		$conn = Set_koneksi::set_koneksi($request);
		$user = Master::selectRaw("user_id,user_name")->where('user_id','=','satya')->first();

		if($sumber_dana=='BOPDA'){
			$kode_dana = '.3.03.';
			$where_rek = "(duk.no_rek_bopda_giro IS NOT NULL OR duk.no_rek_bopda_giro='')";
		}else{
			$kode_dana = '.3.01.';
			$where_rek = "(duk.no_rek_bos_giro IS NOT NULL OR duk.no_rek_bos_giro='')";
		}

		switch ($periode) {
			case 'JANUARI':
			$bulan_pakai = "bulan_1";
			break;
			case 'FEBRUARI':
			$bulan_pakai = "bulan_2";
			break;
			case 'MARET':
			$bulan_pakai = "bulan_3";
			break;
			case 'APRIL':
			$bulan_pakai = "bulan_4";
			break;
			case 'MEI':
			$bulan_pakai = "bulan_5";
			break;
			case 'JUNI':
			$bulan_pakai = "bulan_6";
			break;
			case 'JULI':
			$bulan_pakai = "bulan_7";
			break;
			case 'AGUSTUS':
			$bulan_pakai = "bulan_8";
			break;
			case 'SEPTEMBER':
			$bulan_pakai = "bulan_9";
			break;
			case 'OKTOBER':
			$bulan_pakai = "bulan_10";
			break;
			case 'NOVEMBER':
			$bulan_pakai = "bulan_11";
			break;
			case 'DESEMBER':
			$bulan_pakai = "bulan_12";
			break;
			
			default:
			$bulan_pakai = "bulan_";
			break;
		};

		$total_pegawai = 0;
		$total_barjas = 0;
		$total_all = 0;

		$where_pencairan = [
			'jenis'=>$jenis,
			'sumber_dana'=>$sumber_dana,
			'jenjang'=>$jenjang,
			'status_sekolah'=>$status,
			'periode'=>$periode,
			'tahap'=>$tahap,
		];

		$cek_exists_pencairan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->where($where_pencairan)->first();

		$status_data = 'Simpan';
		if($aksi_button=='Simpan'){
			$status_data = 'Update';
		}

		if(!empty($cek_exists_pencairan)){
			if($aksi_button=='Simpan'){
				$data_update = [
					'id_delivery'=>$id_delivery,
					'tanggal_sp2d'=>$tgl_sp2d,
					'nomor_sp2d'=>$nomor_sp2d,
				];
				DB::connection($conn['conn_status'])->table('budget2021.pencairan')->where($where_pencairan)->update($data_update);
			}else{
				$id_delivery = $cek_exists_pencairan->id_delivery;
				$tgl_sp2d = $cek_exists_pencairan->tanggal_sp2d;
				$nomor_sp2d = $cek_exists_pencairan->nomor_sp2d;
			}

			$npsn = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->where($where_pencairan)->get();
			if($npsn->count()!=0){
				foreach ($npsn as $key => $value) {
					$value->no = ($key+1);
					$value->pegawai = number_format($value->nilai_honor,0,',','.');
					$value->barjas = number_format($value->nilai_barjas,0,',','.');
					$value->total = number_format(($value->nilai_honor + $value->nilai_barjas),0,',','.');

					$total_barjas += $value->nilai_barjas;
					$total_pegawai += $value->nilai_honor;
					$total_all += ($value->nilai_honor + $value->nilai_barjas);
				}
			}
			$total_barjas = number_format($total_barjas,0,',','.');
			$total_pegawai = number_format($total_pegawai,0,',','.');
			$total_all = number_format($total_all,0,',','.');
			
			$sekarang = date('d-m-Y',strtotime($cek_exists_pencairan->tanggal_penarikan));
			$status_data = 'Update';
		}else{
			$where_pencairan_exist = [
				'jenis'=>$jenis,
				'sumber_dana'=>$sumber_dana,
				'jenjang'=>$jenjang,
				'status_sekolah'=>$status,
				'periode'=>$periode,
			];

			$npsn_sudah = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->select('npsn')->where($where_pencairan_exist);

			$npsn = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')
			->leftjoin('public.unit_kerja as uk','uk.unit_id','kd.npsn')
			->leftjoin('public.detail_unit_kerja as duk','duk.unit_id','uk.unit_id')
			->join('budget2021.sdm','sdm.npsn','kd.npsn')
			->selectRaw("kd.npsn")
			->whereRaw("kd.kunci_alokasi='1' AND $where_rek AND (uk.jenjang IS NOT NULL AND uk.jenjang IN ($jenjangnya)) AND sdm.jenis_pegawai!='Pelatih' AND kd.kode_kegiatan LIKE '%$kode_dana%'")
			->whereNotIn('kd.npsn',$npsn_sudah)
			->groupBy('kd.npsn')->get();

			$npsn_pakai = [];
			$no=1;
			if($npsn->count()!=0){
				foreach ($npsn as $key => $val) {
					$sekolah = DB::connection($conn['conn_status'])
					->table('public.unit_kerja as uk')
					->leftjoin('public.detail_unit_kerja as duk','duk.unit_id','uk.unit_id')
					->whereRaw("uk.unit_id='".$val->npsn."'")->first();

					if(!empty($sekolah)){
						$nama_sekolah = $sekolah->unit_name;
						$no_rekening_bos = $sekolah->no_rek_bos_giro;
						$no_rekening_bopda = $sekolah->no_rek_bopda_giro;
					}else{
						$nama_sekolah = '';
						$no_rekening_bos = '';
						$no_rekening_bopda = '';
					}

					$val->nama_sekolah = $nama_sekolah;
					$val->rekening = ($sumber_dana=='BOS') ? $no_rekening_bos : $no_rekening_bopda;

					// HONOR
					$sdm_honor = DB::connection($conn['conn_status'])->table('budget2021.sdm')
					->leftjoin('budget2021.sdm_detail as sd','sd.sdm_id','sdm.sdm_id')
					->selectRaw("
						SUM(sd.bulan_1) as bulan_1,
						SUM(sd.bulan_2) as bulan_2,
						SUM(sd.bulan_3) as bulan_3,
						SUM(sd.bulan_4) as bulan_4,
						SUM(sd.bulan_5) as bulan_5,
						SUM(sd.bulan_6) as bulan_6,
						SUM(sd.bulan_7) as bulan_7,
						SUM(sd.bulan_8) as bulan_8,
						SUM(sd.bulan_9) as bulan_9,
						SUM(sd.bulan_10) as bulan_10,
						SUM(sd.bulan_11) as bulan_11,
						SUM(sd.bulan_12) as bulan_12
						")
					->whereRaw("sdm.npsn='".$val->npsn."' AND (jenis_pegawai!='Pelatih' AND sdm.komponen_id NOT IN ('2.1.1.01.01.01.004.018','2.1.1.01.01.01.004.017','2.1.1.01.01.01.004.016'))")->first();

					$nilai_pegawai = (!empty($sdm_honor) && $jenis=='Pegawai') ? $sdm_honor->$bulan_pakai : 0;
					$val->pegawai = number_format($nilai_pegawai,0,',','.');

					// BARJAS
					$sdm_barjas = DB::connection($conn['conn_status'])->table('budget2021.sdm')
					->leftjoin('budget2021.sdm_detail as sd','sd.sdm_id','sdm.sdm_id')
					->selectRaw("
						SUM(sd.bulan_1) as bulan_1,
						SUM(sd.bulan_2) as bulan_2,
						SUM(sd.bulan_3) as bulan_3,
						SUM(sd.bulan_4) as bulan_4,
						SUM(sd.bulan_5) as bulan_5,
						SUM(sd.bulan_6) as bulan_6,
						SUM(sd.bulan_7) as bulan_7,
						SUM(sd.bulan_8) as bulan_8,
						SUM(sd.bulan_9) as bulan_9,
						SUM(sd.bulan_10) as bulan_10,
						SUM(sd.bulan_11) as bulan_11,
						SUM(sd.bulan_12) as bulan_12
						")
					->whereRaw("sdm.npsn='".$val->npsn."' AND (sdm.komponen_id IN ('2.1.1.01.01.01.004.018','2.1.1.01.01.01.004.017','2.1.1.01.01.01.004.016'))")->first();

					$nilai_barjas = (!empty($sdm_barjas) && $jenis=='Barjas') ? $sdm_barjas->$bulan_pakai : 0;
					$val->barjas = number_format($nilai_barjas,0,',','.');

					$nilai_total = ($nilai_barjas + $nilai_pegawai);
					$val->total = number_format($nilai_total,0,',','.');

					$total_barjas += $nilai_barjas;
					$total_pegawai += $nilai_pegawai;
					$total_all += $nilai_total;

					if($nilai_total!=0){
						$val->no = $no;
						$no++;

						array_push($npsn_pakai,$val);

						$where_pencairan = [
							'npsn'=>$val->npsn,
							'jenis'=>$jenis,
							'sumber_dana'=>$sumber_dana,
							'jenjang'=>$jenjang,
							'status_sekolah'=>$status,
							'periode'=>$periode,
							'tahap'=>$tahap,
						];

						$cek_pencairan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->where($where_pencairan)->first();

						if(!empty($cek_pencairan)){

						}else{
							$data_imbuh = [
								'tanggal_penarikan'=>date('Y-m-d'),
								'nama_sekolah'=>$val->nama_sekolah,
								'rekening'=>$val->rekening,
								'nama_rekening'=>'',
								'nilai_honor'=>$nilai_pegawai,
								'nilai_barjas'=>$nilai_barjas,
								'nilai_modal'=>0,
								'id_delivery'=>$id_delivery,
							];
							$data_insert = array_merge($where_pencairan,$data_imbuh);
							if($aksi_button=='Simpan'){
								$insert_data = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->insert($data_insert);
							}
						}
					}
				}

				$total_barjas = number_format($total_barjas,0,',','.');
				$total_pegawai = number_format($total_pegawai,0,',','.');
				$total_all = number_format($total_all,0,',','.');

				$npsn = $npsn_pakai;
			}
			$sekarang = date('d-m-Y');
		}

		DB::connection($conn['conn_status'])->table('budget2021.pencairan')->where($where_pencairan)->update(
			[
				'tanggal_akses'=>date('Y-m-d'),
				'user_akses'=>(!empty($user)) ? $user->user_name : 'Anonymous',
				'ip_akses'=>GettingIP::get_client_ip(),
			]
		);

		if($jenjang == 'SD'){
			$mengetahui = '<u>M. ARIES HILMI, S.STP</u><br/>198605152004121002';
		}else{
			$mengetahui = '<u>TRI AJI NUGROHO, S.Kom, MM</u><br/>198509172009021001';
		}

		$tgl_skrg = date('d',strtotime($sekarang));
		$angka_bulan = date('m',strtotime($sekarang));
		$tahun_skrg = date('Y',strtotime($sekarang));

		$bulan_skrg = Konversi::nama_bulan($angka_bulan);

		$sekarang = $tgl_skrg.' '.$bulan_skrg.' '.$tahun_skrg;


		// $tahapromawi = Konversi::KonDecRomawi($tahap);

		return response()->json(compact('npsn','total_pegawai','total_barjas','total_all','mengetahui','sekarang','id_delivery','status_data','tgl_sp2d','nomor_sp2d'), 200);
	}

	function cek_tahap_periode(Request $request){
		$jenis = $request->jenis;  //: "Barjas"
		$jenjang = $request->jenjang;  //: "SD"
		$periode = $request->periode;  //: "FEBRUARI"
		$status = $request->status;  //: "NEGERI"
		$sumber_dana = $request->sumber_dana;  //: "BOPDA"

		$request->jenjang = ($jenjang=='SD') ? 'SD' : 'SMP';
		$request->status = ($status=='NEGERI') ? '1' : '2';
		$request->sumberDana = ($sumber_dana=='BOS') ? 'bos' : 'bopda';

		$jenjangnya = ($jenjang=='SD') ? "'SD','MI'" : "'SMP','MTS'";

		$conn = Set_koneksi::set_koneksi($request);

		$cek_pencairan = DB::connection($conn['conn_status'])->table('budget2021.pencairan')->select('tahap')->whereRaw("jenis='$jenis' AND sumber_dana='$sumber_dana' AND jenjang='$jenjang' AND status_sekolah='$status' AND periode='$periode'")->groupBy('tahap')->orderBy('tahap','ASC')->get();

		$list_tahap = [];
		$tahap = '';

		if($cek_pencairan->count()!=0){
			foreach ($cek_pencairan as $key) {
				array_push($list_tahap,$key->tahap);
			}
			array_push($list_tahap,''.($key->tahap+1).'');
			$tahap = ''.($key->tahap+1).'';
		}else{
			array_push($list_tahap,'1');
			$tahap='1';
		}

		return response()->json(compact('tahap','list_tahap'), 200);
	}
}

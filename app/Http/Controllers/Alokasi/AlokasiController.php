<?php

namespace App\Http\Controllers\Alokasi;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use DB;
use App\Http\Controllers\Controller;


class AlokasiController extends Controller
{
	function get_kegiatan(Request $request){
		$npsn = $request->npsn;
		$status_sk = $request->status;
		$sumberDana = $request->sumberDana;

		if($sumberDana=='bopda'){
			$kode = '.3.03.';
			$kode_dana = '3.03';
		}else{
			$kode = '.3.01.';
			$kode_dana = '3.01';
		}

		$t_bulan_1 = 0;
		$t_bulan_2 = 0;
		$t_bulan_3 = 0;
		$t_bulan_4 = 0;
		$t_bulan_5 = 0;
		$t_bulan_6 = 0;
		$t_bulan_7 = 0;
		$t_bulan_8 = 0;
		$t_bulan_9 = 0;
		$t_bulan_10 = 0;
		$t_bulan_11 = 0;
		$t_bulan_12 = 0;
		$t_anggaran = 0;
		$t_sisa = 0;
		$t_total = 0;

		$conn = Set_koneksi::set_koneksi($request);

		$data_kegiatan = DB::connection($conn['conn_status'])->select("
			SELECT * FROM budget2021.kegiatan WHERE npsn='$npsn' AND kode_kegiatan like '%$kode%'
			");

		if(count($data_kegiatan)!=0){
			for ($i=0; $i < count($data_kegiatan); $i++) { 
				$baris = $data_kegiatan[$i];

				if($baris->kode_kegiatan=='5.05.3.03.016' || $baris->kode_kegiatan=='5.05.3.03.122' || $baris->kode_kegiatan=='5.05.3.01.048'){

					if($baris->kode_kegiatan=='5.05.3.03.016' || $baris->kode_kegiatan=='5.05.3.03.122'){
						$str_komponen = GettingIP::str_komponen_id("rekening IN ('5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007','5.1.02.02.01.0013')");

						if($request->status=='1'){
							$where = "sdm.kode_kegiatan = '5.05.3.03.016' AND sdm.komponen_id IN($str_komponen) ";
						}else{
							$where = "sdm.kode_dana='$kode_dana'";
						}
					}else if($baris->kode_kegiatan=='5.05.3.01.048'){
						$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
						$p_komponen_name = 'Jasa Pelatih Ekskul';
						$p_kode_kegiatan = '5.05.3.01.048';
						if($request->status=='1'){
							$where = "sdm.kode_kegiatan='$p_kode_kegiatan' AND sdm.komponen_id='$p_komponen_id' AND sdm.komponen_name='$p_komponen_name'";
						}else{
							$where = "sdm.kode_dana='$kode_dana'";
						}
					}

					$detailnya = DB::connection($conn['conn_status'])->select("
						SELECT sdm.npsn,
						SUM(COALESCE(sd.bulan_1,0)) as bulan_1,
						SUM(COALESCE(sd.bulan_2,0)) as bulan_2,
						SUM(COALESCE(sd.bulan_3,0)) as bulan_3,
						SUM(COALESCE(sd.bulan_4,0)) as bulan_4,
						SUM(COALESCE(sd.bulan_5,0)) as bulan_5,
						SUM(COALESCE(sd.bulan_6,0)) as bulan_6,
						SUM(COALESCE(sd.bulan_7,0)) as bulan_7,
						SUM(COALESCE(sd.bulan_8,0)) as bulan_8,
						SUM(COALESCE(sd.bulan_9,0)) as bulan_9,
						SUM(COALESCE(sd.bulan_10,0)) as bulan_10,
						SUM(COALESCE(sd.bulan_11,0)) as bulan_11,
						SUM(COALESCE(sd.bulan_12,0)) as bulan_12,
						SUM(COALESCE(sdm.nominal,0)) as anggaran 
						FROM budget2021.sdm
						LEFT JOIN budget2021.sdm_detail as sd ON sdm.sdm_id=sd.sdm_id
						WHERE $where AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
						GROUP  BY sdm.kode_kegiatan,sdm.npsn
						");

					if(count($detailnya)!=0){
						$baris->bulan_1 = $detailnya[0]->bulan_1;
						$baris->bulan_2 = $detailnya[0]->bulan_2;
						$baris->bulan_3 = $detailnya[0]->bulan_3;
						$baris->bulan_4 = $detailnya[0]->bulan_4;
						$baris->bulan_5 = $detailnya[0]->bulan_5;
						$baris->bulan_6 = $detailnya[0]->bulan_6;
						$baris->bulan_7 = $detailnya[0]->bulan_7;
						$baris->bulan_8 = $detailnya[0]->bulan_8;
						$baris->bulan_9 = $detailnya[0]->bulan_9;
						$baris->bulan_10 = $detailnya[0]->bulan_10;
						$baris->bulan_11 = $detailnya[0]->bulan_11;
						$baris->bulan_12 = $detailnya[0]->bulan_12;
						$baris->anggaran = $detailnya[0]->anggaran;
						$baris->total = ($detailnya[0]->bulan_1+$detailnya[0]->bulan_2+$detailnya[0]->bulan_3+$detailnya[0]->bulan_4+$detailnya[0]->bulan_5+$detailnya[0]->bulan_6+$detailnya[0]->bulan_7+$detailnya[0]->bulan_8+$detailnya[0]->bulan_9+$detailnya[0]->bulan_10+$detailnya[0]->bulan_11+$detailnya[0]->bulan_12);
						$baris->sisa = $detailnya[0]->anggaran-($detailnya[0]->bulan_1+$detailnya[0]->bulan_2+$detailnya[0]->bulan_3+$detailnya[0]->bulan_4+$detailnya[0]->bulan_5+$detailnya[0]->bulan_6+$detailnya[0]->bulan_7+$detailnya[0]->bulan_8+$detailnya[0]->bulan_9+$detailnya[0]->bulan_10+$detailnya[0]->bulan_11+$detailnya[0]->bulan_12);
					}else{
						$baris->bulan_1 = 0;
						$baris->bulan_2 = 0;
						$baris->bulan_3 = 0;
						$baris->bulan_4 = 0;
						$baris->bulan_5 = 0;
						$baris->bulan_6 = 0;
						$baris->bulan_7 = 0;
						$baris->bulan_8 = 0;
						$baris->bulan_9 = 0;
						$baris->bulan_10 = 0;
						$baris->bulan_11 = 0;
						$baris->bulan_12 = 0;
						$baris->anggaran = 0;
						$baris->total = 0;
						$baris->sisa = 0;
					}
				}else{
					$detailnya = DB::connection($conn['conn_status'])->select("
						SELECT 
						kode_kegiatan,
						SUM(COALESCE(bulan_1,0)) as bulan_1,
						SUM(COALESCE(bulan_2,0)) as bulan_2,
						SUM(COALESCE(bulan_3,0)) as bulan_3,
						SUM(COALESCE(bulan_4,0)) as bulan_4,
						SUM(COALESCE(bulan_5,0)) as bulan_5,
						SUM(COALESCE(bulan_6,0)) as bulan_6,
						SUM(COALESCE(bulan_7,0)) as bulan_7,
						SUM(COALESCE(bulan_8,0)) as bulan_8,
						SUM(COALESCE(bulan_9,0)) as bulan_9,
						SUM(COALESCE(bulan_10,0)) as bulan_10,
						SUM(COALESCE(bulan_11,0)) as bulan_11,
						SUM(COALESCE(bulan_12,0)) as bulan_12,
						SUM(COALESCE(nilai,0)) as anggaran
						FROM budget2021.kegiatan_detail
						WHERE npsn='$baris->npsn' AND kode_kegiatan='$baris->kode_kegiatan'
						GROUP BY kode_kegiatan
						");

					if(count($detailnya)!=0){
						$baris->bulan_1 = $detailnya[0]->bulan_1;
						$baris->bulan_2 = $detailnya[0]->bulan_2;
						$baris->bulan_3 = $detailnya[0]->bulan_3;
						$baris->bulan_4 = $detailnya[0]->bulan_4;
						$baris->bulan_5 = $detailnya[0]->bulan_5;
						$baris->bulan_6 = $detailnya[0]->bulan_6;
						$baris->bulan_7 = $detailnya[0]->bulan_7;
						$baris->bulan_8 = $detailnya[0]->bulan_8;
						$baris->bulan_9 = $detailnya[0]->bulan_9;
						$baris->bulan_10 = $detailnya[0]->bulan_10;
						$baris->bulan_11 = $detailnya[0]->bulan_11;
						$baris->bulan_12 = $detailnya[0]->bulan_12;
						$baris->anggaran = $detailnya[0]->anggaran;
						$baris->total = ($detailnya[0]->bulan_1+$detailnya[0]->bulan_2+$detailnya[0]->bulan_3+$detailnya[0]->bulan_4+$detailnya[0]->bulan_5+$detailnya[0]->bulan_6+$detailnya[0]->bulan_7+$detailnya[0]->bulan_8+$detailnya[0]->bulan_9+$detailnya[0]->bulan_10+$detailnya[0]->bulan_11+$detailnya[0]->bulan_12);
						$baris->sisa = $detailnya[0]->anggaran-($detailnya[0]->bulan_1+$detailnya[0]->bulan_2+$detailnya[0]->bulan_3+$detailnya[0]->bulan_4+$detailnya[0]->bulan_5+$detailnya[0]->bulan_6+$detailnya[0]->bulan_7+$detailnya[0]->bulan_8+$detailnya[0]->bulan_9+$detailnya[0]->bulan_10+$detailnya[0]->bulan_11+$detailnya[0]->bulan_12);
					}else{
						$baris->bulan_1 = 0;
						$baris->bulan_2 = 0;
						$baris->bulan_3 = 0;
						$baris->bulan_4 = 0;
						$baris->bulan_5 = 0;
						$baris->bulan_6 = 0;
						$baris->bulan_7 = 0;
						$baris->bulan_8 = 0;
						$baris->bulan_9 = 0;
						$baris->bulan_10 = 0;
						$baris->bulan_11 = 0;
						$baris->bulan_12 = 0;
						$baris->anggaran = 0;
						$baris->total = 0;
						$baris->sisa = 0;
					}
				}

				$t_bulan_1 += $baris->bulan_1;
				$t_bulan_2 += $baris->bulan_2;
				$t_bulan_3 += $baris->bulan_3;
				$t_bulan_4 += $baris->bulan_4;
				$t_bulan_5 += $baris->bulan_5;
				$t_bulan_6 += $baris->bulan_6;
				$t_bulan_7 += $baris->bulan_7;
				$t_bulan_8 += $baris->bulan_8;
				$t_bulan_9 += $baris->bulan_9;
				$t_bulan_10 += $baris->bulan_10;
				$t_bulan_11 += $baris->bulan_11;
				$t_bulan_12 += $baris->bulan_12;
				$t_anggaran += $baris->anggaran;
				$t_sisa += $baris->sisa;
				$t_total += $baris->total;
			}
		}


		$message = 'success';

		$get_kunci_alokasi = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='$npsn' AND kode_kegiatan LIKE '%$kode%' AND kunci_alokasi='1'")->get()->count();

		$kunci_alokasi = ($get_kunci_alokasi>0) ? true : false;

		$data_spasi = [
			'anggaran'=>'',
			'bulan_1'=>'',
			'bulan_2'=>'',
			'bulan_3'=>'',
			'bulan_4'=>'',
			'bulan_5'=>'',
			'bulan_6'=>'',
			'bulan_7'=>'',
			'bulan_8'=>'',
			'bulan_9'=>'',
			'bulan_10'=>'',
			'bulan_11'=>'',
			'bulan_12'=>'',
			'kode_kegiatan'=>'',
			'nama_kegiatan'=>'',
			'npsn'=>'',
			'sisa'=>'',
			'total'=>'',
		];

		$data_total = [
			'anggaran'=>$t_anggaran,
			'bulan_1'=>$t_bulan_1,
			'bulan_2'=>$t_bulan_2,
			'bulan_3'=>$t_bulan_3,
			'bulan_4'=>$t_bulan_4,
			'bulan_5'=>$t_bulan_5,
			'bulan_6'=>$t_bulan_6,
			'bulan_7'=>$t_bulan_7,
			'bulan_8'=>$t_bulan_8,
			'bulan_9'=>$t_bulan_9,
			'bulan_10'=>$t_bulan_10,
			'bulan_11'=>$t_bulan_11,
			'bulan_12'=>$t_bulan_12,
			'kode_kegiatan'=>'JUMLAH',
			'nama_kegiatan'=>'',
			'npsn'=>$npsn,
			'sisa'=>$t_sisa,
			'total'=>$t_total,
		];

		$data_cw = [
			'anggaran'=>$t_anggaran,
			'bulan_1'=>$t_bulan_1+$t_bulan_2+$t_bulan_3+$t_bulan_4,
			'bulan_2'=>-1,
			'bulan_3'=>-1,
			'bulan_4'=>-1,
			'bulan_5'=>$t_bulan_5+$t_bulan_6+$t_bulan_7+$t_bulan_8,
			'bulan_6'=>-1,
			'bulan_7'=>-1,
			'bulan_8'=>-1,
			'bulan_9'=>$t_bulan_9+$t_bulan_10+$t_bulan_11+$t_bulan_12,
			'bulan_10'=>-1,
			'bulan_11'=>-1,
			'bulan_12'=>-1,
			'kode_kegiatan'=>'JUMLAH CATURWULAN',
			'nama_kegiatan'=>'',
			'npsn'=>$npsn,
			'sisa'=>$t_sisa,
			'total'=>$t_total,
		];

		if($sumberDana=='bopda'){
			if($status_sk=='1'){

				$penerimaan_tahap1 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='1'")->first();
				$penerimaan_tahap2 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='2'")->first();
				$penerimaan_tahap3 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='3'")->first();
				$penerimaan_tahap4 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='4'")->first();
				$penerimaan_tahap5 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='5'")->first();
				$penerimaan_tahap6 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='6'")->first();
				$penerimaan_tahap7 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='7'")->first();
				$penerimaan_tahap8 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='8'")->first();
				$penerimaan_tahap9 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='9'")->first();
				$penerimaan_tahap10 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='10'")->first();
				$penerimaan_tahap11 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='11'")->first();
				$penerimaan_tahap12 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='12'")->first();

				$b_p_1 = (!empty($penerimaan_tahap1)) ? $penerimaan_tahap1->nominal : 0;
				$b_p_2 = (!empty($penerimaan_tahap2)) ? $penerimaan_tahap2->nominal : 0;
				$b_p_3 = (!empty($penerimaan_tahap3)) ? $penerimaan_tahap3->nominal : 0;
				$b_p_4 = (!empty($penerimaan_tahap4)) ? $penerimaan_tahap4->nominal : 0;
				$b_p_5 = (!empty($penerimaan_tahap5)) ? $penerimaan_tahap5->nominal : 0;
				$b_p_6 = (!empty($penerimaan_tahap6)) ? $penerimaan_tahap6->nominal : 0;
				$b_p_7 = (!empty($penerimaan_tahap7)) ? $penerimaan_tahap7->nominal : 0;
				$b_p_8 = (!empty($penerimaan_tahap8)) ? $penerimaan_tahap8->nominal : 0;
				$b_p_9 = (!empty($penerimaan_tahap9)) ? $penerimaan_tahap9->nominal : 0;
				$b_p_10 = (!empty($penerimaan_tahap10)) ? $penerimaan_tahap10->nominal : 0;
				$b_p_11 = (!empty($penerimaan_tahap11)) ? $penerimaan_tahap11->nominal : 0;
				$b_p_12 = (!empty($penerimaan_tahap12)) ? $penerimaan_tahap12->nominal : 0;

			}else{
				$penerimaan_tahap1 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='1'")->first();
				$penerimaan_tahap2 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='2'")->first();

				$b_p_1 = (!empty($penerimaan_tahap1)) ? $penerimaan_tahap1->nominal : 0;
				$b_p_2 = -1;
				$b_p_3 = -1;
				$b_p_4 = -1;
				$b_p_5 = -1;
				$b_p_6 = -1;

				$b_p_7 = (!empty($penerimaan_tahap2)) ? $penerimaan_tahap2->nominal : 0;
				$b_p_8 = -1;
				$b_p_9 = -1;
				$b_p_10 = -1;
				$b_p_11 = -1;
				$b_p_12 = -1;
			}
		}else{
			$penerimaan_tahap1 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='1'")->first();
			$penerimaan_tahap2 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='2'")->first();
			$penerimaan_tahap3 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='3'")->first();

			$sisa_saldo = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->first();

			$sisa = (!empty($sisa_saldo)) ? $sisa_saldo->penerimaan : 0;

			$b_p_1 = (!empty($penerimaan_tahap1)) ? $penerimaan_tahap1->nominal : 0;
			$b_p_2 = -1;
			$b_p_3 = -1;
			$b_p_4 = -1;

			$b_p_5 = (!empty($penerimaan_tahap2)) ? $penerimaan_tahap2->nominal : 0;
			$b_p_6 = -1;
			$b_p_7 = -1;
			$b_p_8 = -1;

			$b_p_9 = (!empty($penerimaan_tahap3)) ? $penerimaan_tahap3->nominal : 0;
			$b_p_10 = -1;
			$b_p_11 = -1;
			$b_p_12 = -1;

			$b_p_1 += $sisa;
		}

		$penerimaan = [
			'anggaran'=>0,
			'bulan_1'=>$b_p_1,
			'bulan_2'=>$b_p_2,
			'bulan_3'=>$b_p_3,
			'bulan_4'=>$b_p_4,
			'bulan_5'=>$b_p_5,
			'bulan_6'=>$b_p_6,
			'bulan_7'=>$b_p_7,
			'bulan_8'=>$b_p_8,
			'bulan_9'=>$b_p_9,
			'bulan_10'=>$b_p_10,
			'bulan_11'=>$b_p_11,
			'bulan_12'=>$b_p_12,
			'kode_kegiatan'=>'PENERIMAAN',
			'nama_kegiatan'=>'',
			'npsn'=>$npsn,
			'sisa'=>0,
			'total'=>0,
		];

		array_push($data_kegiatan,$data_spasi,$data_total,$data_cw,$penerimaan);

		return response()->json(compact('message', 'data_kegiatan','t_total','t_anggaran','kunci_alokasi'), 200);
	}

	// VERTICAL
	// $selectraw = "k.npsn,k.kode_kegiatan,k.nama_kegiatan,SUM(nilai) as anggaran,
	// (SUM(COALESCE(bulan_1,0))+SUM(COALESCE(bulan_2,0))+SUM(COALESCE(bulan_3,0))+SUM(COALESCE(bulan_4,0))+SUM(COALESCE(bulan_5,0))+SUM(COALESCE(bulan_6,0))+SUM(COALESCE(bulan_7,0))+SUM(COALESCE(bulan_8,0))+SUM(COALESCE(bulan_9,0))+SUM(COALESCE(bulan_10,0))+SUM(COALESCE(bulan_11,0))+SUM(COALESCE(bulan_12,0))) as total,'' as aksi";

	// $data_kegiatan = DB::connection($conn['conn_status'])->select("
	// 	SELECT $selectraw
	// 	FROM budget2021.kegiatan as k
	// 	JOIN budget2021.kegiatan_detail as kd ON k.kode_kegiatan=kd.kode_kegiatan
	// 	WHERE k.npsn='$npsn' AND k.kode_kegiatan LIKE '%$kode%' AND k.npsn=kd.npsn
	// 	GROUP BY k.kode_kegiatan,k.nama_kegiatan,k.npsn
	// 	");

	// $row = [];

	// if(count($data_kegiatan)!=0){
	// 	for ($i=0; $i < count($data_kegiatan); $i++) { 
	// 		$children = [];
	// 		$baris = $data_kegiatan[$i];

	// 		$detail = DB::connection($conn['conn_status'])->select("
	// 			SELECT kode_kegiatan,npsn,
	// 			SUM(bulan_1) as bulan_1,
	// 			SUM(bulan_2) as bulan_2,
	// 			SUM(bulan_3) as bulan_3,
	// 			SUM(bulan_4) as bulan_4,
	// 			SUM(bulan_5) as bulan_5,
	// 			SUM(bulan_6) as bulan_6,
	// 			SUM(bulan_7) as bulan_7,
	// 			SUM(bulan_8) as bulan_8,
	// 			SUM(bulan_9) as bulan_9,
	// 			SUM(bulan_10) as bulan_10,
	// 			SUM(bulan_11) as bulan_11,
	// 			SUM(bulan_12) as bulan_12
	// 			FROM budget2021.kegiatan_detail as kd
	// 			WHERE kd.npsn='$npsn' AND kd.kode_kegiatan = '$baris->kode_kegiatan'
	// 			GROUP BY kd.kode_kegiatan,kd.npsn
	// 			");

	// 		for ($j=0; $j < 12; $j++) { 
	// 			$kolom = 'bulan_'.($j+1);
	// 			$data['npsn'] = '';
	// 			$data['kode_kegiatan'] = '';
	// 			$data['nama_kegiatan'] = GettingIP::bulan_angka_to_text($j+1);
	// 			$data['anggaran'] = '';
	// 			$data['total'] = (!empty($detail) && $detail[0]->$kolom != null) ? $detail[0]->$kolom : 0;
	// 			$data['aksi'] = '';
	// 			array_push($children, $data);
	// 		}
	// 		$baris->children = $children;
	// 		array_push($row,$baris);
	// 	}
	// }

	// $data_kegiatan = $row;
	// // VERTICAL

	function detail_kegiatan(Request $request){
		$kode_kegiatan =  $request->kode_kegiatan;
		$npsn = $request->npsn;
		$status_sk = $request->status;
		$sumberDana = $request->sumberDana;

		if($sumberDana=='bos'){
			$kode_dana = '3.01';
		}else{
			$kode_dana = '3.03';
		}

		$conn = Set_koneksi::set_koneksi($request);

		$t_bulan_1 = 0;
		$t_bulan_2 = 0;
		$t_bulan_3 = 0;
		$t_bulan_4 = 0;
		$t_bulan_5 = 0;
		$t_bulan_6 = 0;
		$t_bulan_7 = 0;
		$t_bulan_8 = 0;
		$t_bulan_9 = 0;
		$t_bulan_10 = 0;
		$t_bulan_11 = 0;
		$t_bulan_12 = 0;
		$t_anggaran = 0;
		$t_sisa = 0;
		$t_total = 0;

		if($kode_kegiatan=='5.05.3.03.016'){
			if($request->status=='1'){
				$where = "sdm.kode_kegiatan='$kode_kegiatan'";
			}else{
				$where = "sdm.kode_dana='$kode_dana'";
			}

			$data_kegiatan = DB::connection($conn['conn_status'])->select("
				SELECT *,(bulan_1+bulan_2+bulan_3+bulan_4+bulan_5+bulan_6+bulan_7+bulan_8+bulan_9+bulan_10+bulan_11+bulan_12) as total,
				anggaran-(bulan_1+bulan_2+bulan_3+bulan_4+bulan_5+bulan_6+bulan_7+bulan_8+bulan_9+bulan_10+bulan_11+bulan_12) as sisa,
				CONCAT(subtitle,(CASE WHEN rekening LIKE '%JKN' 
				THEN ' (JKN)' 
				ELSE (CASE WHEN rekening LIKE '%JKK'
				THEN ' (JKK)'
				ELSE (CASE WHEN rekening LIKE '%JK'
				THEN ' (JK)'
				ELSE ''
				END)
				END) 
				END)
				) as subtitle
				FROM (
				SELECT 
				sdm.nama_pegawai as subtitle,
				komponen_name,
				sdm.komponen_id,
				sdm.sdm_id as rekening,
				sdm.hari as qty,'Hari' as satuan,
				sdm.jam as koefisien,'Jam' as satuan_koefisien,
				sdm.bulan as koefisien2,'Bulan' as satuan_koefisien2,
				'' as koefisien3,null as satuan_koefisien3,
				'' as komponen,
				SUM(COALESCE(sdm.nominal,0)) as anggaran,
				SUM(COALESCE(sd.bulan_1,0)) as bulan_1,
				SUM(COALESCE(sd.bulan_2,0)) as bulan_2,
				SUM(COALESCE(sd.bulan_3,0)) as bulan_3,
				SUM(COALESCE(sd.bulan_4,0)) as bulan_4,
				SUM(COALESCE(sd.bulan_5,0)) as bulan_5,
				SUM(COALESCE(sd.bulan_6,0)) as bulan_6,
				SUM(COALESCE(sd.bulan_7,0)) as bulan_7,
				SUM(COALESCE(sd.bulan_8,0)) as bulan_8,
				SUM(COALESCE(sd.bulan_9,0)) as bulan_9,
				SUM(COALESCE(sd.bulan_10,0)) as bulan_10,
				SUM(COALESCE(sd.bulan_11,0)) as bulan_11,
				SUM(COALESCE(sd.bulan_12,0)) as bulan_12
				FROM budget2021.sdm as sdm
				LEFT JOIN budget2021.sdm_detail as sd on sd.sdm_id=sdm.sdm_id
				WHERE $where AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
				GROUP BY sdm.komponen_id,sdm.hari,sdm.bulan,sdm.nama_pegawai,sdm.sdm_id
				ORDER BY sdm.sdm_id ASC
				) as datanya
				");
		}else if($kode_kegiatan == '5.05.3.01.048'){
			$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
			$p_komponen_name = 'Jasa Pelatih Ekskul';
			$p_kode_kegiatan = '5.05.3.01.048';


			if($request->status=='1'){
				$where = "sdm.kode_kegiatan='$p_kode_kegiatan' AND sdm.komponen_id='$p_komponen_id' AND sdm.komponen_name='$p_komponen_name'";
			}else{
				$where = "sdm.kode_dana='$kode_dana'";
			}

			$data_kegiatan = DB::connection($conn['conn_status'])->select("
				SELECT *,(bulan_1+bulan_2+bulan_3+bulan_4+bulan_5+bulan_6+bulan_7+bulan_8+bulan_9+bulan_10+bulan_11+bulan_12) as total,
				anggaran-(bulan_1+bulan_2+bulan_3+bulan_4+bulan_5+bulan_6+bulan_7+bulan_8+bulan_9+bulan_10+bulan_11+bulan_12) as sisa 
				FROM (
				SELECT 
				sdm.nama_pegawai as subtitle,
				komponen_name,
				sdm.komponen_id,
				sdm.sdm_id as rekening,
				sdm.hari as qty,'Hari' as satuan,
				sdm.jam as koefisien,'Jam' as satuan_koefisien,
				sdm.bulan as koefisien2,'Bulan' as satuan_koefisien2,
				'' as koefisien3,null as satuan_koefisien3,
				'' as komponen,
				SUM(COALESCE(sdm.nominal,0)) as anggaran,
				SUM(COALESCE(sd.bulan_1,0)) as bulan_1,
				SUM(COALESCE(sd.bulan_2,0)) as bulan_2,
				SUM(COALESCE(sd.bulan_3,0)) as bulan_3,
				SUM(COALESCE(sd.bulan_4,0)) as bulan_4,
				SUM(COALESCE(sd.bulan_5,0)) as bulan_5,
				SUM(COALESCE(sd.bulan_6,0)) as bulan_6,
				SUM(COALESCE(sd.bulan_7,0)) as bulan_7,
				SUM(COALESCE(sd.bulan_8,0)) as bulan_8,
				SUM(COALESCE(sd.bulan_9,0)) as bulan_9,
				SUM(COALESCE(sd.bulan_10,0)) as bulan_10,
				SUM(COALESCE(sd.bulan_11,0)) as bulan_11,
				SUM(COALESCE(sd.bulan_12,0)) as bulan_12
				FROM budget2021.sdm as sdm
				LEFT JOIN budget2021.sdm_detail as sd on sd.sdm_id=sdm.sdm_id
				WHERE $where AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
				GROUP BY sdm.komponen_id,sdm.hari,sdm.bulan,sdm.nama_pegawai,sdm.sdm_id
				ORDER BY sdm.sdm_id ASC
				) as datanya
				");
		}else if($kode_kegiatan == '5.05.3.03.122'){
			$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
			$p_komponen_name = 'Jasa Pelatih Ekskul';
			$p_kode_kegiatan = '5.05.3.01.048';


			if($request->status=='1'){
				$where = "sdm.kode_kegiatan='$p_kode_kegiatan' AND sdm.komponen_id='$p_komponen_id' AND sdm.komponen_name='$p_komponen_name'";
			}else{
				$where = "sdm.kode_dana='$kode_dana'";
			}

			$data_kegiatan = DB::connection($conn['conn_status'])->select("
				SELECT *,(bulan_1+bulan_2+bulan_3+bulan_4+bulan_5+bulan_6+bulan_7+bulan_8+bulan_9+bulan_10+bulan_11+bulan_12) as total,
				anggaran-(bulan_1+bulan_2+bulan_3+bulan_4+bulan_5+bulan_6+bulan_7+bulan_8+bulan_9+bulan_10+bulan_11+bulan_12) as sisa 
				FROM (
				SELECT 
				sdm.nama_pegawai as subtitle,
				komponen_name,
				sdm.komponen_id,
				sdm.sdm_id as rekening,
				sdm.hari as qty,'Hari' as satuan,
				sdm.jam as koefisien,'Jam' as satuan_koefisien,
				sdm.bulan as koefisien2,'Bulan' as satuan_koefisien2,
				'' as koefisien3,null as satuan_koefisien3,
				'' as komponen,
				SUM(COALESCE(sdm.nominal,0)) as anggaran,
				SUM(COALESCE(sd.bulan_1,0)) as bulan_1,
				SUM(COALESCE(sd.bulan_2,0)) as bulan_2,
				SUM(COALESCE(sd.bulan_3,0)) as bulan_3,
				SUM(COALESCE(sd.bulan_4,0)) as bulan_4,
				SUM(COALESCE(sd.bulan_5,0)) as bulan_5,
				SUM(COALESCE(sd.bulan_6,0)) as bulan_6,
				SUM(COALESCE(sd.bulan_7,0)) as bulan_7,
				SUM(COALESCE(sd.bulan_8,0)) as bulan_8,
				SUM(COALESCE(sd.bulan_9,0)) as bulan_9,
				SUM(COALESCE(sd.bulan_10,0)) as bulan_10,
				SUM(COALESCE(sd.bulan_11,0)) as bulan_11,
				SUM(COALESCE(sd.bulan_12,0)) as bulan_12
				FROM budget2021.sdm as sdm
				LEFT JOIN budget2021.sdm_detail as sd on sd.sdm_id=sdm.sdm_id
				WHERE $where AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
				GROUP BY sdm.komponen_id,sdm.hari,sdm.bulan,sdm.nama_pegawai,sdm.sdm_id
				ORDER BY sdm.sdm_id ASC
				) as datanya
				");
		}else{
			$data_kegiatan = DB::connection($conn['conn_status'])->select("
				SELECT *,
				(COALESCE(bulan_1,0)+COALESCE(bulan_2,0)+COALESCE(bulan_3,0)+COALESCE(bulan_4,0)+COALESCE(bulan_5,0)+COALESCE(bulan_6,0)+COALESCE(bulan_7,0)+COALESCE(bulan_8,0)+COALESCE(bulan_9,0)+COALESCE(bulan_10,0)+COALESCE(bulan_11,0)+COALESCE(bulan_12,0)) as total,
				(anggaran-(COALESCE(bulan_1,0)+COALESCE(bulan_2,0)+COALESCE(bulan_3,0)+COALESCE(bulan_4,0)+COALESCE(bulan_5,0)+COALESCE(bulan_6,0)+COALESCE(bulan_7,0)+COALESCE(bulan_8,0)+COALESCE(bulan_9,0)+COALESCE(bulan_10,0)+COALESCE(bulan_11,0)+COALESCE(bulan_12,0))) as sisa
				FROM (
				SELECT 
				kd.subtitle,'' as komponen_name,kd.komponen_id,kd.rekening,kd.qty,'' as satuan,
				kd.koefisien,kd.satuan_koefisien,
				kd.koefisien2,kd.satuan_koefisien2,
				kd.koefisien3,kd.satuan_koefisien3,
				'' as komponen,
				COALESCE(bulan_1,0) as bulan_1,
				COALESCE(bulan_2,0) as bulan_2,
				COALESCE(bulan_3,0) as bulan_3,
				COALESCE(bulan_4,0) as bulan_4,
				COALESCE(bulan_5,0) as bulan_5,
				COALESCE(bulan_6,0) as bulan_6,
				COALESCE(bulan_7,0) as bulan_7,
				COALESCE(bulan_8,0) as bulan_8,
				COALESCE(bulan_9,0) as bulan_9,
				COALESCE(bulan_10,0) as bulan_10,
				COALESCE(bulan_11,0) as bulan_11,
				COALESCE(bulan_12,0) as bulan_12,kd.nilai as anggaran
				FROM budget2021.kegiatan_detail as kd
				WHERE kd.npsn='$npsn' AND kd.kode_kegiatan = '$kode_kegiatan' AND komponen_id IS NOT NULL
				ORDER BY kd.subtitle ASC
				) as hitung
				");	
			if(count($data_kegiatan)!=0){
				foreach ($data_kegiatan as $key) {
					$sshnya = DB::connection('pgsql')->table('budget2021.ssh2021 as ssh')->whereRaw("ssh.komponen_id='$key->komponen_id' AND rekening='$key->rekening'")->first();
					if(!empty($sshnya)){
						$key->komponen_name = $sshnya->komponen_name;
						$key->satuan = $sshnya->satuan;
					}
				}
			}
		}

		if(count($data_kegiatan)){
			for ($i=0; $i < count($data_kegiatan); $i++) { 
				$baris = $data_kegiatan[$i];

				$t_bulan_1 += $baris->bulan_1;
				$t_bulan_2 += $baris->bulan_2;
				$t_bulan_3 += $baris->bulan_3;
				$t_bulan_4 += $baris->bulan_4;
				$t_bulan_5 += $baris->bulan_5;
				$t_bulan_6 += $baris->bulan_6;
				$t_bulan_7 += $baris->bulan_7;
				$t_bulan_8 += $baris->bulan_8;
				$t_bulan_9 += $baris->bulan_9;
				$t_bulan_10 += $baris->bulan_10;
				$t_bulan_11 += $baris->bulan_11;
				$t_bulan_12 += $baris->bulan_12;
				$t_anggaran += $baris->anggaran;
				$t_sisa += $baris->sisa;
				$t_total += $baris->total;

				$show_koefisien = '';
				if($baris->qty!=null && $baris->satuan!=null){
					$show_koefisien .= $baris->qty.' '.$baris->satuan;
				}

				if($baris->koefisien!=null && $baris->satuan_koefisien!=null){
					if($show_koefisien!=''){
						$show_koefisien .= ' X '.$baris->koefisien.' '.$baris->satuan_koefisien;
					}else{
						$show_koefisien .= $baris->koefisien.' '.$baris->satuan_koefisien;
					}
				}

				if($baris->koefisien2!=null && $baris->satuan_koefisien2!=null){
					if($show_koefisien!=''){
						$show_koefisien .= ' X '.$baris->koefisien2.' '.$baris->satuan_koefisien2;
					}else{
						$show_koefisien .= $baris->koefisien2.' '.$baris->satuan_koefisien2;
					}
				}

				if($baris->koefisien3!=null && $baris->satuan_koefisien3!=null){
					if($show_koefisien!=''){
						$show_koefisien .= ' X '.$baris->koefisien3.' '.$baris->satuan_koefisien3;
					}else{
						$show_koefisien .= $baris->koefisien3.' '.$baris->satuan_koefisien3;
					}
				}

				$baris->hasil_koefisien = $show_koefisien;
			}
		}


		$data_spasi = [
			'anggaran'=>'',
			'bulan_1'=>'',
			'bulan_2'=>'',
			'bulan_3'=>'',
			'bulan_4'=>'',
			'bulan_5'=>'',
			'bulan_6'=>'',
			'bulan_7'=>'',
			'bulan_8'=>'',
			'bulan_9'=>'',
			'bulan_10'=>'',
			'bulan_11'=>'',
			'bulan_12'=>'',
			'koefisien'=>'',
			'koefisien2'=>'',
			'koefisien3'=>'',
			'komponen'=>'',
			'komponen_id'=>'',
			'komponen_name'=>'',
			'qty'=>'',
			'rekening'=>'',
			'satuan'=>'',
			'satuan_koefisien'=>null,
			'satuan_koefisien2'=>null,
			'satuan_koefisien3'=>null,
			'sisa'=>'',
			'subtitle'=>'',
			'hasil_koefisien'=>'',
			'total'=>'',
		];

		$data_total = [

			'anggaran'=>$t_anggaran,
			'bulan_1'=>$t_bulan_1,
			'bulan_2'=>$t_bulan_2,
			'bulan_3'=>$t_bulan_3,
			'bulan_4'=>$t_bulan_4,
			'bulan_5'=>$t_bulan_5,
			'bulan_6'=>$t_bulan_6,
			'bulan_7'=>$t_bulan_7,
			'bulan_8'=>$t_bulan_8,
			'bulan_9'=>$t_bulan_9,
			'bulan_10'=>$t_bulan_10,
			'bulan_11'=>$t_bulan_11,
			'bulan_12'=>$t_bulan_12,
			'koefisien'=>'',
			'koefisien2'=>'',
			'koefisien3'=>'',
			'komponen'=>'',
			'komponen_id'=>'',
			'komponen_name'=>'JUMLAH',
			'qty'=>'',
			'rekening'=>'',
			'satuan'=>'',
			'satuan_koefisien'=>null,
			'satuan_koefisien2'=>null,
			'satuan_koefisien3'=>null,
			'sisa'=>$t_sisa,
			'subtitle'=>'',
			'hasil_koefisien'=>'',
			'total'=>$t_total,
		];

		$data_cw = [
			'anggaran'=>$t_anggaran,
			'bulan_1'=>$t_bulan_1+$t_bulan_2+$t_bulan_3+$t_bulan_4,
			'bulan_2'=>-1,
			'bulan_3'=>-1,
			'bulan_4'=>-1,
			'bulan_5'=>$t_bulan_5+$t_bulan_6+$t_bulan_7+$t_bulan_8,
			'bulan_6'=>-1,
			'bulan_7'=>-1,
			'bulan_8'=>-1,
			'bulan_9'=>$t_bulan_9+$t_bulan_10+$t_bulan_11+$t_bulan_12,
			'bulan_10'=>-1,
			'bulan_11'=>-1,
			'bulan_12'=>-1,
			'koefisien'=>'',
			'koefisien2'=>'',
			'koefisien3'=>'',
			'komponen'=>'',
			'komponen_id'=>'',
			'komponen_name'=>'JUMLAH CATURWULAN',
			'qty'=>'',
			'rekening'=>'',
			'satuan'=>'',
			'satuan_koefisien'=>null,
			'satuan_koefisien2'=>null,
			'satuan_koefisien3'=>null,
			'sisa'=>$t_sisa,
			'subtitle'=>'',
			'hasil_koefisien'=>'',
			'total'=>$t_total,
		];

		if($sumberDana=='bopda'){
			if($status_sk=='1'){

				$penerimaan_tahap1 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='1'")->first();
				$penerimaan_tahap2 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='2'")->first();
				$penerimaan_tahap3 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='3'")->first();
				$penerimaan_tahap4 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='4'")->first();
				$penerimaan_tahap5 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='5'")->first();
				$penerimaan_tahap6 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='6'")->first();
				$penerimaan_tahap7 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='7'")->first();
				$penerimaan_tahap8 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='8'")->first();
				$penerimaan_tahap9 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='9'")->first();
				$penerimaan_tahap10 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='10'")->first();
				$penerimaan_tahap11 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='11'")->first();
				$penerimaan_tahap12 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='12'")->first();

				$b_p_1 = (!empty($penerimaan_tahap1)) ? $penerimaan_tahap1->nominal : 0;
				$b_p_2 = (!empty($penerimaan_tahap2)) ? $penerimaan_tahap2->nominal : 0;
				$b_p_3 = (!empty($penerimaan_tahap3)) ? $penerimaan_tahap3->nominal : 0;
				$b_p_4 = (!empty($penerimaan_tahap4)) ? $penerimaan_tahap4->nominal : 0;
				$b_p_5 = (!empty($penerimaan_tahap5)) ? $penerimaan_tahap5->nominal : 0;
				$b_p_6 = (!empty($penerimaan_tahap6)) ? $penerimaan_tahap6->nominal : 0;
				$b_p_7 = (!empty($penerimaan_tahap7)) ? $penerimaan_tahap7->nominal : 0;
				$b_p_8 = (!empty($penerimaan_tahap8)) ? $penerimaan_tahap8->nominal : 0;
				$b_p_9 = (!empty($penerimaan_tahap9)) ? $penerimaan_tahap9->nominal : 0;
				$b_p_10 = (!empty($penerimaan_tahap10)) ? $penerimaan_tahap10->nominal : 0;
				$b_p_11 = (!empty($penerimaan_tahap11)) ? $penerimaan_tahap11->nominal : 0;
				$b_p_12 = (!empty($penerimaan_tahap12)) ? $penerimaan_tahap12->nominal : 0;

			}else{
				$penerimaan_tahap1 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='1'")->first();
				$penerimaan_tahap2 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='2'")->first();

				$b_p_1 = (!empty($penerimaan_tahap1)) ? $penerimaan_tahap1->nominal : 0;
				$b_p_2 = -1;
				$b_p_3 = -1;
				$b_p_4 = -1;
				$b_p_5 = -1;
				$b_p_6 = -1;

				$b_p_7 = (!empty($penerimaan_tahap2)) ? $penerimaan_tahap2->nominal : 0;
				$b_p_8 = -1;
				$b_p_9 = -1;
				$b_p_10 = -1;
				$b_p_11 = -1;
				$b_p_12 = -1;
			}
		}else{
			$penerimaan_tahap1 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='1'")->first();
			$penerimaan_tahap2 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='2'")->first();
			$penerimaan_tahap3 = DB::connection('pgsql')->table('budget2021.penerimaan')->whereRaw("npsn='$npsn' AND kode_dana='$kode_dana' AND tahap='3'")->first();

			$sisa_saldo = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->first();
			$sisa = (!empty($sisa_saldo)) ? $sisa_saldo->penerimaan : 0;

			$b_p_1 = (!empty($penerimaan_tahap1)) ? $penerimaan_tahap1->nominal : 0;
			$b_p_2 = -1;
			$b_p_3 = -1;
			$b_p_4 = -1;

			$b_p_5 = (!empty($penerimaan_tahap2)) ? $penerimaan_tahap2->nominal : 0;
			$b_p_6 = -1;
			$b_p_7 = -1;
			$b_p_8 = -1;

			$b_p_9 = (!empty($penerimaan_tahap3)) ? $penerimaan_tahap3->nominal : 0;
			$b_p_10 = -1;
			$b_p_11 = -1;
			$b_p_12 = -1;

			$b_p_1 += $sisa;
		}

		$penerimaan = [
			'anggaran'=>0,
			'bulan_1'=>$b_p_1,
			'bulan_2'=>$b_p_2,
			'bulan_3'=>$b_p_3,
			'bulan_4'=>$b_p_4,
			'bulan_5'=>$b_p_5,
			'bulan_6'=>$b_p_6,
			'bulan_7'=>$b_p_7,
			'bulan_8'=>$b_p_8,
			'bulan_9'=>$b_p_9,
			'bulan_10'=>$b_p_10,
			'bulan_11'=>$b_p_11,
			'bulan_12'=>$b_p_12,
			'koefisien'=>'',
			'koefisien2'=>'',
			'koefisien3'=>'',
			'komponen'=>'',
			'komponen_id'=>'',
			'komponen_name'=>'PENERIMAAN',
			'qty'=>'',
			'rekening'=>'',
			'satuan'=>'',
			'satuan_koefisien'=>null,
			'satuan_koefisien2'=>null,
			'satuan_koefisien3'=>null,
			'sisa'=>0,
			'subtitle'=>'',
			'hasil_koefisien'=>'',
			'total'=>0,
		];

		array_push($data_kegiatan, $data_spasi, $data_total, $data_cw, $penerimaan);

		$message = 'success';

		return response()->json(compact('message', 'data_kegiatan'), 200);
	}

	function simpan_bulanan(Request $request){
		$kode_kegiatan =  $request->kode_kegiatan;
		$npsn = $request->npsn;
		$sumberDana = $request->sumberDana;
		$kode_komponen = $request->kode_komponen;
		$rekening = $request->rekening;

		$subtitle = str_replace (["'"],["''"],$request->subtitle);

		$bulan = $request->bulan;

		$conn = Set_koneksi::set_koneksi($request);

		$data_update = [
			'bulan_1'=>$bulan[0],
			'bulan_2'=>$bulan[1],
			'bulan_3'=>$bulan[2],
			'bulan_4'=>$bulan[3],
			'bulan_5'=>$bulan[4],
			'bulan_6'=>$bulan[5],
			'bulan_7'=>$bulan[6],
			'bulan_8'=>$bulan[7],
			'bulan_9'=>$bulan[8],
			'bulan_10'=>$bulan[9],
			'bulan_11'=>$bulan[10],
			'bulan_12'=>$bulan[11],
		];

		// '5.05.3.03.016','5.05.3.01.048'

		if($kode_kegiatan=='5.05.3.01.048' || $kode_kegiatan=='5.05.3.03.016' || $kode_kegiatan=='5.05.3.03.122'){
			$data_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.sdm_detail')->whereRaw("npsn='$npsn' AND komponen_id='$kode_komponen' AND sdm_id='$rekening'")->update($data_update);
		}else{
			$data_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='$npsn' AND kode_kegiatan = '$kode_kegiatan' AND komponen_id='$kode_komponen' AND rekening='$rekening' AND subtitle='$subtitle'")->update($data_update);
		}

		$kegiatan = DB::connection($conn['conn_status'])->select("
			SELECT * FROM budget2021.kegiatan WHERE kode_kegiatan='$kode_kegiatan' LIMIT 1
			");
		
		$nama_kegiatan = $kegiatan[0]->nama_kegiatan;

		if($data_kegiatan){
			$message = 'success';
		}else{	
			$message = 'error';
		}

		return response()->json(compact('message','kode_kegiatan','nama_kegiatan'), 200);
	}

	function kunci_alokasi(Request $request){
		$npsn = $request->npsn;

		$sumberDana = $request->sumberDana;

		if($sumberDana=='bopda'){
			$kode = '.3.03.';
		}else{
			$kode = '.3.01.';
		}

		$conn = Set_koneksi::set_koneksi($request);

		$detail_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='$npsn' AND kode_kegiatan LIKE '%$kode%'")->get();

		if($detail_kegiatan->count()!=0){
			$update = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->whereRaw("npsn='$npsn' AND kode_kegiatan LIKE '%$kode%'")->update(['kunci_alokasi'=>'1']);
			if($update){
				$code = '200';
				$message = 'Berhasil dikunci';
			}else{
				$code = '250';
				$message = 'Gagal dikunci';
			}
		}else{
			$message = 'Tidak ada kegiatan yang diinputkan';
			$code = '404';
		}

		return response()->json(compact('code','message'), 200);
	}
}

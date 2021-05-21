<?php

namespace App\Http\Controllers\Kegiatan;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use DB;
use App\Http\Controllers\Controller;


class AnggaranController extends Controller
{
	function cek_pagu(Request $request){
		$npsn = $request->npsn;
		$sumber = $request->sumber;
		if($sumber=='bos'){
			$kode_sumber = '3.01';
		}else{
			$kode_sumber = '3.03';
		}

		$conn = Set_koneksi::set_koneksi($request);

		if(isset($request->kegiatan_awal)){
			// KEGIATAN AWAL
			$message = '200';
			$get_siswa = '';
			$jumlah_rombel = '';
			$jumlah_siswa = '';
			$jumlah_mbr = '';
			$sebulan = '';
			$setahun = '';
		}else{
			// KEGIATAN MURNI
			$get_siswa = DB::connection($conn['conn_jenjang'])->select("
				SELECT kelas,npsn,COUNT(rombel) as jumlah_rombel,SUM(jumlah_siswa) as jumlah_siswa FROM
				(
				SELECT distinct(sek.npsn), sek.nama, sek.jenjang, sek.status, COUNT(s.id_siswa) as jumlah_siswa, (SELECT kecamatan_name FROM PUBLIC.kecamatan WHERE kecamatan_kode = sek.kec_id) AS kecamatan, sek.is_bos, sek.is_bopda,s.kelas,s.rombel
				FROM PUBLIC.sekolah AS sek
				JOIN PUBLIC.siswa AS s ON sek.npsn = s.npsn
				JOIN PUBLIC.master_user AS m ON sek.npsn = m.npsn
				WHERE (s.alumni IS NOT TRUE OR s.alumni IS NULL)AND s.status_siswa = 'Aktif' AND m.user_enable = TRUE AND sek.npsn = '$npsn'
				GROUP BY sek.npsn,s.kelas,s.rombel
				ORDER BY s.kelas ASC,s.rombel ASC
				) as isinya GROUP BY kelas,npsn
				");

			$total = DB::connection($conn['conn_jenjang'])->select("
				SELECT jenjang,COUNT(rombel) as jumlah_rombel,SUM(jumlah_siswa) as jumlah_siswa FROM
				(
				SELECT distinct(sek.npsn), sek.nama, sek.jenjang, sek.status, COUNT(s.id_siswa) as jumlah_siswa, (SELECT kecamatan_name FROM PUBLIC.kecamatan WHERE kecamatan_kode = sek.kec_id) AS kecamatan, sek.is_bos, sek.is_bopda,s.kelas,s.rombel
				FROM PUBLIC.sekolah AS sek
				JOIN PUBLIC.siswa AS s ON sek.npsn = s.npsn
				JOIN PUBLIC.master_user AS m ON sek.npsn = m.npsn
				WHERE (s.alumni IS NOT TRUE OR s.alumni IS NULL)AND s.status_siswa = 'Aktif' AND m.user_enable = TRUE AND sek.npsn = '$npsn'
				GROUP BY sek.npsn,s.kelas,s.rombel
				ORDER BY s.kelas ASC,s.rombel ASC
				) as isinya GROUP BY jenjang
				");

			$jumlah_mbr = 0;

			if(count($get_siswa)==0){
				$get_siswa = [
					[
						'jenjang'=>'Jenjang',
						'jumlah_rombel'=>0,
						'jumlah_siswa'=>0,
						'jumlah_mbr'=>0,
					]
				];
			}else{
				for ($i=0; $i < count($get_siswa); $i++) { 
					$mbr = DB::connection($conn['conn_jenjang'])->select("
						SELECT COUNT(sek.npsn) as jumlah_mbr
						FROM PUBLIC.sekolah AS sek
						JOIN PUBLIC.siswa AS s ON sek.npsn = s.npsn
						JOIN PUBLIC.master_user AS m ON sek.npsn = m.npsn
						WHERE (s.alumni IS NOT TRUE OR s.alumni IS NULL)AND s.status_siswa = 'Aktif' AND m.user_enable = TRUE AND sek.npsn = '".$get_siswa[$i]->npsn."' AND s.kelas='".$get_siswa[$i]->kelas."' AND (s.verified_gakin2=true and s.gakin=true)
						GROUP BY sek.npsn,s.kelas
						");

					$get_siswa[$i]->jumlah_mbr = (!empty($mbr)) ? $mbr[0]->jumlah_mbr : 0;
					// $get_siswa[$i]->jumlah_mbr = rand(0,10);
					$jumlah_mbr += $get_siswa[$i]->jumlah_mbr;
				}
			}


			if(count($total)==0){
				$jumlah_siswa = 0;
				$jumlah_rombel = 0;
			}else{
				$jumlah_siswa = $total[0]->jumlah_siswa;
				$jumlah_rombel = $total[0]->jumlah_rombel;
			}

			$data_jumlah = [
				'jumlah_mbr'=>$jumlah_mbr,
				'jumlah_rombel'=>$jumlah_rombel,
				'jumlah_siswa'=>$jumlah_siswa,
				'kelas'=>'Jumlah',
				'npsn'=>'',
			];

			array_push($get_siswa, $data_jumlah);

			if($conn['jenjang']=='SD'||$conn['jenjang']=='MI'){
				$h_sebulan = (3014667*$jumlah_rombel)-((($jumlah_siswa*1020000)/12));
				$h_setahun = $h_sebulan*12;

				// $sebulan = "(".$jumlah_rombel."*3014667)-(((".$jumlah_siswa."*1020000)/12) = ".number_format($h_sebulan,0,',','.');
				// $setahun = $h_sebulan.'*12 = '.number_format($h_setahun,0,',','.');

				$sebulan = 3014667;
				$setahun = 1020000;
			}else{
				$h_sebulan = (5354656*$jumlah_rombel)-((($jumlah_siswa*1250000)/12));
				$h_setahun = $h_sebulan*12;

				// $sebulan = "(".$jumlah_rombel."*5354656)-(((".$jumlah_siswa."*1250000)/12) = ".number_format($h_sebulan,0,',','.');
				// $setahun = $h_sebulan.'*12 = '.number_format($h_setahun,0,',','.');

				$sebulan = 5354656;
				$setahun = 1250000;
			}


			$pagu = DB::connection($conn['conn_status'])->select("
				SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.03'
				");

			if(!empty($pagu)){
				$message = 'done';
			}else{
				$message = 'undone';
			}
		}


		return response()->json(compact('message','get_siswa','jumlah_rombel','jumlah_siswa','jumlah_mbr','sebulan','setahun'), 200);
	}

	function kunci_pagu(Request $request){
		$npsn = $request->npsn;
		$sumber = $request->sumber;
		if($sumber=='bos'){
			$kode_sumber = '3.01';
		}else{
			$kode_sumber = '3.03';
		}

		$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

		$conn = Set_koneksi::set_koneksi($request);

		$pagu_rows = DB::connection($conn['conn_status'])->select("
			SELECT * FROM budget2021.kunci_bopda WHERE npsn='$npsn'
			");

		$total_mbr = 0;

		$message = '';

		if(!empty($pagu_rows)){
			$message = "Data sudah tersimpan";
		}else{

			$izinkan_simpan = 1;

			$cek_izin = $this->cek_izin($request)->getContent();
			$cek_izin = json_decode($cek_izin,true);

			$izinkan_simpan = $cek_izin['izinkan_simpan'];
			$message = $cek_izin['message'];

			if($izinkan_simpan==1){
				$get_siswa = DB::connection($conn['conn_jenjang'])->select("
					SELECT npsn,kelas,COUNT(rombel) as jumlah_rombel,SUM(jumlah_siswa) as jumlah_siswa FROM
					(
					SELECT distinct(sek.npsn), sek.nama, sek.jenjang, sek.status, COUNT(s.id_siswa) as jumlah_siswa, (SELECT kecamatan_name FROM PUBLIC.kecamatan WHERE kecamatan_kode = sek.kec_id) AS kecamatan, sek.is_bos, sek.is_bopda,s.kelas,s.rombel
					FROM PUBLIC.sekolah AS sek
					JOIN PUBLIC.siswa AS s ON sek.npsn = s.npsn
					JOIN PUBLIC.master_user AS m ON sek.npsn = m.npsn
					WHERE (s.alumni IS NOT TRUE OR s.alumni IS NULL)AND s.status_siswa = 'Aktif' AND m.user_enable = TRUE AND sek.npsn = '$npsn'
					GROUP BY sek.npsn,s.kelas,s.rombel
					ORDER BY s.kelas ASC,s.rombel ASC
					) as isinya GROUP BY kelas,npsn
					");

				$message = 'Tidak terkunci';
				$simpan = true;

				if(!empty($get_siswa)){
					for ($i=0; $i < count($get_siswa); $i++) {

						$mbr = DB::connection($conn['conn_jenjang'])->select("
							SELECT COUNT(sek.npsn) as jumlah_mbr
							FROM PUBLIC.sekolah AS sek
							JOIN PUBLIC.siswa AS s ON sek.npsn = s.npsn
							JOIN PUBLIC.master_user AS m ON sek.npsn = m.npsn
							WHERE (s.alumni IS NOT TRUE OR s.alumni IS NULL)AND s.status_siswa = 'Aktif' AND m.user_enable = TRUE AND sek.npsn = '".$get_siswa[$i]->npsn."' AND s.kelas='".$get_siswa[$i]->kelas."' AND (s.verified_gakin2=true and s.gakin=true)
							GROUP BY sek.npsn,s.kelas
							");

						$jumlah_mbr = (!empty($mbr)) ? $mbr[0]->jumlah_mbr : 0;
						$total_mbr += $jumlah_mbr;

						$baris = [
							'npsn'=>$npsn,
							'kelas'=>$get_siswa[$i]->kelas,
							'jumlah_rombel'=>$get_siswa[$i]->jumlah_rombel,
							'jumlah_siswa'=>$get_siswa[$i]->jumlah_siswa,
							'jumlah_mbr'=>$jumlah_mbr,
							'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
							'insert_ip'=>GettingIP::get_client_ip(),
							'insert_time'=>date('Y-m-d H:i:s'),
							'bopda_perbulan'=>0,
							'bopda_setahun'=>0,
						];

						$simpan = DB::connection($conn['conn_status'])->table('budget2021.kunci_bopda')->insert($baris);
						if($simpan){
							$message = 'Terkunci';
						}else{
							$message = 'Tidak terkunci';
						}
					}
				}

				$total = DB::connection($conn['conn_jenjang'])->select("
					SELECT jenjang,COUNT(rombel) as jumlah_rombel,SUM(jumlah_siswa) as jumlah_siswa,nama,status FROM
					(
					SELECT distinct(sek.npsn), sek.nama, sek.jenjang, sek.status, COUNT(s.id_siswa) as jumlah_siswa, (SELECT kecamatan_name FROM PUBLIC.kecamatan WHERE kecamatan_kode = sek.kec_id) AS kecamatan, sek.is_bos, sek.is_bopda,s.kelas,s.rombel
					FROM PUBLIC.sekolah AS sek
					JOIN PUBLIC.siswa AS s ON sek.npsn = s.npsn
					JOIN PUBLIC.master_user AS m ON sek.npsn = m.npsn
					WHERE (s.alumni IS NOT TRUE OR s.alumni IS NULL)AND s.status_siswa = 'Aktif' AND m.user_enable = TRUE AND sek.npsn = '$npsn'
					GROUP BY sek.npsn,s.kelas,s.rombel,sek.nama,sek.status
					ORDER BY s.kelas ASC,s.rombel ASC
					) as isinya GROUP BY jenjang,nama,status
					");

				if(!empty($total)){
					$jumlah_rombel = $total[0]->jumlah_rombel;
					$jumlah_siswa = $total[0]->jumlah_siswa;

					if($conn['status']=='1'){
						$rekening= '5.1.02.02.01.0013';
						$kode = '5.05.3.03.016';

						$where = "kd.rekening LIKE '$rekening%' AND kd.kode_kegiatan LIKE '%$kode%'";

						$str_komponen = GettingIP::str_komponen_id("rekening LIKE '$rekening%'");

						$where1 = "sdm.komponen_id IN ($str_komponen) AND sdm.kode_kegiatan LIKE '%$kode%'";

						$nilai = DB::connection($conn['conn_status'])->select("SELECT SUM(nilai) as nominal FROM(

							SELECT SUM(nilai) as nilai FROM budget2021.kegiatan_detail as kd WHERE kd.npsn='$npsn' AND $where
							UNION
							SELECT SUM(nominal) as nilai FROM budget2021.sdm as sdm WHERE npsn='$npsn' AND $where1

							) as anggaran
							");

						$h_setahun = ($nilai[0]->nominal!=null) ? $nilai[0]->nominal : 0;
					}else{
						if($conn['jenjang']=='SD'||$conn['jenjang']=='MI'){
							$h_sebulan = (3014667*$jumlah_rombel)-(($jumlah_siswa*1020000)/12);
							$h_setahun = $h_sebulan*12;
						}else{
							$h_sebulan = (5354656*$jumlah_rombel)-(($jumlah_siswa*1250000)/12);
							$h_setahun = $h_sebulan*12;
						}
					}

					// SIMPAN MBR PAGU PERSONAL
					if($conn['jenjang']=='SD'||$conn['jenjang']=='MI'){
						$pengali = 951300+((951300*10)/100);
					}else{
						$pengali = 1151300+((1151300*10)/100);
					}
					
					$nominal_mbr = $pengali*$total_mbr;

					
					// CEK KUAPPAS
					if($sumber=='bopda'){
						if($request->status!='1'){
							$cek_pagu_personal_kua = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("kode_sumber='3.051' AND npsn='$npsn'")->first();
							if(!empty($cek_pagu_personal_kua)){
								if($cek_pagu_personal_kua->penerimaan<$nominal_mbr){
									$nominal_mbr = $cek_pagu_personal_kua->penerimaan;
								}
							}

							$cek_pagu_operasional_kua = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("kode_sumber='3.031' AND npsn='$npsn'")->first();
							if(!empty($cek_pagu_operasional_kua)){
								if($cek_pagu_operasional_kua->penerimaan<$h_setahun){
									$h_setahun = $cek_pagu_operasional_kua->penerimaan;
								}
							}
						}
					}
					// END CEK KUAPPAS

					$data_mbr = [
						'npsn'=>$npsn,
						'nama_sekolah'=>$total[0]->nama,
						'jenjang'=>$conn['jenjang'],
						'status_sekolah'=>$total[0]->status,
						'jumlah_rombel'=>0,
						'jumlah_siswa'=>$total_mbr,
						'penerimaan'=>$nominal_mbr,
						'kode_sumber'=>'3.05',
					];
					$simpan = DB::connection($conn['conn_status'])->table('budget2021.pagu')->insert($data_mbr);
					// END SIMPAN PAGU PERSONAL


					$baris = [
						'npsn'=>$npsn,
						'nama_sekolah'=>$total[0]->nama,
						'jenjang'=>$conn['jenjang'],
						'status_sekolah'=>$total[0]->status,
						'jumlah_rombel'=>$total[0]->jumlah_rombel,
						'jumlah_siswa'=>$total[0]->jumlah_siswa,
						'penerimaan'=>$h_setahun,
						'kode_sumber'=>'3.03',
					];

					$simpan = DB::connection($conn['conn_status'])->table('budget2021.pagu')->insert($baris);
					if($simpan){
						$message = 'Terkunci';
					}else{
						$message = 'Tidak terkunci';
					}
				}
			}
		}

		$pagu = DB::connection($conn['conn_status'])->select("
			SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.03'
			");

		return response()->json(compact('message','pagu','izinkan_simpan'), 200);
	}

	function get_anggaran(Request $request){
		$npsn = $request->npsn;	
		$sumber = $request->sumber;

		$sebulan = '';
		$setahun = '';

		$total_atas = 0;
		$total_bawah = 0;
		$jumlah_rombel = 0;
		$jumlah_siswa = 0;
		$jumlah_mbr = 0;
		$pagu_rows = [];
		$pesan_error = '';

		if($sumber=='bos'){
			$kode_sumber = '3.01';
			$kode = '.3.01.';
			$data = [
				[
					'rekening'=>'5.1',
					'jenis'=>'Belanja Pegawai'
				],
				[
					'rekening'=>'5.1',
					'jenis'=>'Belanja Barang Jasa'
				],
				[
					'rekening'=>'5.2',
					'jenis'=>'Belanja Modal'
				],
			];

			$data_pagu = [
				[
					'jenis'=>'Sisa saldo 2020'
				],
				[
					'jenis'=>'Pagu BOS 2021'
				],
			];
		}else{
			$kode_sumber = '3.03';
			$kode = '.3.03.';
			if($request->status==2){
				$data = [
					[
						'rekening'=>'5.1.',
						'jenis'=>'Pagu Personal'
					],
					[
						'rekening'=>'5.1.',
						'jenis'=>'Pagu Operasional'
					],
				];
			}else{
				$data = [
					[
						'rekening'=>'5.1',
						'jenis'=>'Belanja Pegawai'
					],
					[
						'rekening'=>"'5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007'",
						'jenis'=>'BPJS'
					],
					[
						'rekening'=>'5.1',
						'jenis'=>'Belanja Personal'
					],
				];
			}

			if($request->status==2){
				$data_pagu = [
					[
						'jenis'=>'Pagu Personal 2021'
					],
					[
						'jenis'=>'Pagu Operasional 2021'
					],
				];
			}else{
				$data_pagu = [
					[
						'jenis'=>'Pagu Personal 2021'
					],
					[
						'jenis'=>'Pagu BOPDA 2021'
					],
				];
			}
		}

		$conn = Set_koneksi::set_koneksi($request);

		$message_cek = '';
		// CEK BEDA AWAL DAN MURNI
		$message_personal = '';
		$message_operasional = '';
		if($request->status!='1'){
			if($request->sumber!='bos'){
				if(isset($request->kegiatan_awal)){

				}else{
					$pagu_personal_awal = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.051'")->first();
					$pagu_personal_murni = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.05'")->first();

					$pagu_operasional_awal = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.031'")->first();
					$pagu_operasional_murni = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.03'")->first();
					if(!empty($pagu_personal_murni)){
						if($pagu_personal_awal->jumlah_siswa<$pagu_personal_murni->jumlah_siswa){
							$message_personal = 'Apabila hasil perhitungan pagu pada kegiatan murni melebihi pagu pada kegiatan awal, maka pagu yang digunakan pada kegiatan murni adalah sesuai dengan pagu pada kegiatan awal.';
						}
					}
					if(!empty($pagu_operasional_murni)){
						if($pagu_operasional_awal->jumlah_siswa<$pagu_operasional_murni->jumlah_siswa){
							$message_operasional = 'Apabila hasil perhitungan pagu pada kegiatan murni melebihi pagu pada kegiatan awal, maka pagu yang digunakan pada kegiatan murni adalah sesuai dengan pagu pada kegiatan awal.';
						}
					}
				}
			}
		}

		// HITUNG KEGIATAN
		for ($i=0;$i<count($data);$i++) {

			$rekening = $data[$i]['rekening'];
			
			if($sumber=='bopda'){
				$kode = '.3.03.';
				if($data[$i]['jenis']=='Belanja Pegawai'){
					$kode = '5.05.3.03.016';
				}else if($data[$i]['jenis']=='Belanja Personal'){
					$kode = '7.06.3.03.031';
				}
			}


			if($data[$i]['jenis']=='BPJS'){
				$where = "kd.rekening IN ($rekening) AND kd.kode_kegiatan LIKE '%$kode%'";

				$str_komponen = GettingIP::str_komponen_id("rekening IN ($rekening)");

				$where1 = "sd.komponen_id IN ($str_komponen) AND sd.kode_kegiatan LIKE '%$kode%' AND (status_perangkaan='1' AND lock='1')";
			}else if($data[$i]['jenis']=='Belanja Barang Jasa'){
				if($request->status=='1'){
					$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
					$p_komponen_name = 'Jasa Pelatih Ekskul';
					$p_kode_kegiatan = '5.05.3.01.048';

					$where = "
					kd.rekening LIKE '$rekening%' AND kd.komponen_id NOT IN ('2.1.1.01.01.02.099.004.1.S','2.1.1.01.01.02.099.004.2.S','2.1.1.01.01.02.099.005.A.S') AND (kd.kode_kegiatan LIKE '%$kode%' AND kd.kode_kegiatan!='5.05.3.01.048')
					";

					$str_komponen = GettingIP::str_komponen_id("rekening LIKE '$rekening%' AND komponen_id NOT IN ('2.1.1.01.01.02.099.004.1.S','2.1.1.01.01.02.099.004.2.S','2.1.1.01.01.02.099.005.A.S')");

					$where1 = "((sd.komponen_id IN ($str_komponen) AND (sd.kode_kegiatan LIKE '%$kode%' AND sd.kode_kegiatan!='5.05.3.01.048')) OR (sd.kode_kegiatan='5.05.3.01.048' AND sd.komponen_id='$p_komponen_id' AND sd.komponen_name='$p_komponen_name' AND (sd.status_perangkaan='1' AND lock='1')))";
				}else{
					$where = "
					kd.rekening LIKE '$rekening%' AND kd.komponen_id NOT IN ('2.1.1.01.01.02.099.004.1.S','2.1.1.01.01.02.099.004.2.S','2.1.1.01.01.02.099.005.A.S') AND (kd.kode_kegiatan LIKE '%$kode%' AND kd.kode_kegiatan!='5.05.3.01.048')
					";

					$where1 = "jenis_pegawai='Pelatih' AND kode_dana='$kode_sumber' AND (sd.status_perangkaan='1' AND lock='1')";
				}
			}else if($data[$i]['jenis']=='Belanja Personal'){
				$where = "kd.rekening LIKE '$rekening%' AND kd.kode_kegiatan LIKE '%$kode%'";

				$str_komponen = GettingIP::str_komponen_id("rekening LIKE '$rekening%'");

				$where1 = "sd.komponen_id IN ($str_komponen) AND sd.kode_kegiatan LIKE '%$kode%'";
			}else if($data[$i]['jenis']=='Pagu Personal'){
				if(isset($request->kegiatan_awal)){
					$where = "kd.kode_kegiatan = '7.06.3.03.123'";
				}else{
					$where = "kd.rekening LIKE '$rekening%' AND kd.kode_kegiatan = '7.06.3.03.123'";
				}

				$str_komponen = GettingIP::str_komponen_id("rekening LIKE '$rekening%'");

				$where1 = "sd.komponen_id IN ($str_komponen) AND sd.kode_kegiatan = '7.06.3.03.123'";
			}else if($data[$i]['jenis']=='Pagu Operasional'){
				if(isset($request->kegiatan_awal)){
					$where = "(kd.kode_kegiatan LIKE '%$kode%' AND kd.kode_kegiatan != '7.06.3.03.123')";
				}else{
					$where = "kd.rekening LIKE '$rekening%' AND (kd.kode_kegiatan LIKE '%$kode%' AND kd.kode_kegiatan != '7.06.3.03.123')";
				}

				$str_komponen = GettingIP::str_komponen_id("rekening LIKE '$rekening%'");

				if($request->status=='1'){
					$where1 = "sd.komponen_id IN ($str_komponen) AND (sd.kode_kegiatan LIKE '%$kode%' AND sd.kode_kegiatan != '7.06.3.03.123')";
				}else{
					$where1 = "((sd.komponen_id IN ($str_komponen) AND (sd.kode_kegiatan LIKE '%$kode%' AND sd.kode_kegiatan != '7.06.3.03.123')) OR (sd.kode_dana = '$kode_sumber' AND (status_perangkaan='1' AND lock='1')))";
				}
			}else if($data[$i]['jenis']=='Belanja Pegawai'){
				if($request->status=='1'){
					$where = "kd.komponen_id IN ('2.1.1.01.01.02.099.004.1.S','2.1.1.01.01.02.099.004.2.S','2.1.1.01.01.02.099.005.A.S') AND kd.kode_kegiatan LIKE '%$kode%' AND kd.kode_kegiatan!='5.05.3.01.048'";
					
					$where1 = "sd.komponen_id IN ('2.1.1.01.01.02.099.004.1.S','2.1.1.01.01.02.099.004.2.S','2.1.1.01.01.02.099.005.A.S') AND (sd.kode_kegiatan LIKE '%$kode%') AND (status_perangkaan='1' AND lock='1')";
				}else{
					$where = "kd.komponen_id IN ('2.1.1.01.01.02.099.004.1.S','2.1.1.01.01.02.099.004.2.S','2.1.1.01.01.02.099.005.A.S') AND kd.kode_kegiatan LIKE '%$kode%' AND kd.kode_kegiatan!='5.05.3.01.048'";
					
					$where1 = "jenis_pegawai != 'Pelatih' AND sd.kode_dana = '$kode_sumber' AND (status_perangkaan='1' AND lock='1')";
				}
			}else{
				$where = "kd.rekening LIKE '$rekening%' AND kd.kode_kegiatan LIKE '%$kode%'";

				$str_komponen = GettingIP::str_komponen_id("rekening LIKE '$rekening%'");

				$where1 = "sd.komponen_id IN ($str_komponen) AND sd.kode_kegiatan LIKE '%$kode%'";
			}

			if(isset($request->kegiatan_awal)){
				$nilai = DB::connection($conn['conn_status'])->select("
					SELECT SUM(nominal) as nominal FROM budget2021.kegiatan_awal as kd 
					WHERE kd.npsn='$npsn' AND $where
					");
			}else{
				$nilai = DB::connection($conn['conn_status'])->select("SELECT SUM(nilai) as nominal FROM(
					
					SELECT SUM(nilai) as nilai FROM budget2021.kegiatan_detail as kd 
					WHERE kd.npsn='$npsn' AND $where
					UNION
					SELECT SUM(nominal) as nilai FROM budget2021.sdm AS sd WHERE npsn='$npsn' AND $where1

					) as anggaran
					");
			}

			$anggaran = ($nilai[0]->nominal!=null) ? $nilai[0]->nominal : 0;

			$total_bawah += $anggaran;

			$data[$i]['anggaran'] = $anggaran;
		}

		// HITUNG Pagu
		$total_rombel = 0;
		$total_siswa = 0;
		$total_mbr = 0;
		for ($i=0; $i < count($data_pagu); $i++) {
			if($data_pagu[$i]['jenis']=='Pagu BOS 2021'){
				$pagu = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND (kode_sumber='$kode_sumber' OR kode_sumber='3.09') ORDER BY kode_sumber ASC
					");
				$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan-$pagu[0]->lebih_salur_tahun_lalu : 0;
				$jumlah_siswa = (!empty($pagu)) ? $pagu[0]->jumlah_siswa : 0;
			}else if($data_pagu[$i]['jenis']=='Pagu BOPDA 2021'){
				$kode = '.3.03.';
				$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
				$p_komponen_name = 'Jasa Pelatih Ekskul';
				$p_kode_kegiatan = '5.05.3.01.048';

				$str_komponen = GettingIP::str_komponen_id("rekening IN ('5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007','5.1.02.02.01.0013')");
				
				$where = "kd.rekening IN ('5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007','5.1.02.02.01.0013') AND kd.kode_kegiatan LIKE '%$kode%'";

				$where1 = "sd.komponen_id IN ($str_komponen) AND sd.kode_kegiatan LIKE '%$kode%' AND (status_perangkaan='1' AND lock='1')";

				$nilai = DB::connection($conn['conn_status'])->select("SELECT SUM(nilai) as nominal FROM(

					SELECT SUM(nilai) as nilai FROM budget2021.kegiatan_detail as kd 
					WHERE kd.npsn='$npsn' AND $where
					UNION
					SELECT SUM(nominal) as nilai FROM budget2021.sdm AS sd WHERE npsn='$npsn' AND $where1

					) as anggaran
					");

				$anggaran = ($nilai[0]->nominal!=null) ? $nilai[0]->nominal : 0;


				$pagu = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.03'
					");

				$pagu_mbr = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.05'
					");

				$pagu_rows = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.kunci_bopda WHERE npsn='$npsn'
					");
				

				// $anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;
				$jumlah_rombel = (!empty($pagu)) ? $pagu[0]->jumlah_rombel : 0;
				$jumlah_siswa = (!empty($pagu)) ? $pagu[0]->jumlah_siswa : 0;
				$jumlah_mbr = (!empty($pagu_mbr)) ? $pagu_mbr[0]->jumlah_siswa : 0;

				// if($conn['status']==1){
				if(count($pagu_rows)!=0){
					for ($j=0; $j < count($pagu_rows); $j++) { 
						$total_rombel += $pagu_rows[$j]->jumlah_rombel;
						$total_siswa += $pagu_rows[$j]->jumlah_siswa;
						$total_mbr += $pagu_rows[$j]->jumlah_mbr;
					}
				}

				$spasi = [
					'bopda_perbulan'=> 0,
					'bopda_setahun'=> 0,
					'insert_ip'=> "127.0.0.1",
					'insert_time'=> "",
					'insert_user'=> "",
					'jumlah_mbr'=> "",
					'jumlah_rombel'=> "",
					'jumlah_siswa'=> "",
					'kelas'=> "",
					'npsn'=> "20533198",
				];
				$tot = [
					'bopda_perbulan'=> 0,
					'bopda_setahun'=> 0,
					'insert_ip'=> "127.0.0.1",
					'insert_time'=> "",
					'insert_user'=> "",
					'jumlah_mbr'=> $total_mbr,
					'jumlah_rombel'=> $total_rombel,
					'jumlah_siswa'=> $total_siswa,
					'kelas'=> "Total data",
					'npsn'=> "20533198",
				];

				array_push($pagu_rows, $spasi, $tot);
				// }
			}else if($data_pagu[$i]['jenis']=='Pagu Operasional 2021'){
				if(isset($request->kegiatan_awal)){
					$pagu = DB::connection($conn['conn_status'])->select("
						SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.031'
						");

					$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;

					if($anggaran<0){
						$data_pagu[$i]['jenis'] = 'Pagu Operasional 2021 (Kebutuhan biaya pendidikan '.$request->unit_name.' Telah tercukupi melalui pendanaan BOS)';
						$anggaran = 0;
						$pesan_error = 'Pagu Operasional 2021 (Kebutuhan biaya pendidikan '.$request->unit_name.' Telah tercukupi melalui pendanaan BOS)';
					}else{
					}

					if(count($pagu)==0){
						$message = $request->unit_name.' tidak ada di SK Walikota';
						$pesan_error = '404';
						return response()->json(compact('message','pesan_error'),200);
					}

					$pagu_mbr = DB::connection($conn['conn_status'])->select("
						SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.051'
						");

					$pagu_rows = DB::connection($conn['conn_status'])->select("
						SELECT * FROM budget2021.kunci_bopda WHERE npsn='$npsn'
						");

				}else{
					$pagu = DB::connection($conn['conn_status'])->select("
						SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.03'
						");

					$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;

					if($anggaran<0){
						$data_pagu[$i]['jenis'] = 'Pagu Operasional 2021 (Kebutuhan biaya pendidikan '.$request->unit_name.' Telah tercukupi melalui pendanaan BOS)';
						$anggaran = 0;
						$pesan_error = 'Pagu Operasional 2021 (Kebutuhan biaya pendidikan '.$request->unit_name.' Telah tercukupi melalui pendanaan BOS)';
					}

					$pagu_mbr = DB::connection($conn['conn_status'])->select("
						SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.05'
						");

					$pagu_rows = DB::connection($conn['conn_status'])->select("
						SELECT * FROM budget2021.kunci_bopda WHERE npsn='$npsn'
						");
					if($message_operasional!=''){
						$data_pagu[$i]['jenis'] .= ' (Jumlah Siswa Pagu Awal = '.$pagu_operasional_awal->jumlah_siswa.',Pagu Murni = '.$pagu_operasional_murni->jumlah_siswa.')';
					}
				}

				$spp = GettingIP::cek_spp($request);
				if($spp['message']=='300'){
					$data_pagu[$i]['jenis'] = 'Pagu Operasional 2021 (SPP '.$request->unit_name.' lebih dari 500000, hanya dapat mengisi biaya personal)';
					$anggaran = 0;
					$pesan_error = 'Pagu Operasional 2021 (SPP '.$request->unit_name.' lebih dari 500000, hanya dapat mengisi biaya personal)';
				}else if($spp['message']=='250'){
					$data_pagu[$i]['jenis'] = 'Pagu Operasional 2021 ('.$request->unit_name.' belum melakukan update SPP di SKPBM pada Tahun Ajaran ini)';
					$anggaran = 0;
					$pesan_error = 'Pagu Operasional 2021 ('.$request->unit_name.' belum melakukan update SPP di SKPBM pada Tahun Ajaran ini)';
				}else{
				}

				if(!empty($pagu)){
					$message_cek = 'done';
				}else{
					$message_cek = 'undone';
				}
				$jumlah_rombel = (!empty($pagu)) ? $pagu[0]->jumlah_rombel : 0;
				$jumlah_siswa = (!empty($pagu)) ? $pagu[0]->jumlah_siswa : 0;
				$jumlah_mbr = (!empty($pagu_mbr)) ? $pagu_mbr[0]->jumlah_siswa : 0;
			}else if($data_pagu[$i]['jenis']=='Pagu Personal 2021'){
				if(isset($request->kegiatan_awal)){
					$pagu = DB::connection($conn['conn_status'])->select("
						SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.051'
						");

					$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;
				}else{
					$pagu = DB::connection($conn['conn_status'])->select("
						SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.05'
						");

					$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;

					if($message_personal!=''){
						$data_pagu[$i]['jenis'] .= ' (Jumlah Siswa Pagu Awal = '.$pagu_personal_awal->jumlah_siswa.',Pagu Murni = '.$pagu_personal_murni->jumlah_siswa.')';
					}
				}
				$jumlah_mbr = (!empty($pagu_mbr)) ? $pagu_mbr[0]->jumlah_siswa : 0;
			}else if($data_pagu[$i]['jenis']=='Sisa saldo 2020'){
				$pagu = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.07'
					");

				$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;
			}else{
				$anggaran = 0;
			}

			$total_atas += $anggaran;

			$data_pagu[$i]['anggaran'] = $anggaran;
		}

		if($request->jenjang=='SD'||$request->jenjang=='MI'){
			$sebulan = 3014667;
			$setahun = 1020000;
		}else{
			$sebulan = 5354656;
			$setahun = 1250000;
		}

		return response()->json(compact('data','data_pagu','total_atas','total_bawah','jumlah_siswa','jumlah_mbr','jumlah_rombel','sebulan','setahun','pagu_rows','pesan_error','message_personal','message_operasional','message_cek'), 200);
	}

	function cek_izin(Request $request){
		$npsn = $request->npsn;
		$jenjang = $request->jenjang;
		$conn = Set_koneksi::set_koneksi($request);
		if($conn['status']!='1'){
			if($jenjang=='MI' || $jenjang=='MTS'){
				$izinkan_simpan = 1;
				$message = 'Ijin diberikan';
			}else{

				$cek_teo = DB::connection($conn['conn_status'])->table('budget2021.ignore_ijin')->where('npsn',$npsn)->first();

				if(!empty($cek_teo)){
					$izinkan_simpan = 1;
					$message = 'Ijin diberikan';
				}else{
					$cek_ijin = DB::connection('pgsql_perijinan')->select("SELECT izin_status, tgl_disetujui, tgl_operasional_baru, tgl_berakhir_baru 
						FROM izin_operasional 
						WHERE izin_status = 'setuju' AND npsn = '$npsn'
						ORDER BY tgl_disetujui DESC LIMIT 1
						");

					if(!empty($cek_ijin)){
						$today = date('Y-m-d');
						$baru = $cek_ijin[0]->tgl_operasional_baru;
						$akhir = $cek_ijin[0]->tgl_berakhir_baru;
						$tgl_setuju = $cek_ijin[0]->tgl_disetujui;
						if($today>=$baru && $today<=$akhir){
							$izinkan_simpan = 1;
							$message = 'Ijin diberikan';
						}else{
							if($today>$tgl_setuju){
								$izinkan_simpan = 1;
								$message = 'Ijin diberikan';
							}else{
								$izinkan_simpan = 0;
								$message = 'Ijin operasional sekolah tidak aktif';
							}
						}
					}else{
						$izinkan_simpan = 0;
						$message = 'Ijin operasional sekolah tidak aktif';
					}
				}
			}
		}else{
			$izinkan_simpan = 1;
			$message = 'Ijin diberikan';
		}

		return response()->json(compact('izinkan_simpan','message'), 200);
	}

	function cek_sisa(Request $request){
		$npsn = $request->npsn;
		$sumber = $request->sumber;
		if($sumber=='bos'){
			$kode_sumber = '3.01';
		}else{
			$kode_sumber = '3.03';
		}

		$conn = Set_koneksi::set_koneksi($request);

		$cek_sisa = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->get();

		if($cek_sisa->count()!=0){
			$message = 'sudah ada';
			$code = '200';
		}else{
			$message = 'belum sudah ada';
			$code = '250';
		}

		return response()->json(compact('message','code'), 200);
	}

	function simpan_sisa(Request $request){
		$npsn = $request->npsn;
		$sumber = $request->sumber;
		if($sumber=='bos'){
			$kode_sumber = '3.01';
		}else{
			$kode_sumber = '3.03';
		}

		$conn = Set_koneksi::set_koneksi($request);

		$sisa = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->first();

		if(!empty($sisa)){
			DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.07'")->update(['penerimaan'=>$request->sisa]);
			$message = 'berhasil disimpan';
			$code = '200';
		}else{
			$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

			$insert = [
				'npsn'=>$npsn,
				'nama_sekolah'=>str_replace(["'"], ["\'"], $user->user_name),
				'jenjang'=>$conn['jenjang'],
				'status_sekolah'=>0,
				'jumlah_rombel'=>0,
				'jumlah_siswa'=>0,
				'penerimaan'=>$request->sisa,
				'kode_sumber'=>'3.07',
			];
			DB::connection($conn['conn_status'])->table('budget2021.pagu')->insert($insert);
			$message = 'berhasil disimpan';
			$code = '200';
		}

		return response()->json(compact('message','code'), 200);
	}

	function cek_kegiatan_awal(Request $request){
		$npsn = $request->npsn;
		$conn = Set_koneksi::set_koneksi($request);

		$kegiatan_awal = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_awal')->whereRaw("npsn='$npsn'")->get();
		if($kegiatan_awal->count()!=0){
			$ka_disetujui = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_awal')->whereRaw("npsn='$npsn' AND status_kegiatan='1'")->get();
			if($kegiatan_awal->count()==$ka_disetujui->count()){
				$code = '200';
				$message = 'Terima kasih sudah mengisi kegiatan awal';
			}else{
				$code = '250';
				$message = 'Kegiatan Awal ada yang belum disetujui penyelia';
			}
		}else{
			$code = '250';
			$message = 'Kegiatan Awal belum diisi';
		}

		return response()->json(compact('message','code'), 200);
	}
}

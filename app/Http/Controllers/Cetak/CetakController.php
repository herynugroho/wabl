<?php

namespace App\Http\Controllers\Cetak;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use DB;
use App\Http\Controllers\Controller;


class CetakController extends Controller
{
	function cetak(Request $request){
		$jenis = $request->jenis;
		
		$conn = Set_koneksi::set_koneksi($request);

		if($jenis=='anggaran'){
			return $this->get_anggaran($request);
		}else if($jenis=='kegiatan'){
			return $this->get_kegiatan($request);
		}
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

		if($sumber=='bos'){
			$kode_sumber = '3.01';
			$kode = '.3.01.';
			$data = [
				[
					'rekening'=>'5.1.02.02.01.0013',
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
			$data = [
				[
					'rekening'=>'5.1.02.02.01.0013',
					'jenis'=>'Belanja Pegawai'
				],
				[
					'rekening'=>'aa',
					'jenis'=>'Tambahan Gaji'
				],
				[
					'rekening'=>"'5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007'",
					'jenis'=>'BPJS'
				],
			];

			$data_pagu = [
				[
					'jenis'=>'Pagu Personal 2021'
				],
				[
					'jenis'=>'Pagu BOPDA 2021'
				],
			];
		}

		$conn = Set_koneksi::set_koneksi($request);

		// HITUNG KEGIATAN
		for ($i=0;$i<count($data);$i++) {

			$rekening = $data[$i]['rekening'];
			
			if($sumber=='bopda'){
				$kode = '.3.03.';
				if($data[$i]['jenis']=='Belanja Pegawai'){
					$kode = '5.05.3.03.016';
				}
			}

			if($data[$i]['jenis']=='BPJS'){
				$where = "ssh.rekening IN ($rekening) AND kd.kode_kegiatan LIKE '%$kode%' AND kd.rekening=ssh.rekening";
				$where1 = "ssh.rekening IN ($rekening) AND sdm.kode_kegiatan LIKE '%$kode%'";
			}else if($data[$i]['jenis']=='Belanja Barang Jasa'){
				$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
				$p_komponen_name = 'Jasa Pelatih Ekskul';
				$p_kode_kegiatan = '5.05.3.01.048';

				$where = "ssh.rekening LIKE '$rekening%' AND ssh.rekening!='5.1.02.02.01.0013' AND (kd.kode_kegiatan LIKE '%$kode%' AND kd.kode_kegiatan!='5.05.3.01.048') AND kd.rekening=ssh.rekening";
				$where1 = "(ssh.rekening LIKE '$rekening%' AND ssh.rekening!='5.1.02.02.01.0013' AND (sdm.kode_kegiatan LIKE '%$kode%' AND sdm.kode_kegiatan!='5.05.3.01.048')) OR (sdm.kode_kegiatan='5.05.3.01.048' AND sdm.komponen_id='$p_komponen_id' AND sdm.komponen_name='$p_komponen_name' AND sdm.status_perangkaan='1')";
			}else{
				$where = "ssh.rekening LIKE '$rekening%' AND kd.kode_kegiatan LIKE '%$kode%' AND kd.rekening=ssh.rekening";
				$where1 = "ssh.rekening LIKE '$rekening%' AND sdm.kode_kegiatan LIKE '%$kode%'";
			}

			$nilai = DB::connection($conn['conn_status'])->select("SELECT SUM(nilai) as nominal FROM(
				
				SELECT SUM(nilai) as nilai FROM budget2021.kegiatan_detail as kd LEFT JOIN budget2021.ssh2021 as ssh ON kd.komponen_id=ssh.komponen_id WHERE kd.npsn='$npsn' AND $where
				UNION
				SELECT SUM(nominal) as nilai FROM budget2021.sdm as sdm LEFT JOIN budget2021.ssh2021 as ssh ON sdm.komponen_id=ssh.komponen_id WHERE npsn='$npsn' AND $where1

				) as anggaran
				");

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
					SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND (kode_sumber='$kode_sumber' OR kode_sumber='3.09')
					");
				$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;
				$jumlah_siswa = (!empty($pagu)) ? $pagu[0]->jumlah_siswa : 0;
			}else if($data_pagu[$i]['jenis']=='Pagu BOPDA 2021'){
				$pagu = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.03'
					");

				$pagu_mbr = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.05'
					");

				$pagu_rows = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.kunci_bopda WHERE npsn='$npsn'
					");

				$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;
				$jumlah_rombel = (!empty($pagu)) ? $pagu[0]->jumlah_rombel : 0;
				$jumlah_siswa = (!empty($pagu)) ? $pagu[0]->jumlah_siswa : 0;
				$jumlah_mbr = (!empty($pagu_mbr)) ? $pagu_mbr[0]->jumlah_siswa : 0;

				if($conn['status']==1){
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
				}

				if(!empty($pagu)){
					if($pagu[0]->jenjang=='SD'){
						$sebulan = 3014667;
						$setahun = 1020000;
					}else{
						$sebulan = 5354656;
						$setahun = 1250000;
					}
				}
			}else if($data_pagu[$i]['jenis']=='Pagu Personal 2021'){
				$pagu = DB::connection($conn['conn_status'])->select("
					SELECT * FROM budget2021.pagu WHERE npsn='$npsn' AND kode_sumber='3.05'
					");

				$anggaran = (!empty($pagu)) ? $pagu[0]->penerimaan : 0;
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

		$nama_column_atas = [
			[
				'label'=> 'Jenis',
				'field'=> 'jenis',
			],
			[
				'label'=> 'Anggaran (Rp)',
				'field'=> 'anggaran',
				'type'=> 'number',
			]
		];


		$nama_column_bawah = [
			[
				'label'=> 'Jenis',
				'field'=> 'jenis',
			],
			[
				'label'=> 'Anggaran (Rp)',
				'field'=> 'anggaran',
				'type'=> 'number',
			]
		];

		$nama_rows_atas = $data;
		$nama_rows_bawah = $data_pagu;
		$judul_atas = 'Anggaran Sekolah';
		$judul_bawah = 'Anggaran Pagu Sekolah';

		return response()->json(compact('nama_rows_atas','nama_rows_bawah','total_atas','total_bawah','jumlah_siswa','jumlah_mbr','jumlah_rombel','sebulan','setahun','pagu_rows','nama_column_atas','nama_column_bawah','judul_atas','judul_bawah'), 200);
	}

	function get_kegiatan(Request $request){
		$npsn = $request->npsn;
		$sumberDana = $request->sumber;

		if($sumberDana=='bopda'){
			$kode = '.3.03.';
			$kode_dana = '3.03';
		}else{
			$kode = '.3.01.';
			$kode_dana = '3.01';
		}

		$conn = Set_koneksi::Set_koneksi($request);

		$p_komponen_id = '2.1.1.01.01.01.004.041.A.S';
		$p_komponen_name = 'Jasa Pelatih Ekskul';
		$p_kode_kegiatan = '5.05.3.01.048';

		$data_kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->selectRaw("npsn,status_kegiatan,'' as no,kode_kegiatan as no_kode,nama_kegiatan as uraian,'' as koefisien,'' as pajak,'' as harga,'' as nilai,'' as cw1,'' as cw2,'' as cw3")->where('budget2021.kegiatan.kode_kegiatan','like','%'.$kode.'%')->where('npsn',$npsn)->get();

		if($data_kegiatan->count()!=0){
			$total_nilai = 0;
			$total_cw1 = 0;
			$total_cw2 = 0;
			$total_cw3 = 0;
			foreach ($data_kegiatan as $index => $key) {
				$key->no = ($index+1);
				if($key->no_kode=='5.05.3.03.016' || $key->no_kode=='5.05.3.03.122'){
					$str_komponen = GettingIP::str_komponen_id("rekening IN ('5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007','5.1.02.02.01.0013')");

					if($request->status=='1'){
						$where = "sdm.kode_kegiatan = '5.05.3.03.016' AND sdm.komponen_id IN($str_komponen) ";
					}else{
						$where = "sdm.kode_dana='$kode_dana'";
					}

					$nilai = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("$where AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')")->sum('budget2021.sdm.nominal');


					$key->nilai = $nilai;

					$get_detail = DB::connection($conn['conn_status'])->table('budget2021.sdm')
					->join('budget2021.sdm_detail as sd','sd.sdm_id','sdm.sdm_id')
					->selectRaw("nama_pegawai,bulan,hari,jam,sdm.komponen_id,sdm.npsn,komponen_name,sdm.keterangan,
						bulan_1,bulan_2,bulan_3,bulan_4,bulan_5,bulan_6,bulan_7,bulan_8,bulan_9,bulan_10,bulan_11,bulan_12,
						'' as no,'' as no_kode,'' as uraian,'' as koefisien,'' as pajak,komponen_harga as harga,nominal as nilai,'' as cw1,'' as cw2,'' as cw3")
					->whereRaw("$where AND sdm.npsn='$npsn' AND (sdm.status_perangkaan='1' AND sdm.lock='1')")->orderBy('komponen_name','ASC')->get();

					$temp_get_detail = [];

					if($get_detail->count()!=0){
						$tmp_subtitle = [];
						foreach ($get_detail as $key1) {

							// SET DATA TEMPT
							$tempt_data = [
								'cw1'=>'',
								'cw2'=>'',
								'cw3'=>'',
								'harga'=>'',
								'koefisien'=>'',
								'nilai'=>'',
								'no'=>'',
								'no_kode'=>'',
								'npsn'=>'',
								'pajak'=>'',
								'status_kegiatan'=>'',
								'uraian'=>'<b>'.$key1->komponen_name.'</b>',
							];
							if(count($tmp_subtitle)!=0){
								if(in_array($key1->komponen_name, $tmp_subtitle)){

								}else{
									array_push($tmp_subtitle, $key1->komponen_name);
									array_push($temp_get_detail, $tempt_data);
								}
							}else{
								array_push($tmp_subtitle, $key1->komponen_name);
								array_push($temp_get_detail, $tempt_data);
							}
							// END SET DATA TEMP

							if($key1->komponen_id!=''){
								$komponen = DB::connection('pgsql')->table('budget2021.ssh2021')->where('komponen_id',$key1->komponen_id)->where('komponen_name',$key1->komponen_name)->first();

								if(!empty($komponen)){
									$key1->uraian = $komponen->komponen_id.'<br>'.$key1->nama_pegawai.'('.$key1->keterangan.')';
									
									$total_nilai += $key1->nilai;
									
									$key1->nilai = $key1->nilai;
									$key1->harga = $komponen->komponen_harga_bulat;
									$cw1 = $key1->bulan_1+$key1->bulan_2+$key1->bulan_3+$key1->bulan_4;
									$cw2 = $key1->bulan_5+$key1->bulan_6+$key1->bulan_7+$key1->bulan_8;
									$cw3 = $key1->bulan_9+$key1->bulan_10+$key1->bulan_11+$key1->bulan_12;
									
									$total_cw1 += $cw1;
									$total_cw2 += $cw2;
									$total_cw3 += $cw3;
									
									$key1->cw1 = $cw1;
									$key1->cw2 = $cw2;
									$key1->cw3 = $cw3;

									$koefisien = ($key1->jam!='') ? $key1->jam.' Jam' : '';

									if($koefisien==''){
										$koefisien .= ($key1->hari!='') ? $key1->hari.' Hari' : '';
									}else{
										$koefisien .= ($key1->hari!='') ? ' X '.$key1->hari.' Hari' : '';
									}

									if($koefisien==''){
										$koefisien .= ($key1->bulan!='') ? $key1->bulan.' Bulan' : '';
									}else{
										$koefisien .= ($key1->bulan!='') ? ' X '.$key1->bulan.' Bulan' : '';
									}
									$key1->koefisien = $koefisien;

								}
							}else{
								$key1->uraian = "<b>".$key1->uraian."</b>";
							}
							array_push($temp_get_detail, $key1);
						}
						$get_detail = $temp_get_detail;
						$key->children = $get_detail;
					}else{
						$key->children = [];
					}
				}else if($key->no_kode=='5.05.3.01.048'){
					if($request->status=='1'){
						$nilai = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("
							kode_kegiatan='$p_kode_kegiatan' AND komponen_id='$p_komponen_id' AND komponen_name='$p_komponen_name' AND npsn='$npsn' AND (status_perangkaan='1' AND lock='1')
							")->sum('budget2021.sdm.nominal');

						$get_detail = DB::connection($conn['conn_status'])->table('budget2021.sdm')
						->join('budget2021.sdm_detail as sd','sd.sdm_id','sdm.sdm_id')
						->selectRaw("nama_pegawai,bulan,hari,jam,sdm.komponen_id,sdm.npsn,komponen_name,sdm.keterangan,
							bulan_1,bulan_2,bulan_3,bulan_4,bulan_5,bulan_6,bulan_7,bulan_8,bulan_9,bulan_10,bulan_11,bulan_12,
							'' as no,'' as no_kode,'' as uraian,'' as koefisien,'' as pajak,komponen_harga as harga,nominal as nilai,'' as cw1,'' as cw2,'' as cw3")
						->whereRaw("
							kode_kegiatan='$p_kode_kegiatan' AND sdm.komponen_id='$p_komponen_id' AND komponen_name='$p_komponen_name' AND sdm.npsn='$npsn' AND (status_perangkaan='1' AND lock='1') AND (sd.komponen_id=sdm.komponen_id)
							")->get();
					}else{
						$nilai = DB::connection($conn['conn_status'])->table('budget2021.sdm')->whereRaw("kode_dana='$kode_dana' AND npsn='$npsn' AND (status_perangkaan='1' AND lock='1')
							")->sum('budget2021.sdm.nominal');

						$get_detail = DB::connection($conn['conn_status'])->table('budget2021.sdm')
						->join('budget2021.sdm_detail as sd','sd.sdm_id','sdm.sdm_id')
						->selectRaw("nama_pegawai,bulan,hari,jam,sdm.komponen_id,sdm.npsn,komponen_name,sdm.keterangan,
							bulan_1,bulan_2,bulan_3,bulan_4,bulan_5,bulan_6,bulan_7,bulan_8,bulan_9,bulan_10,bulan_11,bulan_12,
							'' as no,'' as no_kode,'' as uraian,'' as koefisien,'' as pajak,komponen_harga as harga,nominal as nilai,'' as cw1,'' as cw2,'' as cw3")
						->whereRaw("kode_dana='$kode_dana' AND sdm.npsn='$npsn' AND (status_perangkaan='1' AND lock='1') AND (sd.komponen_id=sdm.komponen_id)
							")->get();
					}

					$key->nilai = $nilai;

					$temp_get_detail = [];

					if($get_detail->count()!=0){
						$tmp_subtitle = [];
						foreach ($get_detail as $key1) {

							// SET DATA TEMPT
							$tempt_data = [
								'cw1'=>'',
								'cw2'=>'',
								'cw3'=>'',
								'harga'=>'',
								'koefisien'=>'',
								'nilai'=>'',
								'no'=>'',
								'no_kode'=>'',
								'npsn'=>'',
								'pajak'=>'',
								'status_kegiatan'=>'',
								'uraian'=>'<b>'.$key1->komponen_name.'</b>',
							];
							if(count($tmp_subtitle)!=0){
								if(in_array($key1->komponen_name, $tmp_subtitle)){

								}else{
									array_push($tmp_subtitle, $key1->komponen_name);
									array_push($temp_get_detail, $tempt_data);
								}
							}else{
								array_push($tmp_subtitle, $key1->komponen_name);
								array_push($temp_get_detail, $tempt_data);
							}
							// END SET DATA TEMP

							if($key1->komponen_id!=''){
								$komponen = DB::connection('pgsql')->table('budget2021.ssh2021')->where('komponen_id',$key1->komponen_id)->where('komponen_name',$key1->komponen_name)->first();

								if(!empty($komponen)){
									$key1->uraian = $komponen->komponen_id.'<br>'.$key1->nama_pegawai.'('.$key1->keterangan.')';
									
									$total_nilai += $key1->nilai;
									
									$key1->nilai = $key1->nilai;
									$key1->harga = $komponen->komponen_harga_bulat;
									$cw1 = $key1->bulan_1+$key1->bulan_2+$key1->bulan_3+$key1->bulan_4;
									$cw2 = $key1->bulan_5+$key1->bulan_6+$key1->bulan_7+$key1->bulan_8;
									$cw3 = $key1->bulan_9+$key1->bulan_10+$key1->bulan_11+$key1->bulan_12;
									
									$total_cw1 += $cw1;
									$total_cw2 += $cw2;
									$total_cw3 += $cw3;
									
									$key1->cw1 = $cw1;
									$key1->cw2 = $cw2;
									$key1->cw3 = $cw3;

									$koefisien = ($key1->jam!='') ? $key1->jam.' Jam' : '';

									if($koefisien==''){
										$koefisien .= ($key1->hari!='') ? $key1->hari.' Hari' : '';
									}else{
										$koefisien .= ($key1->hari!='') ? ' X '.$key1->hari.' Hari' : '';
									}

									if($koefisien==''){
										$koefisien .= ($key1->bulan!='') ? $key1->bulan.' Bulan' : '';
									}else{
										$koefisien .= ($key1->bulan!='') ? ' X '.$key1->bulan.' Bulan' : '';
									}
									$key1->koefisien = $koefisien;

								}
							}else{
								$key1->uraian = "<b>".$key1->uraian."</b>";
							}

							array_push($temp_get_detail, $key1);
						}
						$get_detail = $temp_get_detail;
						$key->children = $get_detail;
					}else{
						$key->children = [];
					}
				}else{
					$nilai = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->where('npsn',$key->npsn)->where('budget2021.kegiatan_detail.kode_kegiatan',$key->no_kode)->sum('budget2021.kegiatan_detail.nilai');

					$key->nilai = $nilai;

					$get_detail = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->selectRaw("npsn,komponen_id,rekening,qty,koefisien as koefisien1,satuan_koefisien,koefisien2,satuan_koefisien2,koefisien3,satuan_koefisien3,pajak,
						bulan_1,bulan_2,bulan_3,bulan_4,bulan_5,bulan_6,bulan_7,bulan_8,bulan_9,bulan_10,bulan_11,bulan_12,
						'' as no,'' as no_kode,subtitle as uraian,'' as koefisien,'' as harga,nilai,'' as cw1,'' as cw2,'' as cw3")->where('npsn',$key->npsn)->where('budget2021.kegiatan_detail.kode_kegiatan',$key->no_kode)->orderByRaw("subtitle asc,nilai desc")->get();
					if($get_detail->count()!=0){
						foreach ($get_detail as $key1) {
							if($key1->komponen_id!=''){
								$komponen = DB::connection('pgsql')->table('budget2021.ssh2021')->where('komponen_id',$key1->komponen_id)->where('rekening',$key1->rekening)->first();

								if(!empty($komponen)){
									$key1->uraian = $komponen->komponen_id.'<br>'.$komponen->komponen_name;
									
									$total_nilai += $key1->nilai;
									
									$key1->nilai = $key1->nilai;
									$key1->harga = $komponen->komponen_harga_bulat;
									$cw1 = $key1->bulan_1+$key1->bulan_2+$key1->bulan_3+$key1->bulan_4;
									$cw2 = $key1->bulan_5+$key1->bulan_6+$key1->bulan_7+$key1->bulan_8;
									$cw3 = $key1->bulan_9+$key1->bulan_10+$key1->bulan_11+$key1->bulan_12;
									
									$total_cw1 += $cw1;
									$total_cw2 += $cw2;
									$total_cw3 += $cw3;
									
									$key1->cw1 = $cw1;
									$key1->cw2 = $cw2;
									$key1->cw3 = $cw3;


									$koefisien = $key1->qty.' '.$komponen->satuan;
									$koefisien .= ($key1->satuan_koefisien!='') ? ' X '.$key1->koefisien1.' '.$key1->satuan_koefisien : '';
									$koefisien .= ($key1->satuan_koefisien2!='') ? ' X '.$key1->koefisien2.' '.$key1->satuan_koefisien2 : '';
									$koefisien .= ($key1->satuan_koefisien3!='') ? ' X '.$key1->koefisien3.' '.$key1->satuan_koefisien3 : '';
									$key1->koefisien = $koefisien;

								}
							}else{
								$key1->uraian = "<b>".$key1->uraian."</b>";
							}
						}
						$key->children = $get_detail;
					}else{
						$key->children = [];
					}
				}


				if($key->status_kegiatan==null){
					$key->status_kegiatan = '<span><b-badge>POSISI ENTRY</b-badge></span>';
				}else if($key->status_kegiatan==0){
					$key->status_kegiatan = '<span><b-badge variant="primary">KIRIM PENYELIA</b-badge></span>';
				}else{
					$key->status_kegiatan = 'Asik';
				}
			}
		}


		$nama_ks = '';
		$nip_ks = '';
		$nik_ks = '';
		$alamat_ks = '';
		$nama_bendahara = '';
		$nama_komite = '';

		$data_ks = DB::connection($conn['conn_status'])->table('public.detail_kepala_sekolah_unit_kerja as ks')->where('unit_id',$npsn)->orderBy('periode_akhir_kepala_sekolah','DESC')->first();
		if(!empty($data_ks)){
			$nama_ks = $data_ks->nama_kepala_sekolah;
			$nip_ks = $data_ks->nip_kepala_sekolah;
			$nik_ks = $data_ks->ktp_kepala_sekolah;
			$alamat_ks = $data_ks->alamat_kepala_sekolah.', '.$data_ks->kecamatan_kepala_sekolah;
		}

		if($sumberDana=='bopda'){
			$data_bendahara = DB::connection($conn['conn_status'])->table('public.detail_bendahara_unit_kerja as ks')->selectRaw("nama_bendahara as nama_bendahara")->where('unit_id',$npsn)->orderBy('periode_akhir_bendahara','DESC')->first();
		}else{
			$data_bendahara = DB::connection($conn['conn_status'])->table('public.detail_bendahara_bos_unit_kerja as ks')->selectRaw("nama_bendahara_bos as nama_bendahara")->where('unit_id',$npsn)->orderBy('periode_akhir_bendahara_bos','DESC')->first();
		}

		if(!empty($data_bendahara)){
			$nama_bendahara = $data_bendahara->nama_bendahara;
		}

		$data_komite = DB::connection($conn['conn_status'])->table('public.detail_komite_unit_kerja as ks')->where('unit_id',$npsn)->orderBy('periode_akhir_ketua_komite_sekolah','DESC')->first();
		if(!empty($data_komite)){
			$nama_komite = $data_komite->nama_ketua_komite_sekolah;
		}

		$data_sekolah = DB::connection($conn['conn_status'])->table('public.detail_unit_kerja as ks')->where('unit_id',$npsn)->first();
		$desa_sekolah = '';
		if(!empty($data_sekolah)){
			$desa_sekolah = strtoupper($data_sekolah->nama_desa.'/ '.$data_sekolah->nama_kecamatan);
		}

		$total_nilai = $total_nilai;
		$total_cw1 = $total_cw1;
		$total_cw2 = $total_cw2;
		$total_cw3 = $total_cw3;

		$message = 'success';
		$nama_rows_atas = $data_kegiatan;
		$tanggal = date('d-m-Y');

		return response()->json(compact('message','nama_rows_atas','total_nilai','total_cw1','total_cw2','total_cw3','tanggal','nama_ks','nip_ks','nik_ks','alamat_ks','nama_bendahara','nama_komite','desa_sekolah'), 200);
	}
}

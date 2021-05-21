<?php

namespace App\Http\Controllers\Realisasi;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use App\Http\Libraries\Konversi;
use DB;
use App\Http\Controllers\Controller;


class RealisasiController extends Controller
{
	function get_subtitle(Request $request){
		$conn = Set_koneksi::Set_koneksi($request);
		$npsn = $request->npsn;
		$kode_kegiatan = $request->kode_kegiatan;

		if($kode_kegiatan=='5.05.3.01.048'){
			$subtitle = DB::connection($conn['conn_status'])->table('budget2021.sdm')->selectRaw("komponen_id")->whereRaw("
				npsn='$npsn' AND jenis_pegawai='Pelatih' AND (status_perangkaan='1' AND lock='1')
				")->groupBy('komponen_id')->get();
			if($subtitle->count()!=0){
				foreach ($subtitle as $key => $value) {
					$ssh = DB::connection('pgsql')->table('budget2021.ssh2021 as ssh')->where('komponen_id',$value->komponen_id)->first();

					$value->text = (!empty($ssh)) ? $ssh->komponen_name: '';
					$value->value = (!empty($ssh)) ? $ssh->komponen_id: '';
				}
			}
		}else if($kode_kegiatan=='5.05.3.03.016'){
			$subtitle = DB::connection($conn['conn_status'])->table('budget2021.sdm')->selectRaw("komponen_id")->whereRaw("
				npsn='$npsn' AND jenis_pegawai!='Pelatih' AND (status_perangkaan='1' AND lock='1')
				")->groupBy('komponen_id')->get();
			if($subtitle->count()!=0){
				foreach ($subtitle as $key => $value) {
					$ssh = DB::connection('pgsql')->table('budget2021.ssh2021 as ssh')->where('komponen_id',$value->komponen_id)->first();

					$value->text = (!empty($ssh)) ? $ssh->komponen_name: '';
					$value->value = (!empty($ssh)) ? $ssh->komponen_id: '';
				}
			}
		}else if($kode_kegiatan=='5.05.3.03.122'){
			$subtitle = DB::connection($conn['conn_status'])->table('budget2021.sdm')->selectRaw("komponen_id")->whereRaw("
				npsn='$npsn' AND jenis_pegawai='Pelatih' AND (status_perangkaan='1' AND lock='1')
				")->groupBy('komponen_id')->get();
			if($subtitle->count()!=0){
				foreach ($subtitle as $key => $value) {
					$ssh = DB::connection('pgsql')->table('budget2021.ssh2021 as ssh')->where('komponen_id',$value->komponen_id)->first();

					$value->text = (!empty($ssh)) ? $ssh->komponen_name: '';
					$value->value = (!empty($ssh)) ? $ssh->komponen_id: '';
				}
			}
		}else{
			$subtitle = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail')->selectRaw("subtitle as text,subtitle as value")->whereRaw("kode_kegiatan='$kode_kegiatan' AND npsn='$npsn'")->groupBy('subtitle')->get();
		}

		return response()->json(compact('subtitle'), 200);
	}

	function get_komponen(Request $request){
		$conn = Set_koneksi::Set_koneksi($request);
		$npsn = $request->npsn;
		$kode_kegiatan = $request->kode_kegiatan;
		$subtitle = $request->subtitle;

		if($kode_kegiatan=='5.05.3.01.048'){
			$komponen = $this->pelatih($request);
		}else if($kode_kegiatan=='5.05.3.03.016' || $kode_kegiatan=='5.05.3.03.122'){
			$komponen = $this->honorer($request);
		}else{
			$komponen = $this->barjas($request);
		}

		return response()->json(compact('komponen'), 200);
	}

	function barjas(Request $request){
		$conn = Set_koneksi::Set_koneksi($request);
		$npsn = $request->npsn;
		$kode_kegiatan = $request->kode_kegiatan;
		$subtitle = $request->subtitle;

		$komponen = DB::connection($conn['conn_status'])->table('budget2021.kegiatan_detail as kd')
		->selectRaw("komponen_id,nilai,qty,pajak,koefisien,satuan_koefisien,koefisien2,satuan_koefisien2,koefisien3,satuan_koefisien3")
		->whereRaw("komponen_id IS NOT null AND npsn='$npsn' AND kode_kegiatan='$kode_kegiatan' AND subtitle='$subtitle'")
		->orderBy('komponen_id','ASC')
		->get();

		if($komponen->count()!=0){
			foreach ($komponen as $key => $value) {
				$ssh = DB::connection('pgsql')->table('budget2021.ssh2021 as ssh')->where('komponen_id',$value->komponen_id)->first();
				$value->nama_komponen = (!empty($ssh)) ? $ssh->komponen_name : '';
				$value->satuan = (!empty($ssh)) ? $ssh->satuan : '';
				$value->harga_satuan = (!empty($ssh)) ? $ssh->komponen_harga_bulat : 0;

				$koefisien_all = str_replace(['.'], [','], $value->qty).' '.$value->satuan;
				$total_koefisien = (double)$value->qty;
				if($value->satuan_koefisien!='' || $value->koefisien>0){
					$koefisien_all .= ' X '.str_replace(['.'], [','], $value->koefisien).' '.$value->satuan_koefisien;
					$total_koefisien *= (double)$value->koefisien;
				}

				if($value->satuan_koefisien2!='' || $value->koefisien2>0){
					$koefisien_all .= ' X '.str_replace(['.'], [','], $value->koefisien2).' '.$value->satuan_koefisien2;
					$total_koefisien *= (double)$value->koefisien2;
				}

				if($value->satuan_koefisien3!='' || $value->koefisien3>0){
					$koefisien_all .= ' X '.str_replace(['.'], [','], $value->koefisien3).' '.$value->satuan_koefisien3;
					$total_koefisien *= (double)$value->koefisien3;
				}

				$value->nilai = number_format($value->nilai,0,',','.');
				$value->koefisien_all = $koefisien_all;
				$value->total_koefisien = str_replace(['.'], [','], $total_koefisien);
			}
		}

		return $komponen;
	}

	function pelatih(Request $request){
		$conn = Set_koneksi::Set_koneksi($request);
		$npsn = $request->npsn;
		$kode_kegiatan = $request->kode_kegiatan;
		$subtitle = $request->subtitle;

		$komponen = DB::connection($conn['conn_status'])->table('budget2021.sdm')
		->join('budget2021.sdm_detail as sd','sd.sdm_id','sdm.sdm_id')
		->selectRaw("sdm.sdm_id,sdm.komponen_id,sdm.nama_pegawai,sdm.jam,sdm.hari,sdm.bulan,sdm.nominal")
		->whereRaw("
			sdm.npsn='$npsn' AND sdm.jenis_pegawai='Pelatih' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
			AND sdm.komponen_id='$subtitle'
			")->get();
		if($komponen->count()!=0){
			foreach ($komponen as $key => $value) {
				$value->kid = $value->komponen_id;
				$value->komponen_id = $value->sdm_id;
				$value->nama_komponen = $value->nama_pegawai;
				$value->nilai = number_format($value->nominal,0,',','.');
				$value->pajak = '';

				$koefisien = '';
				$total_koefisien = (double)1;
				if($value->jam!='' || $value->jam!=0){
					if($koefisien!=''){
						$koefisien .= ' X '.$value->jam.' Jam';
					}else{
						$koefisien .= $value->jam.' Jam';
					}
					$total_koefisien *= (double)$value->jam;
				}
				if($value->hari!='' || $value->hari!=0){
					if($koefisien!=''){
						$koefisien .= ' X '.$value->hari.' Hari';
					}else{
						$koefisien .= $value->hari.' Hari';
					}
					$total_koefisien *= (double)$value->hari;
				}
				if($value->bulan!='' || $value->bulan!=0){
					if($koefisien!=''){
						$koefisien .= ' X '.$value->bulan.' Bulan';
					}else{
						$koefisien .= $value->bulan.' Bulan';
					}
					$total_koefisien *= (double)$value->bulan;
				}

				$value->koefisien_all = $koefisien;
				$value->total_koefisien = $total_koefisien;
			}
		}

		return $komponen;
	}

	function honorer(Request $request){
		$conn = Set_koneksi::Set_koneksi($request);
		$npsn = $request->npsn;
		$kode_kegiatan = $request->kode_kegiatan;
		$subtitle = $request->subtitle;

		$komponen = DB::connection($conn['conn_status'])->table('budget2021.sdm')
		->join('budget2021.sdm_detail as sd','sd.sdm_id','sdm.sdm_id')
		->selectRaw("sdm.sdm_id,sdm.komponen_id,sdm.nama_pegawai,sdm.jam,sdm.hari,sdm.bulan,sdm.nominal")
		->whereRaw("
			sdm.npsn='$npsn' AND sdm.jenis_pegawai!='Pelatih' AND (sdm.status_perangkaan='1' AND sdm.lock='1')
			AND sdm.komponen_id='$subtitle'
			")->get();
		if($komponen->count()!=0){
			foreach ($komponen as $key => $value) {
				$value->kid = $value->komponen_id;
				$value->komponen_id = $value->sdm_id;
				$value->nama_komponen = $value->nama_pegawai;
				$value->nilai = number_format($value->nominal,0,',','.');
				$value->pajak = '';

				$koefisien = '';
				$total_koefisien = (double)1;
				if($value->jam!='' || $value->jam!=0){
					if($koefisien!=''){
						$koefisien .= ' X '.$value->jam.' Jam';
					}else{
						$koefisien .= $value->jam.' Jam';
					}
					$total_koefisien *= (double)$value->jam;
				}
				if($value->hari!='' || $value->hari!=0){
					if($koefisien!=''){
						$koefisien .= ' X '.$value->hari.' Hari';
					}else{
						$koefisien .= $value->hari.' Hari';
					}
					$total_koefisien *= (double)$value->hari;
				}
				if($value->bulan!='' || $value->bulan!=0){
					if($koefisien!=''){
						$koefisien .= ' X '.$value->bulan.' Bulan';
					}else{
						$koefisien .= $value->bulan.' Bulan';
					}
					$total_koefisien *= (double)$value->bulan;
				}

				$value->koefisien_all = $koefisien;
				$value->total_koefisien = $total_koefisien;
			}
		}

		return $komponen;
	}

	function kirim_komponen(Request $request){
		$conn = Set_koneksi::Set_koneksi($request);
		$npsn = $request->npsn;
		$kode_kegiatan = $request->kode_kegiatan;
		$komponen = $request->komponen;
		$kegiatan = DB::connection($conn['conn_status'])->table('budget2021.kegiatan')->where('kode_kegiatan',$kode_kegiatan)->first();

		if(count($komponen)!=0){
			$insert_master_pekerjaan = [
				'created_at'=>null,
				'created_by'=>null,
				'id_market_place'=>null,
				'jenis_pekerjaan'=>null,
				'jenis_pembayaran'=>null,
				'jenjang'=>null,
				'judul_pekerjaan'=>null,
				'jumlah_transfer'=>null,
				'kode_kegiatan'=>$kode_kegiatan,
				'kode_pemesanan_siplah'=>null,
				'kode_referensi'=>null,
				'link_kwitansi'=>null,
				'link_nota'=>null,
				'link_nota_kwitansi'=>null,
				'link_spt_spk'=>null,
				'nama_kegiatan'=>null,
				'nama_market_place'=>null,
				'nama_penyedia'=>null,
				'no_bku'=>null,
				'no_nota'=>null,
				'status'=>null,
				'status_approve'=>null,
				'tanggal_pelaksanaan'=>null,
				'tanggal_pelunasan'=>null,
				'transfer_ke_market_place'=>null,
				'unit_id'=>$npsn,
			];

			$insert = DB::connection($conn['conn_status'])->table('budget2021.master_pekerjaan')->insert($insert_master_pekerjaan);

			$master_pekerjaan = DB::connection($conn['conn_status'])->table('budget2021.master_pekerjaan')->whereRaw("kode_kegiatan = '$kode_kegiatan' AND unit_id = '$npsn'")->orderBy('kode_pekerjaan','DESC')->first();

			for ($i=0; $i < count($komponen); $i++) { 
				$baris = $komponen[$i];
				if(isset($baris['check_komponen_id'])){
					$ssh = DB::connection('pgsql')->table('budget2021.ssh2021 as ssh')->where('komponen_id',$baris['komponen_id'])->first();

					$insert_detail_pekerjaan = [
						'bulan_absen'=> null,
						'created_at'=> null,
						'created_by'=> null,
						'detail_komponen'=> null,
						'detail_no'=> 0,
						'id_tenaga'=> null,
						'kode_kegiatan'=> $kode_kegiatan,
						'kode_pekerjaan'=> $master_pekerjaan->kode_pekerjaan,
						'kode_referensi'=> null,
						'komponen_harga'=> null,
						'komponen_hasil_kali'=> null,
						'komponen_hasil_kali_non_pajak'=> null,
						'komponen_id'=> $baris['komponen_id'],
						'komponen_name'=> $ssh->komponen_name,
						'merk_barang'=> null,
						'nama_kegiatan'=> $kegiatan->nama_kegiatan,
						'pajak'=> null,
						'phh_22'=> null,
						'pph_21'=> null,
						'pph_23'=> null,
						'ptkp'=> null,
						'rekening_code'=> null,
						'satuan'=> null,
						'status_approve'=> null,
						'tanggal_pajak'=> null,
						'unit_id'=> $npsn,
						'volume'=> null,
					];
					$detail_pekerjaan = DB::connection($conn['conn_status'])->table('budget2021.pekerjaan_detail')->insert($insert_detail_pekerjaan);
				}
			}
		}
	}

	function get_penyedia(Request $request){
		$penyedia = DB::connection('pgsql')->table('budget2021.master_penyedia')
		->selectRaw("nama_penyedia as text, id_penyedia as value")
		->orderBy('nama_penyedia','ASC')
		->limit(1000)->get();

		$marketplace = DB::connection('pgsql')->table('budget2021.market_place_siplah')
		->selectRaw("nama_market_place as text,id_market_place as value")
		->orderBy('nama_market_place')->get();

		return response()->json(compact('penyedia','marketplace'), 200);
	}
}

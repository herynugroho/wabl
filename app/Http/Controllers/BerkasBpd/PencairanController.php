<?php

namespace App\Http\Controllers\BerkasBpd;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use App\Http\Libraries\List_berkas;
use DB;
use App\Http\Controllers\Controller;


class PencairanController extends Controller
{
	function list_berkas(Request $request){
		$conn = Set_koneksi::set_koneksi($request);

		$npsn = $request->npsn;

		// PERTAMA
		$berkas_1 = List_berkas::get_list($request,"jenis_berkas IN ('18','19','20','21','22','23')");

		$bulan_now = date('m');

		return response()->json(compact('berkas_1','bulan_now'), 200);
	}

	function simpan_nomor(Request $request){
		$conn = Set_koneksi::set_koneksi($request);
		$npsn = $request->npsn;
		$jenis_berkas = $request->jenis_berkas;

		$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

		$nomor = DB::connection($conn['conn_status'])->table('budget2021.nomor_surat')->whereRaw("npsn='$npsn' AND jenis_berkas='".$jenis_berkas."'")->first();

		if(!empty($nomor)){
			$data = [
				'npsn'=>$npsn,
				'nomor_surat'=>'',
				'jenis_berkas'=>$jenis_berkas,
				'update_nomor'=>null,
				'update_user'=>null,
				'update_ip'=>null,
			];

			$simpan = DB::connection($conn['conn_status'])->table('budget2021.nomor_surat')->whereRaw("npsn='$npsn' AND jenis_berkas='".$jenis_berkas."'")->update($data);
		}else{
			$data = [
				'npsn'=>$npsn,
				'nomor_surat'=>'',
				'jenis_berkas'=>$jenis_berkas,
				'file_upload'=>null,
				'insert_nomor'=>date('Y-m-d H:i:s'),
				'insert_user'=>str_replace(["'"], ["\'"], $user->user_name),
				'insert_ip'=>GettingIP::get_client_ip(),
				'update_nomor'=>null,
				'update_user'=>null,
				'update_ip'=>null,
				'upload_file'=>null,
				'upload_user'=>null,
				'upload_ip'=>null,
				'update_file'=>null,
				'update_file_user'=>null,
				'update_file_ip'=>null,
			];

			$simpan = DB::connection($conn['conn_status'])->table('budget2021.nomor_surat')->insert($data);
		}

		if($simpan){
			$message = "Berhasil disimpan";
			$code = "200";
		}else{
			$message = "Gagal disimpan";
			$code = "250";
		}

		return response()->json(compact('message','code'), 200);
	}

	function generate_format(Request $request){
		$conn = Set_koneksi::set_koneksi($request);
		$qrcode = '';

		$npsn = $request->npsn;
		$jenis = $request->jenis_berkas;

		$user = Master::selectRaw("user_id,user_name")->where('user_id','=',$npsn)->first();

		$berkas = DB::connection($conn['conn_status'])->table('budget2021.master_berkas')->whereRaw("jenis_berkas='$jenis'")->first();

		$qrcode = $berkas->judul_berkas.', '.$npsn.', AKTIF';

		$get_nomor_surat = DB::connection($conn['conn_status'])->table('budget2021.nomor_surat')->whereRaw("jenis_berkas='$jenis' AND npsn='$npsn'")->first();
		$nomor_surat = '';
		$tanggal = date('d-m-Y');  //PENTING
		if(!empty($get_nomor_surat)){
			$nomor_surat = $get_nomor_surat->nomor_surat;  //PENTING
			$tanggal = date('d-m-Y',strtotime($get_nomor_surat->insert_nomor));  //PENTING
		}

		$npsn = $user->user_id;  //PENTING
		$nama_sekolah = $user->user_name;  //PENTING

		// ================================
		// UMUM
		// ================================
		$nama_ks = '';
		$alamat_ks = '';
		$alamat_sekolah = '';
		$nip_ks = '';
		$nik_ks = '';
		$nama_komite = '';
		$nama_bendahara = '';
		$nama_yayasan = '';
		$alamat_yayasan = '';
		$periode = 'Semester I';
		$header_anggaran = 'Target Rencana';
		$list_data_siswa = '';

		$data_ks = DB::connection($conn['conn_status'])->table('public.detail_kepala_sekolah_unit_kerja as ks')->where('unit_id',$npsn)->orderBy('periode_akhir_kepala_sekolah','DESC')->first();
		if(!empty($data_ks)){
			$nama_ks = $data_ks->nama_kepala_sekolah;
			$nip_ks = $data_ks->nip_kepala_sekolah;
			$nik_ks = $data_ks->ktp_kepala_sekolah;
			$alamat_ks = $data_ks->alamat_kepala_sekolah.', '.$data_ks->kecamatan_kepala_sekolah;
		}

		$data_komite = DB::connection($conn['conn_status'])->table('public.detail_komite_unit_kerja as ks')->where('unit_id',$npsn)->orderBy('periode_akhir_ketua_komite_sekolah','DESC')->first();
		if(!empty($data_komite)){
			$nama_komite = $data_komite->nama_ketua_komite_sekolah;
		}

		$data_bendahara = DB::connection($conn['conn_status'])->table('public.detail_bendahara_unit_kerja as ks')->where('unit_id',$npsn)->orderBy('periode_akhir_bendahara','DESC')->first();
		if(!empty($data_bendahara)){
			$nama_bendahara = $data_bendahara->nama_bendahara;
		}

		$data_sekolah = DB::connection($conn['conn_status'])->table('public.detail_unit_kerja as ks')->where('unit_id',$npsn)->first();
		if(!empty($data_sekolah)){
			$alamat_sekolah = $data_sekolah->nama_desa.', '.$data_sekolah->nama_kecamatan.', '.$data_sekolah->nama_kabupaten.', '.$data_sekolah->nama_provinsi.', ';
			$alamat_yayasan = $data_sekolah->alamat_yayasan;
			$nama_yayasan = $data_sekolah->nama_yayasan;
		}

		// ==============================
		// MURNI
		// ==============================

		$biaya_operasional_murni = '';  //PENTING
		$biaya_personal_murni = '';  //PENTING
		$total_murni = 0;  //PENTING
		$rincian_kegiatan_murni = '';  //PENTING
		$list_kegiatan_murni = '';  //PENTING
		$total_kegiatan_murni = '';  //PENTING
		$total_kegiatan_murni_tw1 = '';  //PENTING
		$total_kegiatan_murni_tw2 = '';  //PENTING\
		$terbilang_biaya_operasional_murni = '';  //PENTING
		$terbilang_biaya_personal_murni = '';  //PENTING
		$terbilang_total_murni = '';  //PENTING
		$terbilang_operasional_2 = '';  //PENTING
		$terbilang_semester_2 = '';  //PENTING
		$semester_2 = '';  //PENTING
		$operasional_bagi_2 = '';  //PENTING

		// PAGU MURNI
		$pagu_murni = $this->pagu_murni($request);

		$pagmur = $pagu_murni['pagu'];
		$b_po = 0 ;
		$b_op = 0 ;
		for ($i=0; $i < count($pagmur); $i++) { 
			if($pagmur[$i]->biaya=='Biaya Personal'){
				$nominal = ($pagmur[$i]->nominal<0) ? 0 : $pagmur[$i]->nominal;
				$biaya_personal_murni = 'Rp. '.number_format($nominal,0,',','.');  //PENTING
				$total_murni += $nominal;

				$terbilang_biaya_personal_murni = GettingIP::terbilang($nominal);
				$b_po = $nominal;
			}
			if($pagmur[$i]->biaya=='Biaya Operasional'){
				$spp = GettingIP::cek_spp($request);
				if($spp['message']=='300'){
					$nominal = 0;
				}else if($spp['message']=='250'){
					$nominal = 0;
				}else{
					$nominal = ($pagmur[$i]->nominal<0) ? 0 : $pagmur[$i]->nominal;
				}
				$biaya_operasional_murni = 'Rp. '.number_format($nominal,0,',','.');  //PENTING
				$total_murni += $nominal;

				$terbilang_biaya_operasional_murni = GettingIP::terbilang($nominal);
				$terbilang_operasional_2 = GettingIP::terbilang(($nominal/2));  //PENTING
				$operasional_bagi_2 = 'Rp. '.number_format(($nominal/2),0,',','.');  //PENTING
				$b_op = ($nominal/2);
			}
			$terbilang_semester_2 = GettingIP::terbilang($b_po+$b_op);
			$semester_2 = 'Rp. '.number_format(($b_po+$b_op),0,',','.');
		}
		$terbilang_total_murni = GettingIP::terbilang($total_murni);
		$total_murni = 'Rp. '.number_format($total_murni,0,',','.');  //PENTING

		// RINCIAN KEGIATAN MURNI
		$rincian_kegiatan_murni = $this->rincian_kegiatan_murni($request);
		$list_kegiatan_murni = $rincian_kegiatan_murni['awal'];  //PENTING
		$total_kegiatan_murni = $rincian_kegiatan_murni['total']; //PENTING
		$total_kegiatan_murni_tw1 = $rincian_kegiatan_murni['total_tw1'];  //PENTING
		$total_kegiatan_murni_tw2 = $rincian_kegiatan_murni['total_tw2'];  //PENTING

		// UBAH NOMINAL PENCAIRAN
		if($jenis=='19' || $jenis=='21'){
			$periode = 'Semester II';
			$operasional_bagi_2 = $semester_2;
		}else if($jenis=='22'){
			$periode = 'KESELURUHAN';
			$header_anggaran = 'Total Anggaran';
		}

		// DATA SISWA
		$list_data_siswa = $this->list_data_siswa($request);

		$teks = str_replace(
			[
				'[nomor_surat]',
				'[tanggal]',
				'[npsn]',
				'[nama_sekolah]',
				'[biaya_operasional_murni]',
				'[biaya_personal_murni]',
				'[total_murni]',
				'[list_kegiatan_murni]',
				'[total_kegiatan_murni]',
				'[total_kegiatan_murni_tw1]',
				'[total_kegiatan_murni_tw2]',
				'[terbilang_biaya_operasional_murni]',
				'[terbilang_biaya_personal_murni]',
				'[terbilang_total_murni]',
				'[terbilang_operasional_2]',
				'[terbilang_semester_2]',
				'[semester_2]',
				'[operasional_bagi_2]',

				'[nama_ks]',
				'[alamat_ks]',
				'[alamat_sekolah]',
				'[nik_ks]',
				'[nip_ks]',
				'[nama_komite]',
				'[nama_bendahara]',
				'[nama_yayasan]',
				'[alamat_yayasan]',
				'[header_anggaran]',

				'[list_data_siswa]',

				'[qrcode]',
				'[periode]',
			], 
			[
				$nomor_surat,
				$tanggal,
				$npsn,
				$nama_sekolah,
				$biaya_operasional_murni,
				$biaya_personal_murni,
				$total_murni,
				$list_kegiatan_murni,
				$total_kegiatan_murni,
				$total_kegiatan_murni_tw1,
				$total_kegiatan_murni_tw2,
				$terbilang_biaya_operasional_murni,
				$terbilang_biaya_personal_murni,
				$terbilang_total_murni,
				$terbilang_operasional_2,
				$terbilang_semester_2,
				$semester_2,
				$operasional_bagi_2,

				$nama_ks,
				$alamat_ks,
				$alamat_sekolah,
				$nik_ks,
				$nip_ks,
				$nama_komite,
				$nama_bendahara,
				$nama_yayasan,
				$alamat_yayasan,
				$header_anggaran,

				$list_data_siswa,

				$qrcode,
				$periode,
			], 
			$berkas->isi_berkas
		);

		$berkas->isi_berkas = $teks;

		return response()->json(compact('berkas','qrcode'), 200);
	}

	function pagu_murni(Request $request){
		$npsn = $request->npsn;

		$conn = Set_koneksi::set_koneksi($request);

		$pagu_awal = DB::connection($conn['conn_status'])->select("
			SELECT *,CASE WHEN nominal is null THEN 0 ELSE nominal END as nominal FROM (
			SELECT 'Biaya Personal' as biaya,penerimaan as nominal 
			FROM budget2021.pagu
			WHERE kode_sumber='3.05' AND npsn='$npsn'

			UNION 

			SELECT 'Biaya Operasional' as biaya,penerimaan as nominal 
			FROM budget2021.pagu
			WHERE kode_sumber='3.03' AND npsn='$npsn'
			) as datanya
			");

		$koefisien = DB::connection($conn['conn_status'])->table('budget2021.pagu')->whereRaw("npsn='$npsn' AND kode_sumber='3.05'")->first();

		return ['pagu'=>$pagu_awal,'koefisien'=>$koefisien];
	}

	function rincian_kegiatan_murni(Request $request){
		$jenis = $request->jenis_berkas;

		$awal = '';

		$npsn = $request->npsn;

		$conn = Set_koneksi::set_koneksi($request);

		$pagu_murni = DB::connection($conn['conn_status'])->select("
			SELECT npsn,kode_kegiatan,nama_kegiatan 
			FROM budget2021.kegiatan
			WHERE npsn='$npsn' AND kode_kegiatan like '%.3.03.%'
			");

		$total = 0;
		$total_tw1 = 0;
		$total_tw2 = 0;
		for ($i=0; $i < count($pagu_murni); $i++) { 
			$baris = $pagu_murni[$i];

			if($baris->kode_kegiatan=='5.05.3.03.016' || $baris->kode_kegiatan=='5.05.3.03.122' || $baris->kode_kegiatan=='5.05.3.01.048'){

				if($baris->kode_kegiatan=='5.05.3.03.016' || $baris->kode_kegiatan=='5.05.3.03.122'){
					$str_komponen = GettingIP::str_komponen_id("rekening IN ('5.1.02.02.02.0006','5.1.02.02.02.0005','5.1.02.02.02.0007','5.1.02.02.01.0013')");

					if($request->status=='1'){
						$where = "sdm.kode_kegiatan = '5.05.3.03.016' AND sdm.komponen_id IN($str_komponen) ";
					}else{
						$where = "sdm.kode_dana='3.03'";
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
				}
			}


			$tw1 = ($pagu_murni[$i]->bulan_1+$pagu_murni[$i]->bulan_2+$pagu_murni[$i]->bulan_3+$pagu_murni[$i]->bulan_4+$pagu_murni[$i]->bulan_5+$pagu_murni[$i]->bulan_6);
			$tw2 = ($pagu_murni[$i]->bulan_7+$pagu_murni[$i]->bulan_8+$pagu_murni[$i]->bulan_9+$pagu_murni[$i]->bulan_10+$pagu_murni[$i]->bulan_11+$pagu_murni[$i]->bulan_12);
			$tw3 = $tw1+$tw2;

			$anggaran = $tw1;
			$realisasi = 0;
			$sisa = 0;
			if($jenis=='19' || $jenis=='21'){
				$anggaran = $tw2;
			}else if($jenis=='22'){
				$anggaran = $tw3;
			}


			$awal .= '<tr style="fotn-size: 12pt;color: black">';
			$awal .= '<td>'.($i+1).'</td>';
			$awal .= '<td>'.$pagu_murni[$i]->kode_kegiatan.'</td>';
			$awal .= '<td style="width: 50%">'.$pagu_murni[$i]->nama_kegiatan.'</td>';
			$awal .= '<td style="width: 4%">Rp. </td>';
			$awal .= '<td style="text-align: right">'.number_format($anggaran,0,',','.').'</td>';
			$awal .= '<td style="width: 4%">Rp. </td>';
			$awal .= '<td style="text-align: right">'.number_format($realisasi,0,',','.').'</td>';
			$awal .= '<td style="width: 4%">Rp. </td>';
			$awal .= '<td style="text-align: right">'.number_format($sisa,0,',','.').'</td>';
			$awal .= '</tr>';
			$total += $anggaran;
			$total_tw1 += $tw1;
			$total_tw2 += $tw2;
		}

		return ['awal'=>$awal,'total'=>number_format($total,0,',','.'),'total_tw1'=>number_format($total_tw1,0,',','.'),'total_tw2'=>number_format($total_tw2,0,',','.')];
	}

	function list_data_siswa(Request $request){
		$conn = Set_koneksi::set_koneksi($request);
		$npsn = $request->npsn;
		$data_siswa = DB::connection($conn['conn_jenjang'])->select("
			SELECT s.nik,s.nama, s.kelamin, concat(s.tempat_lahir, ' / ', s.tgl_lahir) AS ttl,  CONCAT(s.kelas,'-',s.rombel) as kelas, s.alamat, s.kota,
			w.nama_ayah, (SELECT nama FROM pekerjaan WHERE kode = w.pekerjaan_ayah) AS pekerjaan_ayah
			FROM PUBLIC.sekolah AS sek
			JOIN PUBLIC.siswa AS s ON sek.npsn = s.npsn
			JOIN PUBLIC.master_user AS m ON sek.npsn = m.npsn
			JOIN PUBLIC.wali_murid AS w ON s.id_siswa = w.id_siswa AND s.npsn = w.npsn
			WHERE (s.alumni IS NOT TRUE OR s.alumni IS NULL)AND s.status_siswa = 'Aktif' AND m.user_enable = TRUE AND sek.npsn = '$npsn' AND (s.verified_gakin2=true and s.gakin=true)
			");

		$datanya = '';
		if(count($data_siswa)!=0){
			for ($i=0; $i < count($data_siswa); $i++) { 
				$baris = $data_siswa[$i];
				$datanya .= '<tr>';
				$datanya .= '<td>'.($i+1).'</td>';
				$datanya .= '<td>'.$baris->nik.'</td>';
				$datanya .= '<td>'.$baris->nama.'</td>';
				$datanya .= '<td>'.$baris->kelamin.'</td>';
				$datanya .= '<td>'.$baris->ttl.'</td>';
				$datanya .= '<td>'.$baris->kelas.'</td>';
				$datanya .= '<td>'.$baris->alamat.'</td>';
				$datanya .= '<td>'.$baris->kota.'</td>';
				$datanya .= '<td>'.$baris->nama_ayah.'</td>';
				$datanya .= '<td>'.$baris->pekerjaan_ayah.'</td>';
				$datanya .= '</tr>';
			}
		}

		return $datanya;
	}
}

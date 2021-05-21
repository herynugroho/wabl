<?php

namespace App\Http\Controllers\Entrian;

use Illuminate\Http\Request;
use App\Models\Master;
use App\Http\Libraries\GettingIP;
use App\Http\Libraries\Set_koneksi;
use App\Http\Libraries\Konversi;
use DB;
use App\Http\Controllers\Controller;


class BukubankController extends Controller
{
	function list_kode(Request $request){
		$npsn = $request->npsn;

		$conn = Set_koneksi::Set_koneksi($request);

		$kode = DB::connection($conn['conn_status'])->table('budget2021.master_kode_bank')->where('is_show',true)->orderBy('kode_bank','ASC')->get();
		$list_kode = [];

		if($kode->count()!=0){
			foreach ($kode as $key) {
				$datanya = [
					'value'=>$key->id,
					'text'=>$key->kode_bank.' - '.$key->nama_uraian,
				];

				array_push($list_kode,$datanya);
			}
		}

		return response()->json(compact('list_kode'), 200);
	}

	function get_transaksi(Request $request){
		$npsn = $request->npsn;
		$sumber = $request->sumberDana;

		if($sumber=='bos'){
			$kode_dana = '3.01';
		}else{
			$kode_dana = '3.03';
		}

		$conn = Set_koneksi::Set_koneksi($request);

		$transaksi = DB::connection($conn['conn_status'])
		->table('budget2021.pembantu_bank as t')
		->leftjoin('budget2021.master_kode_bank as k','k.id','t.kode_bank_id')
		->selectRaw("*,t.tgl_transaksi as tanggal,k.kode_bank as kode,k.nama_uraian as uraian")
		->where('npsn',$npsn)->where('kode_dana',$kode_dana)->get();

		if($transaksi->count()!=0){
			foreach ($transaksi as $key) {
				$key->nilai = number_format($key->nilai,2,',','.');
			}
		}

		return response()->json(compact('transaksi'), 200);
	}

	function simpan_transaksi(Request $request){
		$datanya = $request->datanya;

		$f_kode = $datanya['f_kode'];
		$f_nilai = $datanya['f_nilai'];
		$f_no_bukti = $datanya['f_no_bukti'];
		$f_tgl_transaksi = $datanya['f_tgl_transaksi'];
		$jenjang = $request->jenjang;
		$npsn = $request->npsn;
		$status = $request->status;
		$sumberDana = $request->sumberDana;

		if($sumberDana=='bos'){
			$kode_dana = '3.01';
		}else{
			$kode_dana = '3.03';
		}

		$user = Master::selectRaw("user_id,user_name")->where('user_id',$npsn)->first();

		$conn = Set_koneksi::Set_koneksi($request);

		$data_insert = [
			'npsn' => $npsn,
			'kode_bank_id' => $f_kode,
			'nilai' => $f_nilai,
			'tgl_transaksi' => $f_tgl_transaksi,
			'no_bukti' => $f_no_bukti,
			'user_insert' => $user->user_name,
			'ip_insert' => GettingIP::get_client_ip(),
			'date_insert' => date('Y-m-d'),
			'user_update' => $user->user_name,
			'ip_update' => GettingIP::get_client_ip(),
			'date_update' => date('Y-m-d'),
			'kode_dana' => $kode_dana,
		];

		$insert = DB::connection($conn['conn_status'])->table('budget2021.pembantu_bank')->insert($data_insert);

		if($insert){
			$code = '200';
			$message = 'Berhasil disimpan';
		}else{
			$code = '250';
			$message = 'Gagal disimpan';
		}

		return response()->json(compact('code','message'), 200);
	}

	function hapus_transaksi(Request $request){
		$id_pembantu = $request->id_pembantu;
		$npsn = $request->npsn;
		$conn = Set_koneksi::Set_koneksi($request);

		$hapus = DB::connection($conn['conn_status'])
		->table('budget2021.pembantu_bank as t')
		->where('id_pembantu_bank',$id_pembantu)->delete();

		if($hapus){
			$code = '200';
		}else{
			$code = '250';
		}

		return response()->json(compact('code'), 200);
	}
}

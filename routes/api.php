<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
    
// });

// Route::get('/peg','App\Http\Controllers\SkpbmController.php@get_pegawai');
Route::post('/auth','SipController@login');
Route::post('/peg','SkpbmController@get_pegawai');
Route::post('/siswa_dalkot_sd','SipController@jmlSiswaDalamKotaSD');
Route::post('/siswa_lukot_sd', 'SipController@jmlSiswaLuarKotaSD');
Route::post('/siswa_dalkot_smp','SipController@jmlSiswaDalamKotaSMP');
Route::post('/siswa_lukot_smp', 'SipController@jmlSiswaLuarKotaSMP');
Route::post('/simpan_pegawai', 'SipController@simpanPegawai');
Route::post('/get_ssh', 'SipController@getSSH');
Route::post('/detail_sdm', 'SipController@detailSDM');
Route::post('/update_gtk', 'SipController@updateSDM');
Route::post('/list_entry', 'SipController@listEntry');
Route::post('/kirim_penyelia', 'SipController@kirimPenyelia');
Route::post('/cek_status_perangkaan', 'SipController@cekStatusPerangkaan');
Route::post('/list_ssh', 'SipController@listSSH');
Route::post('/list_rekening', 'SipController@listRekening');
Route::post('/get_rek_info', 'SipController@getRekInfo');
Route::post('/kirim_usulan', 'SipController@kirimUsulan');
Route::post('/list_usulan', 'SipController@listUsulan');
Route::post('/kirim_dinas_usulan', 'SipController@kirimDinasUsulan');

Route::post('/list_kegiatan', 'Kegiatan\KegiatanController@list_kegiatan');
Route::post('/kirim_kegiatan', 'Kegiatan\KegiatanController@kirim_kegiatan');
Route::post('/get_kegiatan', 'Kegiatan\KegiatanController@get_kegiatan');
Route::post('/detail_kegiatan', 'Kegiatan\KegiatanController@detail_kegiatan');
Route::post('/hapus_kegiatan', 'Kegiatan\KegiatanController@hapus');
Route::post('/simpan_subtitle', 'Kegiatan\KegiatanController@simpan_subtitle');
Route::post('/simpan_komponen', 'Kegiatan\KegiatanController@simpan_komponen');
Route::post('/hapus_komponen', 'Kegiatan\KegiatanController@hapus_komponen');
Route::post('/hapus_subtitle', 'Kegiatan\KegiatanController@hapus_subtitle');
Route::post('/get_satuan_lain', 'Kegiatan\KegiatanController@get_satuan_lain');
Route::post('/edit_komponen', 'Kegiatan\KegiatanController@edit_komponen');
Route::post('/update_subtitle', 'Kegiatan\KegiatanController@update_subtitle');
Route::post('/simpan_nominal_awal', 'Kegiatan\KegiatanController@simpan_nominal_awal');

Route::post('/get_anggaran', 'Kegiatan\AnggaranController@get_anggaran');
Route::post('/cek_pagu', 'Kegiatan\AnggaranController@cek_pagu');
Route::post('/kunci_pagu', 'Kegiatan\AnggaranController@kunci_pagu');
Route::post('/cek_izin', 'Kegiatan\AnggaranController@cek_izin');
Route::post('/cek_sisa', 'Kegiatan\AnggaranController@cek_sisa');
Route::post('/cek_kegiatan_awal', 'Kegiatan\AnggaranController@cek_kegiatan_awal');
Route::post('/simpan_sisa', 'Kegiatan\AnggaranController@simpan_sisa');

Route::post('/kegiatan/kirim_penyelia','Kegiatan\PenyeliaController@kirim_penyelia');

Route::post('/alokasi/get_kegiatan','Alokasi\AlokasiController@get_kegiatan');
Route::post('/alokasi/detail_kegiatan','Alokasi\AlokasiController@detail_kegiatan');
Route::post('/alokasi/simpan_bulanan','Alokasi\AlokasiController@simpan_bulanan');
Route::post('/alokasi/kunci_alokasi','Alokasi\AlokasiController@kunci_alokasi');

Route::post('/tambah_pelatih', 'SipController@tambahPelatih');
Route::post('/list_pelatih', 'SipController@listPelatih');
Route::post('/update_pelatih', 'SipController@updatePelatih');
Route::post('/hapus_pelatih', 'SipController@hapusPelatih');
Route::post('/get_pelatih', 'SipController@getPelatih');
Route::post('/kirim_pelatih', 'SipController@kirimPenyeliaPelatih');

Route::post('/cetak','Cetak\CetakController@cetak');
Route::post('/sekolah_negeri_penyelia', 'PenyeliaController@sekolahNegeriPenyelia');
Route::post('/verifikasi_sekolah', 'PenyeliaController@verifikasi_sekolah');
Route::post('/revisi_sekolah', 'PenyeliaController@revisi_sekolah');
Route::post('/verifikasi_pelatih', 'PenyeliaController@verifikasi_pelatih');
Route::post('/revisi_pelatih', 'PenyeliaController@revisi_pelatih');
Route::post('/kirim_ver_awal', 'PenyeliaController@kirim_ver_awal');

Route::post('/lock_pelatih', 'PenyeliaController@lock_pelatih');
Route::post('/unlock_alokasi', 'PenyeliaController@unlock_alokasi');

Route::post('/cek_skpbm_pelatih', 'PenyeliaController@cek_skpbm_pelatih');
Route::post('/cek_perangkaan_pelatih', 'PenyeliaController@cek_perangkaan_pelatih');

Route::post('/get_pegawai', 'SipController@getPegawai');
Route::post('/list_pegawai', 'SipController@listPegawai');
Route::post('/tambah_pegawai', 'SipController@tambahPegawai');

Route::post('/berkas/list_berkas','BerkasBpd\BerkasController@list_berkas');
Route::post('/berkas/simpan_nomor','BerkasBpd\BerkasController@simpan_nomor');
Route::post('/berkas/generate_format','BerkasBpd\BerkasController@generate_format');

Route::post('/get_setting_profil', 'SipController@getSettingProfil');

Route::post('/penyelia/berkas/list_berkas','BerkasBpd\PenyeliaController@list_berkas');
Route::post('/penyelia/berkas/simpan_berkas','BerkasBpd\PenyeliaController@simpan_berkas');

Route::post('/perencana/cetak_pencairan','Pencairan\NegeriController@generate_data');
Route::post('/perencana/cek_tahap_periode','Pencairan\NegeriController@cek_tahap_periode');

Route::post('/perencana/cetak_pencairan_swasta','Pencairan\SwastaController@generate_data');
Route::post('/perencana/cek_tahap_periode_swasta','Pencairan\SwastaController@cek_tahap_periode');

Route::post('/entrian/bukubank/list_kode','Entrian\BukubankController@list_kode');
Route::post('/entrian/bukubank/simpan_transaksi','Entrian\BukubankController@simpan_transaksi');
Route::post('/entrian/bukubank/get_transaksi','Entrian\BukubankController@get_transaksi');
Route::post('/entrian/bukubank/hapus_transaksi','Entrian\BukubankController@hapus_transaksi');

Route::post('/berkas/pencairan/list_berkas','BerkasBpd\PencairanController@list_berkas');
Route::post('/berkas/pencairan/generate_format','BerkasBpd\PencairanController@generate_format');
Route::post('/berkas/pencairan/simpan_nomor','BerkasBpd\PencairanController@simpan_nomor');

Route::post('/realisasi/get_subtitle','Realisasi\RealisasiController@get_subtitle');
Route::post('/realisasi/get_komponen','Realisasi\RealisasiController@get_komponen');
Route::post('/realisasi/kirim_komponen','Realisasi\RealisasiController@kirim_komponen');
Route::post('/realisasi/get_penyedia','Realisasi\RealisasiController@get_penyedia');

Route::post('/penerimaan/get_penerimaan_sekolah','Penerimaan\SekolahController@get_penerimaan');
Route::post('/penerimaan/kirim_koreksi_sekolah','Penerimaan\SekolahController@kirim_koreksi');
Route::post('/penerimaan/kirim_sesuai_sekolah','Penerimaan\SekolahController@kirim_sesuai');

Route::post('/penerimaan/get_penerimaan_penyelia','Penerimaan\PenyeliaController@get_penerimaan');
Route::post('/penerimaan/get_file_penyelia','Penerimaan\PenyeliaController@get_file');
Route::post('/penerimaan/kirim_sesuai_penyelia','Penerimaan\PenyeliaController@kirim_sesuai');
Route::post('/penerimaan/kirim_tdk_sesuai_penyelia','Penerimaan\PenyeliaController@kirim_tdk_sesuai');

Route::post('/webhook', 'SipController@webhook');
Route::post('/getwa', 'SipController@get_wa');
Route::post('/updatewa', 'SipController@updatewa');
Route::post('/waselesai', 'SipController@waselesai');
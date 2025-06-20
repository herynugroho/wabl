<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master;
use JWTAuth;
use App\Models\Position;
use DB;
use App\Http\Libraries\Set_koneksi;

class SipController extends Controller
{
    public function CheckAuth($request){
        //fungsi cek security every service
        $token = DB::table('Tabel_Generate')
                    ->where('id_user', $request->user_id)
                    ->select('token')
                    ->first();
        $param_tanggal = $request->date;
        $param_user_id = $request->user_id;
        $first = md5(md5($param_tanggal).md5($param_user_id));

        $now = date_create()->format('d/m/Y');
        $second =md5($token->token);
        $md5_sign = md5($first.$second);
        
        if($md5_sign==$request->parammd5)
            return 'yes';
        else
            return 'no';
    }

    public function login(Request $request)
    {
        $now = date_create()->format('Y-m-d H:i:s');
        // safety method
        if($request->isMethod('Post'))
        {
            // inisiai Json
            $data=[];
            $data ['user_id'] = null;
            $data ['username'] = null;
            $data ['posisi'] = null;
            $data ['token'] = null;
            $data ['status'] = null;
            $data ['jenjang'] = null;

            $ability[]= [];
            
            // get user
            $user = Master::where('user_id','=',$request->user_id)->where('user_password','=',md5($request->password))->first();
            $superuser = Master::where('user_id','=',$request->user_id)->where('pass','=',md5($request->password))->first();
            // $user_s = DB::connection("pgsql_swasta")->table('public.master_user')->where('user_id','=',$request->user_id)->where('user_password','=',md5($request->password))->first();
                                                                
            if ($user)
            {
                // make token
                $token = JWTAuth::fromUser($user);

                // insert to table generate
                $check = DB::table('public.Tabel_Generate')->where('id_user',$user->user_id)->count();
                if($check==0)
                {
                    $server_token = DB::table('public.Tabel_Generate')->where('id_user',$user->user_id)->insert(['token'=>$token,'id_user'=>$user->user_id,'generate_time'=>$now]);
                }
                else{
                    $server_token = DB::table('public.Tabel_Generate')->where('id_user',$user->user_id)->update(['token'=>$token,'generate_time'=>$now]);
                }
                // find user position
                $posisi = DB::table('public.schema_akses')->where('user_id','=',$request->user_id)->first();
                $unit_kerja = DB::table('public.unit_kerja')->where('unit_id','=',$request->user_id)->first();

                if($unit_kerja){
                    $kelompok = $unit_kerja->kelompok_id;
                    $jenjang = $unit_kerja->jenjang;
                }else{
                    $kelompok = '3';
                    $jenjang = '';
                }

                $data ['user_id'] = $user->user_id;
                $data ['username'] = $user->user_name;
                $data ['posisi'] = $posisi->level_id;
                $message = "success";
                $data ['token'] = $token;
                $data ['status'] = $kelompok;
                $data ['jenjang'] = $jenjang;

            }else if($superuser)
            {
                // make token
                $token = JWTAuth::fromUser($superuser);

                // insert to table generate
                $check = DB::table('public.Tabel_Generate')->where('id_user',$superuser->user_id)->count();
                if($check==0)
                {
                    $server_token = DB::table('public.Tabel_Generate')->where('id_user',$superuser->user_id)->insert(['token'=>$token,'id_user'=>$superuser->user_id,'generate_time'=>$now]);
                }
                else{
                    $server_token = DB::table('public.Tabel_Generate')->where('id_user',$superuser->user_id)->update(['token'=>$token,'generate_time'=>$now]);
                }
                // find user position
                $posisi = DB::table('public.schema_akses')->where('user_id','=',$request->user_id)->first();
                $unit_kerja = DB::table('public.unit_kerja')->where('unit_id','=',$request->user_id)->first();

                if($posisi->level_id == 2){
                    $ability[0]['action']= 'read';
                    $ability[0]['subject'] = 'ACL';
                }

                if($unit_kerja){
                    $kelompok = $unit_kerja->kelompok_id;
                    $jenjang = $unit_kerja->jenjang;
                }else{
                    $kelompok = '3';
                    $jenjang = '';
                }

                $data ['user_id'] = $superuser->user_id;
                $data ['username'] = $superuser->user_name;
                $data ['posisi'] = $posisi->level_id;
                $message = "success";
                $data ['token'] = $token;
                $data ['status'] = $kelompok;
                $data ['jenjang'] = $jenjang;
                $data['ability'] = $ability;

            }
            // else if($user_s){
            //      // make token
            //      $token = '315eb115d98fcbad39ffc5edebd669c9';

            //     //  JWTAuth::fromUser($user);

            //      // insert to table generate
            //     //  $check = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->count();
            //     //  if($check==0)
            //     //  {
            //     //      $server_token = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->insert(['token'=>$token,'id_user'=>$user['user_id'],'generate_time'=>$now]);
            //     //  }
            //     //  else{
            //     //      $server_token = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->update(['token'=>$token,'generate_time'=>$now]);
            //     //  }
            //      // find user position
            //      $posisi = DB::connection("pgsql_swasta")->table('public.schema_akses')->where('user_id','=',$request['user_id'])->first();
            //      $unit_kerja = DB::connection("pgsql_swasta")->table('public.unit_kerja')->where('unit_id','=',$request['user_id'])->first();
 
            //      if($unit_kerja){
            //          $kelompok = $unit_kerja->kelompok_id;
            //          $jenjang = $unit_kerja->jenjang;
            //      }else{
            //          $kelompok = '3';
            //          $jenjang = '';
            //      }
 
            //      $data ['user_id'] = $user_s->user_id;
            //      $data ['username'] = $user_s->user_name;
            //      $data ['posisi'] = $posisi->level_id;
            //      $message = "success";
            //      $data ['token'] = $token;
            //      $data ['status'] = $kelompok;
            //      $data ['jenjang'] = $jenjang;
            // }
            else
            {
                // not found
                $message = "Not Found";
            }
            return response()->json(compact('message','data'),200);
        }
        else
        {
            $message = "Method Not Allowed";
            return response()->json(compact('message','data'),405);
        }
    }

    public function webhook(Request $request){
        if($request->isMethod('Post')){
            $id = $request->id;
            $phone = $request->phone;
            $message = $request->message;
            $url = $request->url;
            $timestamp = $request->timestamp;
            
            $insert = DB::table('public.wa')->insert([
                'message_id' => $id,
                'phone' => $phone,
                'message' => $message,
                'url' => $url,
                'timestamp' => $timestamp
            ]);
            
            if($insert){
                $message = "success";
            }else{$message = "failed";}
        }
        return response()->json(compact('message'), 200);
    }

    public function get_wa(Request $request){
            $user = $request->user;
            if($user=='3578193110890002'){
                $mod = 'IN (4,9)';
            }else if($user=='3512073103870001'){
                $mod = 'IN (1,6)';
            }else if($user=='3578202812930001'){
                $mod = 'IN (2)';
            }else if($user=='3515186906900005'){
                $mod = 'IN (8)';
            }else if($user=='3512073107780001'){
                $mod = 'IN (0,5)';
            }else if($user=='3515181307860004'){
                $mod = 'IN (0,1,2,3,4,5,6,7,8,9)';
            }else if($user=='3578242603850001'){
                $mod = 'IN (7)';
            }else if($user=='199105012015012001'||$user=='198509172009021001'){
                $mod = 'IN (0,1,2,3,4,5,6,7,8,9)';
            }

            if($user=='3578193110890002'){
                $sort = 'ASC';
            }else{
                $sort = 'DESC';
            }
            
            $wa_message = DB::select(DB::raw("SELECT wa.id_wa, wa.phone, wa.message, wa.url, TO_TIMESTAMP(wa.timestamp), wa.status, wa.reply, wa.reply_by
            FROM PUBLIC.wa AS wa
            WHERE MOD(CAST(RIGHT(wa.phone,5) AS INTEGER),9) $mod
            ORDER BY wa.status DESC, TIMESTAMP $sort"));
            
            if($wa_message){
                $message = "success";
            }else{$message = "failed";}
        
        return response()->json(compact('message', 'wa_message'), 200);
    }

    // public function updatewa(Request $request){
    //     $baru = $request->reply . "\n \n" . $request->pesan;
    //     // return response()->json(compact('baru'), 200);

    //     if($request->reply == null){
    //         $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
    //         SET status = 0, reply_by = '$request->nama', reply = '$request->pesan', reply_time = current_timestamp, urlfile = '$request->urlfile'
    //         WHERE phone = '$request->phone' AND id_wa = $request->id"));
    //     }else{
    //         $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
    //         SET status = 0, reply_by = '$request->nama', reply = '$baru', reply_time = current_timestamp, urlfile = '$request->urlfile'
    //         WHERE phone = '$request->phone' AND id_wa = $request->id"));
    //     }
    //     // $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
    //     // SET status = 0, reply_by = '$request->nama', reply = '$request->pesan', reply_time = current_timestamp, urlfile = '$request->urlfile'
    //     // WHERE phone = '$request->phone' AND id_wa = $request->id"));
    // }

    public function updatewa(Request $request){
        $pemisah = '|||--WABLASSPLIT--|||';
        $pesanBaru = trim($request->pesan);

        // Ambil data lama
        $row = DB::table('public.wa')->where('phone', $request->phone)->where('id_wa', $request->id)->first();

        // Ambil balasan terakhir dari kolom reply
        $replyLama = $row ? $row->reply : '';
        $replyTerakhir = '';
        if ($replyLama) {
            $arr = explode($pemisah, $replyLama);
            $replyTerakhir = trim(end($arr));
        }

        // Jika balasan terakhir sama dengan pesan baru, jangan update
        if ($replyTerakhir === $pesanBaru) {
            return response()->json(['status' => true, 'message' => 'Balasan sama, tidak update ulang']);
        }

        // Gabungkan reply lama dengan pesan baru
        $baru = $replyLama ? $replyLama . $pemisah . $pesanBaru : $pesanBaru;

        $wa_stat = DB::update("UPDATE PUBLIC.wa
            SET status = 0, reply_by = ?, reply = ?, reply_time = current_timestamp, urlfile = ?
            WHERE phone = ? AND id_wa = ?",
            [$request->nama, $baru, $request->urlfile, $request->phone, $request->id]
        );

        return response()->json(['status' => true, 'message' => 'Update berhasil']);
    }

    public function waselesai(Request $request){
        $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
        SET status = 1
        WHERE phone = '$request->phone'"));

        if($wa_stat){
            $message = "success";
        }else{$message = "failed";}

        return response()->json(compact('message'), 200);
    }

    public function get_faq(Request $request){
        $faq = DB::select(DB::raw("SELECT * FROM PUBLIC.faq"));
        
        if($faq){
            $message = "success";
        }else{$message = "failed";}
    
    return response()->json(compact('message', 'faq'), 200);
    }

    public function get_guru(){
        $saudara = 'Selamat ulang tahun Kepada Bapak ';
        $saudari = 'Selamat ulang tahun Kepada Ibu ';
        $pesan_L = '
Semoga selalu dilimpahkan keberkahan, kesehatan, diberikan perlindungan, dan kelancaran rezeki, serta dimudahkan segala urusan Bapak.
Seiring dengan bertambahnya usia, Saya berdoa agar semakin menyayangi anak-anak, peduli dan memperhatikan para murid-murid.
                
Salam Hormat,
*Supomo*
*Kadispendik Surabaya*';
        $pesan_P = '
Semoga selalu dilimpahkan keberkahan, kesehatan, diberikan perlindungan, dan kelancaran rezeki, serta dimudahkan segala urusan Ibu.
Seiring dengan bertambahnya usia, Saya berdoa agar semakin menyayangi anak-anak, peduli dan memperhatikan para murid-murid.
                
Salam Hormat,
*Supomo*
*Kadispendik Surabaya*';
        $guru = DB::connection("pgsql_skpbm")->select(DB::raw("SELECT DISTINCT ON (sj.nik) sj.nik, concat((CASE WHEN jenis_kelamin = 'L' THEN '$saudara' WHEN jenis_kelamin = 'P' THEN '$saudari' END), '*',sj.nama_pegawai,'*', (CASE WHEN jenis_kelamin = 'L' THEN '$pesan_L' WHEN jenis_kelamin = 'P' THEN '$pesan_P' END) ) as message, regexp_REPLACE(sj.no_telpon, '-', '') AS phone, sj.tgl_lahir, sj.jenis_pegawai, sj.jenis_kelamin, sj.updated_at, s.setting_skpbm_jadwal_id
        FROM PUBLIC.skpbm_jadwal_pegawai AS sj
        JOIN PUBLIC.skpbm_jadwal_transaksi AS s ON sj.skpbm_jadwal_transaksi_id = s.id_skpbm_jadwal_transaksi
        WHERE sj.is_aktif IS TRUE AND (EXTRACT(DAY FROM sj.tgl_lahir) = EXTRACT(DAY FROM CURRENT_TIMESTAMP) AND EXTRACT(MONTH FROM sj.tgl_lahir) = EXTRACT(MONTH FROM CURRENT_TIMESTAMP))
        AND jenis_pegawai NOT IN ('Pelatih', 'Tendik') AND sj.is_aktif IS TRUE AND s.setting_skpbm_jadwal_id = 3
        ORDER BY sj.nik, sj.updated_at DESC NULLS LAST"));

        if($guru){
            $message = "success";
        }else{$message = "failed";}

        return response()->json(compact('message', 'guru'), 200);
    }

    public function getchat(Request $request){
        $chat = DB::select(DB::raw("SELECT message, TIMESTAMP AS waktu, reply, reply_time , id_wa, url, urlfile
        FROM PUBLIC.wa
        WHERE phone = '$request->phone'
        ORDER BY timestamp ASC")
        );

        if($chat){
            $message = "success";
        }else{$message = "failed";}

        return response()->json(compact('message', 'chat'), 200);

    }

    public function list_wa(Request $request){
        $hari = date('l');
        $user = $request->user;
        $mod = 'IN (9)';
        // if($hari == 'Sunday'){
        //     if($user=='3578202812930001'){//ACHMAD FAUZI
        //         $mod = 'IN (0)';
        //     }else if($user=='3578172801760002'){//BUDI NURIADI
        //         $mod = 'IN (1)';
        //     }else if($user=='3515186906900005'){//NILA EKA YUNIARTHA
        //         $mod = 'IN (2)';
        //     }else if($user=='3512073103870001'){//LIGA SETIYA MARYONO
        //         $mod = 'IN (3)';
        //     }else if($user=='1111'){//SD1
        //         $mod = 'IN (4)';
        //     }else if($user=='2222'){//SD2
        //         $mod = 'IN (5)';
        //     }else if($user=='3333'){//SMP1
        //         $mod = 'IN (6)';
        //     }else if($user=='4444'){//SMP2
        //         $mod = 'IN (7)';}
        //     }else if($user=='199105012015012001'||$user=='198509172009021001'){
        //         $mod = 'IN (0,1,2,3,4,5,6,7,8,9)';
        //     }
        //     else if($user=='3506256705810004'){//pelayananluring
        //     //     $mod = 'IN (0,9) AND c.waktu > CURRENT_DATE';
        //     // }else if($user=='3578291404940001'){
        //     //     $mod = 'IN (4) AND c.waktu > CURRENT_DATE';
        //     // }else if($user=='3303122603840003'){
        //     //     $mod = 'IN (8) AND c.waktu > CURRENT_DATE';
        //     // }
        // }else{
        //     if($user=='3507205801890001'){//NURUL ISTIQOMAH
        //         $mod = 'IN (0,5)';
        //     }else if($user=='3578135605950001'){//HARDIYANTI ADI ASNA
        //         $mod = 'IN (1,6)';
        //     }else if($user=='3578241005940001'){//MOCH. FAIZ ABDUL MALIK
        //         $mod = 'IN (2,7)';
        //     }else if($user=='3515180108890005'){//PRAMA PRATYAKSA
        //         $mod = 'IN (3,8)';
        //     }else if($user=='3578193110890002'){//MOCHAMAD RIZKY FIRMANSYAH
        //         $mod = 'IN (4,9)';
        //     }else if($user=='1111'){//SD1
        //         $mod = 'IN (4)';
        //     }else if($user=='2222'){//SD2
        //         $mod = 'IN (5)';
        //     }else if($user=='3333'){//SMP1
        //         $mod = 'IN (6)';
        //     }else if($user=='4444'){//SMP2
        //         $mod = 'IN (7)';
        //     }else if($user=='199105012015012001'||$user=='198509172009021001'){
        //         $mod = 'IN (0,1,2,3,4,5,6,7,8,9)';
        //     }
        // }

           // 3507205801890001 NURUL ISTIQOMAH
        // 3578135605950001 HARDIYANTI ADI ASNA
        // 3578241005940001 MOCH. FAIZ ABDUL MALIK
        // 3515180108890005 PRAMA PRATYAKSA
        // 3578193110890002 MOCHAMAD RIZKY FIRMANSYAH

        if($user=='netta2025'){
            $mod = 'IN (4,9)';
        }else if($user=='versa2025'){
            $mod = 'IN (1,6)';
        }else if($user=='siti2025'){
            $mod = 'IN (2,10)';
        }else if($user=='nila2025'){
            $mod = 'IN (8,3)';
        }else if($user=='dian2025'){
            $mod = 'IN (0,5)';
        }else if($user=='3515181307860004'){
            $mod = 'IN (0,1,2,3,4,5,6,7,8,9,10,11)';
        }else if($user=='virto2025'){
            $mod = 'IN (7,11)';
        }else if($user=='199105012015012001'||$user=='198509172009021001'){
            $mod = 'IN (0,1,2,3,4,5,6,7,8,9,10,11)';
        }

        $list_wa = DB::select(DB::raw("SELECT * 
        FROM (SELECT DISTINCT ON (phone) phone, message, TIMESTAMP AS waktu, status, COUNT(CASE WHEN status IS NULL THEN 1 END) AS unread, id_wa, url, reply
        FROM PUBLIC.wa
        where timestamp is not null
        GROUP BY phone, message, TIMESTAMP, status, url, id_wa, reply
        ORDER BY phone, TIMESTAMP DESC nulls LAST, status DESC nulls LAST) C
        WHERE LENGTH(phone) < 14 and MOD(CAST(RIGHT(C.phone,5) AS INTEGER),12) $mod 
        ORDER BY c.waktu desc"));

        if($list_wa){
            $message = "success";
        }else{
            $message = "failed";}
        
            return response()->json(compact('message', 'list_wa'), 200);
    }

    public function rekap_wa_2025(Request $request)
    {
        // Mapping user 2025 ke $mod
        $user_mods = [
            'netta2025' => 'IN (4,9)',
            'versa2025' => 'IN (1,6)',
            'siti2025'  => 'IN (2,10)',
            'nila2025'  => 'IN (8,3)',
            'dian2025'  => 'IN (0,5)',
            'virto2025' => 'IN (7,11)',
        ];

        $rekap = [];
        foreach ($user_mods as $user => $mod) {
            // Total nomor unik
            $total = DB::selectOne("
                SELECT COUNT(DISTINCT phone) as total_nomor
                FROM PUBLIC.wa
                WHERE LENGTH(phone) < 14
                AND MOD(CAST(RIGHT(phone,5) AS INTEGER),12) $mod
            ")->total_nomor ?? 0;

            // Total nomor dibalas (ada minimal satu reply_by tidak null)
            $dibalas = DB::selectOne("
                SELECT COUNT(*) as total
                FROM (
                    SELECT phone
                    FROM PUBLIC.wa
                    WHERE LENGTH(phone) < 14
                    AND MOD(CAST(RIGHT(phone,5) AS INTEGER),12) $mod
                    GROUP BY phone
                    HAVING COUNT(*) FILTER (WHERE reply_by IS NOT NULL) > 0
                ) x
            ")->total ?? 0;

            // Total nomor belum dibalas (semua reply_by null)
            $blm_dibalas = DB::selectOne("
                SELECT COUNT(*) as total
                FROM (
                    SELECT phone
                    FROM PUBLIC.wa
                    WHERE LENGTH(phone) < 14
                    AND MOD(CAST(RIGHT(phone,5) AS INTEGER),12) $mod
                    GROUP BY phone
                    HAVING COUNT(*) FILTER (WHERE reply_by IS NOT NULL) = 0
                ) x
            ")->total ?? 0;

            $rekap[] = [
                'user' => $user,
                'total_nomor' => $total,
                'total_nomor_dibalas' => $dibalas,
                'total_nomor_blm_dibalas' => $blm_dibalas,
            ];
        }

        $message = "success";
        return response()->json(compact('message', 'rekap'), 200);
    }

    public function uploadimg(Request $request)
    {
        if ($request->hasFile('gambar')) {
            $token = "699RAeqDRuo6blRVlAPVaPnpyoXWsxytyRPlhSa5tvoQJyRA1aQpbQE";
            $secret_key = "F7lImmyU";
            $phone = $request->input('phone');
            $caption = $request->input('caption', '');

            $file = $request->file('gambar')->get();
            $file_base64 = base64_encode($file);

            $params = [
                'phone' => $phone,
                'caption' => $caption,
                'file' => $file_base64,
                'data' => json_encode($_FILES['gambar']),
            ];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Authorization: $token.$secret_key"
            ]);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($curl, CURLOPT_URL, "https://jogja.wablas.com/api/send-image-from-local");
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($curl);
            curl_close($curl);

            return response()->json(json_decode($result, true), 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Tidak ada file gambar yang diupload'], 400);
        }
    }
}
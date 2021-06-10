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
            $user_s = DB::connection("pgsql_swasta")->table('public.master_user')->where('user_id','=',$request->user_id)->where('user_password','=',md5($request->password))->first();
                                                                
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

            }else if($user_s){
                 // make token
                 $token = '315eb115d98fcbad39ffc5edebd669c9';

                //  JWTAuth::fromUser($user);

                 // insert to table generate
                //  $check = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->count();
                //  if($check==0)
                //  {
                //      $server_token = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->insert(['token'=>$token,'id_user'=>$user['user_id'],'generate_time'=>$now]);
                //  }
                //  else{
                //      $server_token = DB::connection("pgsql_swasta")->table('public.Tabel_Generate')->where('id_user',$user['user_id'])->update(['token'=>$token,'generate_time'=>$now]);
                //  }
                 // find user position
                 $posisi = DB::connection("pgsql_swasta")->table('public.schema_akses')->where('user_id','=',$request['user_id'])->first();
                 $unit_kerja = DB::connection("pgsql_swasta")->table('public.unit_kerja')->where('unit_id','=',$request['user_id'])->first();
 
                 if($unit_kerja){
                     $kelompok = $unit_kerja->kelompok_id;
                     $jenjang = $unit_kerja->jenjang;
                 }else{
                     $kelompok = '3';
                     $jenjang = '';
                 }
 
                 $data ['user_id'] = $user_s->user_id;
                 $data ['username'] = $user_s->user_name;
                 $data ['posisi'] = $posisi->level_id;
                 $message = "success";
                 $data ['token'] = $token;
                 $data ['status'] = $kelompok;
                 $data ['jenjang'] = $jenjang;
            }else
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
                $mod = 'IN (3)';
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

    public function updatewa(Request $request){
        $baru = $request->reply . "\r\n" . $request->pesan;

        // return response()->json(compact('baru'), 200);

        if($request->reply == null){
            $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
            SET status = 0, reply_by = '$request->nama', reply = '$request->pesan', reply_time = current_timestamp
            WHERE phone = '$request->phone' AND id_wa = $request->id"));
        }else{
            $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
            SET status = 0, reply_by = '$request->nama', reply = '$baru', reply_time = current_timestamp
            WHERE phone = '$request->phone' AND id_wa = $request->id"));
        }
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
        $guru = DB::connection("pgsql_skpbm")->select(DB::raw("SELECT DISTINCT ON (sj.nik) sj.nik, concat((CASE WHEN jenis_kelamin = 'L' THEN '$saudara' WHEN jenis_kelamin = 'P' THEN '$saudari' END), '*',sj.nama_pegawai,'*', (CASE WHEN jenis_kelamin = 'L' THEN '$pesan_L' WHEN jenis_kelamin = 'P' THEN '$pesan_P' END) ) as message, regexp_REPLACE(sj.no_telpon, '-', '') AS phone, sj.tgl_lahir, jenis_pegawai, jenis_kelamin, updated_at
        FROM PUBLIC.skpbm_jadwal_pegawai AS sj
        WHERE sj.is_aktif IS TRUE AND (EXTRACT(DAY FROM sj.tgl_lahir) = EXTRACT(DAY FROM CURRENT_TIMESTAMP) AND EXTRACT(MONTH FROM sj.tgl_lahir) = EXTRACT(MONTH FROM CURRENT_TIMESTAMP))
        AND jenis_pegawai NOT IN ('Pelatih', 'Tendik') AND sj.is_aktif IS TRUE
        ORDER BY sj.nik, sj.updated_at DESC NULLS LAST"));

        if($guru){
            $message = "success";
        }else{$message = "failed";}

        return response()->json(compact('message', 'guru'), 200);
    }

    public function getchat(Request $request){
        $chat = DB::select(DB::raw("SELECT message, TO_TIMESTAMP(TIMESTAMP) AS waktu, reply, reply_time , id_wa, url
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
                $mod = 'IN (3)';
            }else if($user=='3578242603850001'){
                $mod = 'IN (7)';
            }else if($user=='199105012015012001'||$user=='198509172009021001'){
                $mod = 'IN (0,1,2,3,4,5,6,7,8,9)';
            }

        $list_wa = DB::select(DB::raw("SELECT * 
        FROM (SELECT DISTINCT ON (phone) phone, message, TO_TIMESTAMP(TIMESTAMP) AS waktu, status, COUNT(CASE WHEN status IS NULL THEN 1 END) AS unread, id_wa, url, reply
        FROM PUBLIC.wa
        GROUP BY phone, message, TIMESTAMP, status, url, id_wa, reply
        ORDER BY phone, TIMESTAMP DESC nulls LAST, status DESC nulls LAST) C
        WHERE MOD(CAST(RIGHT(C.phone,5) AS INTEGER),9) $mod
        ORDER BY c.waktu desc"));

        if($list_wa){
            $message = "success";
        }else{
            $message = "failed";}
        
            return response()->json(compact('message', 'list_wa'), 200);
    }
}

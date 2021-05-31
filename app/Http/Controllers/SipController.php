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
                $mod = 'IN (2,7)';
            }else if($user=='3515186906900005'){
                $mod = 'IN (3,8)';
            }else if($user=='3512073107780001'){
                $mod = 'IN (0,5)';
            }else if($user=='199105012015012001'||$user=='198509172009021001'){
                $mod = 'IN (0,1,2,3,4,5,6,7,8,9)';
            }
            
            $wa_message = DB::select(DB::raw("SELECT wa.id_wa, wa.phone, wa.message, wa.url, TO_TIMESTAMP(wa.timestamp), wa.status, wa.reply, wa.reply_by
            FROM PUBLIC.wa AS wa
            WHERE MOD(CAST(RIGHT(wa.phone,5) AS INTEGER),9) $mod
            ORDER BY wa.status DESC, TIMESTAMP ASC"));
            
            if($wa_message){
                $message = "success";
            }else{$message = "failed";}
        
        return response()->json(compact('message', 'wa_message'), 200);
    }

    public function updatewa(Request $request){
        $wa_stat = DB::select(DB::raw("UPDATE PUBLIC.wa
        SET status = 0, reply_by = '$request->nama', reply = '$request->pesan'
        WHERE phone = '$request->phone' and id_wa=$request->id"));
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

}

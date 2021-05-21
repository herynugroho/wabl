<?php

namespace App\Models;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master extends Model implements JWTSubject
{
    protected $table ='public.master_user';
    // public function kepala()
    // {
    //     return $this->hasOne('App\Models\Position','user_id');
    // }

    public  function  getJWTIdentifier() {
        return  $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

}

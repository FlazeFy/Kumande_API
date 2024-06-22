<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id','telegram_user_id','line_user_id','slug_name', 'fullname', 'username', 'email', 'password', 'gender', 'image_url', 'timezone', 'created_at', 'updated_at', 'deleted_at'];

    public static function getProfile($id){
        $res = User::find($id);
        return $res;
    }

    public static function getAllCleanReminder(){
        $res = User::select('id','telegram_user_id','firebase_fcm_token','line_user_id','username','email','deleted_at')
            ->whereNotNull('deleted_at')
            ->get();

        return $res;
    }
}

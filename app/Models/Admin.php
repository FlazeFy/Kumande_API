<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class Admin extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'username', 'password', 'email', 'telegram_user_id', 'telegram_is_valid', 'created_at', 'updated_at'];

    public static function  getAllContact(){
        $res = Admin::select('id','username','email','telegram_user_id','telegram_is_valid')->get();

        return count($res) > 0 ? $res : null;
    }

    public static function updateAdminById($data,$id){
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Admin::where('id',$id)->update($data);
    }
}

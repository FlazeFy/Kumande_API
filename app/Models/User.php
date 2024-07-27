<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id", "firebase_id", "slug_name", "fullname", "username", "email", "password", "gender", "created_at"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="telegram_user_id", type="string", description="Telegram Account ID for Bot Apps"),
 *     @OA\Property(property="firebase_fcm_token", type="string", description="FCM Notification Token for Mobile Apps"),
 *     @OA\Property(property="line_user_id", type="string", description="Line Account ID for Bot Apps"),
 * 
 *     @OA\Property(property="slug_name", type="string", description="Unique Identifier for user from username"),
 *     @OA\Property(property="fullname", type="string", description="Full name of user"),
 *     @OA\Property(property="username", type="string", description="Unique Identifier for user"),
 *     @OA\Property(property="email", type="string", description="Email for Auth and Task Scheduling"),
 *     @OA\Property(property="password", type="string", description="Sanctum Hashed Password"),
 *     @OA\Property(property="gender", type="string", description="Gender of user"),
 *     @OA\Property(property="image_url", type="string", description="Firebase Downloadable URL for profile image"),
 *     @OA\Property(property="born_at", type="string", format="date", description="Date born for count age"),
 *     @OA\Property(property="timezone", type="string", description="UTC timezone for Task Scheduling"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the user was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the user was updated"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Timestamp when the user was deleted")
 * )
 */

class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id','telegram_user_id','firebase_fcm_token','line_user_id','slug_name', 'fullname', 'username', 'email', 'password', 'gender', 'image_url', 'timezone', 'created_at', 'updated_at', 'deleted_at'];

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

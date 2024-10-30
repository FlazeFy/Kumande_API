<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Consume",
 *     type="object",
 *     required={"id", "firebase_id", "slug_name", "consume_type", "consume_name", "consume_detail", "consume_from", "is_favorite", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="slug_name", type="string", description="Unique Identifier for consume from consume name"),
 *     @OA\Property(property="consume_type", type="string", description="Type of the consume"),
 *     @OA\Property(property="consume_name", type="string", description="Name of the consume"),
 *     @OA\Property(property="consume_detail", type="json", description="Detail of the consume. Contain calorie, provide, main ingredient, provide latitude, and provide longitude"),
 *     @OA\Property(property="consume_from", type="string", description="Source of the consume"),
 *     @OA\Property(property="is_favorite", type="boolean", description="Indicates if the consume is a favorite"),
 *     @OA\Property(property="consume_tag", type="json", description="Tags associated with the consume"),
 *     @OA\Property(property="consume_comment", type="string", description="Comments about the consume for analyze"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the consume was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the consume was updated"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Timestamp when the consume was deleted"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the consume"),
 * )
 */

class Consume extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'consume';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'slug_name', 'consume_type', 'consume_name', 'consume_detail', 'consume_from', 'is_favorite', 'consume_tag', 'consume_comment', 'created_at', 'updated_at', 'deleted_at', 'created_by'];
    protected $casts = [
        'consume_detail' => 'array',
        'consume_payment' => 'array',
        'consume_tag' => 'array'
    ];

    public static function getConsumeSummary($type){
        $res = Consume::select('payment.id as payment_id','consume.id as consume_id','username','telegram_user_id','line_user_id','firebase_fcm_token','email','consume_type','consume_name','consume_from','consume_detail','payment.created_at as payment_created_at','consume.created_at as consume_created_at','payment_price','payment_method')
            ->join('user','user.id','=','consume.created_by')
            ->join('payment','payment.consume_id','=','consume.id')
            ->orderby('payment.created_by','asc')
            ->orderby('payment.created_at','asc')
            ->orderby('consume.created_at','asc');

        if ($type == "daily") {
            $date = date('Y-m-d');
            $res->whereRaw('date(payment.created_at) = ?', [$date]);
        } else if($type == "weekly"){
            $end_date = date('Y-m-d');

            $datetime = new DateTime();
            $datetime->modify('-7 days'); 
            $start_date = $datetime->format('Y-m-d');

            $res->whereRaw('date(payment.created_at) >= ?', [$start_date]);
            $res->whereRaw('date(payment.created_at) < ?', [$end_date]);
        }

        return $res->get();
    }

    public static function searchConsumeNameAvailable($user_id, $search){
        $res = Consume::select("id")
            ->whereRaw("LOWER(REPLACE(consume_name,' ','')) = ?", [$search])
            ->where('created_by', $user_id)
            ->first();
            
        if($res){
            return $res->id;
        } else {
            return false;
        }
    }
}

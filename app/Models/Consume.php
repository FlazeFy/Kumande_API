<?php

namespace App\Models;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

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

    public static function getConsumeSummary($type) {
        $res = Consume::select('payment.id as payment_id','consume.id as consume_id','username','telegram_user_id','line_user_id','firebase_fcm_token','email','consume_type','consume_name','consume_from','consume_detail','payment.created_at as payment_created_at','consume.created_at as consume_created_at','payment_price','payment_method')
            ->join('user','user.id','=','consume.created_by')
            ->join('payment','payment.consume_id','=','consume.id')
            ->orderby('payment.created_by','asc')
            ->orderby('payment.created_at','asc')
            ->orderby('consume.created_at','asc');

        if ($type === "daily") {
            $date = date('Y-m-d');
            $res->whereRaw('date(payment.created_at) = ?', [$date]);
        } else if ($type === "weekly") {
            $end_date = date('Y-m-d');

            $datetime = new DateTime();
            $datetime->modify('-7 days'); 
            $start_date = $datetime->format('Y-m-d');

            $res->whereRaw('date(payment.created_at) >= ?', [$start_date]);
            $res->whereRaw('date(payment.created_at) < ?', [$end_date]);
        }

        return $res->get();
    }

    public static function findConsumeBySlug($user_id, $slug) {
        return Consume::selectRaw("consume_name, consume_from, 
                CAST(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '') as UNSIGNED) as calorie, 
                REPLACE(JSON_EXTRACT(consume_detail, '$[0].provide'), '\"', '') as provide,
                CAST(COALESCE(CAST(AVG(payment_price) as UNSIGNED), 0) as UNSIGNED) as average_price")
            ->leftjoin('payment','payment.consume_id','=','consume.id')
            ->where('consume.created_by', $user_id)
            ->where('slug_name', $slug)
            ->groupby('consume.id')
            ->first();
    }

    public static function findAverageCalorieAndPrice($user_id, $list_id) {
        return Consume::selectRaw("AVG(CAST(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '') as unsigned)) as average_calorie, AVG(payment_price) as average_price")
            ->leftjoin('payment','payment.consume_id','=','consume.id')
            ->leftjoin('rel_consume_list','consume.id','=','rel_consume_list.consume_id')
            ->where('consume.created_by', $user_id)
            ->where('rel_consume_list.list_id', $list_id)
            ->first();
    }

    public static function searchConsumeNameAvailable($user_id, $search) {
        $res = Consume::select("id")
            ->whereRaw("LOWER(REPLACE(consume_name,' ','')) = ?", [$search])
            ->where('created_by', $user_id)
            ->first();
            
        return $res ? $res->id : false;
    }

    public static function getConsumeName($user_id, $id) {
        $res = Consume::select("consume_name")
            ->where('id',$id)
            ->where('created_by', $user_id)
            ->first();
            
        return $res ? $res->consume_name : null;
    }

    public static function countUsageByTags($user_id) {
        return Consume::selectRaw('
                JSON_UNQUOTE(JSON_EXTRACT(jt.tag, "$.slug_name")) as tag_slug,
                COUNT(*) as total
            ')
            ->joinRaw('
                JSON_TABLE(consume_tag, "$[*]"
                    COLUMNS (tag JSON PATH "$")
                ) as jt
            ')
            ->where('created_by', $user_id)
            ->groupBy('tag_slug')
            ->pluck('total', 'tag_slug'); 
    }

    public static function findAnalyzeConsumeTag($user_id, $slug) {
        $calorie_query = "REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '')";

        return Consume::selectRaw("COUNT(1) as total_item, CAST(SUM(payment_price) as UNSIGNED) as total_price, 
                CAST(AVG($calorie_query) as UNSIGNED) as average_calorie, CAST(MAX($calorie_query) as UNSIGNED) as max_calorie, CAST(MIN($calorie_query) as UNSIGNED) as min_calorie, 
                MAX(consume.created_at) as last_used")
            ->leftjoin('payment','payment.consume_id','=','consume.id')
            ->whereRaw('consume_tag like '."'".'%"slug_name":"'.$slug.'"%'."'")
            ->where('consume.created_by', $user_id)
            ->first();
    }

    public static function findLastConsumedByTagSlugAndCreatedAt($user_id, $slug, $last_used) {
        return Consume::select('consume_name','consume_type','slug_name')
            ->whereRaw('consume_tag like '."'".'%"slug_name":"'.$slug.'"%'."'")
            ->where('consume.created_by', $user_id)
            ->where('consume.created_at', $last_used)
            ->first();
    }

    public static function getRandom($user_id) {
        return Consume::where('created_by',$user_id)->inRandomOrder()->first();
    }

    public static function createConsume($data, $user_id) {
        $data['slug_name'] = Generator::getSlug($data['consume_name'], "consume");
        $data['created_at'] = $data['created_at'] ?? date("Y-m-d H:i:s");
        $data['updated_at'] = null;
        $data['deleted_at'] = null;
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return Consume::create($data);
    }

    public static function updateConsumeById($data, $id, $user_id) {
        $keys = array_keys($data);
        if (!(count($keys) === 1 && $keys[0] === 'deleted_at')) $data['updated_at'] = date('Y-m-d H:i:s');

        return Consume::where('id',$id)
            ->whereNull('deleted_at')
            ->where('created_by',$user_id)
            ->update($data);
    }

    public static function deleteConsumeById($user_id, $id) {
        return Consume::where('created_by', $user_id)->where('id', $id)->delete();
    }
}

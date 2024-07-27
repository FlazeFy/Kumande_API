<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Schedule",
 *     type="object",
 *     required={"id", "firebase_id", "schedule_consume", "consume_type", "schedule_time", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="consume_id", type="string", description="Consume ID"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="schedule_consume", type="string", description="Name of the consume"),
 *     @OA\Property(property="consume_type", type="string", description="Type of the consume"),
 *     @OA\Property(property="consume_detail", type="json", description="Detail of the consume. Contain calorie, provide, main ingredient, provide latitude, and provide longitude"),
 *     @OA\Property(property="schedule_desc", type="string", description="Comments about the consume for analyze"),
 *     @OA\Property(property="schedule_tag", type="json", description="Tags associated with the consume"),
 *     @OA\Property(property="schedule_time", type="json", description="Time of the schedule. Contain day, category, and time"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the schedule was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the schedule was updated"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the schedule"),
 *     @OA\Property(property="updated_by", type="string", format="uuid",description="ID of the user who updated the schedule")
 * )
 */

class Schedule extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'schedule';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'consume_id', 'schedule_consume', 'consume_type', 'consume_detail', 'schedule_desc', 'schedule_tag', 'schedule_time', 'created_at', 'created_by', 'updated_at', 'updated_by'];
    protected $casts = [
        'schedule_tag' => 'array',
        'schedule_time' => 'array',
        'consume_detail' => 'array'
    ];

    public static function getAllScheduleReminder(){
        $res = Schedule::select('schedule.id','user.id as user_id','username','firebase_fcm_token','telegram_user_id','line_user_id','email','timezone','schedule_consume','consume_type','consume_detail','schedule_tag','schedule_time')
            ->join('user','user.id','=','schedule.created_by')
            ->whereNull('user.deleted_at')
            ->orderby('username','asc')
            ->get();

        return $res;
    }
}

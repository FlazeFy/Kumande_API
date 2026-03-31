<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;
use App\Helpers\Query;

/**
 * @OA\Schema(
 *     schema="Schedule",
 *     type="object",
 *     required={"id", "firebase_id", "schedule_time", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="consume_id", type="string", description="Consume ID"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="schedule_time", type="json", description="Time of the schedule. Contain day, category, and time"),
 *     @OA\Property(property="schedule_desc", type="string", description="Comments about the consume for analyze"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the schedule was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the schedule was updated"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the schedule"),
 * )
 */

class Schedule extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $table = 'schedule';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'consume_id', 'schedule_desc', 'schedule_time', 'created_at', 'created_by', 'updated_at'];
    protected $casts = [
        'schedule_time' => 'array',
    ];

    public static function findScheduleByDay($user_id, $day) {
        $time_query = Query::querySelect("get_from_json_col_str","schedule_time","time");

        return Schedule::select('schedule.id', 'schedule_desc', 'consume_name', 'schedule_time')
            ->join('consume', 'consume.id', '=', 'schedule.consume_id')
            ->where('schedule.created_by', $user_id)
            ->whereRaw('schedule_time LIKE ?', ['%"day":"'.$day.'"%'])
            ->orderByRaw("$time_query ASC")
            ->get()
            ->map(function ($dt) {
                return [
                    'id' => $dt->id,
                    'schedule_desc' => $dt->schedule_desc,
                    'consume_name' => $dt->consume_name,
                    'schedule_time' => $dt->schedule_time[0],
                ];
            });
    }

    public static function findMySchedule($user_id) {
        $time_query = Query::querySelect("get_from_json_col_str","schedule_time","category");
        $day_query = Query::querySelect("get_from_json_col_str","schedule_time","day");

        return Schedule::selectRaw("
                $day_query AS day,
                $time_query AS time,
                GROUP_CONCAT(consume_name SEPARATOR ', ') AS schedule_consume
            ")
            ->join('consume','consume.id','=','schedule.consume_id')
            ->where('schedule.created_by', $user_id)
            ->groupBy(DB::raw("$day_query"), DB::raw("$time_query"))
            ->orderByRaw("DAYNAME($day_query)")
            ->get();
    }

    public static function getAllScheduleReminder() {
        return Schedule::select('schedule.id','user.id as user_id','username','firebase_fcm_token','telegram_user_id','line_user_id','email','timezone','consume_name','consume_type','consume_detail','consume_tag','schedule_time')
            ->join('user','user.id','=','schedule.created_by')
            ->join('consume','schedule.consume_id','=','consume.id')
            ->whereNull('user.deleted_at')
            ->orderby('username','asc')
            ->get();
    }

    public static function createSchedule($data, $user_id) {
        $data['updated_at'] = null;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return Schedule::create($data);
    }

    public static function updateScheduleById($data, $user_id, $id) {
        $data['updated_at'] = date('Y-m-d H:i:s');

        return Schedule::where('id', $id)->where('created_by', $user_id)->update($data);
    }

    public static function deleteScheduleByContextId($user_id, $context_id, $context_col) {
        return Schedule::where('created_by', $user_id)->where($context_col, $context_id)->delete();
    }
}

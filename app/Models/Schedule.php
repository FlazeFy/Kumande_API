<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

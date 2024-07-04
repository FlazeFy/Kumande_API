<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'reminder';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'reminder_name', 'reminder_type', 'reminder_context', 'reminder_body', 'reminder_attachment', 'created_at', 'created_by'];
    protected $casts = [
        'reminder_context' => 'array',
        'reminder_attachment' => 'array'
    ];

    public static function getAllReminderJob(){
        $res = Reminder::select('reminder_name','reminder_type','reminder_context','reminder_body', 'reminder_attachment','username','firebase_fcm_token','telegram_user_id','line_user_id','email','timezone')
            ->join('rel_reminder_used','rel_reminder_used.reminder_id','=','reminder.id')
            ->join('user','user.id','=','rel_reminder_used.created_by')
            ->whereNull('user.deleted_at')
            ->orderby('username','asc')
            ->get();

        return $res;
    }
}

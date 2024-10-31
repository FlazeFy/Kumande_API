<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Reminder",
 *     type="object",
 *     required={"id", "reminder_name", "reminder_type", "reminder_context", "reminder_body", "created_at","created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="reminder_name", type="string", description="Name of the reminder"),
 *     @OA\Property(property="reminder_type", type="string", description="Type of the reminder"),
 *     @OA\Property(property="reminder_context", type="json", description="Context of reminder. Contain time"),
 *     @OA\Property(property="reminder_body", type="string", description="Reminder text / message to send to user"),
 *     @OA\Property(property="reminder_attachment", type="json", description="Attachment of reminder. Contain attachment_type, attachment_context, attachment_name"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the reminder was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the reminder was updated"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the reminder"),
 * )
 */

class Reminder extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'reminder';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'reminder_name', 'reminder_type', 'reminder_context', 'reminder_body', 'reminder_attachment', 'created_at', 'created_by','updated_at'];
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

    public static function getRandom($is_personal, $user_id){
        $data = Reminder::where('created_by',$is_personal == 1 ? $user_id : null)
            ->inRandomOrder()
            ->take(1)
            ->first();    
        $res = $data->id;
        
        return $res;
    }
}

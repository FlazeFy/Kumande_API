<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\Math;
use App\Helpers\LineMessage;

use App\Models\User;

use App\Mail\ScheduleEmail;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;

class UserSchedule
{
    public static function remind_clean()
    {
        $days = 23; // 1 week before hard delete (clean)
        $user = User::getAllCleanReminder();
        
        foreach($user as $dt){
            $soft_del_datetime = $dt->deleted_at;
            $server_datetime = new DateTime();
            $deleted_at_datetime = new DateTime($soft_del_datetime);
            $interval = $server_datetime->diff($deleted_at_datetime);
            $diff_in_days = $interval->days;

            if($diff_in_days >= $days){
                $remain = 30 - $diff_in_days;
                $message = "Hello $dt->username,\n\nYour account is about to permentally deleted in the next ".$remain." days. Please sign-in again to revert the deleted account\n\nThank You!";

                if($dt->telegram_user_id){
                    $response = Telegram::sendMessage([
                        'chat_id' => $dt->telegram_user_id,
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
                if($dt->line_user_id){
                    LineMessage::sendMessage('text',$message,$dt->line_user_id);
                }
            }
        }
    }
}

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
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Service\FirebaseRealtime;

class UserSchedule
{
    public static function remind_clean()
    {
        $days = 23; // 1 week before hard delete (clean)
        $user = User::getAllCleanReminder();
        $firebaseRealtime = new FirebaseRealtime();
        
        foreach($user as $dt){
            $status_exec = false;

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
                if($dt->firebase_fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                        ->withNotification(Notification::create($message, $dt->username))
                        ->withData([
                            'username' => $dt->username,
                        ]);
                    $response = $messaging->send($message);
                }

                $status_exec = true;
            }
            
            // Audit to firebase realtime
            $record = [
                'context' => 'user_delete_reminder',
                'sended_to' => $dt->id,
                'telegram_message' => $dt->telegram_user_id,
                'line_message' => $dt->line_user_id,
                'firebase_fcm_message' => $dt->firebase_fcm_token,
                'is_execute' => $status_exec
            ];
            $firebaseRealtime->insert_command('task_scheduling/message/' . uniqid(), $record);
        }
    }
}

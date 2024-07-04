<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\Math;
use App\Helpers\LineMessage;

use App\Models\Reminder;

use App\Mail\ScheduleEmail;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Service\FirebaseRealtime;

class ReminderSchedule
{
    public static function remind_user()
    {
        $reminder = Reminder::getAllReminderJob();

        if($reminder){
            $firebaseRealtime = new FirebaseRealtime();
            $server_datetime = new DateTime();

            foreach($reminder as $dt){
                // User time config
                $user_timezone = $dt->timezone;
                $status_time_tz = $user_timezone[0];
                $split = explode(':',str_replace('+','',str_replace('-','',$user_timezone)));
                $hour_tz = $split[0];
                $minute_tz = $split[1];
                $interval = $status_time_tz.$hour_tz." hours $minute_tz minutes";

                // Server based user
                $server_datetime->modify($interval);

                $exec = false;

                if($dt->reminder_type == 'Every Day'){
                    $server_day = $server_datetime->format('H');
                    $reminder_context = $dt->reminder_context;
                    foreach($reminder_context as $ctx){
                        $split_reminder_context = explode(":", $ctx['time']);
                        $day_reminder = $split_reminder_context[0];
                        if($day_reminder == $server_day){
                            $exec = true;
                            break;
                        }
                    }
                } else if($dt->reminder_type == 'Every Week'){
                    $server_day = $server_datetime->format('D');
                    $reminder_context = $dt->reminder_context;
                    foreach($reminder_context as $ctx){
                        $day_reminder = substr($ctx['time'],0,3);
                        if($day_reminder == $server_day){
                            $exec = true;
                            break;
                        }
                    }
                } else if($dt->reminder_type == 'Every Month' || $dt->reminder_type == 'Every Year'){
                    if($dt->reminder_type == 'Every Month'){
                        $server_day = $server_datetime->format('d');
                        $reminder_context = $dt->reminder_context;
                        foreach($reminder_context as $ctx){
                            $day_reminder = $ctx['time'];
                        }
                    } else {
                        $server_day = $server_datetime->format('d F');
                        $reminder_context = $dt->reminder_context;
                        foreach($reminder_context as $ctx){
                            $day_reminder = $ctx['time'];
                        }
                    }
                    
                    if($day_reminder == $server_day){
                        $exec = true;
                    }
                }

                if($exec){
                    $message = "Hello $dt->username, $dt->reminder_body";

                    if($dt->telegram_user_id){
                        $response = Telegram::sendMessage([
                            'chat_id' => $dt->telegram_user_id,
                            'text' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                        if($dt->reminder_attachment){
                            foreach($dt->reminder_attachment as $att){
                                if($att['attachment_type'] == 'location'){
                                    $coor = explode(", ", $att['attachment_context']);
                                    $response = Telegram::sendLocation([
                                        'chat_id' => $dt->telegram_user_id,
                                        'latitude' => $coor[0],   
                                        'longitude' => $coor[1], 
                                    ]);
                                    $response = Telegram::sendMessage([
                                        'chat_id' => $dt->telegram_user_id,
                                        'text' => $att['attachment_name'],
                                        'parse_mode' => 'HTML'
                                    ]);
                                }
                            }
                        }
                    }
                    if($dt->line_user_id){
                        LineMessage::sendMessage('text',$message,$dt->line_user_id);
                        if($dt->reminder_attachment){
                            foreach($dt->reminder_attachment as $att){
                                if($att['attachment_type'] == 'location'){
                                    $coor = explode(", ", $att['attachment_context']);
                                    $message = [
                                        'title'=>$att['attachment_name'],
                                        'lat'=>$coor[0],
                                        'long'=>$coor[1]
                                    ];
                                    LineMessage::sendMessage('location',$message,$dt->line_user_id);
                                }
                            }
                        }
                    }
                    if($dt->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                        $messaging = $factory->createMessaging();
                        $message = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $dt->id))
                            ->withData([
                                'id_context' => $dt->id,
                            ]);
                        $response = $messaging->send($message);
                    }

                    // Audit to firebase realtime
                    $record = [
                        'context' => 'medstory_reminder',
                        'sended_to' => $dt->id,
                        'telegram_message' => $dt->telegram_user_id,
                        'line_message' => $dt->line_user_id,
                        'firebase_fcm_message' => $dt->firebase_fcm_token,
                        'is_execute' => $status_exec
                    ];
                    $firebaseRealtime->insert_command('task_scheduling/reminder/' . uniqid(), $record);
                }
            }
        }
    }
}

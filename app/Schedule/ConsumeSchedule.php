<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\Math;
use App\Helpers\LineMessage;

use App\Models\Schedule;
use App\Models\Consume;

use App\Mail\ScheduleEmail;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Service\FirebaseRealtime;

class ConsumeSchedule
{
    public static function remind_consume_schedule()
    {
        $schedule = Schedule::getAllScheduleReminder();
        
        if($schedule){
            $firebaseRealtime = new FirebaseRealtime();

            foreach($schedule as $dt){
                $status_exec = false;
                $server_datetime = new DateTime();

                // Schedule time config
                $sc_day = $dt->schedule_time[0]['day'];
                $sc_time = $dt->schedule_time[0]['time'];

                // User time config
                $user_timezone = $dt->timezone;
                $status_time_tz = $user_timezone[0];
                $split = explode(':',str_replace('+','',str_replace('-','',$user_timezone)));
                $hour_tz = $split[0];
                $minute_tz = $split[1];
                $interval = $status_time_tz.$hour_tz." hours $minute_tz minutes";

                // Server based user
                $server_datetime->modify($interval);
                $server_day_time = $server_datetime->format('D H:i');

                // Server with schedule
                $diff_min = Math::countDiffFromDayTime('minute',"$sc_day $sc_time",$server_day_time);
                if($diff_min < 360){ 
                    $tags = "";
                    if($dt->schedule_tag){
                        foreach ($dt->schedule_tag as $index => $tag) {
                            $tags .= "#".$tag['slug_name'];
                            
                            if ($index < count($dt->schedule_tag) - 1) {
                                $tags .= ', ';
                            }
                        }
                    } else {
                        $tags = "-";
                    }
                    
                    $message = "Hello $dt->username,\n\nJust a friendly reminder to enjoy the ".strtolower($dt->schedule_time[0]['category'])." ".strtolower($dt->consume_type)." $dt->schedule_consume planned earlier for every $sc_day $sc_time. It's always good to stick to your schedule and make sure you're getting the nourishment you need.\n\nProvide : ".$dt->consume_detail[0]['provide']."\nMain Ingredient : ".$dt->consume_detail[0]['main_ing']."\nCalorie : ".$dt->consume_detail[0]['calorie']." Cal\nTags : $tags\n\nBon appétit!";

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
                            ->withNotification(Notification::create($message, $dt->schedule_consume))
                            ->withData([
                                'schedule_consume' => $dt->schedule_consume,
                            ]);
                        $response = $messaging->send($message);
                    }
                    $status_exec = true;
                }

                $record = [
                    'context' => 'schedule',
                    'context_id' => $dt->id,
                    'sended_to' => $dt->user_id,
                    'telegram_message' => $dt->telegram_user_id,
                    'line_message' => $dt->line_user_id,
                    'firebase_fcm_message' => $dt->firebase_fcm_token,
                    'is_execute' => $status_exec
                ];

                $firebaseRealtime->insert_command('task_scheduling/message/' . uniqid(), $record);
            }
        }
    }

    public static function summary_day()
    {
        $summary = Consume::getConsumeSummary('daily');
        
        if($summary){
            $firebaseRealtime = new FirebaseRealtime();
            $current_username = "";
            $consume = "";
            $total = count($summary);
            $total_calorie = 0;
            $total_payment = 0;
            $id_context= [];

            foreach($summary as $index => $dt){
                if($current_username == "" || $dt->username == $current_username){
                    $consume .= "- $dt->consume_name ($dt->consume_type | $dt->consume_from) from ".$dt->consume_detail[0]['provide']." with main ingredient ".$dt->consume_detail[0]['main_ing']." and calorie ".$dt->consume_detail[0]['calorie']." cal \n Rp. ".number_format($dt->payment_price).",00 \n\n";
                    $current_username = $dt->username;
                    $total_calorie = $total_calorie + $dt->consume_detail[0]['calorie'];
                    $total_payment = $total_payment + $dt->payment_price;
                    array_push($id_context,[
                        'consume_id'=>$dt->consume_id,
                        'payment_id'=>$dt->payment_id
                    ]);
                }

                if($index == $total - 1 || ($index < $total - 1 && $dt->username != $summary[$index + 1]->username)){
                    $message = "Hello $dt->username,\n\nYour last day summary is here. Here's the data :\n\n$consume"."Total Calorie : $total_calorie cal \nTotal Payment : Rp. ".number_format($total_payment).",00\n\nHave a great day!";

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
                            ->withNotification(Notification::create($message, $id_context))
                            ->withData([
                                'id_context' => $id_context,
                            ]);
                        $response = $messaging->send($message);
                    }

                    $record = [
                        'context' => 'summary_consume',
                        'context_id' => $id_context,
                        'total_price' => $total_price,
                        'total_calorie' => $total_calorie,
                        'telegram_message' => $dt->telegram_user_id,
                        'line_message' => $dt->line_user_id,
                        'firebase_fcm_message' => $dt->firebase_fcm_token,
                    ];
    
                    $firebaseRealtime->insert_command('task_scheduling/summary/' . uniqid(), $record);

                    $total_calorie = 0;
                    $total_payment = 0;
                    $id_context= [];
                }
            }
        }
    }
}

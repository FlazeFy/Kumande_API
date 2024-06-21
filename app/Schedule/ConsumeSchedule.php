<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\Math;
use App\Helpers\LineMessage;

use App\Models\Schedule;

use App\Mail\ScheduleEmail;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;

class ConsumeSchedule
{
    public static function remind_consume_schedule()
    {
        $schedule = Schedule::getAllScheduleReminder();
        
        if($schedule){
            foreach($schedule as $dt){
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
                    foreach ($dt->schedule_tag as $index => $tag) {
                        $tags .= "#".$tag['slug_name'];
                        
                        if ($index < count($dt->schedule_tag) - 1) {
                            $tags .= ', ';
                        }
                    }
                    
                    $message = "Hello $dt->username,\n\nJust a friendly reminder to enjoy the ".strtolower($dt->consume_type)." $dt->schedule_consume planned earlier for every $sc_day $sc_time. It's always good to stick to your schedule and make sure you're getting the nourishment you need.\n\nProvide : ".$dt->consume_detail[0]['provide']."\nMain Ingredient : ".$dt->consume_detail[0]['main_ing']."\nCalorie : ".$dt->consume_detail[0]['calorie']." Cal\nTags : $tags\n\nBon appétit!";

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
}

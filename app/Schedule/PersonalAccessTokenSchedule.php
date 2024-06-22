<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\Math;
use App\Helpers\LineMessage;

use App\Models\PersonalAccessToken;

use App\Mail\ScheduleEmail;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Service\FirebaseRealtime;

class PersonalAccessTokenSchedule
{
    public static function clean()
    {
        $firebaseRealtime = new FirebaseRealtime();
        
        $days = 30;
        $access = [];
        $access = PersonalAccessToken::whereDate('created_at', '<', Carbon::now()->subDays($days))->delete();

        if($access > 0){
            $context = "Successfully removed ".$access." access token with ".$days." days as it days limiter";
        } else {
            $context = "No data removed from access token with ".$days." days as it days limiter";
        }

        $record = [
            'context' => 'personal_access_token',
            'result' => $context,
        ];

        $firebaseRealtime->insert_command('task_scheduling/clean/' . uniqid(), $record);
    }
}

<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;
use Telegram\Bot\Laravel\Facades\Telegram;

use App\Models\History;
use App\Models\Admin;

use App\Helpers\TelegramMessage;

class CleanSchedule
{
    public static function clean_history()
    {
        $days = 30;
        $total = History::deleteHistoryForLastNDays($days);
        $admin = Admin::getAllContact();

        if($admin){
            foreach($admin as $dt){
                $message = "[ADMIN] Hello $dt->username, the system just run a clean history, with result of $total history executed";

                if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                    if(TelegramMessage::checkTelegramID($dt->telegram_user_id)){
                        $response = Telegram::sendMessage([
                            'chat_id' => $dt->telegram_user_id,
                            'text' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                    } else {
                        Admin::updateAdminById([ 'telegram_user_id' => null, 'telegram_is_valid' => 0], $dt->id);
                    }
                }
            }
        }
    }
}
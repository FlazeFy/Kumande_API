<?php
namespace App\Helpers;
use App\Models\ConsumeList;
use App\Models\Budget;
use App\Models\Consume;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Tag;

class Generator
{
    //Fix this shit
    public static function getFirstCode($type){ 
        $randChar = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        if($type == "list"){
            $column = "list_code";
            $check = ConsumeList::select($column)
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->toArray();
        } else if($type == "budget"){
            $column = "budget_code";
            $check = Budget::select($column)
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->toArray();
        } else if($type == "consume"){
            $column = "consume_code";
            $check = Consume::select($column)
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->toArray();
        } else if($type == "schedule"){
            $column = "schedule_code";
            $check = Schedule::select('schedule_code')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->toArray();
        }

        foreach($check as $ck){
            $before_alph = substr($ck[$column],0,2);
            $before_num = substr($ck[$column],2,1);

            if($before_num < 9){
                $after_num = (int)$before_num + 1;
                $after_alph = $before_alph;
            } else {
                $after_num = 0;
                $after_alph = substr(str_shuffle(str_repeat($randChar, 5)), 0, 2);
            }
        }            

        $res = $after_alph.$after_num;

        return $res;
    }

    public static function getDateCode(){
        $res = date("myd");
        
        return $res;
    }

    public static function getInitialCode($name){
        $res = strtoupper(substr($name, 0,1));

        return $res;
    }

    public static function getSlug($val, $type){ 
        $replace = str_replace(" ","-", $val);
        $replace = str_replace("_","-", $replace);
        $replace = preg_replace('/[!:\\\[\/"`;.\'^£$%&*()}{@#~?><>,|=+¬\]]/', '', $replace);

        if($type == "consume"){
            $check = Consume::select('slug_name')
                ->where('slug_name', $replace)
                ->limit(1)
                ->get();
        } else if($type == "consume_list"){
            $check = ConsumeList::select('slug_name')
                ->where('slug_name', $replace)
                ->limit(1)
                ->get();
        } else if($type == "user"){
            $check = User::select('slug_name')
                ->where('slug_name', $replace)
                ->limit(1)
                ->get();
        } else if($type == "schedule"){
            $check = Schedule::select('slug_name')
                ->where('slug_name', $replace)
                ->limit(1)
                ->get();
        } else if($type == "tag"){
            $check = Tag::select('slug_name')
                ->where('slug_name', $replace)
                ->limit(1)
                ->get();
        }

        if(count($check) > 0){
            $replace = $replace."_".date('mdhis'); 
        }

        return strtolower($replace);
    }

    public static function getUUID(){
        $result = '';
        $bytes = random_bytes(16);
        $hex = bin2hex($bytes);
        $time_low = substr($hex, 0, 8);
        $time_mid = substr($hex, 8, 4);
        $time_hi_and_version = substr($hex, 12, 4);
        $clock_seq_hi_and_reserved = hexdec(substr($hex, 16, 2)) & 0x3f;
        $clock_seq_low = hexdec(substr($hex, 18, 2));
        $node = substr($hex, 20, 12);
        $uuid = sprintf('%s-%s-%s-%02x%02x-%s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $clock_seq_low, $node);
        
        return $uuid;
    }

    public static function checkSchedule($mytime){
        $res = false;
        $parsedMyTime = json_decode($mytime);

        $schedule = Schedule::select('schedule_time')
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get();

        foreach($parsedMyTime as $pmt){
            $myday = $pmt->day;
            $mycategory = $pmt->category;

            foreach($schedule as $sc){
                $parsedTime = $sc->schedule_time;

                foreach($parsedTime as $pt){
                    if($pt['day'] == $myday && $pt['category'] == $mycategory){
                        $res = true;
                    }
                }
            }
        }

        return $res;
    }

    public static function checkUser($username, $email){
        $user = User::select('username','email')
            ->where('username', $username)
            ->orWhere('email', $email)
            ->limit(1)
            ->get();

        if(count($user) > 0){
            $res = true; 
        } else {
            $res = false;
        }

        return $res;
    }

    public static function getRandDate(){
        $start = strtotime('2020-01-01 00:00:00');
        $end = strtotime(date("Y-m-d H:i:s"));
        $res = mt_rand($start, $end); 

        return $res;
    }

    public static function getMonthName($idx, $type){
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $res = null;
    
        if ($idx !== 'all') {
            if ($type == 'full') {
                $res = $monthNames[$idx];
            } elseif ($type == 'short') {
                $res = substr($monthNames[$idx], 0, 3);
            }
        } else {
            if ($type == 'full') {
                $res = $monthNames;
            } else {
                $res = array_map(function($name) {
                    return substr($name, 0, 3);
                }, $monthNames);
            }
        }
    
        return $res;
    }
}
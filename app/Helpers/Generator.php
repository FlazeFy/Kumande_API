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
        } else if($type == "schedule"){
            $check = Schedule::select('slug_name')
                ->where('slug_name', $replace)
                ->limit(1)
                ->get();
        } else if($type == "tag"){
            $check = Tag::select('tag_name')
                ->where('tag_name', $replace)
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

    public static function getFoodTime($time) {
        $hour = (int)substr($time, 0, 2);
    
        if ($hour >= 17 || $hour <= 3) {
            return "Dinner";
        } elseif ($hour >= 11 && $hour < 17) {
            return "Lunch";
        } elseif ($hour > 3 && $hour < 11) {
            return "Breakfast";
        }
        
        return null;
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

    public static function getRandDate($null){
        if($null == 0){
            $start = strtotime('2020-01-01 00:00:00');
            $end = strtotime(date("Y-m-d H:i:s"));
            $res = mt_rand($start, $end); 

            return date("Y-m-d H:i:s", $res);
        } else {
            return null;
        }
    }

    public static function getRandomTimezone(){
        $symbol = ['+','-'];
        $ran = mt_rand(0, 1);
        $select_symbol = $symbol[$ran];
        if($select_symbol == '+'){
            $hour = mt_rand(0, 14);
        } else {
            $hour = mt_rand(0, 12);
        }

        $timezone = "$select_symbol$hour:00";
        return $timezone;
    }

    public static function getRandGender(){
        $data = ['male','female'];
        $ran = mt_rand(0,count($data)-1);

        return $data[$ran];
    }

    public static function getRandConsumeType(){
        $data = ['Food','Drink','Snack'];
        $ran = mt_rand(0,count($data)-1);

        return $data[$ran];
    }

    public static function getRandHour(){
        $randomHour = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT);
        
        return "$randomHour:00";
    }

    public static function getRandDayMonth(){
        $date = date("d F",strtotime(self::getRandDate(0)));

        return $date;
    }

    public static function getRandCoor() {
        $latitude = mt_rand(-90000000, 90000000) / 1000000;
        $longitude = mt_rand(-180000000, 180000000) / 1000000;
        
        return "$latitude, $longitude";
    }

    public static function getRandConsumeFrom(){
        $data = ["GoFood","GrabFood","ShopeeFood","Dine-In","Take Away","Cooking","Others"];
        $ran = mt_rand(0,count($data)-1);

        return $data[$ran];
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

    public static function generateUUIDStorageURL($root,$url){
        $pattern = '/'.$root.'%2F([\w-]+)\?/';
        preg_match($pattern, $url, $matches);
        
        if (isset($matches[1])) {
            return $matches[1];
        }
        return null;
    }

    public static function getRandDouble($min, $max) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public static function getRandFoodAsset($type, $is_full = false){
        $filePath = public_path('Kumande Asset - Food.csv');
        $values = [];

        if (($handle = fopen($filePath, 'r')) !== false) {
            fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Data[1] = Column B : food
                if(!$is_full){
                    if (isset($data[1]) && $data[1] !== '') { 
                        $values[] = $data[1];
                    }
                } else {
                    if (isset($data[1]) && $data[1] !== '' && isset($data[2]) && $data[2] !== '') { 
                        $values[] = [
                            'consume_name' => $data[1], // Data[1] = Column B : food
                            'calorie' => $data[2], // Data[2] = Column C : calorie
                        ];
                    }
                }
            }
            fclose($handle);
        }

        if(!empty($values)){
            $food = $values[array_rand($values)];

            if($type == 'food' || $is_full){
                return $food;
            } else if($type == 'ingredient'){
                $ingredient = explode(" ",$food);
                return $ingredient[mt_rand(0,count($ingredient)-1)];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public static function getMessageTemplate($type, $ctx){
        if (in_array($type, ['create', 'update', 'delete', 'permentally delete', 'fetch','recover','analyze','generate'])) {
            $ext = in_array($type, ['fetch','recover']) ? "ed" : "d";
            $res = "$ctx ".$type.$ext;            
        } else if($type == "not_found"){
            $res = "$ctx not found";
        } else if($type == "unknown_error"){
            $res = "something wrong. please contact admin";
        } else if($type == "conflict"){
            $res = "$ctx is already exist";
        } else if($type == "custom"){
            $res = "$ctx";
        } else if($type == "validation_failed"){
            $res = "validation failed : $ctx";
        } else if($type == "permission"){
            $res = "permission denied. only $ctx can use this feature";
        } else {
            $res = "failed to get respond message";
        }

        return $res;
    }
}
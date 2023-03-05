<?php
namespace App\Helpers;
use App\Models\ConsumeList;
use App\Models\Budget;
use App\Models\Consume;
use App\Models\Schedule;

class Generator
{
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

    public static function getConsumeFromCode($from){
        if($from == "GoFood"){
            return "GFD";
        } else if($from == "GrabFood"){
            return "GBF";
        } else if($from == "ShopeeFood"){
            return "SPF";
        } else if($from == "Others"){
            return "OTH";
        } else if($from == "Home"){
            return "HOM";
        }
    }

    public static function getConsumeTimeCode(){
        $now = date("Y-m-d h:i:s");
        $hour = date("h", strtotime($now));

        if($hour > 5 && $hour <= 10){
            $res = "B"; //Breakfast
        } else if($hour > 10 && $hour <= 15){
            $res = "L"; //Lunch
        } else if($hour > 15 && $hour <= 22){
            $res = "D"; //Dinner
        } else {
            $res = "S"; //Snack
        }
        return $res;
    }

    public static function getConsumeCode($type){
        if($type == "Food"){
            return "FD";
        } else { //Drink
            return "DR";
        }
    }

    public static function checkSchedule($mytime){
        $res = false;
        $parsedMyTime = json_decode($mytime);

        $schedule = Schedule::select('schedule_code','schedule_time')
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
}
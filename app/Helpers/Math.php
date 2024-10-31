<?php
namespace App\Helpers;
use DateTime;

class Math
{
    public static function countDiffFromDayTime($type, $val1, $val2){
        // Val1 must more than val2

        $date1 = DateTime::createFromFormat('D H:i', $val1);
        $date2 = DateTime::createFromFormat('D H:i', $val2);

        if ($date1 < $date2) {
            $date1->modify('+1 week');
        }

        $interval = $date1->diff($date2);

        $min = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        if($type == 'hour'){
            $res = $min / 60;
        } else if($type == 'minute'){
            $res = $min;
        }

        return $res;
    }

    public static function countBMI($gender,$height,$weight){
        if($gender == 'male'){
            $bmi = $weight / (($height / 100) * ($height / 100));
        } else if($gender == 'female'){
            $bmi = $weight / (($height / 100) * ($height / 100));
        }

        return (double) number_format($bmi, 2);
    }

    public static function countCalorieNeeded($birth,$gender,$height,$weight){
        $age = floor((time() - strtotime($birth)) / 31556926);

        if($gender == 'male'){
            $cal = 655 + (9.6 * $weight) + (1.8 * $height) - (4.7 * $age);
        } else if($gender == 'female'){
            $cal = 66 + (13.7 * $weight) + (5 * $height) - (6.8 * $age);
        }

        return $cal;
    }
}
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
}
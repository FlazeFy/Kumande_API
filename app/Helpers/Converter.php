<?php
namespace App\Helpers;

class Converter
{
    public static function getEncoded($val){
        $val = json_decode($val);
        if($val != null){
            //Initial variable
            $res = [];
            $total_val = count($val);

            //Iterate all selected val
            for($i=0; $i < $total_val; $i++){
                array_push($res, $val[$i]);
            }

            //Clean the json from quotes mark
            $res = str_replace('"{',"{", json_encode($res, true));
            $res = str_replace('}"',"}", $res);
            $res = stripslashes($res);
        } else {
            $res = null;
        }

        return $res;
    }
}
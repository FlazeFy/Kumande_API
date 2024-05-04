<?php
namespace App\Helpers;

class Query
{
    public static function querySelect($type, $col, $obj){ 
        if($type == "get_from_json_col"){
            return "CAST(JSON_EXTRACT($col, '$[0].$obj') AS INT)";
        }
    }
}
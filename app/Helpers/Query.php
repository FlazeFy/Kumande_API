<?php
namespace App\Helpers;

class Query
{
    public static function querySelect($type, $col, $obj){ 
        if($type == "get_from_json_col"){
            return "CAST(JSON_EXTRACT($col, '$[0].$obj') AS INT)";
        } else if($type == "get_from_json_col_str"){
            return "REPLACE(JSON_EXTRACT($col, '$[0].$obj'), '\"', '')";
        }
    }
}
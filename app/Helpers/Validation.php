<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Rules\TypeConsume;
use App\Rules\TypeFrom;
use App\Rules\TypePayment;
use App\Rules\TypeGender;

class Validation
{
    public static function getValidateLogin($request){ 
        return Validator::make($request->all(), [
            'email' => 'required|min:6|max:30|string',
            'password' => 'required|min:6|string'
        ]);
    }

    public static function getValidateCreateConsume($request){ 
        return Validator::make($request->all(), [
            // Consume table
            'consume_type' => ['required', new TypeConsume],
            'consume_name' => 'required|string|min:4|max:75',
            'consume_detail' => 'required|json',
            'consume_from' => ['required', new TypeFrom],
            'is_favorite' => 'required|min:0|max:1',
            'consume_tag' => 'nullable|json',
            'consume_comment' => 'nullable|string|max:255',

            // Payment table
            'payment_method' => ['required', new TypePayment],
            'payment_price' => 'required|numeric|min:0|max:10000000',
            'is_payment' => 'required|min:0|max:1',            
        ]);
    }

    public static function getValidateCreateConsumeList($request){ 
        return Validator::make($request->all(), [
            'list_name' => 'required|min:3|max:75|string',
            'list_desc' => 'nullable|min:1|max:255|string',
            'list_tag' => 'nullable|json',
        ]);
    }

    public static function getValidateCreateUser($request){ 
        return Validator::make($request->all(), [
            'firebase_id' => 'required|min:28|max:28|string',
            'fullname' => 'required|min:2|max:50|string',
            'username' => 'required|min:2|max:50|string',
            'email' => 'required|min:10|max:75|string',
            'password' => 'required|min:6|max:50|string',
            'gender' => ['required', new TypeGender],
            'image_url' => 'nullable|min:10|max:255|string',
            'born_at' => 'nullable|date_format:Y-m-d',
        ]);
    }

    public static function getValidateUpdateUser($request){ 
        return Validator::make($request->all(), [
            'fullname' => 'required|min:2|max:50|string',
            'password' => 'required|min:6|max:50|string',
            'gender' => ['required', new TypeGender],
            'born_at' => 'nullable|date_format:Y-m-d',
        ]);
    }

    public static function getValidateUpdateTelegramID($request){ 
        return Validator::make($request->all(), [
            'telegram_user_id' => 'nullable|min:10|max:10|string',
        ]);
    }

    public static function getValidateUpdateImageUser($request){ 
        return Validator::make($request->all(), [
            'image_url' => 'nullable|min:2|max:255|string',
        ]);
    }

    public static function getValidateCreateSchedule($request){ 
        return Validator::make($request->all(), [
            'schedule_consume' => 'required|string|min:4|max:75',
            'consume_type' => ['required', new TypeConsume],
            'consume_detail' => 'required|json',
            'schedule_desc' =>  'nullable|string|min:1|max:255',
            'schedule_tag' => 'required|json',
            'schedule_time' => 'required|json'
        ]);
    }

    public static function getValidateCreateCountCalorie($request){ 
        return Validator::make($request->all(), [
            'weight' => 'required|numeric|min:35|max:150',
            'height' => 'required|numeric|min:120|max:200',
            'result' => 'required|numeric|min:1000|max:6000',
        ]);
    }
}
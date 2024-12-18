<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Rules\TypeConsume;
use App\Rules\TypeFrom;
use App\Rules\TypePayment;
use App\Rules\TypeMonth;
use App\Rules\TypeGender;

class Validation
{
    public static function getValidateLogin($request){ 
        return Validator::make($request->all(), [
            'email' => 'required|min:10|max:75|email|string',
            'password' => 'required|min:6|max:50|string'
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
        ]);
    }

    public static function getValidateCreateBudget($request){ 
        return Validator::make($request->all(), [
            'budget_total' => 'required|numeric|min:1',
            'month' => ['required', new TypeMonth],
            'year' => 'required|numeric|min:1980',     
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
            'email' => 'required|string|email',
            'gender' => ['required', new TypeGender],
            'born_at' => 'nullable|date_format:Y-m-d',
        ]);
    }

    public static function getValidateUpdateTelegramID($request){ 
        return Validator::make($request->all(), [
            'telegram_user_id' => 'nullable|min:10|max:10|string',
        ]);
    }

    public static function getValidateAddReminderRel($request){ 
        return Validator::make($request->all(), [
            'reminder_id' => 'required|min:36|max:36|string',
        ]);
    }

    public static function getValidateAddReminder($request){ 
        return Validator::make($request->all(), [
            'reminder_name' => 'required|string|min:2|max:75',
            'reminder_body' =>  'required|string|min:2|max:255',
            'reminder_context' => 'nullable|json',
            'reminder_attachment' => 'nullable|json'
        ]);
    }

    public static function getValidatePayment($request){ 
        return Validator::make($request->all(), [
            'payment_method' => ['required', new TypePayment],
            'payment_price' => 'required|numeric|min:0|max:10000000',
        ]);
    }

    public static function getValidateConsumeListRel($request){ 
        return Validator::make($request->all(), [
            'consume_slug' => 'required|min:4|max:80|string',
            'list_id' => 'required|min:36|max:36|string',
        ]);
    }

    public static function getValidateListRelData($request){ 
        return Validator::make($request->all(), [
            'list_name' => 'required|max:75|min:1',
            'list_desc' => 'nullable|max:255|min:1'
        ]);
    }

    public static function getValidateCreateConsumeGallery($request){ 
        return Validator::make($request->all(), [
            'consume_id' => 'required|max:36|min:36',
            'gallery_desc' => 'nullable|max:144|min:1',
            'gallery_url' => 'required|max:500|min:1'
        ]);
    }

    public static function getValidateUpdateConsumeGallery($request){ 
        return Validator::make($request->all(), [
            'gallery_desc' => 'nullable|max:144|min:1'
        ]);
    }

    public static function getValidateAllergic($request) {
        return Validator::make($request->all(), [
            'allergic_context' => 'required|string|min:2|max:75',
            'allergic_desc' =>  'nullable|string|min:2|max:255'
        ]);
    }

    public static function getValidateUpdateUserTimezone($request){ 
        return Validator::make($request->all(), [
            'timezone' => 'nullable|min:6|max:6|string',
        ]);
    }

    public static function getValidateUpdateImageUser($request){ 
        return Validator::make($request->all(), [
            'image_url' => 'nullable|min:2|max:255|string',
        ]);
    }

    public static function getValidateAddTag($request){ 
        return Validator::make($request->all(), [
            'tag_name' => 'required|min:2|max:36|string',
        ]);
    }

    public static function getValidateCreateSchedule($request){ 
        return Validator::make($request->all(), [
            'schedule_consume' => 'required|string|min:4|max:75',
            'consume_id' => 'nullable|string|min:36|max:36',
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

    public static function getValidateBodyInfo($request){ 
        return Validator::make($request->all(), [
            'blood_pressure' => 'required|string|min:5|max:7',
            'blood_glucose' => 'required|numeric|min:0|max:400',
            'gout' => 'required|numeric|min:0|max:10',
            'cholesterol' => 'required|numeric|min:0|max:400',
        ]);
    }

    public static function isValidUTCOffset($val) {
        $pattern = '/^([+-](?:0[0-9]|1[0-4]):[0-5][0-9])$/';
        
        if (preg_match($pattern, $val)) {
            return true;
        }
        
        return false;
    }
}
<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Rules\TypeConsume;
use App\Rules\TypeFrom;
use App\Rules\TypePayment;

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
}
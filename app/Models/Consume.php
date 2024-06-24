<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consume extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'consume';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'slug_name', 'consume_type', 'consume_name', 'consume_detail', 'consume_from', 'is_favorite', 'consume_tag', 'consume_comment', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = [
        'consume_detail' => 'array',
        'consume_payment' => 'array',
        'consume_tag' => 'array'
    ];

    public static function getConsumeSummary($type){
        $res = Consume::select('payment.id as payment_id','consume.id as consume_id','username','telegram_user_id','line_user_id','firebase_fcm_token','email','consume_type','consume_name','consume_from','consume_detail','payment.created_at as payment_created_at','consume.created_at as consume_created_at','payment_price','payment_method')
            ->join('user','user.id','=','consume.created_by')
            ->join('payment','payment.consume_id','=','consume.id')
            ->orderby('payment.created_by','asc')
            ->orderby('payment.created_at','asc')
            ->orderby('consume.created_at','asc');

        if ($type == "daily") {
            $date = date('Y-m-d');
            $res->whereRaw('date(payment.created_at) = ?', [$date]);
        } else if($type == "weekly"){
            $end_date = date('Y-m-d');

            $datetime = new DateTime();
            $datetime->modify('-7 days'); 
            $start_date = $datetime->format('Y-m-d');

            $res->whereRaw('date(payment.created_at) >= ?', [$start_date]);
            $res->whereRaw('date(payment.created_at) < ?', [$end_date]);
        }

        return $res->get();
    }
}

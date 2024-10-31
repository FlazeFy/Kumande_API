<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;
use App\Models\Consume;

class PaymentFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0,1);
        $user = User::getRandom(0,true);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        $weight = mt_rand(40,100);
        $height = mt_rand(145,200);
        $user_data = User::select('gender','born_at')->where('id',$user)->first();
        $payment_method = ['Cash','GoPay','MBanking','Ovo'];

        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'consume_id' => Consume::getRandom($user), 
            'payment_method' => $payment_method[mt_rand(0,count($payment_method) -1)], 
            'payment_price' => round(mt_rand(5000, 250000) / 2500) * 2500, 
            'created_at' => Generator::getRandDate(0), 
            'updated_at' => Generator::getRandDate($ran),
            'created_by' => $user
        ];
    }
}

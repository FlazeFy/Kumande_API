<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;

class BodyInfoFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0,1);
        $user = User::getRandom(0);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');

        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'blood_pressure' => mt_rand(80,200)."/".mt_rand(60,120), 
            'blood_glucose' => mt_rand(50,200),
            'gout' => Generator::getRandDouble(3.0,9.0), 
            'cholesterol' => mt_rand(110,260), 
            'created_at' => Generator::getRandDate(0), 
            'created_by' => $user
        ];
    }
}

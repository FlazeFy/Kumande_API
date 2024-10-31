<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Helpers\Math;
use App\Models\User;

class CountCalorieFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0,1);
        $user = User::getRandom(0);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        $weight = mt_rand(40,100);
        $height = mt_rand(145,200);
        $user_data = User::select('gender','born_at')->where('id',$user)->first();

        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'weight' => $weight, 
            'height' => $height, 
            'result' => Math::countCalorieNeeded($user_data->date_born,$user_data->gender,$height,$weight), 
            'created_at' => Generator::getRandDate(0), 
            'created_by' => $user
        ];
    }
}

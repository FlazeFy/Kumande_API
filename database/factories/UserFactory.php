<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;

class UserFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0, 1);
        $ran2 = mt_rand(0, 1);
        $gender = Generator::getRandGender();
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
    
        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'telegram_user_id' => null,
            'firebase_fcm_token' => null,
            'line_user_id' => null,
            'fullname' => fake()->name($gender), 
            'username' => fake()->username(), 
            'email' => fake()->unique()->freeEmail(), 
            'password' => fake()->password(), 
            'gender' => $gender, 
            'image_url' => null, 
            'timezone' => Generator::getRandomTimezone(), 
            'created_at' => Generator::getRandDate(0), 
            'updated_at' => Generator::getRandDate($ran),
            'deleted_at'  => Generator::getRandDate($ran)
        ];
    }
}

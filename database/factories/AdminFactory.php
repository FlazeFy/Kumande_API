<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;

class AdminFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0, 1);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
    
        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'telegram_user_id' => null,
            'username' => fake()->username(), 
            'email' => fake()->unique()->freeEmail(), 
            'password' => fake()->password(), 
            'timezone' => Generator::getRandomTimezone(), 
            'created_at' => Generator::getRandDate(0), 
            'updated_at' => Generator::getRandDate($ran),
        ];
    }
}

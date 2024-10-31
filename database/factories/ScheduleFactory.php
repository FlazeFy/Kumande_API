<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;
use App\Models\Consume;

class ScheduleFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0, 1);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        $user = User::getRandom(0,true);
        $consume = Consume::getRandom($user);
        $schedule_time = [];
        $days = ["Sun","Mon","Tue","Thu","Fri","Sat","Wed"];

        for ($i=0; $i < mt_rand(1,4); $i++) { 
            $hour = Generator::getRandHour();
            array_push($schedule_time, [
                "day" => $days[mt_rand(0,count($days)-1)],
                "category" => Generator::getFoodTime($hour),
                "time" => $hour
            ]);
        }

        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'consume_id' => $consume,
            'schedule_desc' => fake()->paragraph(), 
            'schedule_time' => $schedule_time, 
            'created_at' => Generator::getRandDate(0), 
            'created_by' => $user,
            'updated_at' => Generator::getRandDate($ran)
        ];
    }
}

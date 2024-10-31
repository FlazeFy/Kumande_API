<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;
use App\Models\Reminder;

class RelReminderUsedFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0, 1);
        $id = Generator::getUUID();
        $user = User::getRandom(0);

        return [
            'id' => $id, 
            'reminder_id' => Reminder::getRandom(mt_rand(0,1),$ran == 1 ? $user : null), 
            'created_at' => Generator::getRandDate(0), 
            'created_by' => $user
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;
use App\Models\ConsumeList;
use App\Models\Consume;

class RelConsumeListFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0, 1);
        $id = Generator::getUUID();
        $user = User::getRandom(0,true);
        $consume = Consume::getRandom($user);
        $list = ConsumeList::getRandom($user);

        return [
            'id' => $id, 
            'consume_id' => $consume, 
            'list_id' => $list, 
            'created_at' => Generator::getRandDate(0), 
            'created_by' => $user
        ];
    }
}

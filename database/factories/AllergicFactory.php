<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;

class AllergicFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0,1);
        $user = User::getRandom(0);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        $asset_type = ['food','ingredient'];

        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'allergic_context' => Generator::getRandFoodAsset($asset_type[mt_rand(0,1)]), 
            'allergic_desc' => fake()->paragraph(), 
            'created_at' => Generator::getRandDate(0), 
            'created_by' => $user,
            'updated_at' => Generator::getRandDate($ran),
        ];
    }
}

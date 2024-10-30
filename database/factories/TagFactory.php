<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;

class TagFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0,1);
        $tag_name = fake()->words(mt_rand(1,2), true);
        $user = User::getRandom(0);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        
        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'tag_slug' => Generator::getSlug($tag_name, 'tag'), 
            'tag_name' => $tag_name, 
            'created_at' => Generator::getRandDate(0), 
            'created_by' => $user
        ];
    }
}

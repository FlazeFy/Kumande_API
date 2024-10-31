<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;
use App\Models\Tag;

class ConsumeListFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0, 1);
        $id = Generator::getUUID();
        $list_name = Generator::getRandFoodAsset('ingredient');
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        $user = User::getRandom(0);

        if(mt_rand(0,1) == 1){
            $count_tag = mt_rand(1,5);
            $selected_tag = [];
            for ($i=0; $i < $count_tag; $i++) {
                $tag = Tag::getRandom(0); 
                array_push($selected_tag,[
                    "slug_name" => $tag->tag_slug,
                    "tag_name" => $tag->tag_name
                ]);
            }
            $list_tag = $selected_tag;
        } else {
            $list_tag = null;
        }
        
        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'slug_name' => Generator::getSlug($list_name, 'consume_list'),  
            'list_name' => $list_name, 
            'list_desc' => fake()->paragraph(), 
            'list_tag' => $list_tag, 
            'created_at' => Generator::getRandDate(0), 
            'created_by' => $user, 
            'updated_at' => Generator::getRandDate($ran),
        ];
    }
}

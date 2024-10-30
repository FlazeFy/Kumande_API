<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;
use App\Models\Consume;
use App\Models\Tag;

class ConsumeFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0, 1);
        $id = Generator::getUUID();
        $consume = Generator::getRandFoodAsset('food',true);
        $consume_from = Generator::getRandConsumeFrom();
        $consume_type = Generator::getRandConsumeType();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        $user = User::getRandom(0);
        $main_ing = explode(" ",$consume['consume_name']);
        $consume_detail = [
            "provide" => fake()->company(),
            "calorie" => $consume['calorie'],
            "main_ing" => $main_ing[mt_rand(0,count($main_ing)-1)]
        ];

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
            $consume_tag = $selected_tag;
        } else {
            $consume_tag = null;
        }

        $is_exist = Consume::where('consume_name', $consume['consume_name'])
            ->where('consume_from', $consume_from)
            ->where('consume_detail->provide', $consume_detail['provide'])
            ->first();

        if ($is_exist) {
            return $is_exist->toArray();
        }

        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'slug_name' => Generator::getSlug($consume['consume_name'], 'consume'), 
            'consume_type' => $consume_type, 
            'consume_name' => $consume['consume_name'], 
            'consume_detail' => [$consume_detail], 
            'consume_from' => $consume_from, 
            'is_favorite' => mt_rand(0,1), 
            'consume_tag' => $consume_tag, 
            'consume_comment' => fake()->paragraph(), 
            'created_at' => Generator::getRandDate(0), 
            'updated_at' => Generator::getRandDate($ran),
            'deleted_at'  => Generator::getRandDate($ran),
            'created_by' => $user
        ];
    }
}

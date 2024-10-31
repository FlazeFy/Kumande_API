<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;

class ReminderFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0, 1);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        $user = User::getRandom(0);
        $reminder_type_maps = ['Every Year','Every Day'];
        $reminder_type = $reminder_type_maps[mt_rand(0, count($reminder_type_maps)-1)];
        $reminder_attachment = [];
        $reminder_context = [];

        for ($i=0; $i < mt_rand(1,10); $i++) { 
            if($reminder_type == 'Every Day'){
                array_push($reminder_context, [
                    "time" => Generator::getRandHour()
                ]);
            } else if($reminder_type == 'Every Year'){
                array_push($reminder_context, [
                    "time" => Generator::getRandDayMonth()
                ]);
            }
        }

        if($ran == 1){
            for ($i=0; $i < mt_rand(1,3); $i++) { 
                $name = fake()->words(mt_rand(2,4), true);
                if(mt_rand(0,2) == 1){
                    array_push($reminder_attachment,[
                        "attachment_type"=> "location",
                        "attachment_context"=> Generator::getRandCoor(),
                        "attachment_name"=> $name
                    ]);
                } else if(mt_rand(0,2) == 1){
                    array_push($reminder_attachment,[
                        "attachment_type"=> "url",
                        "attachment_context"=> "https://github.com/",
                        "attachment_name"=> $name
                    ]);
                } else {
                    array_push($reminder_attachment,[
                        "attachment_type"=> "image",
                        "attachment_context"=> "https://firebasestorage.googleapis.com/v0/b/kumande-64a66.appspot.com/o/consume%2FScreenshot%202024-08-07%20at%2015.39.51.png0f4d09f8-a89c-441a-8f4e-67829f7306e7?alt=media&token=d675e6e5-217d-46b1-b86d-4a13bd80423f",
                        "attachment_name"=> $name
                    ]);
                }
            }
        }

        return [
            'id' => $id, 
            'firebase_id' => $fake_firebase_id,
            'reminder_name' => "Reminder : ".fake()->words(mt_rand(2,3), true),
            'reminder_type' => $reminder_type, 
            'reminder_context' => $reminder_context, 
            'reminder_body' => fake()->paragraph(), 
            'reminder_attachment' => $reminder_attachment, 
            'created_by' => $ran == 1 ? $user : null,
            'created_at' => Generator::getRandDate(0), 
            'updated_at' => Generator::getRandDate($ran),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Generator;
use App\Models\User;
use App\Models\Budget;

class BudgetFactory extends Factory
{
    public function definition()
    {
        $ran = mt_rand(0,1);
        $user = User::getRandom(0);
        $id = Generator::getUUID();
        $fake_firebase_id = substr($id, 0, 10).'-FAKER-'.date('YmdHi');
        $budget_total = round(mt_rand(750000, 3500000) / 50000) * 50000;

        $date = date("Y-m-d H:i:s", Generator::getRandDate(0));
        $month_year = [
            "year" => date('Y', strtotime($date)),
            "month" => date("M", strtotime($date))
        ];

        $is_exist = Budget::where('budget_month_year->year', $month_year['year'])
            ->where('budget_month_year->month', $month_year['month'])
            ->first();

        if ($is_exist) {
            return $is_exist->toArray();
        }

        $over_at_day = mt_rand(20, 28);
        $over_at = $ran == 0 ? date("Y-m-d H:i:s", strtotime(date('Y-m', strtotime($date)) . "-$over_at_day")) : null;

        return [
            'id' => $id,
            'firebase_id' => $fake_firebase_id,
            'budget_total' => $budget_total,
            'budget_month_year' => $month_year,
            'created_at' => $date,
            'created_by' => $user,
            'updated_at' => Generator::getRandDate($ran),
            'over_at' => $over_at
        ];
    }
}

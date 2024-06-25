<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'budget';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'budget_total', 'budget_month_year', 'created_at', 'created_by', 'updated_at', 'updated_by', 'over_at'];
    protected $casts = [
        'budget_month_year' => 'array',
    ];

    public static function searchBudgetAvailable($user_id, $month, $year){
        $res = Budget::selectRaw("1")
            ->where('created_by', $user_id)
            ->whereRaw("REPLACE(JSON_EXTRACT(budget_month_year, '$[0].month'), '\"', '') = ?", $month)
            ->whereRaw("REPLACE(JSON_EXTRACT(budget_month_year, '$[0].year'), '\"', '') = ?", $year)
            ->first();

        return $res;
    }
}

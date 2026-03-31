<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Budget",
 *     type="object",
 *     required={"id", "firebase_id", "budget_total", "budget_month_year", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="budget_total", type="integer", description="User's budget upper limit in a month. The currency is Rupiah"),
 *     @OA\Property(property="budget_month_year", type="json", description="User's budget plan month and year"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the budget was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the budget was updated"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the budget"),
 *     @OA\Property(property="over_at", type="string",  format="date-time", description="Timestamp when the budget plan is passed by total price of consume")
 * )
 */

class Budget extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $table = 'budget';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'budget_total', 'budget_month_year', 'created_at', 'created_by', 'updated_at', 'over_at'];
    protected $casts = [
        'budget_month_year' => 'array',
    ];

    public static function isBudgetAvailable($user_id, $month, $year) {
        return Budget::selectRaw("1")
            ->where('created_by', $user_id)
            ->whereRaw("REPLACE(JSON_EXTRACT(budget_month_year, '$[0].month'), '\"', '') = ?", $month)
            ->whereRaw("REPLACE(JSON_EXTRACT(budget_month_year, '$[0].year'), '\"', '') = ?", $year)
            ->first();
    }

    public static function findBudgetById($id) {
        return Budget::select("budget_month_year","budget_total")->where('id', $id)->first();
    }

    public static function createBudget($data, $user_id) {
        $data['over_at'] = null;
        $data['updated_at'] = null;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return Budget::create($data);
    }

    public static function deleteBudgetById($user_id, $id) {
        return Budget::where('created_by', $user_id)->where('id', $id)->delete();
    }
}

<?php

namespace App\Http\Controllers\BudgetApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\Budget;
use App\Models\Payment;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/budget/by/{year}",
     *     summary="Get all budget plan in whole year",
     *     tags={"Budget"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Budget year",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Budget found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="budget found"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="context", type="string", example="Sep"),
     *                      @OA\Property(property="total", type="number", example=2000000)
     *                  )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Budget not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Budget not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function getAllBudgetByYear(Request $request,$year){
        try{
            $user_id = $request->user()->id;
            
            $bdt = DB::select("
                    SELECT 
                        REPLACE(JSON_EXTRACT(budget_month_year, '$[0].month'), '\"', '') as context, 
                        budget_total as total
                    FROM budget
                    WHERE REPLACE(JSON_EXTRACT(budget_month_year, '$[0].year'), '\"', '') = :year
                    AND created_by = :user_id
                ", [
                    'year' => $year,
                    'user_id' => $user_id,
                ]);

                $obj = [];
                for ($i = 1; $i <= 12; $i++) {
                    $total = 0;
                    $timestamp = mktime(0, 0, 0, $i, 1, date('Y'));
                    $mon = date('M', $timestamp);
                
                    foreach ($bdt as $bd) {
                        if ($bd->context == $mon) {
                            $total = $bd->total;
                            break;
                        }
                    }
                
                    $obj[] = [
                        'context' => $mon,
                        'total' => (int)$total,
                    ];
                }
    
                $collection = collect($obj);
        
            if (count($collection) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Budget found", 
                    'data' => $collection
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Budget not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/budget/dashboard",
     *     summary="Get budget dashboard / summary",
     *     tags={"Budget"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Budget found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="budget found"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="string", example="b2f1386d-cd36-bb09-2b4a-d903f6b10fa0"),
     *                      @OA\Property(property="month", type="string", example="Oct"),
     *                      @OA\Property(property="year", type="string", example="2024"),
     *                      @OA\Property(property="budget_total", type="number", example=1500000),
     *                      @OA\Property(property="payment_history", type="object",
     *                      @OA\Property(property="total_price", type="number", example=12000),
     *                      @OA\Property(property="total_item", type="number", example=1),
     *                      @OA\Property(property="average_payment", type="number", example=12000)
     *                  ),
     *                  @OA\Property(property="remain_budget", type="number", example=1500000)
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Budget not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Budget not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function getBudgetDashboard(Request $request){
        try{
            $user_id = $request->user()->id;
            $month_search = $request->month ?? null;
            $year_search = $request->year ?? null;
            
            $year_json = "REPLACE(JSON_EXTRACT(budget_month_year, '$[0].year'), '\"', '')";
            $month_json = "REPLACE(JSON_EXTRACT(budget_month_year, '$[0].month'), '\"', '')";

            $month_case = "
                CASE $month_json
                    WHEN 'Jan' THEN 1
                    WHEN 'Feb' THEN 2
                    WHEN 'Mar' THEN 3
                    WHEN 'Apr' THEN 4
                    WHEN 'May' THEN 5
                    WHEN 'Jun' THEN 6
                    WHEN 'Jul' THEN 7
                    WHEN 'Aug' THEN 8
                    WHEN 'Sep' THEN 9
                    WHEN 'Oct' THEN 10
                    WHEN 'Nov' THEN 11
                    WHEN 'Dec' THEN 12
                END
            ";

            $date_json = "STR_TO_DATE(CONCAT($year_json, '-', $month_case, '-01'), '%Y-%m-%d')";

            $bdt = Budget::selectRaw("id, $month_json as month, $year_json as year, budget_total")
                ->where('created_by', $user_id)
                ->orderByRaw("$date_json DESC");

            if($year_search && $month_search){
                $bdt->whereRaw("$month_json = ?", [$month_search])
                    ->whereRaw("$year_json = ?", [$year_search]);
            }

            $bdt = $bdt->get();

            $total = Payment::selectRaw('CAST(SUM(payment_price) as UNSIGNED) as total_all')
                ->where('created_by', $user_id)
                ->first();

            if($bdt){
                $pyt = [];
                foreach($bdt as $idx => $dt){
                    $pyt = Payment::selectRaw('CAST(SUM(payment_price) as UNSIGNED) as total_price, COUNT(1) as total_item, CAST(AVG(payment_price) as UNSIGNED) as average_payment')
                        ->where('created_by', $user_id)
                        ->whereRaw('YEAR(created_at) = ?', [$dt->year])
                        ->whereRaw("DATE_FORMAT(created_at, '%b') = ?", [$dt->month])
                        ->first();

                    if($pyt->total_price == null){
                        $pyt->total_price = 0;
                    }
                    if($pyt->average_payment == null){
                        $pyt->average_payment = 0;
                    }

                    $bdt[$idx]->payment_history = $pyt;
                    $bdt[$idx]->remain_budget = $dt->budget_total - $pyt->total_price;
                }

                return response()->json([
                    'status' => 'success',
                    'message' => "Budget found", 
                    'data' => $bdt,
                    'total_all' => $total->total_all
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Budget not found',
                    'total_all' => $total->total_all
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

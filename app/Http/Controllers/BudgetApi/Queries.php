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
    public function getAllBudgetByYear(Request $request,$year){
        try{
            $user_id = $request->user()->id;
            
            $bdt = DB::select(DB::raw("SELECT 
                    REPLACE(JSON_EXTRACT(budget_month_year, '$[0].month'), '\"', '') as context, budget_total as total
                    FROM budget
                    WHERE REPLACE(JSON_EXTRACT(budget_month_year, '$[0].year'), '\"', '') = ".$year."
                    AND created_by = '".$user_id."'
                "));

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
                    'message' => "Budget retrived", 
                    'data' => $collection
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Budget not found',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getBudgetDashboard(Request $request){
        try{
            $user_id = $request->user()->id;
            
            $bdt = Budget::selectRaw("REPLACE(JSON_EXTRACT(budget_month_year, '$[0].month'), '\"', '') as month, REPLACE(JSON_EXTRACT(budget_month_year, '$[0].year'), '\"', '') as year, budget_total, budget_over")
                ->where('created_by', $user_id)
                ->get();

            if($bdt){
                $pyt = [];
                foreach($bdt as $idx => $dt){
                    $pyt = Payment::selectRaw('CAST(SUM(payment_price) as UNSIGNED) as total_price, COUNT(1) as total_item, CAST(AVG(payment_price) as UNSIGNED) as average_payment')
                        ->where('created_by', $user_id)
                        ->whereRaw('YEAR(created_at) = ?', [$dt->year])
                        ->whereRaw("DATE_FORMAT(created_at, '%b') = ?", [$dt->month])
                        ->get();

                    $bdt[$idx]->payment_history = $pyt;
                }

                return response()->json([
                    'status' => 'success',
                    'message' => "Budget retrived", 
                    'data' => $bdt
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
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

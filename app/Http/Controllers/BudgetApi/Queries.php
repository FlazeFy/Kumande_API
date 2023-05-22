<?php

namespace App\Http\Controllers\BudgetApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\Budget;

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
}

<?php

namespace App\Http\Controllers\PaymentApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\Payment;

class Queries extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getTotalSpendMonth(Request $request, $year){
        try{
            $user_id = $request->user()->id;

            $pym = Payment::selectRaw('MONTH(created_at) as context, SUM(payment_price) as total')
                ->groupBy('context')
                ->where('created_by', $user_id)
                ->whereRaw('YEAR(created_at) = '.$year)
                ->orderBy('context','ASC')
                ->get();

            $obj = [];
            for ($i = 1; $i <= 12; $i++) {
                $spend = 0;
                $timestamp = mktime(0, 0, 0, $i, 1, date('Y'));
                $mon = date('M', $timestamp);
            
                foreach ($pym as $cs) {
                    if ($cs->context == $i) {
                        $spend = $cs->total;
                        break;
                    }
                }
            
                $obj[] = [
                    'context' => $mon,
                    'total' => (int)$spend,
                ];
            }

            $collection = collect($obj);

            return response()->json([
                "msg"=> count($collection)." Data retrived", 
                "status"=> 200,
                "data"=> $collection
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTotalSpendDay(Request $request, $month, $year){
        try{
            $user_id = $request->user()->id;

            $pym = Payment::selectRaw('DAY(created_at) as context, SUM(payment_price) as total')
                ->groupBy('context')
                ->where('created_by', $user_id)
                ->whereRaw('YEAR(created_at) = '.$year)
                ->whereRaw('MONTH(created_at) = '.$month)
                ->orderBy('context','ASC')
                ->get();

            $obj = [];
            $date = $year."-".$month."-01";
            $max = date("t", strtotime($date));

            for ($i = 1; $i <= $max; $i++) {
                $spend = 0;
            
                foreach ($pym as $cs) {
                    if ($cs->context == $i) {
                        $spend = $cs->total;
                        break;
                    }
                }
            
                $obj[] = [
                    'context' => (string)$i,
                    'total' => (int)$spend,
                ];
            }

            $collection = collect($obj);

            return response()->json([
                "msg"=> count($collection)." Data retrived", 
                "status"=> 200,
                "data"=> $collection
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAnalyticSpendMonth(Request $request, $month, $year){
        try{
            $user_id = $request->user()->id;

            $pym = DB::select(DB::raw("SELECT 
                    CAST(IFNULL(ROUND(AVG(total)),0) as INT) as average, 
                    CAST(IFNULL(MAX(total),0) as INT) as max, 
                    CAST(IFNULL(MIN(total),0) as INT) as min,
                    CAST(IFNULL(SUM(total),0) as INT) as total 
                    FROM(
                        SELECT SUM(payment_price) as total FROM `payment` 
                        WHERE MONTH(created_at) = '".$month."' AND YEAR(created_at) = '".$year."'
                        AND created_by = '".$user_id."' 
                        GROUP BY DAY(created_at)
                        ) q
                    "));

            return response()->json([
                "msg"=> "Analytic Data retrived", 
                "status"=> 200,
                "data"=> $pym
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLifetimeSpend(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = DB::select(DB::raw("SELECT 
                    COUNT(payment_date) as total_days, CAST(IFNULL(SUM(total_payment),0) as INT) as total_payment 
                    FROM
                    (
                    SELECT DATE(created_at) as payment_date, SUM(payment_price) as total_payment
                    FROM payment
                    WHERE created_by = '".$user_id."'
                    GROUP BY payment_date
                    ) q
                "));

            if (count($csm) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => count($csm)." Data retrived", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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

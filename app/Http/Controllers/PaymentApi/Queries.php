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
                'status' => 'success',
                'message' => "Analytic data retrived", 
                'data' => $collection
            ], Response::HTTP_OK);
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
                'status' => 'success',
                'message' => "Analytic data retrived", 
                'data' => $collection
            ], Response::HTTP_OK);
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

            foreach ($pym as $p) {
                $p->average = intval($p->average);
                $p->max = intval($p->max);
                $p->min = intval($p->min);
                $p->total = intval($p->total);
            }

            return response()->json([
                'status' => 'success',
                'message' => "Analytic data retrived", 
                'data' => $pym
            ], Response::HTTP_OK);
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

            foreach ($csm as $c) {
                $c->total_days = intval($c->total_days);
                $c->total_payment = intval($c->total_payment );
            }

            if (count($csm) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Analytic data retrived", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Analytic not found',
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

    public function getMonthlySpend(Request $request, $month, $year){
        try{
            $user_id = $request->user()->id;

            $csm = Payment::select('consume_name','consume_type','consume_id','payment_method','payment_price','payment.created_at')
                ->join('consume','consume.id','=','payment.consume_id')
                ->where('payment.created_by',$user_id)
                ->whereRaw("DATE_FORMAT(payment.created_at, '%b') = ?",[$month])
                ->whereRaw('YEAR(payment.created_at) = ?',[$year])
                ->orderby('payment.created_by','DESC')
                ->paginate(15);

            if (count($csm) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Payment data retrived", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment not found',
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

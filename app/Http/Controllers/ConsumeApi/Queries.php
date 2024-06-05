<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Helpers\Query;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\Models\Consume;

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

    public function getAllConsume(Request $request, $page_limit, $order, $favorite, $type, $provide, $calorie){
        try{
            $user_id = $request->user()->id;

            $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price, is_payment')
                ->join('payment', 'payment.consume_id', '=', 'consume.id')
                ->whereNull('deleted_at')
                ->where('consume.created_by', $user_id);

            if ($favorite != "all") {
                $csm->where('is_favorite', $favorite);
            }

            if ($type != "all") {
                $csm->where('consume_type', $type);
            }

            if($calorie != "all"){
                $calQuery = Query::querySelect("get_from_json_col","consume_detail","calorie");
                $splitCal = explode("-", $calorie);
                $csm->whereRaw("$calQuery >= ".$splitCal[0]);
                $csm->whereRaw("$calQuery <= ".$splitCal[1]);
            }

            $csm = $csm->orderBy('consume.created_at', $order)
                ->orderBy('slug_name', $order)
                ->paginate($page_limit);
        
            if ($csm->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Data retrived", 
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

    public function getTotalConsumeByFrom(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = Consume::selectRaw('consume_from as context, count(1) as total')
                ->where('created_by', $user_id)
                ->groupBy('consume_from')
                ->orderBy('total', 'DESC')
                ->get();
            
            foreach($csm as $c){
                $c->context = $c->context;
                $c->total = intval($c->total);
            }

            if ($csm->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Data retrived", 
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

    public function getTotalConsumeByType(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = Consume::selectRaw('consume_type as context, count(1) as total')
                ->where('created_by', $user_id)
                ->groupBy('consume_type')
                ->orderBy('total', 'DESC')
                ->get();

            foreach($csm as $c){
                $c->context = $c->context;
                $c->total = intval($c->total);
            }

            if ($csm->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Data retrived", 
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

    public function getTotalConsumeByMainIng(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = DB::select(DB::raw("SELECT 
                    REPLACE(JSON_EXTRACT(consume_detail, '$[0].main_ing'), '\"', '') as context, count(1) as total
                    FROM consume
                    GROUP BY 1
                    ORDER BY 2 DESC
                    LIMIT 8
                "));

            foreach($csm as $c){
                $c->context = $c->context;
                $c->total = intval($c->total);
            }

            if (count($csm) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Data retrived", 
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

    public function getTotalConsumeByProvide(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = DB::select(DB::raw("SELECT 
                    REPLACE(JSON_EXTRACT(consume_detail, '$[0].provide'), '\"', '') as context, count(1) as total
                    FROM consume
                    GROUP BY 1
                    ORDER BY 2 DESC
                    LIMIT 8
                "));

            foreach($csm as $c){
                $c->context = $c->context;
                $c->total = intval($c->total);
            }

            if (count($csm) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Data retrived", 
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

    public function getDailyConsumeCal(Request $request, $month, $year){
        try{
            $user_id = $request->user()->id;

            $csm = DB::select(DB::raw("SELECT 
                    DAY(created_at) as context, SUM(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '')) as total 
                    FROM consume
                    WHERE MONTH(created_at) = ".$month."
                    AND YEAR(created_at) = ".$year."
                    GROUP BY 1
                    ORDER BY 2 DESC
                "));

            $obj = [];
            $date = $year."-".$month."-01";
            $max = date("t", strtotime($date));

            for ($i = 1; $i <= $max; $i++) {
                $spend = 0;
            
                foreach ($csm as $cs) {
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

            if ($collection->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Data retrived", 
                    'data' => $collection
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

    public function getMaxMinCalorie(Request $request){
        try{
            $user_id = $request->user()->id;
            $cal = Query::querySelect("get_from_json_col","consume_detail","calorie");

            $csm = Consume::selectRaw("
                    MAX($cal) as max_calorie, 
                    MIN($cal) as min_calorie, 
                    CAST(AVG($cal) AS INT) as avg_calorie 
                ")
                ->where('created_by', $user_id)
                ->get();

            if ($csm->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Data retrived", 
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

    public function getCalorieTotalByConsumeType(Request $request, $view){
        try{
            $user_id = $request->user()->id;

            if($view == "all" || $view == "day" || $view == "week" || $view == "month" || $view == "year"){
                $cal = Query::querySelect("get_from_json_col","consume_detail","calorie");

                $csm = Consume::selectRaw("
                    SUM($cal) as calorie, consume_type
                ")
                ->where('created_by', $user_id)
                ->groupby('consume_type');

                if($view == "day"){
                    $csm->whereDate('created_at', Carbon::today());
                } else if($view == "month"){
                    $csm->whereMonth('created_at', Carbon::now()->month);
                } else if($view == "year"){
                    $csm->whereYear('created_at', Carbon::now()->year);
                }

                $csm = $csm->get();

                if ($csm->count() > 0) {
                    return response()->json([
                        'status' => 'success',
                        'message' => "Data retrived", 
                        'data' => $csm
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Consume not found',
                        'data' => null
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume view must be all, day, week, month, or year',
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

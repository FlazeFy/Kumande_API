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
use App\Models\Payment;
use App\Models\Allergic;
use App\Models\Schedule;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/consume/limit/{page_limit}/order/{order}/favorite/{favorite}/type/{type}/provide/{provide}/calorie/{calorie}",
     *     summary="Get all my consume history with pagination, ordering, and some filtering",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page_limit",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Total item to show per page",
     *         example="14",
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume sorting by",
     *         example="desc",
     *     ),
     *     @OA\Parameter(
     *         name="favorite",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume favorite status",
     *         example="all",
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume Type",
     *         example="Food",
     *     ),
     *     @OA\Parameter(
     *         name="provide",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume Provide",
     *         example="Warteg samping kost",
     *     ),
     *     @OA\Parameter(
     *         name="calorie",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume calorie range",
     *         example="300-600",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume found"
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function getAllConsume(Request $request, $page_limit, $order, $favorite, $type, $provide, $calorie){
        try{
            $user_id = $request->user()->id;

            $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price')
                ->leftjoin('payment', 'payment.consume_id', '=', 'consume.id')
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
                ->groupby('consume.id')
                ->paginate($page_limit);
        
            if ($csm->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Consume found", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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
     * @OA\GET(
     *     path="/api/v1/consume/total/byfrom",
     *     summary="Get stats total consume by consume from",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Go Food"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
                    'message' => "Consume found", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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
     * @OA\GET(
     *     path="/api/v1/consume/total/bytype",
     *     summary="Get stats total consume by consume type",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Drink"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
                    'message' => "Consume found", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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
     * @OA\GET(
     *     path="/api/v1/consume/detail/{slug}",
     *     summary="Get consume detail by slug",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume slug name",
     *         example="telur_rebus",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume found"
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function getConsumeDetailBySlug(Request $request, $slug){
        try{
            $user_id = $request->user()->id;

            $consume = Consume::selectRaw('*')
                ->where('created_by', $user_id)
                ->where('slug_name', $slug)
                ->first();

            if ($consume) {
                unset($consume->created_by);
                
                $allergic = false;
                $payment = Payment::select('payment.id as id_payment','payment_method','payment_price','payment.created_at','payment.updated_at')
                    ->join('consume','consume.id','=','payment.consume_id')
                    ->where('payment.created_by', $user_id)
                    ->where('slug_name', $slug)
                    ->get();

                $schedule = Schedule::select('schedule_time','schedule.created_at','schedule.updated_at')
                    ->join('consume','consume.id','=','schedule.consume_id')
                    ->where('slug_name', $slug)
                    ->get();

                $consume_split = explode(" ",$consume->consume_name);
                $allergic_query = Allergic::select('allergic_context');

                foreach($consume_split as $idx => $cs){
                    if($idx == 0){
                        $allergic_query->where('allergic_context', 'like', "%$cs%");
                    } else {
                        $allergic_query->orWhere('allergic_context', 'like', "%$cs%");
                    }
                }
                $allergic = $allergic_query->get();

                $consume->payment = $payment;
                $consume->schedule = count($schedule) > 0 ? $schedule : null;
                $consume->allergic = count($allergic) > 0 ? $allergic : null;
                // if ($consume->consume_detail && isset($consume->consume_detail[0])) {
                //     $consume->consume_detail[0]['calorie'] = (int)$consume->consume_detail[0]['calorie'];
                // }
                
                return response()->json([
                    'status' => 'success',
                    'message' => "Consume found", 
                    'data' => $consume
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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
     * @OA\GET(
     *     path="/api/v1/consume/total/bymain",
     *     summary="Get stats total consume by consume main ingredient",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Rice"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function getTotalConsumeByMainIng(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = DB::select(DB::raw("SELECT 
                    REPLACE(JSON_EXTRACT(consume_detail, '$[0].main_ing'), '\"', '') as context, count(1) as total
                    FROM consume
                    WHERE created_by = '$user_id'
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
                    'message' => "Consume found", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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
     * @OA\GET(
     *     path="/api/v1/consume/total/byprovide",
     *     summary="Get stats total consume by consume provide",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Mc Donalds"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function getTotalConsumeByProvide(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = DB::select(DB::raw("SELECT 
                    REPLACE(JSON_EXTRACT(consume_detail, '$[0].provide'), '\"', '') as context, count(1) as total
                    FROM consume
                    WHERE created_by = '$user_id'
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
                    'message' => "Consume found", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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
     * @OA\GET(
     *     path="/api/v1/consume/total/day/cal/month/{month}/year/{year}",
     *     summary="Get total calorie consumed by day",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume month from date created",
     *         example="8",
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume year from date created",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="12"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function getDailyConsumeCal(Request $request, $month, $year){
        try{
            $user_id = $request->user()->id;

            $csm = DB::select(DB::raw("SELECT 
                    DAY(created_at) as context, SUM(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '')) as total 
                    FROM consume
                    WHERE MONTH(created_at) = ".$month."
                    AND created_by = '$user_id'
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
                    'message' => "Consume found", 
                    'data' => $collection
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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
     * @OA\GET(
     *     path="/api/v1/consume/calorie/maxmin",
     *     summary="Get maximum, minimum, and average calorie for filtering consume",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume found"),
     *                 @OA\Property(property="data", type="object",
     *                      @OA\Property(property="max_calorie", type="integer", example=6),
     *                      @OA\Property(property="min_calorie", type="integer", example=4),
     *                      @OA\Property(property="avg_calorie", type="integer", example=4)
     *                 )
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
                ->first();

            if ($csm) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Consume found", 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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
     * @OA\GET(
     *     path="/api/v1/consume/calorie/bytype/{view}",
     *     summary="Get total calorie consumed by its type per day/week/month/year",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="view",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Type of calorie view. Can be 'day', 'week', 'month', or 'year'",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume found"
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Consume view must be all, day, week, month, or year"
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
    public function getCalorieTotalByConsumeType(Request $request, $view){
        try{
            $user_id = $request->user()->id;

            if($view == "all" || $view == "day" || $view == "week" || $view == "month" || $view == "year"){
                $cal = Query::querySelect("get_from_json_col","consume_detail","calorie");

                $csm = Consume::selectRaw("
                    CAST(SUM($cal)as UNSIGNED) as calorie, consume_type
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
                        'message' => "Consume found", 
                        'data' => $csm
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Consume not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume view must be all, day, week, month, or year',
                    'data' => null
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * @OA\GET(
     *     path="/api/v1/consume/by/context/{ctx}/{target}",
     *     summary="Get consume (consume custom)",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="ctx",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Context of consume. Can be 'all', 'provide', or 'main_ing'",
     *         example="main_ing",
     *     ),
     *     @OA\Parameter(
     *         name="target",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Search key based on context",
     *         example="rice",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume found"
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Consume context not valid"
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
    public function getConsumeByContext(Request $request, $ctx, $target){
        try{
            $user_id = $request->user()->id;
            $is_ctx_valid = true;

            $consume = Consume::selectRaw('id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag')
                ->where('created_by',$user_id)
                ->orderby('created_at','desc');

            if($ctx != "all"){
                if ($ctx == 'provide' || $ctx == 'main_ing') {
                    $consume->whereRaw("REPLACE(JSON_UNQUOTE(JSON_EXTRACT(consume_detail, '$[0].$ctx')), '\"', '') = ?", $target);
                } else if($ctx == 'consume_from' || $ctx == 'consume_type'){
                    $consume->where($ctx,$target);
                } else if($ctx == 'month'){
                    $consume->whereRaw("MONTH(created_at) = ?",$target);
                } else if($ctx == 'month_year'){
                    $date = explode("_", $target);
                    $consume->whereRaw("MONTH(created_at) = ?",$date[0])
                        ->whereRaw("YEAR(created_at) = ?",$date[1]);
                } else {
                    $is_ctx_valid = false;
                }

                if($request->limit){
                    $consume = $consume->limit($request->limit)
                        ->get();
                } 
            } else {
                $date = explode("_", $request->date);

                $consume->where(function ($query) use ($request, $date) {
                    $query->whereRaw("REPLACE(JSON_UNQUOTE(JSON_EXTRACT(consume_detail, '$[0].provide')), '\"', '') = ?", [$request->provide])
                        ->orWhereRaw("REPLACE(JSON_UNQUOTE(JSON_EXTRACT(consume_detail, '$[0].main_ing')), '\"', '') = ?", [$request->main_ing])
                        ->orWhere('consume_from', $request->consume_from)
                        ->orWhere('consume_type', $request->consume_type)
                        ->orWhereRaw("MONTH(created_at) = ?", [$date[0]])
                        ->orWhereRaw("YEAR(created_at) = ?", [$date[1]]);
                    });
            }
            $consume = $consume->get();

            if($is_ctx_valid){
                if ($consume->count() > 0) {
                    foreach ($consume as $idx => $csm) {
                        $schedule = Schedule::select('schedule_time')
                            ->where('id', $csm['id'])
                            ->get();
    
                        $consume[$idx]->schedule = $schedule;
                    }
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => "Consume found", 
                        'data' => $consume
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Consume not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume context not valid',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * @OA\GET(
     *     path="/api/v1/consume/list/select",
     *     summary="Get list consume)",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="slug_name", type="string", example="bakso-campur"),
     *                          @OA\Property(property="consume_name", type="string", example="Bakso Campur"),
     *                          @OA\Property(property="consume_type", type="string", example="Food")
     *                 )
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function getListConsume(Request $request){
        try{
            $user_id = $request->user()->id;

            $consume = Consume::select('slug_name', 'consume_name','consume_type')
                ->where('created_by',$user_id)
                ->orderby('consume_name','asc')
                ->get();

            if ($consume->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Consume found", 
                    'data' => $consume
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
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

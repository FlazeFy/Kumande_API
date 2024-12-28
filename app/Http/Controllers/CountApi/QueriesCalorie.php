<?php

namespace App\Http\Controllers\CountApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\CountCalorie;

// Helpers
use App\Helpers\Generator;

class QueriesCalorie extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/count/calorie",
     *     summary="Get latest count calorie data",
     *     tags={"Count"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Count data found"
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
     *         description="Count data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Count data not found")
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
    public function getLastCountCalorie(Request $request){
        try{
            $user_id = $request->user()->id;

            $cal = CountCalorie::select('weight', 'height', 'result','created_at')
                ->where('created_by', $user_id)
                ->orderBy('created_at', 'DESC')
                ->limit(1)->get();

            if($cal){
                foreach ($cal as $c) {
                    $c->weight = intval($c->weight);
                    $c->height = intval($c->height);
                    $c->result = intval($c->result);
                    $c->created_at = $c->created_at;
                }

                return response()->json([
                    "message"=> "Count data found", 
                    "status"=> 'success',
                    "data"=> $cal
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message"=> "Count data not found", 
                    "status"=> 'success',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/count/calorie/fulfill/{date}",
     *     summary="Get total calorie and fullfiled from date",
     *     tags={"Count"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Calorie date",
     *         example="2024-08-08",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Count data found"
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
     *         description="Count data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Count data not found")
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
    public function getFulfillCalorie(Request $request,$date){
        try{
            $user_id = $request->user()->id;

            $csm = DB::select(DB::raw("SELECT SUM(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '')) as total, 
                    (SELECT result FROM count_calorie ORDER BY created_at DESC limit 1) as target
                    FROM consume
                    where date(created_at) = '".$date."'
                "));

            foreach($csm as $c){
                $c->target = intval($c->target);
                $c->total = intval($c->total);
            }

            if($csm){
                return response()->json([
                    'status' => 'success',
                    'message' => "Count data found", 
                    'data' => $csm[0]
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => "Count data not found",
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

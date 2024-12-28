<?php

namespace App\Http\Controllers\CountApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\CountCalorie;
use App\Models\Consume;

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
     *         description="analytic data fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="analytic data fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="weight", type="integer", example=62),
     *                 @OA\Property(property="height", type="integer", example=182),
     *                 @OA\Property(property="result", type="integer", example=1800),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-14 02:28:37"),
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
                ->first();

            if($cal){
                return response()->json([
                    "message"=> Generator::getMessageTemplate("fetch", 'count data'), 
                    "status"=> 'success',
                    "data"=> $cal
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message"=> Generator::getMessageTemplate("not_found", 'count data'), 
                    "status"=> 'failed',
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
     *         description="Count data found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="count data fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=240),
     *                 @OA\Property(property="target", type="integer", example=1900),
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

            $csm = Consume::selectRaw(
                    "SUM(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '')) as total"
                )
                ->selectRaw("(SELECT result FROM count_calorie ORDER BY created_at DESC LIMIT 1) as target")
                ->whereDate('created_at', $date)
                ->first();
            
            if($csm){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'count data'), 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'count data'),
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

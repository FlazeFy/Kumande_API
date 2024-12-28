<?php

namespace App\Http\Controllers\UserApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Math;

// Models
use App\Models\BodyInfo;
use App\Models\CountCalorie;

class QueriesBodyInfo extends Controller
{
     /**
     * @OA\GET(
     *     path="/api/v1/user/body_info",
     *     summary="Get my body info (Medstory)",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User body info found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="body info fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="blood_pressure", type="string", example="126/90"),
     *                 @OA\Property(property="blood_glucose", type="integer", example=82),
     *                 @OA\Property(property="gout", type="number", format="float", example=5.8),
     *                 @OA\Property(property="cholesterol", type="integer", example=178),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-10 09:56:46"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="weight", type="integer", example=62),
     *                 @OA\Property(property="height", type="integer", example=182),
     *                 @OA\Property(property="result", type="integer", example=1800),
     *                 @OA\Property(property="calorie_updated", type="string", format="date-time", example="2024-07-30 23:56:40"),
     *                 @OA\Property(property="born_at", type="string", format="date", example="2001-08-08"),
     *                 @OA\Property(property="age", type="integer", example=23),
     *                 @OA\Property(property="bmi", type="number", format="float", example=18.72)
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
     *         description="User body info not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User body info not found")
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
    public function getMyLatestBodyInfo(Request $request){
        try{
            $user_id = $request->user()->id;

            $usr = BodyInfo::select('blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'body_info.created_at','gender')
                ->join('user','user.id','=','body_info.created_by')
                ->where('body_info.created_by', $user_id)
                ->orderby('body_info.created_at','desc')
                ->first();

            $cal = CountCalorie::selectRaw('weight,height,result,count_calorie.created_at as calorie_updated,gender,born_at,TIMESTAMPDIFF(YEAR, born_at, CURDATE()) AS age')
                ->join('user','user.id','=','count_calorie.created_by')
                ->where('count_calorie.created_by',$user_id)
                ->orderby('count_calorie.created_at','desc')
                ->first();

            if($cal){
                $cal->bmi = Math::countBMI($cal->gender,$cal->height,$cal->weight);
            }

            if ($usr && $cal) {
                $usrArray = $usr->toArray();
                $calArray = $cal->toArray();
                $bodyInfo = array_merge($usrArray, $calArray);
            
                return response()->json([
                    "message" => Generator::getMessageTemplate("fetch", 'body info'),
                    "status" => 'success',
                    "data" => $bodyInfo
                ], Response::HTTP_OK);
            } elseif ($usr) {
                return response()->json([
                    "message" => Generator::getMessageTemplate("fetch", 'body info'),
                    "status" => 'success',
                    "data" => $usr
                ], Response::HTTP_OK);
            } elseif ($cal) {
                return response()->json([
                    "message" => Generator::getMessageTemplate("fetch", 'body info'),
                    "status" => 'success',
                    "data" => $cal
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message" => Generator::getMessageTemplate("not_found", 'body info'),
                    "status" => 'failed',
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
     *     path="/api/v1/user/my_body_history",
     *     summary="Get all my body info (Medstory) & Calorie data",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User body history found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="body history fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="body_info", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="0264b734-3e92-11ef-8fb5-3216422910e9"),
     *                         @OA\Property(property="blood_pressure", type="string", example="126/90"),
     *                         @OA\Property(property="blood_glucose", type="integer", example=82),
     *                         @OA\Property(property="gout", type="number", format="float", example=5.8),
     *                         @OA\Property(property="cholesterol", type="integer", example=178),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-10 09:56:46")
     *                     )
     *                 ),
     *                 @OA\Property(property="calorie", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="83e64f09-e15f-f1a1-0e97-eafa226008db"),
     *                         @OA\Property(property="weight", type="integer", example=62),
     *                         @OA\Property(property="height", type="integer", example=182),
     *                         @OA\Property(property="result", type="integer", example=1800),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-30 23:56:40")
     *                     )
     *                 ),
     *                 @OA\Property(property="dashboard", type="object",
     *                     @OA\Property(property="max_blood_glucose", type="integer", example=82),
     *                     @OA\Property(property="min_blood_glucose", type="integer", example=82),
     *                     @OA\Property(property="max_gout", type="number", format="float", example=5.8),
     *                     @OA\Property(property="min_gout", type="number", format="float", example=5.8),
     *                     @OA\Property(property="max_cholesterol", type="integer", example=178),
     *                     @OA\Property(property="min_cholesterol", type="integer", example=178),
     *                     @OA\Property(property="max_weight", type="integer", example=62),
     *                     @OA\Property(property="min_weight", type="integer", example=62),
     *                     @OA\Property(property="max_height", type="integer", example=182),
     *                     @OA\Property(property="min_height", type="integer", example=182)
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
     *         description="User body history not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User body history not found")
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
    public static function getMyBodyHistory(Request $request){
        try{
            $user_id = $request->user()->id;

            $usr = BodyInfo::select('body_info.id','blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'body_info.created_at')
                ->join('user','user.id','=','body_info.created_by')
                ->where('body_info.created_by', $user_id)
                ->orderby('body_info.created_at','desc')
                ->get();

            $cal = CountCalorie::select('count_calorie.id','weight','height','result','count_calorie.created_at')
                ->join('user','user.id','=','count_calorie.created_by')
                ->where('count_calorie.created_by',$user_id)
                ->orderby('count_calorie.created_at','desc')
                ->get();

            $dashboard = BodyInfo::selectRaw('MAX(blood_glucose) as max_blood_glucose, MIN(blood_glucose) as min_blood_glucose, MAX(gout) as max_gout, 
                MIN(gout) as min_gout, MAX(cholesterol) as max_cholesterol, MIN(cholesterol) as min_cholesterol,
                MAX(weight) as max_weight, MIN(weight) as min_weight, MAX(height) as max_height, MIN(height) as min_height')
                ->leftjoin('count_calorie','count_calorie.created_by','=','body_info.created_by')
                ->where('count_calorie.created_by', $user_id)
                ->orwhere('body_info.created_by', $user_id)
                ->first();

            if ($usr || $cal) {
                return response()->json([
                    "data" => (object)[
                        "body_info" => $usr,
                        "calorie" => $cal,
                        "dashboard" => $dashboard
                    ],
                    "message" => Generator::getMessageTemplate("fetch", 'body history'),
                    "status" => 'success'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message" => Generator::getMessageTemplate("not_found", 'body history'),
                    "status" => 'failed',
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

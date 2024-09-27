<?php

namespace App\Http\Controllers\ScheduleApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Schedule;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/schedule",
     *     summary="Get list schedule consume in a week",
     *     tags={"Schedule"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Schedule found"
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
     *         description="Schedule not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Schedule not found")
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
    public function getMySchedule(Request $request){
        try{
            $user_id = $request->user()->id;

            $sch = DB::select(DB::raw("SELECT 
                    day, time, GROUP_CONCAT(schedule_consume SEPARATOR ', ') AS schedule_consume
                    FROM (
                    SELECT 
                        REPLACE(JSON_EXTRACT(schedule_time, '$[0].day'), '\"', '') AS day, 
                        REPLACE(JSON_EXTRACT(schedule_time, '$[0].category'), '\"', '') AS time,
                            schedule_consume
                        FROM `schedule`
                        WHERE created_by = '".$user_id."'
                    ) AS q
                    GROUP BY 1, 2
                    ORDER BY DAYNAME(1)
                "));
        
            if (count($sch) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Schedule found", 
                    'data' => $sch
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Schedule not found',
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
     *     path="/api/v1/schedule/day/{day}",
     *     summary="Get schedule consume in a day",
     *     tags={"Schedule"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Schedule found"
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
     *         description="Schedule not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Schedule not found")
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
    public function getTodaySchedule(Request $request, $day){
        try{
            $user_id = $request->user()->id;

            $sch = Schedule::select('*')
                ->where('created_by', $user_id)
                ->whereRaw("schedule_time LIKE '%".'"'."day".'"'.":".'"'.$day.'"'."%'")
                ->orderByRaw("JSON_EXTRACT(schedule_time, '$[0].time') ASC")
                ->get();

            if($sch){
                return response()->json([
                    "message"=> "Schedule found", 
                    "status"=> 'success',
                    "data"=> $sch
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message"=> "Schedule not found", 
                    "status"=> 'failed',
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

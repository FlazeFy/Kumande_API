<?php

namespace App\Http\Controllers\ScheduleApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\Schedule;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Query;

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
     *         description="Schedule found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="schedule fetched"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="day", type="string", example="Tue"),
     *                     @OA\Property(property="time", type="string", example="Lunch"),
     *                     @OA\Property(property="schedule_consume", type="string", example="Semangka potong, Nasi Warteg (Tahu Kari, Sayur Jantung Pisang, Terong Sambal)")
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
            $time_query = Query::querySelect("get_from_json_col_str","schedule_time","category");
            $day_query = Query::querySelect("get_from_json_col_str","schedule_time","day");

            $sch = Schedule::selectRaw("
                    $day_query AS day,
                    $time_query AS time,
                    GROUP_CONCAT(consume_name SEPARATOR ', ') AS schedule_consume
                ")
                ->join('consume','consume.id','=','schedule.consume_id')
                ->where('schedule.created_by', $user_id)
                ->groupBy(DB::raw("$day_query"), DB::raw("$time_query"))
                ->orderByRaw("DAYNAME($day_query)")
                ->get();
        
            if (count($sch) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'schedule'), 
                    'data' => $sch
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'schedule'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
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
     *         description="Schedule found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="schedule fetched"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="51d5660a-4140-4938-2ec8-952d75d08117"),
     *                     @OA\Property(property="consume_name", type="string", example="Bakso Urat"),
     *                     @OA\Property(property="schedule_desc", type="string", example="Patungan bagi 3 (John, Jane, Doe)"),
     *                     @OA\Property(property="schedule_time",type="object",
     *                             @OA\Property(property="day", type="string", example="Fri"),
     *                             @OA\Property(property="category", type="string", example="Breakfast"),
     *                             @OA\Property(property="time", type="string", example="07:00")
     *                     ),
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
            $time_query = Query::querySelect("get_from_json_col_str","schedule_time","time");

            $sch = Schedule::select('schedule.id','schedule_desc','consume_name','schedule_time')
                ->join('consume','consume.id','=','schedule.consume_id')
                ->where('schedule.created_by', $user_id)
                ->whereRaw("schedule_time LIKE '%".'"'."day".'"'.":".'"'.$day.'"'."%'")
                ->orderByRaw("$time_query ASC")
                ->get();

            if(count($sch) > 0){
                $res = [];
                foreach ($sch as $dt) {
                    $res[] = [
                        'id' => $dt->id,
                        'schedule_desc' => $dt->schedule_desc,
                        'consume_name' => $dt->consume_name,
                        'schedule_time' => $dt->schedule_time[0],
                    ];    
                }

                return response()->json([
                    "message"=> Generator::getMessageTemplate("fetch", 'schedule'), 
                    "status"=> 'success',
                    "data"=> $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message"=> Generator::getMessageTemplate("custom", 'no schedule for today'), 
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
}

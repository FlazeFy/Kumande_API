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
    public function getMySchedule(Request $request) {
        try {
            $user_id = $request->user()->id;

            $res = Schedule::findMySchedule($user_id);        
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'schedule'), 
                    'data' => $res
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
    public function getScheduleByDay(Request $request, $day) {
        try {
            $user_id = $request->user()->id;

            $res = Schedule::findScheduleByDay($user_id, $day);
            if (count($res) > 0) {
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

<?php

namespace App\Http\Controllers\ReminderApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

// Models
use App\Models\Reminder;

// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/reminder",
     *     summary="Get list available reminder",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reminders found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="reminder fetched"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="reminder_id", type="string", example="1c8a4d88-d9b0-11ed-afa1-0242ac120002"),
     *                     @OA\Property(property="reminder_name", type="string", example="Reminder : Weekly Juice"),
     *                     @OA\Property(property="reminder_type", type="string", example="Every Year"),
     *                     @OA\Property(property="reminder_context", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="time", type="string", example="02 July")
     *                         )
     *                     ),
     *                     @OA\Property(property="reminder_body", type="string", example="Drink Orange Juice, Apple Juice, Carrot Juice, or Mango Juice"),
     *                     @OA\Property(property="reminder_attachment", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="attachment_type", type="string", example="location"),
     *                             @OA\Property(property="attachment_context", type="string", example="-6.22686285578315, 106.82139153159926"),
     *                             @OA\Property(property="attachment_name", type="string", example="Alfamidi")
     *                         )
     *                     ),
     *                     @OA\Property(property="id_rel_reminder", type="string", example="b48111fc-d11a-ffc5-1850-7b03c2e913fa")
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
     *         description="Reminder not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Reminder not found")
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
    public function getListReminder(Request $request){
        try{
            $user_id = $request->user()->id;

            $res = Reminder::select('reminder.id as reminder_id','reminder_name','reminder_type','reminder_context','reminder_body','reminder_attachment','rel_reminder_used.id as id_rel_reminder')
                ->leftjoin('rel_reminder_used','rel_reminder_used.reminder_id','=','reminder.id')
                ->orderby('reminder.created_at','DESC')
                ->where('reminder.created_by',$user_id)
                ->orwhereNull('reminder.created_by')
                ->get();

            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'reminder'), 
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'reminder'),
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

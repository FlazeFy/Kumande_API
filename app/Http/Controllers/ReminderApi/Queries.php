<?php

namespace App\Http\Controllers\ReminderApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Models\Reminder;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/reminder",
     *     summary="Get list available reminder",
     *     tags={"Reminder"},
     *     @OA\Response(
     *         response=200,
     *         description="Reminder found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reminder not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
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
                    'message' => "Reminder found", 
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Reminder not found',
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

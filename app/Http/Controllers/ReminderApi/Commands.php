<?php

namespace App\Http\Controllers\ReminderApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Models\Reminder;
use App\Models\RelReminderUsed;

class Commands extends Controller
{
    public function createReminderRel(Request $request){
        try{
            $validator = Validation::getValidateAddReminder($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {     
                $user_id = $request->user()->id;
                $id = Generator::getUUID();

                $res = RelReminderUsed::create([
                    'id' => $id, 
                    'reminder_id' => $request->reminder_id, 
                    'created_by' => $user_id, 
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => "Reminder turned on!", 
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Reminder failed to created',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function deleteReminderRel(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            $res = RelReminderUsed::where('created_by',$user_id)
                ->where('id',$id)
                ->delete();
        
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Reminder turned off!", 
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Reminder failed to deleted',
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

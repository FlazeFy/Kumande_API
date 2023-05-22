<?php

namespace App\Http\Controllers\ScheduleApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Converter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

use App\Models\Schedule;

class Commands extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function deleteScheduleById($id){
        try{
            Schedule::where('id', $id)->delete();

            return response()->json([
                "msg"=> "Data deleted", 
                "status"=> 200
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateScheduleData(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'schedule_consume' => 'required|max:75|min:1',
                'schedule_desc' => 'nullable|max:255|min:1',
                'schedule_tag' => 'nullable|json',
                'schedule_time' => 'required|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $sch = Schedule::where('id', $id)->update([
                    'schedule_consume' => $request->schedule_consume,
                    'schedule_desc' => $request->schedule_desc,
                    'schedule_tag' => $request->schedule_tag,
                    'schedule_time' => $request->schedule_time,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Schedule updated',
                    'data' => $sch
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createSchedule(Request $request){
        try{
            $validator = Validation::getValidateCreateSchedule($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $check = Generator::checkSchedule($request->schedule_time);
                $user_id = $request->user()->id;

                if(!$check){
                    $id = Generator::getUUID();
                    $slug = Generator::getSlug($request->schedule_consume, "schedule");

                    $jsonDetail = Converter::getEncoded($request->consume_detail);
                    $jsonTag = Converter::getEncoded($request->schedule_tag);
                    $jsonTime = Converter::getEncoded($request->schedule_time);
                    $detail = json_decode($jsonDetail, true);
                    $tag = json_decode($jsonTag, true);
                    $time = json_decode($jsonTime, true);

                    $sch = Schedule::create([
                        'id' => $id, 
                        'firebase_id' => $request->firebase_id, 
                        'slug_name' => $slug, 
                        'schedule_consume' => $request->schedule_consume,
                        'consume_type' => $request->consume_type,
                        'consume_detail' => $detail,
                        'schedule_desc' => $request->schedule_desc,
                        'schedule_tag' => $tag,
                        'schedule_time' => $time,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => $user_id,
                        'updated_at' => null,
                        'updated_by' => null
                    ]);

                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $request->token_fcm)
                        ->withNotification(Notification::create('You have successfully added new meals to schedule called ', $request->schedule_consume))
                        ->withData([
                            'schedule_consume' => $request->schedule_consume,
                        ]);
                    $response = $messaging->send($message);
            
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Schedule created',
                        'data' => $sch
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'There is a schedule with same day and category',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

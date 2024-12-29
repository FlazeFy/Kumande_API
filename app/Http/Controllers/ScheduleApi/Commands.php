<?php

namespace App\Http\Controllers\ScheduleApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

// Models
use App\Models\Schedule;
use App\Models\User;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Converter;

class Commands extends Controller
{
    /**
     * @OA\DELETE(
     *     path="/api/v1/schedule/delete/{id}",
     *     summary="Delete schedule by id",
     *     tags={"Schedule"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Schedule ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Schedule delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Schedule deleted"),
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
    public function deleteScheduleById($id){
        try{
            $user_id = $request->user()->id;

            $res = Schedule::where('id', $id)
                ->where('created_by',$user_id)
                ->delete();

            if($res){
                return response()->json([
                    "message"=> Generator::getMessageTemplate("delete", 'schedule'), 
                    "status"=> 'success'
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
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/schedule/update/data/{id}",
     *     summary="Update schedule by id",
     *     tags={"Schedule"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Schedule ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Schedule update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Schedule updated"),
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
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
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
    public function updateScheduleDataById(Request $request, $id){
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

                if($sch){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", 'schedule')
                    ], Response::HTTP_OK);   
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'schedule')
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/schedule/create",
     *     summary="Create schedule",
     *     tags={"Schedule"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Schedule create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Schedule created"),
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
     *         response=409,
     *         description="Item is exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="There is a schedule with same day and category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
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
    public function createSchedule(Request $request){
        try{
            $success_add = 0;
            $user_id = $request->user()->id;
            $schedule_consume = "";

            if(!$request->schedule_consume){
                $schedule = json_decode($request->getContent(), true);

                foreach($schedule as $dt){
                    $validator = Validation::getValidateCreateSchedule(new Request($dt));
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => 'error',
                            'result' => $validator->errors()
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    } else { 
                        $reqnew = new Request($dt);
                        $check = Generator::checkSchedule($reqnew['schedule_time']);

                        if(!$check){
                            $id = Generator::getUUID();

                            $jsonDetail = Converter::getEncoded($dt['consume_detail']);
                            $jsonTag = Converter::getEncoded($dt['schedule_tag']);
                            $jsonTime = Converter::getEncoded($dt['schedule_time']);
                            $detail = json_decode($jsonDetail, true);
                            $tag = json_decode($jsonTag, true);
                            $time = json_decode($jsonTime, true);

                            $sch = Schedule::create([
                                'id' => $id, 
                                'firebase_id' => $dt['firebase_id'], 
                                'consume_id' => $dt['consume_id'], 
                                'schedule_consume' => $dt['schedule_consume'],
                                'consume_type' => $dt['consume_type'],
                                'consume_detail' => $detail,
                                'schedule_desc' => $dt['schedule_desc'],
                                'schedule_tag' => $tag,
                                'schedule_time' => $time,
                                'created_at' => date("Y-m-d H:i:s"),
                                'created_by' => $user_id,
                                'updated_at' => null,
                                'updated_by' => null
                            ]);

                            $success_add++;
                            $schedule_consume .= $dt['schedule_consume'];
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("conflict", 'schedule'),
                            ], Response::HTTP_CONFLICT);
                        }
                    }
                }
            } else {
                $validator = Validation::getValidateCreateSchedule($request);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'result' => $validator->errors()
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                } else {    
                    $check = Generator::checkSchedule($request->schedule_time);
                    if(!$check){
                        $id = Generator::getUUID();

                        $jsonDetail = Converter::getEncoded($request->consume_detail);
                        $jsonTag = Converter::getEncoded($request->schedule_tag);
                        $jsonTime = Converter::getEncoded($request->schedule_time);
                        $detail = json_decode($jsonDetail, true);
                        $tag = json_decode($jsonTag, true);
                        $time = json_decode($jsonTime, true);

                        $sch = Schedule::create([
                            'id' => $id, 
                            'firebase_id' => $request->firebase_id, 
                            'consume_id' => $request->consume_id, 
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

                        $schedule_consume .= $request->schedule_consume;
                        $success_add++;
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("conflict", 'schedule'),
                        ], Response::HTTP_CONFLICT);
                    }
                }
            }

            if($success_add > 0){
                $user_data = User::getProfile($user_id);
                $fcm_token = $user_data->firebase_fcm_token;
                if($fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $fcm_token)
                        ->withNotification(Notification::create('You have successfully added new meals to schedule called ', $schedule_consume))
                        ->withData([
                            'schedule_consume' => $schedule_consume,
                        ]);
                    $response = $messaging->send($message);
                }
        
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("create", 'schedule'),
                    'data' => $sch
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("unknown_error", null),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

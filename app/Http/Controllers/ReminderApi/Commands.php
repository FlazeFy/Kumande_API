<?php

namespace App\Http\Controllers\ReminderApi;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Converter;
use App\Helpers\Validation;

// Models
use App\Models\Reminder;
use App\Models\RelReminderUsed;
use App\Models\User;

class Commands extends Controller
{
     /**
     * @OA\POST(
     *     path="/api/v1/reminder/rel",
     *     summary="Create reminder relation",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reminder relation create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Reminder turned on!"),
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
    public function createReminderRel(Request $request){
        try{
            $validator = Validation::getValidateAddReminderRel($request);

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
                        'status' => 'error',
                        'message' => Generator::getMessageTemplate("unknown_error", null)
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/reminder/rel/{rel_id}",
     *     summary="Delete reminder relation by id",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="rel_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Reminder Relation ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reminder relation delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Reminder turned off!"),
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
     *             @OA\Property(property="message", type="string", example="Reminder relation not found")
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
    public function deleteReminderRelByRelId(Request $request, $id){
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
                    'message' => 'Reminder relation not found',
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
     * @OA\DELETE(
     *     path="/api/v1/reminder/delete/{id}",
     *     summary="Delete reminder by id",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Reminder ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reminder delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Reminder is deleted"),
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
    public function deleteReminderById(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            $reminder = Reminder::where('created_by',$user_id)
                ->where('id', $id)
                ->delete();

            if($reminder){
                RelReminderUsed::where('created_by',$user_id)
                    ->where('reminder_id',$id)
                    ->delete();

                $user = User::select('username','telegram_user_id','line_user_id','firebase_fcm_token')
                    ->where('id',$user_id)
                    ->first();

                $message = "Hello $user->username,\n\n$request->reminder_name has been deleted. Please check your current reminder list!";

                if($user->telegram_user_id){
                    $response = Telegram::sendMessage([
                        'chat_id' => $user->telegram_user_id,
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
                if($user->line_user_id){
                    LineMessage::sendMessage('text',$message,$user->line_user_id);
                }
                if($user->firebase_fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $user->firebase_fcm_token)
                        ->withNotification(Notification::create($message, $user->username));
                    $response = $messaging->send($message);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => "Reminder is deleted", 
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
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * @OA\POST(
     *     path="/api/v1/reminder/add",
     *     summary="Create reminder",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reminder create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Reminder is created"),
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
     *             @OA\Property(property="message", type="string", example="Reminder already exist")
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
    public function createReminder(Request $request){
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

                $check = Reminder::selectRaw('1')
                    ->where('created_by', $user_id)
                    ->where('reminder_name', $request->reminder_name)
                    ->first();

                if(!$check){
                    if($request->reminder_context){
                        $jsonReminderContext = Converter::getEncoded($request->reminder_context);
                        $reminder_context = json_decode($jsonReminderContext, true);
                    }
                    if($request->reminder_attachment){
                        $jsonReminderAttachment = Converter::getEncoded($request->reminder_attachment);
                        $reminder_attachment = json_decode($jsonReminderAttachment, true);
                    } else {
                        $reminder_attachment = null;
                    }

                    $res = Reminder::create([
                        'id' => $id, 
                        'reminder_name' => "Reminder : ".$request->reminder_name, 
                        'reminder_type' => $request->reminder_type, 
                        'reminder_context' => $reminder_context, 
                        'reminder_body' => $request->reminder_body, 
                        'reminder_attachment' => $reminder_attachment,
                        'created_by' => $user_id, 
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                
                    if ($res) {
                        if($request->test_remind){
                            $user = User::select('username','telegram_user_id','line_user_id','firebase_fcm_token')
                                ->where('id',$user_id)
                                ->first();

                            $message = "Hello $user->username,\n\nYou has been created a new reminder. This is the demo with message $request->reminder_body.\n\nThis reminder is set to $request->reminder_type";

                            if($request->reminder_context){
                                $message .= " with detail for ";
                                $contexts = $jsonReminderContext;
                                $total = count($contexts);

                                foreach($contexts as $idx => $dt){
                                    $message .= $dt['time'];

                                    if($idx < $total - 1){
                                        $message .= ", ";
                                    } else {
                                        $message .= ".";
                                    }
                                }
                            } else {
                                $message .= " but the time detail has not configured yet. Set it up to use it";
                            }
                            $message .= "\n\nThank You!";

                            if($user->telegram_user_id){
                                $response = Telegram::sendMessage([
                                    'chat_id' => $user->telegram_user_id,
                                    'text' => $message,
                                    'parse_mode' => 'HTML'
                                ]);
                            }
                            if($user->line_user_id){
                                LineMessage::sendMessage('text',$message,$user->line_user_id);
                            }
                            if($user->firebase_fcm_token){
                                $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                                $messaging = $factory->createMessaging();
                                $message = CloudMessage::withTarget('token', $user->firebase_fcm_token)
                                    ->withNotification(Notification::create($message, $id))
                                    ->withData([
                                        'id' => $id,
                                    ]);
                                $response = $messaging->send($message);
                            }
                        }

                        return response()->json([
                            'status' => 'success',
                            'message' => "Reminder is created", 
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Reminder already exist',
                    ], Response::HTTP_CONFLICT);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

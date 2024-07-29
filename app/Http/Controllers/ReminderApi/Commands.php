<?php

namespace App\Http\Controllers\ReminderApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Helpers\Generator;
use App\Helpers\Converter;
use App\Helpers\Validation;

use App\Models\Reminder;
use App\Models\RelReminderUsed;
use App\Models\User;

use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class Commands extends Controller
{
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
    public function deleteReminder(Request $request, $id){
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

                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => "Reminder deleted", 
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Reminder failed to deleted',
                    ], Response::HTTP_NOT_FOUND);
                }
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
                            'message' => "Reminder created", 
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Reminder failed to created',
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Reminder has already exist',
                    ], Response::HTTP_CONFLICT);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

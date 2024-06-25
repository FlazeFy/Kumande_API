<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Converter;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

use App\Models\Consume;
use App\Models\Payment;
use App\Models\User;

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

    public function deleteConsumeById($id){
        Consume::where('id', $id)->delete();

        return response()->json([
            "message"=> "Data deleted", 
            "status"=> 200
        ]);
    }

    public function updateConsumeData(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'consume_type' => 'required|max:10|min:1',
                'consume_name' => 'required|json',
                'consume_from' => 'required|max:10|min:1',
                'consume_payment' => 'required|json',
                'consume_tag' => 'nullable|json',
                'consume_comment' => 'nullable|max:255|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $csm = Consume::where('id', $id)->update([
                    'consume_type' => $request->consume_type,
                    'consume_name' => $request->consume_name,
                    'consume_from' => $request->consume_from,
                    'consume_payment' => $request->consume_payment,
                    'consume_tag' => $request->consume_tag,
                    'consume_comment' => $request->consume_comment,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Consume updated',
                    'data' => $csm
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateConsumeFavorite(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'is_favorite' => 'required|max:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $csm = Consume::where('id', $id)->update([
                    'is_favorite' => $request->is_favorite,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Consume updated',
                    'data' => $csm
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createConsume(Request $request){
        try{
            $validator = Validation::getValidateCreateConsume($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $user_id = $request->user()->id;
                $id = Generator::getUUID();
                $clean_name = strtolower(str_replace(' ','',$request->consume_name));
                $name_ava = Consume::searchConsumeNameAvailable($user_id, $clean_name);
                $payment_only = true;

                if(!$name_ava){
                    $payment_only = false;
                    $slug = Generator::getSlug($request->consume_name, "consume");
                    $jsonDetail = Converter::getEncoded($request->consume_detail);
                    $jsonTag = Converter::getEncoded($request->consume_tag);
                    // $jsonDetail = json_encode($request->consume_detail);
                    // $jsonTag =  json_encode($request->consume_tag);
                    $detail = json_decode($jsonDetail, true);
                    $tag = json_decode($jsonTag, true);

                    $csm = Consume::create([
                        'id' => $id,
                        'slug_name' => $slug,
                        'firebase_id' => $request->firebase_id,
                        'consume_type' => $request->consume_type,
                        'consume_name' => $request->consume_name,
                        'consume_detail' => $detail,
                        'consume_from' => $request->consume_from,
                        'is_favorite' => $request->is_favorite,
                        'consume_tag' => $tag,
                        'consume_comment' => $request->consume_comment,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => null,
                        'deleted_at' => null,
                        'created_by' => $user_id,
                        'updated_by' => null,
                        'deleted_by' => null,
                    ]);
                } else {
                    $id = $name_ava;
                }

                $pym = Payment::create([
                    'id' => Generator::getUUID(),
                    'consume_id' => $id,
                    'payment_method' => $request->payment_method,  
                    'payment_price' => $request->payment_price,
                    'is_payment' => $request->is_payment,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => null,
                    'created_by' => $user_id,
                    'updated_by' => null,
                ]);

                $user = User::getProfile($user_id);
                $fcm_token = $user->firebase_fcm_token;
                if($fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $fcm_token)
                        ->withNotification(Notification::create('You have successfully added new meals to history called ', $request->consume_name))
                        ->withData([
                            'consume_name' => $request->consume_name,
                        ]);
                    $response = $messaging->send($message);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => $payment_only ? 'You have add new payment' : 'You have add new payment and consume',
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

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
use App\Models\Schedule;
use App\Models\Payment;
use App\Models\RelConsumeList;
use App\Models\User;

class Commands extends Controller
{
    /**
     * @OA\DELETE(
     *     path="/api/v1/consume/delete/{id}",
     *     summary="Delete consume by id",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume is deleted"),
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function deleteConsumeById(Request $request, $id){
        try{ 
            $user_id = $request->user()->id;
            $res = Consume::where('id', $id)
                ->where('created_by',$user_id)
                ->delete();

            if($res){
                Schedule::where('consume_id', $id)
                    ->where('created_by',$user_id)
                    ->delete();

                RelConsumeList::where('consume_id', $id)
                    ->where('created_by',$user_id)
                    ->delete();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Consume is deleted',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/consume/update/data/{id}",
     *     summary="Update consume by id",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume is update"),
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function updateConsumeData(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'consume_type' => 'required|max:10|min:1',
                'consume_name' => 'required|max:75|min:3',
                'consume_from' => 'required|max:10|min:1',
                'consume_tag' => 'nullable|json',
                'consume_comment' => 'nullable|max:255|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $jsonDetail = Converter::getEncoded($request->consume_detail);
                $detail = json_decode($jsonDetail, true);

                $csm = Consume::where('id', $id)->update([
                    'consume_type' => $request->consume_type,
                    'consume_name' => $request->consume_name,
                    'consume_from' => $request->consume_from,
                    'consume_tag' => $request->consume_tag,
                    'consume_detail' => $detail,
                    'consume_comment' => $request->consume_comment,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                if($csm){
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Consume is updated',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Consume not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/consume/update/favorite/{id}",
     *     summary="Update consume favorite by id",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume favorite update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume favorite is update"),
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
     *         description="Consume favorite not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume favorite not found")
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

                if($csm){
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Consume favorite is updated',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Consume favorite not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/consume/create",
     *     summary="Create consume",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="You have add new payment and consume"),
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

                    if($request->created_at){
                        $created_at = $request->created_at;
                    } else {
                        $created_at = date("Y-m-d H:i:s");
                    }

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
                        'created_at' => $created_at,
                        'updated_at' => null,
                        'deleted_at' => null,
                        'created_by' => $user_id,
                    ]);
                } else {
                    $id = $name_ava;
                }

                if($request->payment_price != 0 && ($request->payment_method != "Free" || $request->payment_method != "Gift")){
                    $pym = Payment::create([
                        'id' => Generator::getUUID(),
                        'consume_id' => $id,
                        'payment_method' => $request->payment_method,  
                        'payment_price' => $request->payment_price,
                        'created_at' => $created_at,
                        'updated_at' => null,
                        'created_by' => $user_id,
                    ]);
                    $payment_only = false;
                }

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
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

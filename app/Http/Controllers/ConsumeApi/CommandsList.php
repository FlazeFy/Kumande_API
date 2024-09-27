<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\ConsumeList;
use App\Models\Consume;
use App\Models\RelConsumeList;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

use App\Helpers\Validation;
use App\Helpers\Generator;
use App\Helpers\Converter;

class CommandsList extends Controller
{
    /**
     * @OA\DELETE(
     *     path="/api/v1/list/delete/{list_id}",
     *     summary="Delete consume list by list id",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="list_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume List ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume list delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="List is deleted"),
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
     *         description="Consume list not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="List not found")
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
    public function deleteListByListId(Request $request,$list_id){
        try {
            $user_id = $request->user()->id;

            $rel_res = RelConsumeList::where('list_id',$list_id)
                ->where('created_by',$user_id)
                ->delete();

            $res = ConsumeList::where('id', $list_id)
                ->where('created_by',$user_id)
                ->delete();

            if($res){
                return response()->json([
                    "message"=> "List is deleted", 
                    "status"=> 'success'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'List not found',
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
     * @OA\DELETE(
     *     path="/api/v1/list/deleteRel/{rel_id}",
     *     summary="Delete consume list relation by list id",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="rel_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume List Relation ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume list relation delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume removed from list"),
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
     *         description="Consume list relation not found",
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
    public function deleteListRelationByRelId(Request $request,$rel_id){
        try {
            $user_id = $request->user()->id;

            $res = RelConsumeList::where('id', $rel_id)
                ->where('created_by',$user_id)
                ->delete();

            if($res){
                return response()->json([
                    "message"=> "Consume removed from list", 
                    "status"=> 'success'
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
     *     path="/api/v1/list/update/data/{list_id}",
     *     summary="Update consume list by list id",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="list_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume List ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume list update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="List is updated"),
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
     *         description="Consume list not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="List not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
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
    public function updateListData(Request $request, $id){
        try{
            $validator = Validation::getValidateListRelData($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;

                $check = ConsumeList::where('id','!=',$id)
                    ->where('list_name',$request->list_name)
                    ->where('created_by',$user_id)
                    ->first();

                if(!$check){
                    $csl = ConsumeList::where('id', $id)
                        ->where('created_by',$user_id)
                        ->update([
                        'list_name' => $request->list_name,
                        'list_desc' => $request->list_desc,
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);

                    if($csl){
                        return response()->json([
                            'status' => 'success',
                            'message' => 'List is updated',
                            'rows_affected' => $csl
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'List not found',
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'List name has been used',
                    ], Response::HTTP_CONFLICT);
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
     *     path="/api/v1/list/create",
     *     summary="Create consume list",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume list create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="List is created"),
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
     *         description="Conflicted item",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="List is already exist"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
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
    public function createList(Request $request){
        try{
            $validator = Validation::getValidateCreateConsumeList($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;

                if(ConsumeList::getAvailableListName($request->list_name, $user_id)){
                    $slug = Generator::getSlug($request->list_name, "consume_list");

                    if($request->list_tag){
                        $jsonTag = Converter::getEncoded($request->list_tag);
                        $tag = json_decode($jsonTag, true);
                    } else {
                        $tag = null;
                    }
    
                    $csl = ConsumeList::create([
                        'id' => Generator::getUUID(),
                        'firebase_id' => $request->firebase_id,
                        'slug_name' => $slug,
                        'list_name' => $request->list_name,
                        'list_desc' => $request->list_desc,
                        'list_tag' => $tag,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => $user_id,
                        'updated_at' => null,
                        'updated_by' => null,
                    ]);

                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $request->token_fcm)
                        ->withNotification(Notification::create('You have successfully added new list called ', $request->list_name))
                        ->withData([
                            'list_name' => $request->list_name,
                        ]);
                    $response = $messaging->send($message);
    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'List created',
                        'data' => $csl
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'List is already exist',
                    ], Response::HTTP_CONFLICT);
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
     *     path="/api/v1/list/createRel",
     *     summary="Create consume list relation",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume list relation create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume is added to list"),
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
     *         description="Consume to add to list relation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume not found"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflicted item",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume has been used in this list"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
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
    public function createListRelation(Request $request){
        try{
            $validator = Validation::getValidateConsumeListRel($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {     
                $user_id = $request->user()->id;

                $csm = Consume::select('id')
                    ->where('slug_name', $request->consume_slug)
                    ->first();

                if($csm){
                    $check_rel = RelConsumeList::selectRaw('1')
                        ->where('consume_id',$csm->id)
                        ->where('list_id',$request->list_id)
                        ->where('created_by',$user_id)
                        ->first();

                    if($check_rel){
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Consume has been used in this list',
                        ], Response::HTTP_CONFLICT);
                    } else {
                        $rel = RelConsumeList::create([
                            'id' => Generator::getUUID(),
                            'consume_id' => $csm->id, 
                            'list_id' => $request->list_id, 
                            'created_at' => date('Y-m-d H:i:s'), 
                            'created_by' => $user_id
                        ]);
        
                        if($rel){
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Consume is added to list',
                            ], Response::HTTP_OK);
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                'message' => 'Something error please contact admin',
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
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
}

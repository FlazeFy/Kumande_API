<?php

namespace App\Http\Controllers\UserApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Telegram\Bot\Laravel\Facades\Telegram;

// Models
use App\Models\BodyInfo;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;

class CommandsBodyInfo extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/user/body_info/create",
     *     summary="Create body info",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Body info create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Body info created"),
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
    public function createBodyInfo(Request $request){
        try{
            $validator = Validation::getValidateBodyInfo($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $user_id = $request->user()->id;

                $check = BodyInfo::create([
                    'id' => Generator::getUUID(), 
                    'blood_pressure' => $request->blood_pressure, 
                    'blood_glucose' => $request->blood_glucose, 
                    'gout' => $request->gout,  
                    'cholesterol' => $request->cholesterol, 
                    'created_at' => date('Y-m-d H:i:s'), 
                    'created_by' => $user_id
                ]);

                if($check){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("create", 'body info'),
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("unknown_error", null)
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
     * @OA\DELETE(
     *     path="/api/v1/user/body_info/delete/{id}",
     *     summary="Delete body info by id",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Body Info ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Body info delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Body info deleted"),
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
     *         description="Body info not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Body info not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Body info id is not valid to process",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Body info id not valid")
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
    public function deleteBodyInfo(Request $request, $id){
        try {
            $user_id = $request->user()->id;
            $success = 0;
            $failed = 0;
            $ids = explode(",", $id);

            foreach($ids as $dt){
                $res = BodyInfo::where('created_by',$user_id)
                    ->where('id',$dt)
                    ->delete();

                if($res){
                    $success++;
                } else {
                    $failed++;
                }
            }

            if(count($ids) > 0){
                if($success > 0){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("delete", "$success body info"),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'body info'),
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("custom", 'body info ID not valid')
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\CountApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\CountCalorie;

// Helpers
use App\Helpers\Validation;
use App\Helpers\Generator;

class CommandsCalorie extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/count/calorie",
     *     summary="Create count calorie",
     *     tags={"Count"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Count calorie create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Count calorie created"),
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
    public function createCountCalorie(Request $request){
        try{
            $validator = Validation::getValidateCreateCountCalorie($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;

                $ccl = CountCalorie::create([
                    'id' => Generator::getUUID(),
                    'firebase_id' => $request->firebase_id,
                    'weight' => $request->weight,
                    'height' => $request->height,
                    'result' => $request->result,
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => $user_id,
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]);

                if($ccl){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("create", 'count calorie'),
                        'data' => $ccl
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'error',
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
     *     path="/api/v1/count/calorie/{id}",
     *     summary="Delete count calorie data by id",
     *     tags={"Count"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Count calorie ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Calorie data delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="2 Count calorie are deleted"),
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
     *         description="Count calorie data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Count calorie not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Calorie data id is not valid to process",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Count calorie id not valid")
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
    public function deleteCountCalorie(Request $request, $id){
        try {
            $user_id = $request->user()->id;
            $success = 0;
            $failed = 0;
            $ids = explode(",", $id);

            foreach($ids as $dt){
                $res = CountCalorie::where('created_by',$user_id)
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
                        'message' => Generator::getMessageTemplate("delete", "$success count calorie"),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'count calorie'),
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("custom", 'count calorie id not valid')
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

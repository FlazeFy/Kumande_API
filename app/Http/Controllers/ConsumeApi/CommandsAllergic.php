<?php

namespace App\Http\Controllers\ConsumeApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Models
use App\Models\Allergic;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;

class CommandsAllergic extends Controller
{
    /**
     * @OA\PUT(
     *     path="/api/v1/analytic/allergic/{id}",
     *     summary="Update allergic favorite by id",
     *     tags={"Analytic"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Allergic ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Allergic update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Allergic updated | Nothing to Change"),
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
     *         description="Allergic not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Allergice not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Item is exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Allergic context already exist")
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
    public function updateAllergicById(Request $request, $id){
        try{
            $validator = Validation::getValidateAllergic($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {    
                $user_id = $request->user()->id;

                $check = Allergic::selectRaw('1')
                    ->whereRaw('LOWER(allergic_context) = ?', [strtolower($request->allergic_context)])
                    ->where('id','!=',$id)
                    ->where('created_by', $user_id)
                    ->first();

                if($check){
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("conflict", 'allergic context'),
                    ], Response::HTTP_CONFLICT);
                } else {
                    $exist = Allergic::where('id', $id)
                        ->where('created_by', $user_id)
                        ->first();

                    if($exist){
                        $res = Allergic::where('id',$id)
                            ->where('created_by',$user_id)
                            ->update([
                                'allergic_context' => $request->allergic_context, 
                                'allergic_desc'  => $request->allergic_desc, 
                            ]);

                        if($res){
                            return response()->json([
                                'status' => 'success',
                                'message' => Generator::getMessageTemplate("update", 'allergic'),
                            ], Response::HTTP_OK);
                        } else {
                            return response()->json([
                                'status' => 'success',
                                'message' => Generator::getMessageTemplate("custom", 'nothing to change'),
                            ], Response::HTTP_OK);
                        }
                    } else {
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("not_found", 'allergic'),
                        ], Response::HTTP_NOT_FOUND);
                    }
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
     *     path="/api/v1/analytic/allergic",
     *     summary="Create allergic",
     *     tags={"Analytic"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Allergic update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Allergic updated | Nothing to Change"),
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
     *             @OA\Property(property="message", type="string", example="Allergic context already exist")
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
    public function createAllergic(Request $request){
        try{
            $validator = Validation::getValidateAllergic($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {    
                $user_id = $request->user()->id;

                $check = Allergic::selectRaw('1')
                    ->whereRaw('LOWER(allergic_context) = ?', [strtolower($request->allergic_context)])
                    ->where('created_by', $user_id)
                    ->first();

                if($check){
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("conflict", 'allergic context'),
                    ], Response::HTTP_CONFLICT);
                } else {
                    $id = Generator::getUUID();
                    $res = Allergic::create([
                        'id' => $id,
                        'allergic_context' => $request->allergic_context, 
                        'allergic_desc'  => $request->allergic_desc, 
                        'created_by' => $user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => null
                    ]);

                    if($res){
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("create", 'allergic'),
                            'data' => $res
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
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
     *     path="/api/v1/analytic/allergic/{id}",
     *     summary="Delete allergic by id",
     *     tags={"Analytic"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Allergic ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Allergic delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Allergic deleted"),
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
     *         description="Allergic not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Allergic not found")
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
    public function deleteAllergicById(Request $request,$id){
        try{
            $user_id = $request->user()->id;
            $res = Allergic::where('id',$id)
                ->where('created_by',$user_id)
                ->delete();

            if($res){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("delete", 'allergic'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'allergic'),
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

<?php

namespace App\Http\Controllers\UserApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Http\Controllers\Controller;
use Telegram\Bot\Laravel\Facades\Telegram;

use App\Models\BodyInfo;

class CommandsBodyInfo extends Controller
{
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
                        'message' => 'Body info created',
                        'data' => $user
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => "Something error please contact admin"
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
    public function deleteBodyInfo(Request $request, $id){
        try {
            $user_id = $request->user()->id;

            $res = BodyInfo::where('created_by',$user_id)
                ->where('id',$id)
                ->delete();

            if($res){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Body info deleted',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Body info not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers\CountApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use App\Models\CountCalorie;

use App\Helpers\Validation;
use App\Helpers\Generator;

class CommandsCalorie extends Controller
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

                return response()->json([
                    'status' => 'success',
                    'message' => 'Count calorie created',
                    'data' => $ccl
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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
                        'message' => "$success Calorie data deleted",
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Calorie data not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'id not valid'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

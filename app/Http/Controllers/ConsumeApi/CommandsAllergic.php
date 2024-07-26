<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Allergic;

class CommandsAllergic extends Controller
{
    public function updateAllergic(Request $request, $id){
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
                        'message' => 'Allergic context already exist',
                    ], Response::HTTP_CONFLICT);
                } else {
                    $res = Allergic::where('id',$id)
                        ->where('created_by',$user_id)
                        ->update([
                            'allergic_context' => $request->allergic_context, 
                            'allergic_desc'  => $request->allergic_desc, 
                        ]);

                    if($res){
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Allergic updated',
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Allergic not found',
                        ], Response::HTTP_NOT_FOUND);
                    }
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

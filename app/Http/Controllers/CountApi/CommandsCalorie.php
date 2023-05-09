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
                    'created_at' => date("Y-m-d h:i:s"),
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
}

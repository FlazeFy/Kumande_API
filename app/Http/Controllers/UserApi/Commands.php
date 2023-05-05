<?php

namespace App\Http\Controllers\UserApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Http\Controllers\Controller;

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

    public function createUser(Request $request){
        try{
            $validator = Validation::getValidateCreateUser($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $check = Generator::checkUser($request->username, $request->email);

                if(!$check){
                    $id = Generator::getUUID();
                    $slug = Generator::getSlug($request->username, "user");

                    $user = User::create([
                        'id' => $id,
                        'firebase_id' => $request->firebase_id,
                        'slug_name' => $slug,
                        'fullname' => $request->fullname,
                        'username'  => $request->username,
                        'email' => $request->email,
                        'password' => $request->password,
                        'gender' => $request->gender,
                        'image_url' => $request->image_url,
                        'born_at' => $request->born_at,
                        'created_at' => date("Y-m-d h:i:s"),
                        'updated_at' => null,
                        'deleted_at' => null
                    ]);
            
                    return response()->json([
                        'status' => 'success',
                        'message' => 'User created',
                        'data' => $user
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => "This email or username is already been used"
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
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

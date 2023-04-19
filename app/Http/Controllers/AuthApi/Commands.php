<?php

namespace App\Http\Controllers\AuthApi;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

use App\Helpers\Validation;

class Commands extends Controller
{
    //
    public function login(Request $request)
    {
        $validator = Validation::getValidateLogin($request);

        if ($validator->fails()) {
            $errors = $validator->messages();

            return response()->json([
                'status' => 422,
                'result' => $errors,
                'token' => null
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $user = User::where('username', $request->username)->first();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => "Username doesn't exist",
                    'result' => null,
                    'token' => null,                
                ], Response::HTTP_UNAUTHORIZED);
            } else if ($user && ($request->password != $user->password)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Wrong password',
                    'result' => null,
                    'token' => null,                
                ], Response::HTTP_UNAUTHORIZED);
            } else {
                $token = $user->createToken('login')->plainTextToken;

                return response()->json([
                    'status' => 200,
                    'message' => 'Login success',
                    'result' => $user,
                    'token' => $token,                
                ], Response::HTTP_OK);
            }
        }
        
    }
}

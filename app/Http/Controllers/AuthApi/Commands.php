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
                'status' => 'failed',
                'result' => $errors,
                'token' => null
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Email doesn't exist",
                    'result' => null,
                    'token' => null,                
                ], Response::HTTP_UNAUTHORIZED);
            } else if ($user && ($request->password != $user->password)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Wrong password',
                    'result' => null,
                    'token' => null,                
                ], Response::HTTP_UNAUTHORIZED);
            } else {
                $token = $user->createToken('login')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login success',
                    'result' => $user,
                    'token' => $token,                
                ], Response::HTTP_OK);
            }
        }
        
    }
}

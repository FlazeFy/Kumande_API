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
   /**
     * @OA\POST(
     *     path="/api/v1/login",
     *     summary="Sign in into the apps",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Login success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login success"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="id", type="string", example="157c5f24-8f7f-11ee-b9d1-0242ac120002"),
     *                 @OA\Property(property="firebase_id", type="string", example="jR3MvJo11zRigqNpNKaxFIfNuJg1"),
     *                 @OA\Property(property="telegram_user_id", type="string", example="1317625124"),
     *                 @OA\Property(property="firebase_fcm_token", type="string", nullable=true, example=null),
     *                 @OA\Property(property="line_user_id", type="string", example="U3356dbezw7f21e278e2ba81c71ec2ms8"),
     *                 @OA\Property(property="slug_name", type="string", example="leonardho"),
     *                 @OA\Property(property="fullname", type="string", example="Leonardho R Sitanggangg"),
     *                 @OA\Property(property="username", type="string", example="leonardho"),
     *                 @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                 @OA\Property(property="password", type="string", example="nopass123"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="image_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="born_at", type="string", format="date", example="2001-08-08"),
     *                 @OA\Property(property="timezone", type="string", example="+07:00"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-04-19T09:56:39.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-11T23:38:47.000000Z"),
     *                 @OA\Property(property="deleted_at", type="string", nullable=true, example=null)
     *             ),
     *             @OA\Property(property="token", type="string", example="1111|f30IYHxqQt9HkrLnOp6qGgVSDyflbNgQukbmzGLx0e22d7ef")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Auth failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout success | Email doesn't exist | Wrong password"),
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
    public function login(Request $request)
    {
        try{
            $validator = Validation::getValidateLogin($request);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response()->json([
                    'status' => 'failed',
                    'message' => $errors,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => "Email doesn't exist",        
                    ], Response::HTTP_UNAUTHORIZED);
                } else if ($user && ($request->password != $user->password)) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Wrong password',            
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
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

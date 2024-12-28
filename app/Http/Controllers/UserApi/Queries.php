<?php

namespace App\Http\Controllers\UserApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\User;

// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/user",
     *     summary="Get my profile info",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="user fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="account fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                 @OA\Property(property="username", type="string", example="flazefy"),
     *                 @OA\Property(property="fullname", type="string", example="testingleonardho"),
     *                 @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-20 22:53:47")
     *             ),
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
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User not found")
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
    public function getMyProfile(Request $request){
        try{
            $user_id = $request->user()->id;

            $usr = User::select('id','username','fullname','email','gender','born_at','created_at','updated_at')
                ->where('id', $user_id)
                ->first();

            if($usr){
                return response()->json([
                    "message"=> Generator::getMessageTemplate("fetch", 'account'), 
                    "status"=> 'success',
                    "data"=> $usr
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message"=> Generator::getMessageTemplate("not_found", 'account'), 
                    "status"=> 'failed',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

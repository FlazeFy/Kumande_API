<?php

namespace App\Http\Controllers\UserApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/user",
     *     summary="Get my profile info",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="User found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function getMyProfile(Request $request){
        try{
            $user_id = $request->user()->id;

            $usr = User::select('id','fullname','password','email','gender','born_at','created_at','updated_at')
                ->where('id', $user_id)
                ->limit(1)
                ->get();

            if($usr){
                return response()->json([
                    "message"=> "User found", 
                    "status"=> 'success',
                    "data"=> $usr
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message"=> "User not found", 
                    "status"=> 'success',
                    "data"=> null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
